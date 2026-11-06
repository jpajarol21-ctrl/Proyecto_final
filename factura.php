

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

$id = (int)($_GET["id"] ?? 0);

$sql = $conexion->prepare(
    "SELECT v.id, v.fecha, v.vendedor, v.total,
            p.nombre, p.marca, d.cantidad, d.precio
     FROM ventas v
     INNER JOIN detalle_ventas d ON v.id = d.venta_id
     INNER JOIN productos p ON d.producto_id = p.id
     WHERE v.id = ?"
);

$sql->bind_param("i", $id);
$sql->execute();

$resultado = $sql->get_result();

if ($resultado->num_rows === 0) {
    die("Factura no encontrada.");
}

$venta = $resultado->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura | Humbecel Tecnologia</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            color: #000;
        }

        .factura {
            max-width: 650px;
            margin: auto;
            border: 1px solid #ccc;
            padding: 30px;
        }

        h1, h2 {
            text-align: center;
        }

        .dato {
            margin: 12px 0;
        }

        .total {
            text-align: right;
            font-size: 20px;
            font-weight: bold;
        }

        button {
            display: block;
            margin: 25px auto;
            padding: 12px 25px;
            cursor: pointer;
        }

        @media print {
            button {
                display: none;
            }

            body {
                margin: 0;
            }
        }
    </style>
</head>

<body>

<div class="factura">

    <h1>Humbecel Tecnologia</h1>
    <h2>FACTURA DE VENTA</h2>

    <hr>

    <p class="dato">
        <strong>Factura:</strong>
        <?php echo $venta["id"]; ?>
    </p>

    <p class="dato">
        <strong>Fecha:</strong>
        <?php echo htmlspecialchars($venta["fecha"]); ?>
    </p>

    <p class="dato">
        <strong>Vendedor:</strong>
        <?php echo htmlspecialchars($venta["vendedor"]); ?>
    </p>

    <hr>

    <h3>Detalle de la venta</h3>

    <p><strong>Producto:</strong>
        <?php echo htmlspecialchars($venta["nombre"]); ?>
    </p>

    <p><strong>Marca:</strong>
        <?php echo htmlspecialchars($venta["marca"]); ?>
    </p>

    <p><strong>Cantidad:</strong>
        <?php echo $venta["cantidad"]; ?>
    </p>

    <p><strong>Precio unitario:</strong>
        $<?php echo number_format($venta["precio"], 0, ",", "."); ?>
    </p>

    <hr>

    <p class="total">
        Total: $<?php echo number_format($venta["total"], 0, ",", "."); ?>
    </p>

    <h3>¡Gracias por su compra!</h3>

    <button onclick="window.print()">
        Descargar factura PDF
    </button>

</div>

</body>
</html>
