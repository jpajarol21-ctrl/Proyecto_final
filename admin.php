
<?php
session_start();

if (
    !isset($_SESSION["usuario"], $_SESSION["rol"]) ||
    $_SESSION["rol"] !== "Administrador"
) {
    header("Location: login.html");
    exit;
}

require_once "conexion.php";

// CONSULTAR PRODUCTOS
if (($_GET["accion"] ?? "") === "listar") {
    header("Content-Type: application/json; charset=utf-8");

    $consulta = $conexion->query("SELECT * FROM productos ORDER BY id DESC");

    echo json_encode($consulta->fetch_all(MYSQLI_ASSOC));
    exit;
}

// RECIBIR SOLICITUDES
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    header("Content-Type: application/json; charset=utf-8");

    $datos = json_decode(file_get_contents("php://input"), true);

    if (!is_array($datos)) {
        echo json_encode(["success" => false, "error" => "Datos no válidos"]);
        exit;
    }

    $accion = $datos["accion"] ?? "";

    if ($accion === "guardar") {
        $nombre = trim($datos["nombre"] ?? "");
        $marca = trim($datos["marca"] ?? "");
        $almacenamiento = $datos["almacenamiento"] ?? "";
        $precio = (float) ($datos["precio"] ?? 0);
        $stock = (int) ($datos["stock"] ?? -1);
        $imagen = $datos["imagen"] ?? "";
        $id = (int) ($datos["id"] ?? 0);

        if (!$nombre || !$marca || !$almacenamiento || $precio <= 0 || $stock < 0) {
            echo json_encode(["success" => false, "error" => "Datos incorrectos"]);
            exit;
        }

        if ($id > 0) {
            $sql = $conexion->prepare(
                "UPDATE productos SET nombre=?, marca=?, almacenamiento=?,
                precio=?, stock=?, imagen=? WHERE id=?"
            );

            $sql->bind_param(
                "sssdisi",
                $nombre,
                $marca,
                $almacenamiento,
                $precio,
                $stock,
                $imagen,
                $id
            );
        } else {
            $sql = $conexion->prepare(
                "INSERT INTO productos (nombre, marca, almacenamiento, precio, stock, imagen)
                VALUES (?, ?, ?, ?, ?, ?)"
            );

            $sql->bind_param(
                "sssdis",
                $nombre,
                $marca,
                $almacenamiento,
                $precio,
                $stock,
                $imagen
            );
        }

        echo json_encode(["success" => $sql->execute()]);
        exit;
    }

    if ($accion === "eliminar") {
        $id = (int) ($datos["id"] ?? 0);

        $sql = $conexion->prepare("DELETE FROM productos WHERE id=?");
        $sql->bind_param("i", $id);

        echo json_encode(["success" => $sql->execute()]);
        exit;
    }

    echo json_encode(["success" => false, "error" => "Acción no válida"]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Panel Administrativo | Humbecel Tecnologia</title>

    <link rel="stylesheet" href="admin.css">
</head>

<body>

<header class="header">
    <a href="index.html" class="logo">
        Humbecel <span>Tecnologia</span>
    </a>

    <nav>
        <a href="index.html">Ver tienda</a>
        <a href="historial_ventas.php">Historial de ventas</a>
        <button id="cerrarSesion" class="logout-btn">Cerrar sesión</button>
    </nav>
</header>

<main class="container">

    <section class="welcome">
        <div>
            <p class="eyebrow">PANEL DE CONTROL</p>
            <h1>Administración de <span>Humbecel Tecnologia</span></h1>
            <p>Gestiona los productos y el inventario de tu tienda.</p>
        </div>
    </section>

    <!-- RESUMEN -->
    <section class="stats">

        <article class="stat-card">
            <span class="stat-icon">▣</span>
            <div>
                <p>Productos registrados</p>
                <h2 id="totalProductos">0</h2>
            </div>
        </article>

        <article class="stat-card">
            <span class="stat-icon">▤</span>
            <div>
                <p>Unidades disponibles</p>
                <h2 id="totalUnidades">0</h2>
            </div>
        </article>

        <article class="stat-card">
            <span class="stat-icon">!</span>
            <div>
                <p>Productos agotados</p>
                <h2 id="productosAgotados">0</h2>
            </div>
        </article>

    </section>

    <!-- FORMULARIO -->
    <section class="panel">

        <div class="panel-heading">
            <div>
                <p class="eyebrow">INVENTARIO</p>
                <h2 id="tituloFormulario">Registrar producto</h2>
            </div>
        </div>

        <form id="productoForm">

            <input type="hidden" id="productoId">

            <div class="form-grid">

                <div class="form-group">
                    <label for="nombre">Nombre del celular</label>
                    <input
                        type="text"
                        id="nombre"
                        placeholder="Ej. iPhone 15"
                        maxlength="80"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="marca">Marca</label>

                    <select id="marca" required>
                        <option value="">Seleccionar marca</option>
                        <option value="Apple">Apple</option>
                        <option value="Samsung">Samsung</option>
                        <option value="Xiaomi">Xiaomi</option>
                        <option value="Motorola">Motorola</option>
                        <option value="Honor">Honor</option>
                        <option value="Otra">Otra</option>
                    </select>
                </div>

                <!-- NUEVO CAMPO PARA ESCRIBIR UNA MARCA -->
                <div class="form-group" id="nuevaMarcaGrupo" hidden>
                    <label for="nuevaMarca">Escribir nueva marca</label>

                    <input
                        type="text"
                        id="nuevaMarca"
                        placeholder="Ej. Huawei, Tecno, Realme..."
                        maxlength="50"
                    >
                </div>

                <div class="form-group">
                    <label for="almacenamiento">Almacenamiento</label>

                    <select id="almacenamiento" required>
                        <option value="">Seleccionar</option>
                        <option>64 GB</option>
                        <option>128 GB</option>
                        <option>256 GB</option>
                        <option>512 GB</option>
                        <option>1 TB</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="precio">Precio (COP)</label>

                    <input
                        type="number"
                        id="precio"
                        min="1"
                        step="1"
                        placeholder="Ej. 1899000"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="stock">Cantidad disponible</label>

                    <input
                        type="number"
                        id="stock"
                        min="0"
                        step="1"
                        placeholder="Ej. 10"
                        required
                    >
                </div>

                <div class="form-group image-group">
                    <label for="imagenProducto">Fotografía del celular</label>

                    <input
                        type="file"
                        id="imagenProducto"
                        accept="image/jpeg,image/png,image/webp"
                    >

                    <small>
                        Formatos permitidos: JPG, PNG y WEBP.
                        Tamaño máximo: 700 KB.
                    </small>

                    <div class="image-preview-container">
                        <img
                            id="vistaPrevia"
                            src=""
                            alt="Vista previa del producto"
                            hidden
                        >

                        <span id="textoVistaPrevia">
                            <span class="camera-icon">▧</span>
                            <br>Aquí aparecerá la fotografía
                        </span>
                    </div>
                </div>

            </div>

            <div class="form-buttons">

                <button
                    type="submit"
                    class="primary-btn"
                    id="guardarBtn"
                >
                    Guardar producto
                </button>

                <button
                    type="button"
                    class="secondary-btn"
                    id="cancelarEdicion"
                    hidden
                >
                    Cancelar edición
                </button>

            </div>

            <p id="mensaje" class="message" aria-live="polite"></p>

        </form>
    </section>

    <!-- TABLA -->
    <section class="panel">

        <div class="panel-heading">
            <div>
                <p class="eyebrow">CONTROL DE PRODUCTOS</p>
                <h2>Productos registrados</h2>
            </div>

            <input
                type="search"
                id="buscarProducto"
                placeholder="Buscar producto..."
                class="search-input"
            >
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Producto</th>
                        <th>Marca</th>
                        <th>Almacenamiento</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody id="tablaProductos"></tbody>
            </table>
        </div>

        <p id="sinProductos" class="empty-message" hidden>
            No hay productos registrados.
        </p>

    </section>

</main>

<footer class="footer">
    <p>© 2026 Humbecel Tecnologia. Panel administrativo.</p>
</footer>

<script>

const $ = id => document.getElementById(id);

const formulario = $("productoForm");
const campos = ["nombre", "marca", "almacenamiento", "precio", "stock"];
const LIMITE_IMAGEN = 700 * 1024;

let productos = [];

const precioCOP = valor => Number(valor).toLocaleString("es-CO", {
    style: "currency",
    currency: "COP",
    maximumFractionDigits: 0
});

function mensaje(texto, tipo = "success") {
    $("mensaje").textContent = texto;
    $("mensaje").className = "message " + tipo;
}

function leerImagen(archivo) {
    return new Promise((resolve, reject) => {
        const lector = new FileReader();

        lector.onload = () => resolve(lector.result);
        lector.onerror = () => reject();

        lector.readAsDataURL(archivo);
    });
}

// MOSTRAR CAMPO DE NUEVA MARCA
$("marca").addEventListener("change", () => {

    const esOtra = $("marca").value === "Otra";

    $("nuevaMarcaGrupo").hidden = !esOtra;
    $("nuevaMarca").required = esOtra;

    if (esOtra) {
        $("nuevaMarca").focus();
    } else {
        $("nuevaMarca").value = "";
    }

});

// VISTA PREVIA
$("imagenProducto").addEventListener("change", () => {

    const archivo = $("imagenProducto").files[0];

    if (!archivo) return;

    if (!["image/jpeg", "image/png", "image/webp"].includes(archivo.type)) {
        mensaje("Selecciona una imagen JPG, PNG o WEBP.", "error");
        $("imagenProducto").value = "";
        return;
    }

    if (archivo.size > LIMITE_IMAGEN) {
        mensaje("La imagen no puede superar los 700 KB.", "error");
        $("imagenProducto").value = "";
        return;
    }

    const lector = new FileReader();

    lector.onload = () => {
        $("vistaPrevia").src = lector.result;
        $("vistaPrevia").hidden = false;
        $("textoVistaPrevia").hidden = true;

        mensaje("Fotografía seleccionada correctamente.");
    };

    lector.readAsDataURL(archivo);

});

// CONSULTAR PRODUCTOS
async function cargarProductos() {

    try {

        const respuesta = await fetch("admin.php?accion=listar");

        if (!respuesta.ok) throw new Error();

        productos = await respuesta.json();

        mostrarProductos();

    } catch {

        mensaje("No se pudieron consultar los productos de MySQL.", "error");

    }

}

// ACTUALIZAR RESUMEN Y TABLA
function mostrarProductos() {

    const busqueda = $("buscarProducto").value.toLowerCase().trim();

    const filtrados = productos.filter(p =>
        String(p.nombre).toLowerCase().includes(busqueda) ||
        String(p.marca).toLowerCase().includes(busqueda)
    );

    $("tablaProductos").innerHTML = "";

    filtrados.forEach(p => {

        const fila = document.createElement("tr");
        const celdaImagen = document.createElement("td");

        if (p.imagen) {

            const img = document.createElement("img");

            img.src = p.imagen;
            img.alt = p.nombre;
            img.className = "table-product-image";

            celdaImagen.appendChild(img);

        }

        fila.appendChild(celdaImagen);

        [p.nombre, p.marca, p.almacenamiento, precioCOP(p.precio), p.stock]
            .forEach(dato => {

                const celda = document.createElement("td");

                celda.textContent = dato;

                fila.appendChild(celda);

            });

        const acciones = document.createElement("td");

        acciones.className = "actions";

        const editar = document.createElement("button");

        editar.textContent = "Editar";
        editar.className = "edit-btn";
        editar.onclick = () => editarProducto(p.id);

        const eliminar = document.createElement("button");

        eliminar.textContent = "Eliminar";
        eliminar.className = "delete-btn";
        eliminar.onclick = () => eliminarProducto(p.id);

        acciones.append(editar, eliminar);

        fila.appendChild(acciones);

        $("tablaProductos").appendChild(fila);

    });

    $("sinProductos").hidden = filtrados.length > 0;

    $("sinProductos").textContent = productos.length
        ? "No se encontraron productos con esa búsqueda."
        : "No hay productos registrados.";

    $("totalProductos").textContent = productos.length;

    $("totalUnidades").textContent = productos.reduce(
        (s, p) => s + Number(p.stock), 0
    );

    $("productosAgotados").textContent = productos.filter(
        p => Number(p.stock) === 0
    ).length;

}

// GUARDAR O ACTUALIZAR
formulario.addEventListener("submit", async e => {

    e.preventDefault();

    const id = $("productoId").value;

    const anterior = productos.find(
        p => Number(p.id) === Number(id)
    );

    const archivo = $("imagenProducto").files[0];

    if (!anterior && !archivo) {
        mensaje("Selecciona una fotografía para el producto.", "error");
        return;
    }

    // OBTENER MARCA SELECCIONADA O PERSONALIZADA
    const marcaSeleccionada = $("marca").value === "Otra"
        ? $("nuevaMarca").value.trim()
        : $("marca").value;

    if (!marcaSeleccionada) {
        mensaje("Debes escribir el nombre de la nueva marca.", "error");
        $("nuevaMarca").focus();
        return;
    }

    if (marcaSeleccionada.length > 50) {
        mensaje("La marca no puede superar los 50 caracteres.", "error");
        return;
    }

    let imagen = anterior?.imagen || "";

    try {

        if (archivo) {

            if (archivo.size > LIMITE_IMAGEN) {
                throw new Error("La imagen supera los 700 KB.");
            }

            if (!["image/jpeg", "image/png", "image/webp"].includes(archivo.type)) {
                throw new Error("Formato de imagen no permitido.");
            }

            imagen = await leerImagen(archivo);

        }

        const datos = Object.fromEntries(
            campos.map(c => [c, $(c).value.trim()])
        );

        // REEMPLAZAR EL VALOR "OTRA" POR LA MARCA ESCRITA
        datos.marca = marcaSeleccionada;

        datos.accion = "guardar";
        datos.id = id || null;
        datos.precio = Number(datos.precio);
        datos.stock = Number(datos.stock);
        datos.imagen = imagen;

        if (
            !datos.nombre ||
            !datos.marca ||
            !datos.almacenamiento ||
            !Number.isFinite(datos.precio) ||
            datos.precio <= 0 ||
            !Number.isInteger(datos.stock) ||
            datos.stock < 0
        ) {
            throw new Error("Revisa los datos ingresados.");
        }

        $("guardarBtn").disabled = true;

        const respuesta = await fetch("admin.php", {

            method: "POST",

            headers: {
                "Content-Type": "application/json"
            },

            body: JSON.stringify(datos)

        });

        const resultado = await respuesta.json();

        if (!respuesta.ok || !resultado.success) {
            throw new Error(resultado.error || "No se pudo guardar el producto.");
        }

        await cargarProductos();

        limpiarFormulario();

        mensaje(
            id
                ? "Producto actualizado correctamente."
                : "Producto registrado correctamente."
        );

    } catch (error) {

        mensaje(
            error.message || "Error al guardar el producto en MySQL.",
            "error"
        );

    } finally {

        $("guardarBtn").disabled = false;

    }

});

// EDITAR
function editarProducto(id) {

    const p = productos.find(
        item => Number(item.id) === Number(id)
    );

    if (!p) return;

    $("productoId").value = p.id;

    $("nombre").value = p.nombre;

    // COMPROBAR SI LA MARCA EXISTE EN LA LISTA
    const marcas = [
        "Apple",
        "Samsung",
        "Xiaomi",
        "Motorola",
        "Honor"
    ];

    if (marcas.includes(p.marca)) {

        $("marca").value = p.marca;

        $("nuevaMarcaGrupo").hidden = true;
        $("nuevaMarca").required = false;
        $("nuevaMarca").value = "";

    } else {

        // SI ES UNA MARCA PERSONALIZADA, MOSTRARLA
        $("marca").value = "Otra";

        $("nuevaMarcaGrupo").hidden = false;
        $("nuevaMarca").required = true;
        $("nuevaMarca").value = p.marca;

    }

    $("almacenamiento").value = p.almacenamiento;
    $("precio").value = p.precio;
    $("stock").value = p.stock;

    $("imagenProducto").value = "";

    $("vistaPrevia").src = p.imagen || "";
    $("vistaPrevia").hidden = !p.imagen;
    $("textoVistaPrevia").hidden = !!p.imagen;

    $("tituloFormulario").textContent = "Editar producto";
    $("guardarBtn").textContent = "Actualizar producto";
    $("cancelarEdicion").hidden = false;

    mensaje("Puedes modificar los datos del producto.");

    window.scrollTo({
        top: 0,
        behavior: "smooth"
    });

}

// ELIMINAR
async function eliminarProducto(id) {

    if (!confirm("¿Estás seguro de que deseas eliminar este producto?")) {
        return;
    }

    try {

        const respuesta = await fetch("admin.php", {

            method: "POST",

            headers: {
                "Content-Type": "application/json"
            },

            body: JSON.stringify({
                accion: "eliminar",
                id: Number(id)
            })

        });

        const resultado = await respuesta.json();

        if (!respuesta.ok || !resultado.success) {
            throw new Error();
        }

        await cargarProductos();

        mensaje("Producto eliminado correctamente.");

    } catch {

        mensaje(
            "No se pudo eliminar el producto. Comprueba si tiene ventas registradas.",
            "error"
        );

    }

}

// LIMPIAR FORMULARIO
function limpiarFormulario() {

    formulario.reset();

    $("productoId").value = "";

    $("vistaPrevia").src = "";
    $("vistaPrevia").hidden = true;

    $("textoVistaPrevia").hidden = false;

    // OCULTAR CAMPO DE NUEVA MARCA
    $("nuevaMarcaGrupo").hidden = true;
    $("nuevaMarca").required = false;
    $("nuevaMarca").value = "";

    $("tituloFormulario").textContent = "Registrar producto";

    $("guardarBtn").textContent = "Guardar producto";

    $("cancelarEdicion").hidden = true;

}

// CANCELAR EDICIÓN
$("cancelarEdicion").addEventListener("click", () => {

    limpiarFormulario();

    mensaje("Edición cancelada.");

});

// BUSCAR PRODUCTOS
$("buscarProducto").addEventListener("input", mostrarProductos);

// CERRAR SESIÓN
$("cerrarSesion").addEventListener("click", () => {

    window.location.href = "cerrar_sesion.php";

});

// INICIAR PANEL
cargarProductos();

</script>

</body>
</html>
