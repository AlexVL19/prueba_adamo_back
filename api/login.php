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

//Se establecen los parámetros de la cookie de sesión, y luego se inicia la sesión en caso de que ya tengamos una.
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

//Se importa la configuración y conexión de la base de datos
include "../database/database.php";

//Se recuperan los datos del formulario de ingreso
$email = $_POST['email'] ?? null;
$password = $_POST['password'] ?? null;
$hashed_password = '';

//Se verifica que el método de la petición sea POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    //Verificación de campos vacíos
    if (empty($email) || empty($password)) {
        echo json_encode([
            'alert_message' => 'Todos los campos son requeridos.',
            'redirect' => null
        ]);

        exit();
    }

    //Verificación del formato de correo electrónico
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'alert_message' => 'La dirección de correo electrónico no es válida.',
            'redirect' => null
        ]);

        exit();
    }

    //Se verifica en la base de datos si este usuario existe con base a su correo electrónico
    $sql_login = "SELECT id, names, email, password, first_login FROM users WHERE email = ?";
    $stmt_login = $connection->prepare($sql_login);

    //Si la conexión no está preparada, devolver mensaje de error
    if (!$stmt_login) {
        echo json_encode([
            'alert_message' => 'Ha habido un error al iniciar sesión. Inténtalo de nuevo.',
            'redirect' => null
        ]);

        exit();
    }

    $stmt_login->bind_param('s', $email);
    $stmt_login->execute();
    $stmt_login->store_result();

    //Si no devuelve ningún registro desde la BD, entonces no existe una cuenta asociada al correo electrónico
    if ($stmt_login->num_rows() !== 1) {
        echo json_encode([
            'alert_message' => 'No existe una cuenta asociada al correo electrónico.',
            'redirect' => null
        ]);

        exit();
    }

    //Se utiliza la consulta anterior para asignar los valores a estas variables
    $stmt_login->bind_result($sqlId, $sqlNames, $sqlEmail, $hashed_password, $sqlFirstLogin);
    $stmt_login->fetch();
    $stmt_login->close();

    //Se verifica si la contraseña que introducimos en el formulario coincide con el hash que ya está almacenado
    if (!password_verify($password, $hashed_password)) {
        echo json_encode([
            'alert_message' => 'Los datos son incorrectos. Inténtalo de nuevo.',
            'redirect' => null
        ]);

        exit();
    }

    //Se regenera el ID de la sesión y se generan valores nuevos para su posterior uso
    session_regenerate_id(true);

    $_SESSION['user'] = [
        'id' => $sqlId,
        'names' => $sqlNames,
        'email' => $sqlEmail,
        'first_login' => $sqlFirstLogin
    ];

    //Si el usuario inicia sesión por primera vez, entonces redirige al cambio de contraseña
    if ($sqlFirstLogin === 0) {
        echo json_encode([
            'redirect' => 'change-password'
        ]);
    }

    //Sino, al dashboard
    else {
        echo json_encode([
            'redirect' => 'dashboard'
        ]);
    }

    exit();
}
?>