<?php
require_once 'config.php';

header('Content-Type: application/json');

$response = array('success' => false, 'data' => array());

try {
    $sql = "SELECT id, name, email, logo FROM employees ORDER BY id DESC";
    $result = $conn->query($sql);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $response['data'][] = $row;
        }
        $response['success'] = true;
    } else {
        throw new Exception('Failed to fetch employees');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>