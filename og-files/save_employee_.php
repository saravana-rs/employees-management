<?php
require_once 'config.php';
// require_once 'send_email.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    // Input
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $existingLogo = isset($_POST['existingLogo']) ? $_POST['existingLogo'] : '';
    $image_removed = isset($_POST['image_removed']) ? $_POST['image_removed'] : '0';


    // Validation
    if (empty($name)){

        echo json_encode([
          'success' => false,
          'message' => 'Employee name is required',
          'field' => 'name'
        ]);
        exit;
    } 
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
        echo json_encode([
          'success' => false,
          'message' => 'Invalid email',
          'field' => 'email'
        ]);
        exit;
    } 

    // Check duplicate email
    $stmt = $conn->prepare("SELECT id FROM employees WHERE email=? AND id!=?");
    $stmt->bind_param("si", $email, $id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode([
          'success' => false,
          'message' => 'Email already exists for another employee',
          'field' => 'email'
        ]);
        exit;

    }
        

    // Handle logo upload
    $logoFileName = $existingLogo; // default
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
        $allowedTypes = ['image/jpeg','image/png','image/gif','image/jpg'];
        $maxSize = 5 * 1024 * 1024;

        if (!in_array($_FILES['logo']['type'], $allowedTypes)) throw new Exception('Invalid file type');
        if ($_FILES['logo']['size'] > $maxSize) throw new Exception('File too large');

        // Delete old file if exists
        if($image_removed !="0" && !empty($image_removed) && file_exists($upload_dir . $image_removed)  ){
             unlink($upload_dir . $image_removed);
        }
   

        // Use original filename
        $logoFileName = basename($_FILES['logo']['name']);
        $uploadPath = $upload_dir . $logoFileName;

        // If filename exists, make unique
        if (file_exists($uploadPath)) {
            $logoFileName = $logoFileName = $_FILES['logo']['name']; // original file name

            $uploadPath = $upload_dir . $logoFileName;
        }

        if (!move_uploaded_file($_FILES['logo']['tmp_name'], $uploadPath)) {
            throw new Exception('Failed to upload file');
        }
    }

    // Save to DB
    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE employees SET name=?, email=?, logo=? WHERE id=?");
        $stmt->bind_param("sssi", $name, $email, $logoFileName, $id);
        $isUpdate = true;
    } else {
        $stmt = $conn->prepare("INSERT INTO employees (name,email,logo,email_sent) VALUES (?,?,?,0)");
        $stmt->bind_param("sss", $name, $email, $logoFileName);
        $isUpdate = false;
    }

    if ($stmt->execute()) {
        $employeeId = $isUpdate ? $id : $conn->insert_id;

        // Send email for new employees
        if (!$isUpdate) {
            $emailSent = sendWelcomeEmail($email, $name, $logoFileName);
            if ($emailSent) {
                $stmt2 = $conn->prepare("UPDATE employees SET email_sent=1 WHERE id=?");
                $stmt2->bind_param("i", $employeeId);
                $stmt2->execute();
            }
        }

        $response['success'] = true;
        $response['message'] = $isUpdate ? 'Employee updated successfully' : 'Employee added successfully';
        if (!$isUpdate && !empty($emailSent)) $response['message'] .= ' and welcome email sent';
    } else {
        throw new Exception('Failed to save employee data');
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    // Remove uploaded file if error
    if (isset($uploadPath) && file_exists($uploadPath)) unlink($uploadPath);
}

echo json_encode($response);
$conn->close();
?>
