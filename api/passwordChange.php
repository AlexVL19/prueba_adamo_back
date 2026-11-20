<?php

/*Se establecen las reglas del CORS para poder recibir peticiones del dominio de prueba de Vite. Tiene que comprobar el origen, las
credenciales que se utilizarán, y los métodos que puede utilizar.*/
$allowed_origin = 'http://localhost:5173';

if (isset($_SERVER['HTTP_ORIGIN']) &&
    $_SERVER['HTTP_ORIGIN'] === $allowed_origin) {

    header("Access-Control-Allow-Origin: $allowed_origin");
    header('Access-Control-Allow-Credentials: true');
}

header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

//Se incluye la configuración de la base de datos y consigue las cookies de sesión
include "../database/database.php";

session_start();

//Consigue los campos del formulario
$newPassword = $_POST['password'] ?? null;
$verifyPassword = $_POST['verify_pass'] ?? null;

//Si no existe ninguna cookie de sesión, entonces el usuario no está autorizado para cambiar la contraseña, y redirigirá al login.
if (!isset($_SESSION['user'])) {
    http_response_code(401);

    echo json_encode([
        'redirect' => 'login',
        'message' => 'No estás autorizado/a para cambiar la contraseña.',
        'sesion' => $_SESSION['user']
    ]);

    exit();
}

else {

    //Verifica si el método de la solicitud es POST, para poder proceder con la solicitud
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        //Verificación de campos vacíos
        if (empty($newPassword) || empty($verifyPassword)) {
            echo json_encode([
                "alert_message" => "Todos los campos son requeridos.",
                "redirect" => null
            ]);

            exit();
        }

        //Verificación de equivalencia de las dos contraseñas
        if ($verifyPassword !== $newPassword) {
            echo json_encode([
                "alert_message" => "Las contraseñas no coinciden. Inténtalo de nuevo.",
                "redirect" => null
            ]);

            exit();
        }

        //Se encripta la contraseña nueva con un hash
        $hashed_password = password_hash($newPassword, PASSWORD_DEFAULT);

        //Petición SQL para actualizar la contraseña y el estado actual del usuario, ya habiendo efectuado su primer inicio de sesión.
        $sql_update_password = "UPDATE users SET password = ?, first_login = 1 WHERE id = ?";
        $stmt_update_password = $connection->prepare($sql_update_password);
        $stmt_update_password->bind_param('si', $hashed_password, $_SESSION['user']['id']);
        $stmt_update_password->execute();
        $stmt_update_password->close();

        //Se redirige al dashboard
        echo json_encode([
            "redirect" => 'dashboard'
        ]);
    }
}
