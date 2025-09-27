<?php
require_once 'config.php';

require_once 'vendor/autoload.php';


use Dompdf\Dompdf;
use Dompdf\Options;

// Get employee ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die('Invalid employee ID');
}

// Fetch employee data
$sql = "SELECT * FROM employees WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die('Employee not found');
}

$employee = $result->fetch_assoc();

// Prepare logo for PDF
$logoHtml = '';
if (!empty($employee['logo'])) {
    $logoPath = __DIR__ . '/uploads/' . $employee['logo'];
    if (file_exists($logoPath)) {
        $imageData = base64_encode(file_get_contents($logoPath));
        $imageType = pathinfo($logoPath, PATHINFO_EXTENSION);
        $logoHtml = '<img src="data:image/' . $imageType . ';base64,' . $imageData . '" style="max-width: 200px; max-height: 200px; border-radius: 10px;">';
    }
}

// Create HTML content for PDF
// Create HTML content for PDF
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        .container {
            width: 100%;
            max-width: 700px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .employee-info {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .info-row {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        .info-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .label {
            font-weight: bold;
            color: #333;
            display: inline-block;
            width: 150px;
        }
        .value {
            color: #666;
        }
        .logo-container {
            text-align: center;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            color: #999;
            font-size: 12px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Employee Information Card</h1>
            <p style="margin: 5px 0; opacity: 0.9;">Employee Management System</p>
        </div>
        
        <div class="content">
            ' . (!empty($logoHtml) ? '
            <div class="logo-container">
                <h3 style="color: #666; margin-bottom: 15px;">Employee Logo</h3>
                ' . $logoHtml . '
            </div>' : '') . '

            <div class="employee-info">
                <div class="info-row">
                    <span class="label">Name:</span>
                    <span class="value">' . htmlspecialchars($employee['name']) . '</span>
                </div>
                
                <div class="info-row">
                    <span class="label">Registration Date:</span>
                    <span class="value">' . date('F j, Y', strtotime($employee['created_at'])) . '</span>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p>Generated on: ' . date('F j, Y, g:i a') . '</p>
            <p>This is a system-generated document.</p>
        </div>
    </div>
</body>
</html>';

// Initialize Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Output the PDF
$filename = 'employee_' . $employee['id'] . '_' . date('Ymd_His') . '.pdf';
$dompdf->stream($filename, array('Attachment' => true));

$conn->close();
?>