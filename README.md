# MailClass
## How to Use the Mailgun PHP SDK Example

Follow these steps to use the Mailgun PHP SDK example:

1. **Installation:**
   - Make sure you have Composer installed.
   - Run `composer require mailgun/mailgun-php` in your project directory to install the Mailgun PHP SDK.

2. **Configuration:**
   - Create a file named `.env` in the root of your project directory.
   - Open the `.env` file and add the following lines:
     ```
     MAILGUN_API_KEY=your_mailgun_api_key
     MAILGUN_DOMAIN=your_mailgun_domain
     ```
   - Replace `your_mailgun_api_key` with your Mailgun API key and `your_mailgun_domain` with your Mailgun domain.
   - Save the `.env` file.

3. **Import Classes:**
   - In your PHP file, require the Composer autoload file at the top: `require_once __DIR__ . '/vendor/autoload.php'`.
   - Import the necessary classes: `use Dotenv\Dotenv;` and `use Mail\Mailer;`.

...

5. **Create a `Mailer` Instance:**
   ```php
   $mailer = new Mailer($mailgunApiKey, $mailgunDomain);

Set Email Details:
$mailer->setFrom('sender@example.com', 'Sender Name');
$mailer->addTo('recipient@example.com', 'Recipient Name');
$mailer->setHTML('<p>This is the HTML content of the email.</p>');
$mailer->setText('This is the plain text content of the email.');
$mailer->addAttachment(['/path/to/attachment.pdf']);

Handle the Response:
if ($mailer->send()) {
    echo 'Email sent successfully.';
} else {
    echo 'Failed to send email.';
}
That's it! You have successfully used the example to send emails using the Mailgun API. Make sure you have valid API credentials and adjust the email details according to your requirements. Feel free to customize the Mailer class or add additional functionality as needed.

