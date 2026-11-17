
<?php

$servidor = "localhost";
$usuario = "root";
$contrasena = "";
$base_datos = "control_inventario";

// Crear conexión
$conexion = new mysqli(
    $servidor,
    $usuario,
    $contrasena,
    $base_datos
);

// Comprobar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Configurar caracteres
$conexion->set_charset("utf8mb4");

?>
