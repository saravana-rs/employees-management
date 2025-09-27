<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'employee_management';

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");

// Create uploads directory if it doesn't exist
$upload_dir = 'uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}




function logEmailError($email, $employeeId, $name, $error = '') {
    $logDir = 'logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logMessage = sprintf(
        "[%s] FAILED - To: %s | ID: %d | Name: %s | Error: %s" . PHP_EOL,
        date('Y-m-d H:i:s'),
        $email,
        $employeeId,
        $name,
        $error
    );
    
    file_put_contents($logDir . '/email_errors.log', $logMessage, FILE_APPEND | LOCK_EX);
}
?>