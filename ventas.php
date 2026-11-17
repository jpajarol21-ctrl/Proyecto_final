
<?php
session_start();

if (
    !isset($_SESSION["usuario"]) ||
    !isset($_SESSION["rol"]) ||
    $_SESSION["rol"] !== "Vendedor"
) {
    header("Location: login.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Ventas | Humbecel Tecnologia</title>

    <link rel="stylesheet" href="ventas.css">
</head>

<body>
 
<header class="header">

    <a href="ventas.php" class="logo">
        Humbecel<span>Tecnologia</span>
    </a>

    <div class="usuario">
        Vendedor:
        <?php echo htmlspecialchars($_SESSION["usuario"]); ?>
    </div>

    <a href="cerrar_sesion.php" class="salir">
        Cerrar sesión
    </a>

</header>

<main class="contenedor">

    <section class="bienvenida">

        <h1>Panel de ventas</h1>

        <p>
            Desde aquí puedes registrar las ventas
            y consultar el catálogo de productos.
        </p>

    </section>

    <section class="opciones">

        <a href="registrar_venta.php" class="tarjeta">

            <div class="icono">🛒</div>

            <h2>Registrar venta</h2>

            <p>
                Guarda las ventas realizadas
                a los clientes.
            </p>

            <span>Registrar venta →</span>

        </a>

        <a href="catalogo.php" class="tarjeta">

            <div class="icono">📱</div>

            <h2>Consultar catálogo</h2>

            <p>
                Consulta los celulares, precios
                actuales y existencias.
            </p>

            <span>Ver catálogo →</span>

        </a>

    </section>

</main>

<footer>
    <p>© 2026 Humbecel Tecnologia. Todos los derechos reservados.</p>
</footer>

</body>
</html>
