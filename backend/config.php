<?php

if (!function_exists('env_val')) {
    function env_val(string $key, $default = null)
    {
        $val = getenv($key);
        if ($val !== false && $val !== '') {
            return $val;
        }
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return $_ENV[$key];
        }
        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
            return $_SERVER[$key];
        }
        return $default;
    }
}

$envFiles = [
    __DIR__ . '/.env',
    dirname(__DIR__) . '/.env'
];

foreach ($envFiles as $envFile) {
    if (file_exists($envFile) && is_readable($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#'))
                continue;
            if (str_contains($line, '=')) {
                [$k, $v] = explode('=', $line, 2);
                $k = trim($k);
                $v = trim($v, " \t\n\r\0\x0B\"'");
                if (getenv($k) === false) {
                    putenv("{$k}={$v}");
                    $_ENV[$k] = $v;
                }
            }
        }
        break;
    }
}

return [
    'mail' => [
        'host' => env_val('MAIL_HOST'),
        'port' => (int) env_val('MAIL_PORT'),
        'username' => env_val('MAIL_USERNAME'),
        'password' => env_val('MAIL_PASSWORD'),
        'encryption' => env_val('SMTP_ENCRYPTION'),
        'from_email' => env_val('MAIL_USERNAME'),
        'from_name' => env_val('SMTP_FROM_NAME'),
        'timeout' => 15
    ],

    'admin_emails' => array_values(array_filter(
        array_map('trim', explode(',', (string) env_val('ADMIN_EMAILS'))),
        fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL)
    )),


    'paths' => [
        'data_dir' => __DIR__ . DIRECTORY_SEPARATOR . 'data',
        'submissions_file' => __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'submissions.json',
        'emails_log' => __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'emails.log',
    ]
];
