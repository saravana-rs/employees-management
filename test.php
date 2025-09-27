<?php
require  'mailer.php';

$to = 'saravanakumar.techie@gmail.com';
$subject = 'Test Email';
$body = '<h1>Hello!</h1><p>This is a test email.</p>';

if(sendMail($to, $subject, $body)) {
    echo "Mail sent successfully!";
} else {
    echo "Mail sending failed!";
}