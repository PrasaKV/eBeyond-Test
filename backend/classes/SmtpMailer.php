<?php

class SmtpMailer {
    private string $host;
    private int $port;
    private string $username;
    private string $password;
    private string $encryption;
    private string $fromEmail;
    private string $fromName;
    private int $timeout;
    private array $logs = [];

    public function __construct(array $config = []) {
        $this->host = $config['host'] ?? 'smtp.gmail.com';
        $this->port = (int)($config['port'] ?? 587);
        $this->username = $config['username'] ?? '';
        $this->password = $config['password'] ?? '';
        $this->encryption = strtolower($config['encryption'] ?? ($this->port === 465 ? 'ssl' : 'tls'));
        $this->fromEmail = $config['from_email'] ?? $this->username;
        $this->fromName = $config['from_name'] ?? 'Movie Library';
        $this->timeout = (int)($config['timeout'] ?? 15);
    }

    
    public function send($to, string $subject, string $htmlBody, string $plainBody = '', ?string $replyTo = null): array {
        $this->logs = [];
        $recipients = is_array($to) ? $to : array_map('trim', explode(',', $to));
        $recipients = array_values(array_filter($recipients, fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL)));

        if (empty($recipients)) {
            return [
                'success' => false,
                'message' => 'No valid recipient email addresses provided.',
                'logs' => $this->logs
            ];
        }

        
        $cleanPassword = str_replace(' ', '', $this->password);

