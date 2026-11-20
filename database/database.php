<?php 

$hostname = "localhost";
$username = "root";
$password = "";
$database = "bd_prueba_adamo";

$connection = new mysqli($hostname, $username, $password, $database);

if ($connection->connect_error) {
    die("La conexión no ha sido exitosa: " . $connection->connect_error);
}   
?>