<?php
/**
 * EXAMPLE FILE - SmtpMailer.php template
 * 
 * Ini adalah file class untuk SMTP Gmail connection.
 * File ini BISA di-upload ke GitHub (tidak ada credentials).
 * 
 * Untuk production, copy ke send-email.php dan ganti credentials disana.
 */

class SmtpMailer
{
    private $host = 'smtp.gmail.com';
    private $port = 587;
    private $username;
    private $password;
    private $fromEmail;
    private $fromName;

    public function __construct($username, $password, $fromEmail, $fromName = '')
    {
        $this->username = $username;
        $this->password = $password;
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName ?: $fromEmail;
    }

    public function send($to, $subject, $body, $isHtml = false)
    {
        try {
            $socket = @fsockopen($this->host, $this->port, $errno, $errstr, 30);
            
            if (!$socket) {
                throw new Exception("Connection to SMTP failed: $errstr ($errno)");
            }

            $this->getResponse($socket);
            $this->sendCommand($socket, "EHLO localhost");
            $this->getResponse($socket);

            $this->sendCommand($socket, "STARTTLS");
            $this->getResponse($socket);

            if (!stream_context_set_option($socket, 'ssl', 'verify_peer', false)) {
                throw new Exception("Failed to set SSL context");
            }
            if (!stream_context_set_option($socket, 'ssl', 'verify_peer_name', false)) {
                throw new Exception("Failed to set SSL peer name");
            }
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new Exception("Failed to enable TLS");
            }

            $this->sendCommand($socket, "EHLO localhost");
            $this->getResponse($socket);

            $this->sendCommand($socket, "AUTH LOGIN");
            $this->getResponse($socket);

            $this->sendCommand($socket, base64_encode($this->username));
            $this->getResponse($socket);

            $this->sendCommand($socket, base64_encode($this->password));
            $response = $this->getResponse($socket);
            if (strpos($response, '235') === false) {
                throw new Exception("Gmail authentication failed");
            }

            $this->sendCommand($socket, "MAIL FROM:<{$this->fromEmail}>");
            $this->getResponse($socket);

            $this->sendCommand($socket, "RCPT TO:<{$to}>");
            $this->getResponse($socket);

            $this->sendCommand($socket, "DATA");
            $this->getResponse($socket);

            $headers = "From: {$this->fromName} <{$this->fromEmail}>\r\n";
            $headers .= "To: {$to}\r\n";
            $headers .= "Subject: " . $this->encodeSubject($subject) . "\r\n";
            if ($isHtml) {
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            } else {
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            }
            $headers .= "Content-Transfer-Encoding: 8bit\r\n";
            $headers .= "MIME-Version: 1.0\r\n";

            $message = $headers . "\r\n" . $body;

            fwrite($socket, $message . "\r\n.\r\n");
            $this->getResponse($socket);

            $this->sendCommand($socket, "QUIT");

            fclose($socket);
            return true;

        } catch (Exception $e) {
            throw $e;
        }
    }

    private function sendCommand(&$socket, $command)
    {
        fwrite($socket, $command . "\r\n");
    }

    private function getResponse(&$socket)
    {
        $response = '';
        while ($line = fgets($socket, 512)) {
            $response .= $line;
            if (substr($line, 3, 1) == ' ') {
                break;
            }
        }
        return $response;
    }

    private function encodeSubject($subject)
    {
        return '=?UTF-8?B?' . base64_encode($subject) . '?=';
    }
}
?>