        $socket = null;
        try {
            $prefix = ($this->encryption === 'ssl' || $this->port === 465) ? 'ssl://' : 'tcp://';
            $remote = $prefix . $this->host . ':' . $this->port;
            
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                    'allow_self_signed' => false
                ]
            ]);

            $this->log("Connecting to {$remote}...");
            $socket = @stream_socket_client($remote, $errno, $errstr, $this->timeout, STREAM_CLIENT_CONNECT, $context);
            if (!$socket) {
                throw new Exception("Connection failed: {$errstr} ({$errno})");
            }

            stream_set_timeout($socket, $this->timeout);

            
            $res = $this->readResponse($socket);
            $this->assertCode($res, [220], "Server connection greeting");

            
            $res = $this->sendCommand($socket, "EHLO " . gethostname());
            $this->assertCode($res, [250], "EHLO command");

            
            if ($this->encryption === 'tls' || $this->port === 587) {
                $res = $this->sendCommand($socket, "STARTTLS");
                $this->assertCode($res, [220], "STARTTLS command");

                $cryptoMethod = STREAM_CRYPTO_METHOD_TLS_CLIENT;
                if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
                    $cryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
                }
                if (defined('STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT')) {
                    $cryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
                }

                $cryptoOk = stream_socket_enable_crypto($socket, true, $cryptoMethod);
                if (!$cryptoOk) {
                    throw new Exception("TLS negotiation failed.");
                }
                $this->log("TLS encryption established successfully.");

                
                $res = $this->sendCommand($socket, "EHLO " . gethostname());
                $this->assertCode($res, [250], "EHLO after TLS");
            }

            
            if (!empty($this->username)) {
                $authSuccess = false;
                $authError = '';

                
                $res = $this->sendCommand($socket, "AUTH LOGIN");
                if ($this->isCode($res, 334)) {
                    $this->sendCommand($socket, base64_encode($this->username));
                    $res = $this->sendCommand($socket, base64_encode($cleanPassword));
                    if ($this->isCode($res, 235)) {
                        $authSuccess = true;
                    } else {
                        $authError = trim($res);
                    }
                }

                if (!$authSuccess) {
                    throw new Exception("Authentication failed for {$this->username}: " . $authError);
                }
                $this->log("Authenticated successfully as {$this->username}");
            }

            
            $fromAddress = filter_var($this->fromEmail, FILTER_VALIDATE_EMAIL) ? $this->fromEmail : $this->username;
            $res = $this->sendCommand($socket, "MAIL FROM:<{$fromAddress}>");
            $this->assertCode($res, [250], "MAIL FROM");

            
            foreach ($recipients as $recipient) {
                $res = $this->sendCommand($socket, "RCPT TO:<{$recipient}>");
                $this->assertCode($res, [250, 251], "RCPT TO <{$recipient}>");
            }

            
            $res = $this->sendCommand($socket, "DATA");
            $this->assertCode($res, [354], "DATA command");

            
            $mimeMessage = $this->buildMimeMessage($recipients, $subject, $htmlBody, $plainBody, $replyTo);

            
            $res = $this->sendCommand($socket, $mimeMessage . "\r\n.");
            $this->assertCode($res, [250], "Payload delivery");

            
            $this->sendCommand($socket, "QUIT");
            @fclose($socket);

            $this->log("Email delivered successfully to " . implode(', ', $recipients));
            return [
                'success' => true,
                'message' => 'Email sent successfully via Gmail SMTP.',
                'logs' => $this->logs
            ];
        } catch (Exception $e) {
            $this->log("SMTP ERROR: " . $e->getMessage());
            if ($socket && is_resource($socket)) {
                @fwrite($socket, "QUIT\r\n");
                @fclose($socket);
            }
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'logs' => $this->logs
            ];
        }
    }

    private function buildMimeMessage(array $recipients, string $subject, string $htmlBody, string $plainBody, ?string $replyTo): string {
        $boundary = "==Multipart_Boundary_x" . md5(uniqid(microtime(), true)) . "x";
        $fromEncoded = "=?UTF-8?B?" . base64_encode($this->fromName) . "?= <" . $this->fromEmail . ">";
        $subjectEncoded = "=?UTF-8?B?" . base64_encode($subject) . "?=";
        $toHeader = implode(', ', $recipients);

        $headers = [];
        $headers[] = "Date: " . date(DATE_RFC2822);
        $headers[] = "From: {$fromEncoded}";
        $headers[] = "To: {$toHeader}";
        if ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $headers[] = "Reply-To: <{$replyTo}>";
        }
        $headers[] = "Subject: {$subjectEncoded}";
        $headers[] = "Message-ID: <" . time() . "." . uniqid() . "@" . parse_url($this->host, PHP_URL_HOST) . ">";
        $headers[] = "X-Mailer: PHP/" . phpversion() . " (eFlix SMTP Mailer)";
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: multipart/alternative; boundary=\"{$boundary}\"";

        
        if (empty($plainBody)) {
            $plainBody = strip_tags(preg_replace('/<br\s*\/?>/i', "\n", $htmlBody));
        }

        $bodyParts = [];
        
        
        $bodyParts[] = "--{$boundary}";
        $bodyParts[] = "Content-Type: text/plain; charset=UTF-8";
        $bodyParts[] = "Content-Transfer-Encoding: base64\r\n";
        $bodyParts[] = chunk_split(base64_encode($plainBody));

        
        $bodyParts[] = "--{$boundary}";
        $bodyParts[] = "Content-Type: text/html; charset=UTF-8";
        $bodyParts[] = "Content-Transfer-Encoding: base64\r\n";
        $bodyParts[] = chunk_split(base64_encode($htmlBody));

        $bodyParts[] = "--{$boundary}--";

        $fullData = implode("\r\n", $headers) . "\r\n\r\n" . implode("\r\n", $bodyParts);

        
        $fullData = str_replace(["\r\n", "\r", "\n"], ["\n", "\n", "\r\n"], $fullData);

        
        $fullData = preg_replace('/^\./m', '..', $fullData);

        return $fullData;
    }

    private function sendCommand($socket, string $cmd): string {
        $logCmd = preg_replace('/(AUTH\s+PLAIN\s+)[^\r\n]+/i', '$1[TOKEN HIDDEN]', $cmd);
        if (strlen($cmd) === 24 && !str_contains($cmd, ' ')) {
            
            $this->log("Client: [CREDENTIAL HIDDEN]");
        } else {
            $this->log("Client: {$logCmd}");
        }

        fwrite($socket, $cmd . "\r\n");
        return $this->readResponse($socket);
    }

    private function readResponse($socket): string {
        $data = '';
        while ($line = fgets($socket, 512)) {
            $data .= $line;
            
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        $this->log("Server: " . trim($data));
        return $data;
    }

    private function isCode(string $res, int $expectedCode): bool {
        return (int)substr($res, 0, 3) === $expectedCode;
    }

    private function assertCode(string $res, array $expectedCodes, string $step): void {
        $code = (int)substr($res, 0, 3);
        if (!in_array($code, $expectedCodes, true)) {
            throw new Exception("{$step} failed with server code {$code}: " . trim($res));
        }
    }

    private function log(string $msg): void {
        $this->logs[] = "[" . date('H:i:s') . "] " . $msg;
    }

    public function getLogs(): array {
        return $this->logs;
    }
}
