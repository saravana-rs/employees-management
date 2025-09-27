<?php


$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db   = 'employee_management'; // change to your DB

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(['error' => 'DB connect failed: ' . $mysqli->connect_error]);
    exit;
}
$mysqli->set_charset('utf8mb4');

// Helper to escape
function esc($v){ global $mysqli; return $mysqli->real_escape_string($v); }



?>