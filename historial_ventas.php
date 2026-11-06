
<?php
session_start();

if (
    !isset($_SESSION["usuario"]) ||
    !isset($_SESSION["rol"]) ||
    $_SESSION["rol"] !== "Administrador"
) {
    header("Location: login.html");
    exit;
}

require_once "conexion.php";

// CONSULTAR HISTORIAL DE VENTAS
$sql = "
    SELECT
        v.id,
        v.fecha,
        v.vendedor,
        v.total,
        GROUP_CONCAT(
            CONCAT(p.nombre, ' (', d.cantidad, ')')
            SEPARATOR ', '
        ) AS productos
    FROM ventas v
    INNER JOIN detalle_ventas d ON v.id = d.venta_id
    INNER JOIN productos p ON d.producto_id = p.id
    GROUP BY v.id, v.fecha, v.vendedor, v.total
    ORDER BY v.fecha DESC
";

$resultado = $conexion->query($sql);

// RESUMEN DE VENTAS
$resumen = $conexion->query("
    SELECT
        COUNT(*) AS cantidad,
        COALESCE(SUM(total), 0) AS total
    FROM ventas
")->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Historial de ventas | Humbecel Tecnologia</title>

    <link rel="stylesheet" href="admin.css">
</head>

<body>

<!-- ENCABEZADO -->
<header class="header">

    <a href="admin.php" class="logo">
        Tech<span>Cell</span>
    </a>

    <nav>
        <a href="admin.php">Volver al administrador</a>

        <button
            type="button"
            id="cerrarSesion"
            class="logout-btn"
        >
            Cerrar sesión
        </button>
    </nav>

</header>

<main class="container">

    <!-- BIENVENIDA -->
    <section class="welcome">

        <div>
            <p class="eyebrow">REGISTRO DE OPERACIONES</p>

            <h1>Historial de <span>ventas</span></h1>

            <p>
                Consulta las ventas realizadas, los productos vendidos
                y los valores registrados en Humbecel Tecnologia.
            </p>
        </div>

    </section>

    <!-- RESUMEN -->
    <section class="stats">

        <article class="stat-card">

            <span class="stat-icon">▣</span>

            <div>
                <p>Ventas realizadas</p>

                <h2>
                    <?php echo number_format($resumen["cantidad"]); ?>
                </h2>
            </div>

        </article>

        <article class="stat-card">

            <span class="stat-icon">$</span>

            <div>
                <p>Total vendido</p>

                <h2>
                    <?php
                    echo "$" . number_format(
                        $resumen["total"],
                        0,
                        ",",
                        "."
                    );
                    ?>
                </h2>
            </div>

        </article>

    </section>

    <!-- TABLA DE VENTAS -->
    <section class="panel">

        <div class="panel-heading">

            <div>
                <p class="eyebrow">CONTROL DE OPERACIONES</p>

                <h2>Registro de ventas</h2>
            </div>

        </div>

        <div class="table-container">

            <table>

                <thead>
                    <tr>
                        <th>Factura</th>
                        <th>Fecha</th>
                        <th>Vendedor</th>
                        <th>Productos</th>
                        <th>Total</th>
                        <th>Comprobante</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if ($resultado && $resultado->num_rows > 0): ?>

                        <?php while ($venta = $resultado->fetch_assoc()): ?>

                            <tr>

                                <td>
                                    #<?php echo (int)$venta["id"]; ?>
                                </td>

                                <td>
                                    <?php
                                    echo date(
                                        "d/m/Y H:i",
                                        strtotime($venta["fecha"])
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars($venta["vendedor"]);
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars($venta["productos"]);
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo "$" . number_format(
                                        $venta["total"],
                                        0,
                                        ",",
                                        "."
                                    );
                                    ?>
                                </td>

                                <td>
                                    <a
                                        href="factura.php?id=<?php echo (int)$venta["id"]; ?>"
                                        target="_blank"
                                    >
                                        Ver factura
                                    </a>
                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="6">
                                Todavía no hay ventas registradas.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>

</main>

<!-- PIE DE PÁGINA -->
<footer class="footer">

    <p>© 2026 Humbecel Tecnologia. Panel administrativo.</p>

</footer>

<script>
$("cerrarSesion")?.addEventListener("click", () => {
    window.location.href = "cerrar_sesion.php";
});
</script>

</body>
</html>
