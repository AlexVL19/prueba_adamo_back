<?php

//Se establecen las cabeceras necesarias para aceptar peticiones de otros dominios, en este caso el dominio de pruebas de Vite.
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With");

//Se incluye la configuración de la base de datos y la configuración del correo electrónico a enviar
include "../database/database.php";
require_once __DIR__ . '/../mail/mailer.php';

//Se recuperan los datos del formulario
$names = $_POST['names'] ?? null;
$last_names = $_POST['last_names'] ?? null;
$email = $_POST['email'] ?? null;

/* Función que genera una contraseña aleatoria a partir de ciertos caracteres y una longitud definida. Esta contraseña será utilizada para
adjuntarla al correo electrónico y luego encriptarla */
function generatePassword()
{
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $length = 12;
    $password = '';

    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[random_int(0, strlen($characters) - 1)];
    }

    return $password;
}

//Verifica si el método de la petición es POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    //Verificación de campos vacíos
    if (empty($names) || empty($last_names) || empty($email)) {
        echo json_encode([
            'alert_message' => 'Todos los campos son requeridos.',
            'redirect' => null
        ]);

        exit();
    }

    //Verificación del formato del correo electrónico
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'alert_message' => 'La dirección de correo electrónico no es válida.',
            'redirect' => null
        ]);

        exit();
    }

    //Verifica en la base de datos si el correo electrónico existe
    $sql_check_email = "SELECT email FROM users WHERE email = ?";
    $stmt_check_email = $connection->prepare($sql_check_email);

    if (!$stmt_check_email) {
        echo json_encode([
            'alert_message' => 'Hubo un error al verificar el correo electrónico. Inténtalo de nuevo.',
            'redirect' => null
        ]);

        exit();
    }

    $stmt_check_email->bind_param('s', $email);
    $stmt_check_email->execute();
    $stmt_check_email->store_result();

    if ($stmt_check_email->num_rows > 0) {
        echo json_encode([
            'alert_message' => 'Este correo electrónico ya está asignado a una cuenta.',
            'redirect' => null
        ]);

        exit();
    }

    $stmt_check_email->close();

    //Se genera una contraseña temporal, y una contraseña encriptada. La encriptada será enviada a la base de datos
    $temporary_password = generatePassword();
    $hashed_password = password_hash($temporary_password, PASSWORD_DEFAULT);

    //Inserción de los datos del usuario mediante consulta SQL
    $sql_insert_user = "INSERT INTO users (names, last_names, email, password) VALUES (?, ?, ?, ?)";
    $stmt_insert_user = $connection->prepare($sql_insert_user);
    $stmt_insert_user->bind_param('ssss', $names, $last_names, $email, $hashed_password);
    $stmt_insert_user->execute();
    $last_inserted = $connection->insert_id;
    $stmt_insert_user->close();

    //Se envía el correo electrónico con la contraseña temporal
    $email_sent = sendEmail($email, $temporary_password);

    //Una vez enviado el correo, se procede a iniciar sesión
    if ($email_sent) {
        echo json_encode([
            'redirect' => 'login'
        ]);
    }
    
    //Error si el módulo de correos electrónicos no envía nada
    else {
        echo json_encode([
            'alert_message' => 'Usuario registrado, pero ocurrió un error al enviar el correo electrónico.',
            'redirect' => null
        ]);
    }
}
