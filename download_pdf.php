<?php
$file = $_GET['file'] ?? '';

if (empty($file)) {
    die('File not specified');
}

$filepath = 'pdfs/' . $file;

if (!file_exists($filepath)) {
    die('File not found');
}

// Set headers for download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
header('Content-Length: ' . filesize($filepath));

// Output the file
readfile($filepath);
exit;
?>