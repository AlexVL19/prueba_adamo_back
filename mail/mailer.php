<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

//Se cargan las variables de entorno externas mediante una librería de PHP llamada phpdotenv.
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

//Función que configura el correo electrónico, como el remitente o el destinatario
function sendEmail($email, $password) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP(); //Se establece un protocolo simple de correo electrónico mediante Gmail con autenticación
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        $mail->Username = $_ENV['GMAIL_USER']; //Usuario de Gmail que vamos a utilizar para el correo electrónico
        $mail->Password = $_ENV['GMAIL_APP_PASSWORD']; //Contraseña de aplicación

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->CharSet = 'UTF-8';

        //Establece el remitente
        $mail->setFrom(
            $_ENV['GMAIL_USER'],
            'Sistema de usuarios'
        );

        //Establece el destinatario
        $mail->addAddress($email);

        $mail->isHTML(true); //Usamos HTML en el cuerpo del correo
        $mail->Subject = 'Tu contraseña provisional'; //Asunto del correo

        $safePassword = htmlspecialchars($password, ENT_QUOTES, 'UTF-8');

        //Cuerpo del correo electrónico
        $mail->Body = "
            <h2>¡Te damos la bienvenida a AdamoServices!</h2>

            <p>Tu cuenta ha sido creada correctamente.</p>

            <p>Tu contraseña provisional es:</p>

            <h3>$safePassword</h3>

            <p>Por tu seguridad, deberás cambiar la contraseña al iniciar sesión.</p>
        ";

        $mail->AltBody =
            "Tu contraseña provisional es: " . $password;

        $mail->send();

        return true;

    } catch (Exception $e) {

        error_log(
            'Error al enviar correo: ' . $mail->ErrorInfo
        );

        return false;
    }
}