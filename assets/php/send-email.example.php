<?php
/**
 * EXAMPLE FILE - Copy ini menjadi send-email.php dan ganti credentials dengan milik kamu
 * 
 * SETUP:
 * 1. Buat file baru: assets/php/send-email.php
 * 2. Copy-paste isi file ini ke send-email.php
 * 3. Ganti GMAIL_USERNAME, GMAIL_PASSWORD dengan akun Gmail kamu
 * 4. Dapatkan App Password di https://myaccount.google.com/apppasswords
 * 5. Jangan commit send-email.php ke GitHub - sudah di-exclude di .gitignore
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Load SMTP Mailer
require_once __DIR__ . '/SmtpMailer.php';

// ========== GANTI CREDENTIALS DIBAWAH INI ==========
define('GMAIL_USERNAME', 'your-email@gmail.com');          // Ganti dengan email Gmail kamu
define('GMAIL_PASSWORD', 'your-app-password-16-char');    // Ganti dengan App Password (16 karakter)
define('GMAIL_FROM_EMAIL', 'your-email@gmail.com');       // Sama seperti GMAIL_USERNAME
define('GMAIL_FROM_NAME', 'Your Name Portfolio');         // Nama yang muncul di email
// ===================================================

/**
 * Utility function untuk respond dengan JSON
 */
function respond_json($success, $message, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'message' => $message
    ]);
    exit;
}

// Error handler untuk catch exception & error
set_exception_handler(function($e) {
    respond_json(false, 'Exception: ' . $e->getMessage(), 500);
});

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    respond_json(false, "Error: $errstr in $errfile:$errline", 500);
});

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error) {
        respond_json(false, 'Fatal: ' . $error['message'], 500);
    }
});

// Handle preflight & method check
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond_json(false, 'Method not allowed. Use POST.', 405);
}

// Validasi semua field required
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

if (!$name) respond_json(false, 'Name is required', 400);
if (!$email) respond_json(false, 'Email is required', 400);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) respond_json(false, 'Invalid email format', 400);
if (!$subject) respond_json(false, 'Subject is required', 400);
if (!$message) respond_json(false, 'Message is required', 400);

// Recipient email (GANTI DENGAN EMAIL KAMU)
$to = 'your-receiving-email@gmail.com';

// Email subject
$emailSubject = "New Portfolio Contact: " . $subject;

// Email body
$emailBody = "You have received a new message from your portfolio website.\n\n";
$emailBody .= "Name: " . htmlspecialchars($name) . "\n";
$emailBody .= "Email: " . htmlspecialchars($email) . "\n";
$emailBody .= "Subject: " . htmlspecialchars($subject) . "\n";
$emailBody .= "Message:\n" . htmlspecialchars($message) . "\n\n";
$emailBody .= "---\n";
$emailBody .= "This message was sent from your portfolio contact form.";

// Send email via Gmail SMTP
try {
    $mailer = new SmtpMailer(GMAIL_USERNAME, GMAIL_PASSWORD, GMAIL_FROM_EMAIL, GMAIL_FROM_NAME);
    $mailer->send($to, $emailSubject, $emailBody, false);
    $mailSent = true;
} catch (Exception $e) {
    $mailSent = false;
    $mailError = $e->getMessage();
}

if ($mailSent) {
    // Also send confirmation email to user
    $confirmationBody = "Hello " . htmlspecialchars($name) . ",\n\n";
    $confirmationBody .= "Thank you for contacting me through my portfolio website.\n\n";
    $confirmationBody .= "I have received your message and will get back to you as soon as possible.\n\n";
    $confirmationBody .= "Your message:\n";
    $confirmationBody .= "Subject: " . htmlspecialchars($subject) . "\n";
    $confirmationBody .= "Message: " . htmlspecialchars($message) . "\n\n";
    $confirmationBody .= "Best regards,\n";
    $confirmationBody .= "Ari Abdul Aziz";

    try {
        $mailer = new SmtpMailer(GMAIL_USERNAME, GMAIL_PASSWORD, GMAIL_FROM_EMAIL, GMAIL_FROM_NAME);
        $mailer->send($email, "We received your message", $confirmationBody, false);
    } catch (Exception $e) {
        // Log error but don't fail - main email was sent
    }

    respond_json(true, 'Message sent successfully!', 200);
} else {
    respond_json(false, 'Failed to send message. Error: ' . ($mailError ?? 'Unknown error'), 500);
}
?>
