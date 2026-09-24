<?php
header('Content-Type: application/json; charset=UTF-8');
echo json_encode([
    'status' => 'online',
    'endpoints' => [
        'POST /api/contact.php' => 'Submit contact form'
    ]
]);
