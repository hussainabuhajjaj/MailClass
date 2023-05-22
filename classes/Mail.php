<?php
namespace Mail;

require_once __DIR__ . '/../vendor/autoload.php';

use Mailgun\Mailgun;
use PDO;

class Mailer {
    private $from;
    private $to = [];
    private $replyTo;
    private $cc = [];
    private $bcc = [];
    private $attachments = [];
    private $html;
    private $text;
    private $headers = [];
    private $validation = false;
    private $mailgun;
    private $adminEmail;
    private $dbConnection;
    private $logFilePath;

    public function __construct(string $apiKey, string $domain) {
        $this->mailgun = Mailgun::create($apiKey);
        $this->mailgunDomain = $domain;
        $this->adminEmail = 'admin@example.com';
        $this->dbConnection = new PDO('mysql:host=localhost;dbname=mydatabase', 'root', '');
        $this->logFilePath = '../file.log';
    }

    public function setFrom($email, $name) {
        $this->from = "$name <$email>";
    }

    public function addTo($email, $name) {
        $this->to[$email] = $name;
    }

    public function addReplyTo($email, $name) {
        $this->replyTo = "$name <$email>";
    }

    public function addCc($email, $name) {
        $this->cc[$email] = $name;
    }

    public function addBcc($email, $name) {
        $this->bcc[$email] = $name;
    }

    public function addAttachment(array $filePaths) {
        $this->attachments = array_merge($this->attachments, $filePaths);
    }

    public function setHTML($html) {
        $this->html = $html;
    }

    public function setText($text) {
        $this->text = $text;
    }

    public function addHeader($name, $value) {
        $this->headers[$name] = $value;
    }

    public function enableValidation() {
        $this->validation = true;
    }

    public function send() {
        if ($this->validation && !$this->validate()) {
            return false;
        }

        $message = [
            'from' => $this->from,
            'to' => $this->to,
            'subject' => 'Your subject here',
            'html' => $this->html,
            'text' => $this->text,
            'h:Reply-To' => $this->replyTo,
            'cc' => $this->cc,
            'bcc' => $this->bcc,
        ];

        // Add custom headers
        foreach ($this->headers as $name => $value) {
            $message['h:' . $name] = $value;
        }

        // Retry mechanism
        $retryCount = 0;
        $maxRetries = 3;

        while ($retryCount < $maxRetries) {
            try {
                $response = $this->mailgun->messages()->send($this->mailgunDomain, $message);

                // Log the email
                $this->logEmail($this->from, $this->to, $message['subject'], $this->text);

                return $response->http_response_code == 200;
            } catch (\Exception $e) {
                // Log the error
                $this->logError($e->getMessage());

                // Increment retry count
                $retryCount++;
                usleep(1000000); // Delay for 1 second before retrying
            }
        }

        return false;
    }

    public function setAdminEmail($email) {
        $this->adminEmail = $email;
    }

    private function validate() {
        if (empty($this->from) || empty($this->to) || (empty($this->text) && empty($this->html))) {
            return false;
        }

        return true;
    }

    private function logEmail($from, $to, $subject, $body) {
        // Log email to database
        $this->logToDatabase($from, $to, $subject, $body);

        // Log email to file
        $this->logToFile($from, $to, $subject, $body);

        // Send email to admin
        $this->sendToAdmin($from, $to, $subject, $body);
    }

    private function logToDatabase($from, $to, $subject, $body) {
        $query = $this->dbConnection->prepare("INSERT INTO emails (`from`, `to`, `subject`, `body`) VALUES (?, ?, ?, ?)");
        $query->execute([$from, implode(',', $to), $subject, $body]);
    }

    private function logToFile($from, $to, $subject, $body) {
        $logData = [
            'From: ' . $from,
            'To: ' . implode(',', $to),
            'Subject: ' . $subject,
            'Body: ' . $body,
            '---'
        ];
    
        $logFilePath = __DIR__ . '../file.log';
    
        if (!file_exists($logFilePath)) {
            touch($logFilePath); // Create the file if it doesn't exist
            chmod($logFilePath, 0644); // Set appropriate permissions
        }
    
        file_put_contents($logFilePath, implode("\n", $logData), FILE_APPEND);
    }

    private function sendToAdmin($from, $to, $subject, $body) {
        $adminMessage = [
            'from' => $this->from,
            'to' => $this->adminEmail,
            'subject' => 'Email Log: ' . $subject,
            'text' => "Email sent to: " . implode(',', $to) . "\n\n" . $body,
        ];

        $this->mailgun->messages()->send($this->mailgunDomain, $adminMessage);
    }

    private function logError($message) {
        // Log the error to a suitable logging mechanism (e.g., database, file, etc.)
        $this->logErrorToDatabase($message);
        $this->logErrorToFile($message);
    }

    private function logErrorToDatabase($message) {
        $query = $this->dbConnection->prepare("INSERT INTO errors (`message`) VALUES (?)");
        $query->execute([$message]);
    }

    private function logErrorToFile($message) {
        $logData = [
            'Error: ' . $message,
            '---'
        ];

        file_put_contents($this->logFilePath, implode("\n", $logData), FILE_APPEND);
    }
}
