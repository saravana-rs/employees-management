<?php
require_once 'config.php';

header('Content-Type: application/json');

$response = array('success' => false, 'message' => '');

try {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    
    if ($id <= 0) {
        throw new Exception('Invalid employee ID');
    }
    
    // First, get the employee's logo to delete the file
    $sql = "SELECT logo FROM employees WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $employee = $result->fetch_assoc();
        
        // Delete the logo file if exists
        if (!empty($employee['logo']) && file_exists($upload_dir . $employee['logo'])) {
            unlink($upload_dir . $employee['logo']);
        }
        
        // Delete the employee record
        $deleteSql = "DELETE FROM employees WHERE id = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bind_param("i", $id);
        
        if ($deleteStmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Employee deleted successfully';
        } else {
            throw new Exception('Failed to delete employee');
        }
    } else {
        throw new Exception('Employee not found');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$conn->close();
?>