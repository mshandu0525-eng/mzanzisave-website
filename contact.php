<?php
/**
 * Contact form handler for PHP hosting (HostAfrica / DirectAdmin).
 * The page posts here when it is not running on Netlify.
 * Sends each inquiry by email and answers with JSON.
 */

// Where inquiries are delivered.
const RECIPIENT = 'mshandu0525@gmail.com';
// Sender address on your own domain, so mail servers accept it.
const SENDER    = 'website@mzanzisave.co.za';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function reply(int $status, array $body): void {
    http_response_code($status);
    echo json_encode($body);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    reply(405, ['ok' => false, 'error' => 'Method not allowed']);
}

// Spam trap: real visitors never fill this hidden field.
if (!empty($_POST['bot-field'])) {
    reply(200, ['ok' => true]);
}

// Strip line breaks from single-line fields (blocks email header injection).
function field(string $key, int $max = 200): string {
    $v = trim((string)($_POST[$key] ?? ''));
    $v = str_replace(["\r", "\n", "%0a", "%0d"], ' ', $v);
    return mb_substr($v, 0, $max);
}

$name    = field('name');
$company = field('company');
$email   = field('email');
$service = field('service', 60);
$message = mb_substr(trim((string)($_POST['message'] ?? '')), 0, 5000);

$services = ['App Development', 'Modern Websites', 'Business Automation', 'Process Innovation', 'Not sure yet'];

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !in_array($service, $services, true)) {
    reply(422, ['ok' => false, 'error' => 'Please fill in your name, a valid email and a service.']);
}

// Simple rate limit: one inquiry per visitor every 30 seconds.
session_start();
if (isset($_SESSION['last_inquiry']) && time() - $_SESSION['last_inquiry'] < 30) {
    reply(429, ['ok' => false, 'error' => 'Please wait a moment before sending again.']);
}
$_SESSION['last_inquiry'] = time();

$subject = 'Website inquiry: ' . $service . ($company !== '' ? ' – ' . $company : '');
$body = "New inquiry from mzanzisave.co.za\n\n"
      . "Name:    $name\n"
      . "Company: " . ($company !== '' ? $company : '-') . "\n"
      . "Email:   $email\n"
      . "Service: $service\n\n"
      . "Message:\n" . ($message !== '' ? $message : '-') . "\n";

$headers = implode("\r\n", [
    'From: MzanziSave Website <' . SENDER . '>',
    'Reply-To: ' . $name . ' <' . $email . '>',
    'Content-Type: text/plain; charset=UTF-8',
    'X-Mailer: PHP/' . phpversion(),
]);

$sent = mail(RECIPIENT, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headers, '-f' . SENDER);

$sent
    ? reply(200, ['ok' => true])
    : reply(500, ['ok' => false, 'error' => 'Mail could not be sent.']);
