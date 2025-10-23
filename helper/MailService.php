<?php

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

        $this->mailer->isSMTP();
        $this->mailer->Host = 'smtp.gmail.com';
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = 'preguntadostpfinal.pw2@gmail.com';
        $this->mailer->Password = 'mktsemvsyduyanfy';
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = 587;

        $this->mailer->setFrom('preguntadostpfinal.pw2@gmail.com', 'PW2 - Preguntados');
    }

    public function enviarBienvenida($usuario, $mail)
    {
        try {
            $this->mailer->clearAllRecipients(); 
            $this->mailer->addAddress($mail, $usuario);

            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Bienvenido al Preguntados';
            $this->mailer->AltBody = "Hola .".$usuario."! Gracias por registrarte.";
            $this->mailer->Body = "<h1>Hola $usuario!</h1><p>Gracias por registrarte.</p>
                                  <a href='/inicio/ingresoPorMail'>Link para validar la cuenta</a>";
//            TODO: linkear el ID del usuario


            $this->mailer->send();
        } catch (Exception $e) {
            error_log("Error al enviar email: " . $this->mailer->ErrorInfo);
        }
    }
}
