<?php
// database connect
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'employee_management';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// common folder  uploads
$upload_dir = 'uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}



// testing purpose
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