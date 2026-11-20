# Backend de prueba técnica de Desarrollador Junior - AdamoServices
Esto es la mitad de un proyecto de prueba técnica, siendo este el backend desarrollado en PHP. Este proyecto pretende simular un módulo de inicio de sesión, en donde el usuario puede registrarse, iniciar sesión y cambiar su contraseña.

## Características principales
- El usuario puede registarse, especificando su nombre, apellido y correo electrónico.
- Se envía una contraseña provisional al correo electrónico del recién registrado.
- Al ingresar por primera vez, el sistema le solicitará al usuario una nueva contraseña.
- Se muestra un dashboard al final del flujo.

## Instalación
Para utilizar el proyecto, se requieren de varias dependencias y credenciales que son externas. Se debe tener PHP y Composer instalados de antemano:

- Primero, descarga el archivo .zip desde el repositorio en GitHub.

- Accede al directorio del proyecto:
cd prueba-adamo-back

- Para instalar phpdotenv (dependencia para utilizar variables de entorno externas), usa este comando dentro del directorio del proyecto en consola:
composer require vlucas/phpdotenv

- Posteriormente, instalaremos PHPMailer, que es una dependencia para enviar correos electrónicos mediante SMTP. Utiliza este comando dentro del directorio del proyecto en consola:
composer require phpmailer/phpmailer

- Finalmente, necesitaremos de unas credenciales de Gmail para enviar esos correos electrónicos. Sigue estos pasos para obtenerlas:
1. Accede a la configuración de cualquier cuenta de Google.
2. Activa la verificación en dos pasos.
3. Busca la opción 'Contraseñas de aplicaciones'.
4. Genera una contraseña nueva para esta aplicación.
5. Guardar esta contraseña en la variable de entorno GMAIL_APP_PASSWORD en el archivo .env.
6. La variable de entorno GMAIL_USER es el correo de la cuenta de Google.

## Inicio del servidor desde consola
Ejecuta este comando en consola:
php -S localhost:8000