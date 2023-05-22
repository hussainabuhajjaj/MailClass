<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once 'classes/Mail.php';

use Dotenv\Dotenv;
use Mail\Mailer;

// Load environment variables from .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Retrieve Mailgun API key and domain from environment variables
$mailgunApiKey = $_ENV['MAILGUN_API_KEY'];
$mailgunDomain = $_ENV['MAILGUN_DOMAIN'];

// Database connection code...

// Create a new instance of the Mailer class
$mailer = new Mailer($mailgunApiKey, $mailgunDomain);

// Set the email details
$mailer->setFrom('hussain.h.ff32@gmail.com', 'Sender Name');
$mailer->addTo('hussain.h.ff32@gmail.com', 'Recipient Name');
$mailer->setHTML('<p>This is the HTML content of the email.</p>');
$mailer->setText('This is the plain text content of the email.');
$mailer->addAttachment(['/path/to/attachment.pdf']);
$mailer->enableValidation();

// Send the email
if ($mailer->send()) {
    echo 'Email sent successfully.';
} else {
    echo 'Failed to send email.';
}

?>
