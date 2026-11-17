
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

require_once "conexion.php";

$sql = $conexion->query(
    "SELECT id, nombre, marca, almacenamiento, precio, stock, imagen
     FROM productos
     ORDER BY nombre ASC"
);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Catálogo | Humbecel Tecnologia </title>
    <link rel="stylesheet" href="ventas.css">
</head>

<body>

<header class="header">

    <a href="ventas.php" class="logo">
        Humbecel<span>Tecnologia</span>
    </a>

    <div class="usuario">
        Vendedor: <?php echo htmlspecialchars($_SESSION["usuario"]); ?>
    </div>

    <a href="cerrar_sesion.php" class="salir">
        Cerrar sesión
    </a>

</header>

<main class="contenedor">

    <section class="bienvenida">
        <h1>Catálogo de celulares</h1>
        <p>Consulta los precios y las existencias disponibles.</p>
    </section>

    <section class="opciones">

        <?php if ($sql && $sql->num_rows > 0): ?>

            <?php while ($producto = $sql->fetch_assoc()): ?>

                <article class="tarjeta">

                    <?php if (!empty($producto["imagen"])): ?>

                        <img
                            src="<?php echo htmlspecialchars($producto["imagen"]); ?>"
                            alt="Fotografía del producto"
                            style="width:100%; max-width:180px; height:150px; object-fit:contain;"
                        >

                    <?php endif; ?>

                    <h2>
                        <?php echo htmlspecialchars($producto["nombre"]); ?>
                    </h2>

                    <p>
                        Marca:
                        <?php echo htmlspecialchars($producto["marca"]); ?>
                    </p>

                    <p>
                        Almacenamiento:
                        <?php echo htmlspecialchars($producto["almacenamiento"]); ?>
                    </p>

                    <p>
                        Precio:
                        <?php echo "$" . number_format($producto["precio"], 0, ",", "."); ?>
                    </p>

                    <p>
                        Existencias:
                        <?php echo (int)$producto["stock"]; ?>
                    </p>

                    <?php if ((int)$producto["stock"] > 0): ?>
                        <span>Disponible</span>
                    <?php else: ?>
                        <span>Agotado</span>
                    <?php endif; ?>

                </article>

            <?php endwhile; ?>

        <?php else: ?>

            <p>No hay productos registrados actualmente.</p>

        <?php endif; ?>

    </section>

    <p>
        <a href="ventas.php">← Volver al panel de ventas</a>
    </p>

</main>

<footer>
    <p>© 2026 Humbecel Tecnologia. Todos los derechos reservados.</p>
</footer>

</body>
</html>
