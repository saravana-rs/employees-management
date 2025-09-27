<?php
require_once 'mailer.php';

function sendWelcomeEmail($toEmail, $employeeName, $photo = null) { 
    $subject = "Welcome to Employee Management System";

    $logoHtml = '';
    if (!empty($photo)) {
    
        $protocol = 'http'; 
        $host = $_SERVER['HTTP_HOST']; 
        $basePath = '/employees-management'; 
        
        $logoPath = $protocol . '://' . $host . $basePath . '/uploads/' . $photo;
        
        $logoHtml = '
        <div style="text-align: center; margin: 20px 0;">
            <img src="' . $logoPath . '" 
                 style="max-width:150px; border-radius:8px; border: 1px solid #ddd; display: block; margin: 0 auto;">
        </div>';
    }

    $message = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background-color: #f4f4f4;">
        <div style="max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h2 style="color: #2c3e50; margin-bottom: 20px; text-align: center;">Hi ' . htmlspecialchars($employeeName) . ',</h2>
            
            <p style="font-size: 16px; text-align: center; color: #555; margin-bottom: 20px;">
                Welcome to our Employee Management System!
            </p>
            
            ' . $logoHtml . '
            
            <div style="background: #f8f9fa; padding: 15px; border-left: 4px solid #3498db; margin: 20px 0;">
                <p style="margin: 0; font-size: 14px;">
                    <strong>Registration Date:</strong> ' . date('F j, Y, g:i a') . '
                </p>
            </div>
            
            <p style="font-size: 15px; line-height: 1.6; color: #555;">
                We are excited to have you on board.
            </p>
            <p style="font-size: 12px; color: #999; text-align: center;">
                © ' . date('Y') . ' Employee Management System. This is an automated email, please do not reply.
            </p>
        </div>
    </body>
    </html>';

    try {
        return sendMail($toEmail, $subject, $message);
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
        return false;
    }
}
?>