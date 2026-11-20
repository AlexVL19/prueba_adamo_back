<?php
/*Se establecen las reglas del CORS para poder recibir peticiones del dominio de prueba de Vite. Tiene que comprobar el origen, las
credenciales que se utilizarán, y los métodos que puede utilizar.*/
$allowed_origin = 'http://localhost:5173';

if (
    isset($_SERVER['HTTP_ORIGIN']) &&
    $_SERVER['HTTP_ORIGIN'] === $allowed_origin
) {

    header("Access-Control-Allow-Origin: $allowed_origin");
    header('Access-Control-Allow-Credentials: true');
}

header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Elimina los datos de la sesión
    $_SESSION = [];

    // Elimina las cookies de sesión
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Destruye la sesión y redirige al menú principal
    session_destroy();

    echo json_encode([
        'redirect' => 'login'
    ]);

    exit;
}
