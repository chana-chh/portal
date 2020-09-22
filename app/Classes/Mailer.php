<?php

namespace App\Classes;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    private $mail = null;
    private $config = [];

    public function __construct($mail, $config)
    {
        $this->mail = $mail;
        $this->config = $config;
    }

    public function sendMail(array $to, $subject, $body, $html = true, $from = null, $from_name = null)
    {
        try {
            $this->mail->isSMTP();
            $this->mail->Host = $this->config['host'];
            $this->mail->SMTPAuth = true;
            $this->mail->Username = $this->config['username'];
            $this->mail->Password = $this->config['password'];
            $this->mail->Port = $this->config['port'];
            if ($this->config['port'] === 465) {
                $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($this->config['port'] === 587) {
                $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            $from = $from !== null ? : $this->config['from'];
            $from_name = $from_name !== null ? : $this->config['from_name'];

            $this->mail->setFrom($from, $from_name);

            // $this->mail->SMTPKeepAlive = true; // SMTP connection will not close after each email sent, reduces SMTP overhead

            // brisanje adresa to, cc, bcc ...
            $this->mail->clearAllRecipients();

            foreach ($to as $t) {
                $this->mail->addAddress($t['email'], $t['name']);
            }

            $this->mail->Subject = $subject;

            if ($html) {
                $this->mail->isHTML(true);
                $this->mail->Body = $body;
                $this->mail->AltBody = "Molimo vas da koristite mail klijent koji podržava HTML";
            } else {
                $this->mail->isHTML(false);
                $this->mail->Body = $body;
            }

            $this->mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
