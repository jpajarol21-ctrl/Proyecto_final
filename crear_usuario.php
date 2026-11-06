
<?php
require_once "conexion.php";

$mensaje = "";
$claseMensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $usuario = isset($_POST["usuario"]) ? trim($_POST["usuario"]) : "";
    $contrasena = isset($_POST["contrasena"]) ? $_POST["contrasena"] : "";

    if ($usuario !== "" && $contrasena !== "") {

        $hash = password_hash($contrasena, PASSWORD_DEFAULT);

        $sql = $conexion->prepare(
            "INSERT INTO usuarios (usuario, contrasena) VALUES (?, ?)"
        );

        if ($sql) {
            $sql->bind_param("ss", $usuario, $hash);

            if ($sql->execute()) {
                $mensaje = "Usuario registrado correctamente.";
                $claseMensaje = "success";
            } else {
                $mensaje = "No se pudo registrar. El usuario puede existir.";
                $claseMensaje = "error";
            }

            $sql->close();
        } else {
            $mensaje = "Error en la consulta: " . $conexion->error;
            $claseMensaje = "error";
        }

    } else {
        $mensaje = "Completa todos los campos.";
        $claseMensaje = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear usuario - TechCell</title>
    <link rel="stylesheet" href="crear_usuario.css">
</head>
<body>

<header class="header">
    <a href="index.html" class="logo">Tech<span>Cell</span></a>
    <a href="login.html" class="back-link">Volver al inicio de sesión</a>
</header>

<main class="login-container">
    <section class="login-card">

        <div class="login-icon">TC</div>

        <p class="eyebrow">TECHCELL</p>

        <h1>Crear <span>usuario</span></h1>

        <p class="description">
            Registra un usuario para acceder al sistema.
        </p>

        <form method="POST" action="crear_usuario.php">

            <div class="form-group">
                <label for="usuario">Nombre de usuario</label>
                <input type="text" id="usuario" name="usuario" required>
            </div>

            <div class="form-group">
                <label for="contrasena">Contraseña</label>
                <input type="password" id="contrasena" name="contrasena" required>
            </div>

            <button type="submit" class="login-btn">
                Crear usuario
            </button>

            <p class="message <?php echo $claseMensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </p>

        </form>

        <div class="login-footer">
            <p>Registro de usuarios del sistema TechCell.</p>
        </div>

    </section>
</main>

<footer class="footer">
    <p>TechCell - Sistema de gestión de celulares</p>
</footer>

</body>
</html>
