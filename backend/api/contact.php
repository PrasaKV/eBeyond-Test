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

$storageDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data';
if (!is_dir($storageDir)) {
    mkdir($storageDir, 0777, true);
}

$storageFile = $storageDir . DIRECTORY_SEPARATOR . 'submissions.json';
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
file_put_contents($storageFile, json_encode($submissions, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

$adminEmails = 'dumidu.kodithuwakku@ebeyonds.com, prabhath.senadheera@ebeyonds.com';
$adminSubject = 'New Contact Form Submission - Movie Library';
$adminBody = "A new contact form submission has been received:\n\n" .
    "Name: " . $firstName . " " . $lastName . "\n" .
    "Email: " . $email . "\n" .
    "Telephone: " . ($telephone !== '' ? $telephone : 'N/A') . "\n" .
    "Date & Time: " . $newRecord['submittedAt'] . "\n\n" .
    "Message:\n" . $message . "\n";

$adminHeaders = "From: no-reply@ebeyonds.com\r\n" .
    "Reply-To: " . $email . "\r\n" .
    "X-Mailer: PHP/" . phpversion();

@mail($adminEmails, $adminSubject, $adminBody, $adminHeaders);

$userSubject = 'We have received your message - Movie Library';
$userBody = "Dear " . $firstName . ",\n\n" .
    "Thank you for reaching out to us. We have received your message and our team will get back to you shortly.\n\n" .
    "Summary of your submission:\n" .
    "Name: " . $firstName . " " . $lastName . "\n" .
    "Email: " . $email . "\n" .
    "Telephone: " . ($telephone !== '' ? $telephone : 'N/A') . "\n" .
    "Message:\n" . $message . "\n\n" .
    "Best regards,\n" .
    "Movie Library Team\n";

$userHeaders = "From: no-reply@ebeyonds.com\r\n" .
    "X-Mailer: PHP/" . phpversion();

@mail($email, $userSubject, $userBody, $userHeaders);

$emailLogFile = $storageDir . DIRECTORY_SEPARATOR . 'emails.log';
$logEntry = "[" . date('Y-m-d H:i:s') . "] Submission ID: " . $newRecord['id'] . "\n" .
    "--- USER AUTO-RESPONSE ---\nTo: " . $email . "\nSubject: " . $userSubject . "\nBody:\n" . $userBody . "\n" .
    "--- ADMIN NOTIFICATION ---\nTo: " . $adminEmails . "\nSubject: " . $adminSubject . "\nBody:\n" . $adminBody . "\n" .
    str_repeat("=", 50) . "\n\n";

file_put_contents($emailLogFile, $logEntry, FILE_APPEND);

http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'Thank you! Your message has been submitted successfully.'
]);
