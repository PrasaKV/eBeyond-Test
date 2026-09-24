<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$config = require dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/classes/SmtpMailer.php';

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!is_array($data)) {
    $data = $_POST;
}

$firstName = isset($data['firstName']) ? trim((string)$data['firstName']) : '';
$lastName  = isset($data['lastName']) ? trim((string)$data['lastName']) : '';
$email     = isset($data['email']) ? trim((string)$data['email']) : '';
$telephone = isset($data['telephone']) ? trim((string)$data['telephone']) : '';
$message   = isset($data['message']) ? trim((string)$data['message']) : '';
$terms     = !empty($data['terms']);

$errors = [];

if ($firstName === '') {
    $errors['firstName'] = 'First name is required.';
}

if ($lastName === '') {
    $errors['lastName'] = 'Last name is required.';
}

if ($email === '') {
    $errors['email'] = 'Email is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please enter a valid email address.';
}

if ($message === '') {
    $errors['message'] = 'Message is required.';
}

if (!$terms) {
    $errors['terms'] = 'You must agree to the Terms & Conditions.';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

$storageDir = $config['paths']['data_dir'];
if (!is_dir($storageDir)) {
    @mkdir($storageDir, 0777, true);
}

$storageFile = $config['paths']['submissions_file'];
$submissions = [];

if (file_exists($storageFile)) {
    $fileContents = file_get_contents($storageFile);
    $decoded = json_decode($fileContents, true);
    if (is_array($decoded)) {
        $submissions = $decoded;
    }
}

$newRecord = [
    'id' => uniqid('sub_', true),
    'firstName' => $firstName,
    'lastName' => $lastName,
    'email' => $email,
    'telephone' => $telephone,
    'message' => $message,
    'submittedAt' => date('Y-m-d H:i:s'),
    'ipAddress' => $_SERVER['REMOTE_ADDR'] ?? ''
];

$submissions[] = $newRecord;
@file_put_contents($storageFile, json_encode($submissions, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

$mailer = new SmtpMailer($config['mail']);

$adminRecipients = $config['admin_emails'];
$adminSubject = 'New Contact Form Submission - eFlix Movie Library';

$adminHtml = "
<!DOCTYPE html>
<html>
<head>
  <meta charset='utf-8'>
  <style>
    body { font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #0b0c10; color: #c5c6c7; margin: 0; padding: 20px; }
    .card { max-width: 600px; margin: 0 auto; background: #1f2833; border: 1px solid #45a29e; border-radius: 8px; overflow: hidden; }
    .header { background: #0b0c10; padding: 20px 24px; border-bottom: 2px solid #66fcf1; }
    .header h2 { margin: 0; color: #66fcf1; font-size: 20px; }
    .content { padding: 24px; }
    .field { margin-bottom: 16px; }
    .label { font-size: 12px; text-transform: uppercase; color: #45a29e; font-weight: bold; margin-bottom: 4px; }
    .value { font-size: 15px; color: #ffffff; background: #0b0c10; padding: 10px 14px; border-radius: 4px; word-break: break-word; }
    .footer { padding: 16px 24px; font-size: 12px; color: #8892b0; border-top: 1px solid #2d3748; text-align: center; }
  </style>
</head>
<body>
  <div class='card'>
    <div class='header'>
      <h2>eFlix Contact Form &mdash; New Submission</h2>
    </div>
    <div class='content'>
      <div class='field'>
        <div class='label'>Sender Name</div>
        <div class='value'>" . htmlspecialchars($firstName . ' ' . $lastName) . "</div>
      </div>
      <div class='field'>
        <div class='label'>Email Address</div>
        <div class='value'><a href='mailto:" . htmlspecialchars($email) . "' style='color: #66fcf1; text-decoration: none;'>" . htmlspecialchars($email) . "</a></div>
      </div>
      <div class='field'>
        <div class='label'>Telephone</div>
        <div class='value'>" . htmlspecialchars($telephone !== '' ? $telephone : 'Not provided') . "</div>
      </div>
      <div class='field'>
        <div class='label'>Date &amp; Time</div>
        <div class='value'>" . htmlspecialchars($newRecord['submittedAt']) . "</div>
      </div>
      <div class='field'>
        <div class='label'>Message</div>
        <div class='value' style='white-space: pre-wrap;'>" . nl2br(htmlspecialchars($message)) . "</div>
      </div>
    </div>
    <div class='footer'>
      Submission ID: " . htmlspecialchars($newRecord['id']) . " &bull; IP: " . htmlspecialchars($newRecord['ipAddress']) . "
    </div>
  </div>
</body>
</html>";

$adminPlain = "A new contact form submission has been received:\n\n" .
    "Name: " . $firstName . " " . $lastName . "\n" .
    "Email: " . $email . "\n" .
    "Telephone: " . ($telephone !== '' ? $telephone : 'N/A') . "\n" .
    "Date & Time: " . $newRecord['submittedAt'] . "\n" .
    "Submission ID: " . $newRecord['id'] . "\n\n" .
    "Message:\n" . $message . "\n";

$adminSendResult = $mailer->send(
    $adminRecipients,
    $adminSubject,
    $adminHtml,
    $adminPlain,
    $email 
);

if (!$adminSendResult['success']) {
    $fallbackHeaders = "From: no-reply@ebeyonds.com\r\n" .
        "Reply-To: " . $email . "\r\n" .
        "X-Mailer: PHP/" . phpversion();
    @mail(implode(', ', $adminRecipients), $adminSubject, $adminPlain, $fallbackHeaders);
}

$userSubject = 'We have received your message - eFlix Movie Library';

$userHtml = "
<!DOCTYPE html>
<html>
<head>
  <meta charset='utf-8'>
  <style>
    body { font-family: 'Helvetica Neue', Arial, sans-serif; background-color: #0b0c10; color: #c5c6c7; margin: 0; padding: 20px; }
    .card { max-width: 600px; margin: 0 auto; background: #1f2833; border: 1px solid #45a29e; border-radius: 8px; overflow: hidden; }
    .header { background: #0b0c10; padding: 24px; border-bottom: 2px solid #66fcf1; text-align: center; }
    .header h2 { margin: 0; color: #66fcf1; font-size: 22px; }
    .header p { margin: 6px 0 0; color: #8892b0; font-size: 14px; }
    .content { padding: 24px; }
    .greeting { font-size: 16px; color: #ffffff; margin-bottom: 16px; }
    .summary-box { background: #0b0c10; border-left: 3px solid #66fcf1; padding: 14px 16px; margin: 20px 0; border-radius: 0 4px 4px 0; }
    .summary-title { font-size: 13px; font-weight: bold; text-transform: uppercase; color: #45a29e; margin-bottom: 10px; }
    .summary-item { font-size: 14px; margin-bottom: 6px; }
    .summary-label { color: #8892b0; }
    .summary-val { color: #ffffff; }
    .footer { padding: 20px 24px; font-size: 12px; color: #8892b0; border-top: 1px solid #2d3748; text-align: center; }
  </style>
</head>
<body>
  <div class='card'>
    <div class='header'>
      <h2>eFlix Movie Library</h2>
      <p>Thank you for getting in touch!</p>
    </div>
    <div class='content'>
      <div class='greeting'>Dear " . htmlspecialchars($firstName) . ",</div>
      <p>Thank you for reaching out to us. We have successfully received your inquiry and our team will review it and get back to you shortly.</p>
      
      <div class='summary-box'>
        <div class='summary-title'>Summary of your inquiry:</div>
        <div class='summary-item'><span class='summary-label'>Name: </span><span class='summary-val'>" . htmlspecialchars($firstName . ' ' . $lastName) . "</span></div>
        <div class='summary-item'><span class='summary-label'>Email: </span><span class='summary-val'>" . htmlspecialchars($email) . "</span></div>
        <div class='summary-item'><span class='summary-label'>Telephone: </span><span class='summary-val'>" . htmlspecialchars($telephone !== '' ? $telephone : 'N/A') . "</span></div>
        <div class='summary-item'><span class='summary-label'>Date: </span><span class='summary-val'>" . htmlspecialchars($newRecord['submittedAt']) . "</span></div>
        <div class='summary-item' style='margin-top: 10px;'><span class='summary-label'>Message:</span><br><span class='summary-val' style='white-space: pre-wrap;'>" . nl2br(htmlspecialchars($message)) . "</span></div>
      </div>

      <p style='margin-top: 20px;'>If you have any urgent requests, please feel free to reply directly to this email.</p>
      <p style='color: #ffffff; margin-bottom: 0;'>Best regards,<br><strong>eFlix Movie Library Team</strong></p>
    </div>
    <div class='footer'>
      &copy; " . date('Y') . " eFlix Entertainment &bull; Submission Reference: " . htmlspecialchars($newRecord['id']) . "
    </div>
  </div>
</body>
</html>";

$userPlain = "Dear " . $firstName . ",\n\n" .
    "Thank you for reaching out to us. We have received your message and our team will get back to you shortly.\n\n" .
    "Summary of your submission:\n" .
    "Name: " . $firstName . " " . $lastName . "\n" .
    "Email: " . $email . "\n" .
    "Telephone: " . ($telephone !== '' ? $telephone : 'N/A') . "\n" .
    "Message:\n" . $message . "\n\n" .
    "Best regards,\n" .
    "eFlix Movie Library Team\n";

$userSendResult = $mailer->send(
    $email,
    $userSubject,
    $userHtml,
    $userPlain
);

if (!$userSendResult['success']) {
    $fallbackHeaders = "From: no-reply@ebeyonds.com\r\n" .
        "X-Mailer: PHP/" . phpversion();
    @mail($email, $userSubject, $userPlain, $fallbackHeaders);
}

$emailLogFile = $config['paths']['emails_log'];
$logEntry = "[" . date('Y-m-d H:i:s') . "] Submission ID: " . $newRecord['id'] . "\n" .
    "--- USER AUTO-RESPONSE ---\n" .
    "To: " . $email . "\n" .
    "Subject: " . $userSubject . "\n" .
    "Status: " . ($userSendResult['success'] ? 'SUCCESS (SMTP)' : 'FAILED (' . $userSendResult['message'] . ')') . "\n" .
    "--- ADMIN NOTIFICATION ---\n" .
    "To: " . implode(', ', $adminRecipients) . "\n" .
    "Subject: " . $adminSubject . "\n" .
    "Status: " . ($adminSendResult['success'] ? 'SUCCESS (SMTP)' : 'FAILED (' . $adminSendResult['message'] . ')') . "\n" .
    "--- SMTP LOGS ---\n" .
    implode("\n", array_slice($adminSendResult['logs'], -10)) . "\n" .
    str_repeat("=", 60) . "\n\n";

@file_put_contents($emailLogFile, $logEntry, FILE_APPEND);

http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'Thank you! Your message has been submitted successfully.',
    'submissionId' => $newRecord['id'],
    'mailStatus' => [
        'adminEmail' => $adminSendResult['success'],
        'userEmail' => $userSendResult['success']
    ]
]);
