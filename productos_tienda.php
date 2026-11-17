
<?php
header("Content-Type: application/json; charset=utf-8");

require_once "conexion.php";

$sql = "SELECT id, nombre, marca, almacenamiento, precio, stock, imagen
        FROM productos
        WHERE stock > 0
        ORDER BY nombre ASC";

$resultado = $conexion->query($sql);

if (!$resultado) {
    http_response_code(500);
    echo json_encode([
        "error" => "No se pudieron consultar los productos."
    ]);
    exit;
}

$productos = [];

while ($fila = $resultado->fetch_assoc()) {
    $productos[] = $fila;
}

echo json_encode(
    $productos,
    JSON_UNESCAPED_UNICODE
);

$conexion->close();
?>
