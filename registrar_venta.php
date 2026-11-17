
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

$mensaje = "";
$tipo = "";
$facturaId = 0;

// REGISTRAR VENTA
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $producto_id = (int)($_POST["producto_id"] ?? 0);
    $cantidad = (int)($_POST["cantidad"] ?? 0);

    if ($producto_id <= 0 || $cantidad <= 0) {
        $mensaje = "Selecciona un celular y una cantidad válida.";
        $tipo = "error";
    } else {

        try {
            $conexion->begin_transaction();

            $consulta = $conexion->prepare(
                "SELECT precio, stock FROM productos WHERE id = ? FOR UPDATE"
            );
            $consulta->bind_param("i", $producto_id);
            $consulta->execute();

            $producto = $consulta->get_result()->fetch_assoc();

            if (!$producto) {
                throw new Exception("El celular seleccionado no existe.");
            }

            if ($producto["stock"] <= 0 || $cantidad > $producto["stock"]) {
                throw new Exception("No hay suficientes unidades disponibles.");
            }

            $precio = $producto["precio"];
            $total = $precio * $cantidad;
            $vendedor = $_SESSION["usuario"];

            $venta = $conexion->prepare(
                "INSERT INTO ventas (vendedor, total) VALUES (?, ?)"
            );
            $venta->bind_param("sd", $vendedor, $total);
            $venta->execute();

            $venta_id = $conexion->insert_id;

            $detalle = $conexion->prepare(
                "INSERT INTO detalle_ventas
                (venta_id, producto_id, cantidad, precio)
                VALUES (?, ?, ?, ?)"
            );
            $detalle->bind_param("iiid", $venta_id, $producto_id, $cantidad, $precio);
            $detalle->execute();

            $actualizar = $conexion->prepare(
                "UPDATE productos SET stock = stock - ? WHERE id = ?"
            );
            $actualizar->bind_param("ii", $cantidad, $producto_id);
            $actualizar->execute();

            $conexion->commit();

            $facturaId = $venta_id;
            $mensaje = "Venta registrada correctamente. Factura: " . $venta_id;
            $tipo = "success";

        } catch (Throwable $e) {
            $conexion->rollback();
            $mensaje = $e->getMessage();
            $tipo = "error";
        }
    }
}

// CONSULTAR CELULARES DISPONIBLES
$resultado = $conexion->query(
    "SELECT id, nombre, marca, almacenamiento, precio, stock
     FROM productos
     WHERE stock > 0
     ORDER BY nombre ASC"
);

if (!$resultado) {
    die("Error al consultar los productos: " . $conexion->error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar venta | TechCell</title>
    <link rel="stylesheet" href="ventas.css">
</head>

<body>

<header class="header">
    <a href="ventas.php" class="logo">Tech<span>Cell</span></a>

    <div class="usuario">
        Vendedor: <?php echo htmlspecialchars($_SESSION["usuario"]); ?>
    </div>

    <a href="cerrar_sesion.php" class="salir">Cerrar sesión</a>
</header>

<main class="contenedor">

    <section class="bienvenida">
        <h1>Registrar venta</h1>
        <p>Selecciona un celular y registra la cantidad vendida.</p>
    </section>

    <?php if ($mensaje): ?>
        <p class="message <?php echo htmlspecialchars($tipo); ?>">
            <?php echo htmlspecialchars($mensaje); ?>
        </p>
    <?php endif; ?>

    <?php if ($facturaId > 0): ?>
        <div class="acciones-factura">
            <a href="factura.php?id=<?php echo $facturaId; ?>"
               target="_blank" class="boton-factura">
                Consultar factura PDF
            </a>
        </div>
    <?php endif; ?>

    <section class="formulario-venta">

        <form method="POST" action="registrar_venta.php" class="formulario">

            <label for="producto_id">Seleccionar celular:</label>

            <select name="producto_id" id="producto_id" required>
                <option value="">-- Selecciona un celular --</option>

                <?php while ($producto = $resultado->fetch_assoc()): ?>
                    <option value="<?php echo (int)$producto["id"]; ?>">
                        <?php echo htmlspecialchars($producto["nombre"]); ?>
                        - <?php echo htmlspecialchars($producto["marca"]); ?>
                        - <?php echo htmlspecialchars($producto["almacenamiento"]); ?>
                        | Precio: $<?php echo number_format($producto["precio"], 0, ",", "."); ?>
                        | Stock: <?php echo (int)$producto["stock"]; ?>
                    </option>
                <?php endwhile; ?>

            </select>

            <label for="cantidad">Cantidad:</label>

            <input
                type="number"
                name="cantidad"
                id="cantidad"
                min="1"
                step="1"
                placeholder="Escribe la cantidad"
                required
            >

            <button type="submit" class="boton-registrar">
                Registrar venta
            </button>

        </form>

        <?php if ($resultado->num_rows === 0): ?>
            <p style="color:#b00020; text-align:center; margin-top:15px;">
                No hay celulares con existencias disponibles.
            </p>
        <?php endif; ?>

    </section>

    <div class="volver">
        <a href="ventas.php">Volver al panel de ventas</a>
    </div>

</main>

</body>
</html>
