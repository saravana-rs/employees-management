<?php
require_once 'config.php';
require_once 'email-templates/sendWelcomeEmail.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    // Input
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $existingLogo = isset($_POST['existingLogo']) ? $_POST['existingLogo'] : '';
    $image_removed = isset($_POST['image_removed']) ? $_POST['image_removed'] : '0';

    if (empty($name)) {
        echo json_encode(['success'=>false,'message'=>'Employee name is required','field'=>'name']);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success'=>false,'message'=>'Invalid email','field'=>'email']);
        exit;
    }

    // Check for duplicate email
    $stmt = $conn->prepare("SELECT id FROM employees WHERE email=? AND id!=?");
    $stmt->bind_param("si", $email, $id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success'=>false,'message'=>'Email already exists for another employee','field'=>'email']);
        exit;
    }

    $isUpdate = $id > 0;

    // For new employee: insert first to get ID
    if (!$isUpdate) {
        $stmt = $conn->prepare("INSERT INTO employees (name,email,email_sent) VALUES (?,?,0)");
        $stmt->bind_param("ss", $name, $email);
        if (!$stmt->execute()) throw new Exception('Failed to add employee');
        $id = $conn->insert_id; // Now we have employee ID
    }

    // Employee folder
    $employeeDir = "uploads/employee_$id/";
    if (!is_dir($employeeDir)) mkdir($employeeDir, 0777, true);

    // Delete old file if requested
    if ($image_removed !== "0" && !empty($image_removed) && file_exists($employeeDir . $image_removed)) {
        unlink($employeeDir . $image_removed);
    }

    // Handle logo upload
    $logoFileName = $existingLogo; // default
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
        $allowedTypes = ['image/jpeg','image/png','image/gif','image/jpg'];
        $maxSize = 5 * 1024 * 1024;

        if (!in_array($_FILES['logo']['type'], $allowedTypes)) throw new Exception('Invalid file type');
        if ($_FILES['logo']['size'] > $maxSize) throw new Exception('File too large');

        $logoFileName = basename($_FILES['logo']['name']);
        $uploadPath = $employeeDir . $logoFileName;

        // Ensure unique filename in this folder
        $originalName = pathinfo($logoFileName, PATHINFO_FILENAME);
        $extension = pathinfo($logoFileName, PATHINFO_EXTENSION);
        $counter = 1;
        while (file_exists($uploadPath)) {
            $logoFileName = $originalName . "_$counter." . $extension;
            $uploadPath = $employeeDir . $logoFileName;
            $counter++;
        }

        if (!move_uploaded_file($_FILES['logo']['tmp_name'], $uploadPath)) {
            throw new Exception('Failed to upload file');
        }
    }

    // Update employee data (name, email, logo)
    $stmt = $conn->prepare("UPDATE employees SET name=?, email=?, logo=? WHERE id=?");
    $stmt->bind_param("sssi", $name, $email, $logoFileName, $id);
    if (!$stmt->execute()) throw new Exception('Failed to save employee data');

    // Send welcome email for new employee
    if (!$isUpdate) {
        $emailSent = sendWelcomeEmail($email, $name, $logoFileName);
        if ($emailSent) {
            $stmt2 = $conn->prepare("UPDATE employees SET email_sent=1 WHERE id=?");
            $stmt2->bind_param("i", $id);
            $stmt2->execute();
        } else {
            error_log("Failed to send welcome email to: " . $email);
        }
    }

    $response['success'] = true;
    $response['message'] = $isUpdate ? 'Employee updated successfully' : 'Employee added successfully';
    if (!$isUpdate && !empty($emailSent)) $response['message'] .= ' and welcome email sent';

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    // Remove uploaded file if error
    if (isset($uploadPath) && file_exists($uploadPath)) unlink($uploadPath);
}

echo json_encode($response);
$conn->close();
?>
