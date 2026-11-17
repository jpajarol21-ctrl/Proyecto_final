```php
<?php

session_start();

require_once "conexion.php";

// Verificar que el formulario se envíe mediante POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.html");
    exit;
}

// Recibir los datos
$usuario = trim($_POST["usuario"] ?? "");
$contrasena = $_POST["contrasena"] ?? "";

// Validar campos vacíos
if ($usuario === "" || $contrasena === "") {
    header("Location: login.html");
    exit;
}

// Consultar usuario en la base de datos
$sql = $conexion->prepare(
    "SELECT usuario, contrasena
     FROM usuarios
     WHERE usuario = ?"
);

if (!$sql) {
    die("Error en la consulta: " . $conexion->error);
}

$sql->bind_param("s", $usuario);
$sql->execute();

$resultado = $sql->get_result();
$fila = $resultado->fetch_assoc();

// Verificar si existe el usuario
if (!$fila) {
    $sql->close();
    $conexion->close();
    die("El usuario no existe.");
}

// Verificar contraseña
if (!password_verify($contrasena, $fila["contrasena"])) {
    $sql->close();
    $conexion->close();
    die("La contraseña es incorrecta.");
}

// Regenerar identificador de sesión
session_regenerate_id(true);

// Guardar información de sesión
$_SESSION["usuario"] = $fila["usuario"];

// Identificar el tipo de usuario
$tipoUsuario = strtolower(trim($fila["usuario"]));

// Redireccionar según el usuario
if ($tipoUsuario === "admin") {

    $_SESSION["rol"] = "Administrador";

    $sql->close();
    $conexion->close();

    header("Location: admin.php");
    exit;
    } elseif ($tipoUsuario === "vendedor") {

    $_SESSION["rol"] = "Vendedor";

    $sql->close();
    $conexion->close();

    header("Location: ventas.php");
    exit;

} elseif ($tipoUsuario === "cliente") {

    $_SESSION["rol"] = "Cliente";

    $sql->close();
    $conexion->close();

    header("Location: index.html");
    exit;

} else {

    session_unset();
    session_destroy();

    $sql->close();
    $conexion->close();

    die("Usuario sin permisos asignados.");
}

?>
```