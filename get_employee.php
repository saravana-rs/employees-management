<?php
require_once 'config.php';

header('Content-Type: application/json');

$response = array('success' => false, 'data' => null);

try {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($id <= 0) {
        throw new Exception('Invalid employee ID');
    }
    
    $sql = "SELECT id, name, email, logo FROM employees WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $response['data'] = $result->fetch_assoc();
        $response['success'] = true;
    } else {
        throw new Exception('Employee not found');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>