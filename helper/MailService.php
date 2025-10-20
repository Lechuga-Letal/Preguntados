<?php

// Incluir PHPMailer (las 3 clases)
require_once __DIR__ . '/../phpmailer/src/Exception.php';
require_once __DIR__ . '/../phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    private $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);

        // Configuración SMTP (puedes moverla a config luego)
        $this->mailer->isSMTP();
        $this->mailer->Host = 'smtp.gmail.com';
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = 'vetconnect.twi@gmail.com';
        $this->mailer->Password = 'pwsqjxaglmqowzlj';
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = 587;

        // Remitente
        $this->mailer->setFrom('vetconnect.twi@gmail.com', 'PW2 - Preguntados');
    }

    public function enviarBienvenida($usuario, $mail)
    {
        try {
            $this->mailer->clearAllRecipients(); // Importante si se usa el mismo objeto más de una vez
            $this->mailer->addAddress($mail, $usuario);

            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Bienvenido a la plataforma';
            $this->mailer->Body = "<h1>Hola $usuario!</h1><p>Gracias por registrarte.</p>";
            $this->mailer->AltBody = "Hola $usuario! Gracias por registrarte.";

            $this->mailer->send();
        } catch (Exception $e) {
            error_log("Error al enviar email: " . $this->mailer->ErrorInfo);
        }
    }
}
