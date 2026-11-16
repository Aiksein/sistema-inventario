
<?php
session_start();

$archivo = '/var/www/inventario-data/products.json';

if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

function cargarProductos($archivo) {
    if (!file_exists($archivo)) return [];
    $datos = json_decode(file_get_contents($archivo), true);
    return is_array($datos) ? $datos : [];
}

function guardarProductos($archivo, $productos) {
    $json = json_encode($productos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents($archivo, $json, LOCK_EX) !== false;
}

function escapar($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

$productos = cargarProductos($archivo);
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        http_response_code(403);
        exit('Solicitud no válida.');
    }

    $accion = $_POST['accion'] ?? '';

    if ($accion === 'agregar') {
        $nombre = trim($_POST['nombre'] ?? '');
        $categoria = trim($_POST['categoria'] ?? '');
        $stock = filter_var($_POST['stock'] ?? null, FILTER_VALIDATE_INT);
        $minimo = filter_var($_POST['minimo'] ?? null, FILTER_VALIDATE_INT);

        if ($nombre === '' || $categoria === '' || $stock === false ||
            $minimo === false || $stock < 0 || $minimo < 0) {
            $mensaje = 'Completa correctamente todos los campos.';
        } else {
            $productos[] = [
                'id' => bin2hex(random_bytes(8)),
                'nombre' => $nombre,
                'categoria' => $categoria,
                'stock' => $stock,
                'minimo' => $minimo
            ];
            $mensaje = guardarProductos($archivo, $productos)
                ? 'Producto agregado correctamente.'
                : 'No se pudo guardar el producto.';
        }
    }

    if ($accion === 'editar') {
        $id = $_POST['id'] ?? '';
        foreach ($productos as &$producto) {
            if ($producto['id'] === $id) {
                $nombre = trim($_POST['nombre'] ?? '');
                $categoria = trim($_POST['categoria'] ?? '');
                $stock = filter_var($_POST['stock'] ?? null, FILTER_VALIDATE_INT);
                $minimo = filter_var($_POST['minimo'] ?? null, FILTER_VALIDATE_INT);

                if ($nombre !== '' && $categoria !== '' &&
                    $stock !== false && $minimo !== false &&
                    $stock >= 0 && $minimo >= 0) {
                    $producto['nombre'] = $nombre;
                    $producto['categoria'] = $categoria;
                    $producto['stock'] = $stock;
                    $producto['minimo'] = $minimo;
                    $mensaje = 'Producto actualizado correctamente.';
                } else {
                    $mensaje = 'Los datos ingresados no son válidos.';
                }
                break;
            }
        }
        unset($producto);
        if ($mensaje === '') $mensaje = 'Producto no encontrado.';
        if (str_contains($mensaje, 'actualizado')) {
            if (!guardarProductos($archivo, $productos)) {
                $mensaje = 'No se pudo guardar el cambio.';
            }
        }
    }

    if ($accion === 'eliminar') {
        $id = $_POST['id'] ?? '';
        $cantidadAnterior = count($productos);
        $productos = array_values(array_filter(
            $productos,
            fn($p) => $p['id'] !== $id
        ));

        $mensaje = count($productos) < $cantidadAnterior
            ? (guardarProductos($archivo, $productos)
                ? 'Producto eliminado correctamente.'
                : 'No se pudo guardar el cambio.')
            : 'Producto no encontrado.';
    }

    $_SESSION['mensaje'] = $mensaje;
    header('Location: index.php');
    exit;
}

if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']);
}

$editarId = $_GET['editar'] ?? '';
$productoEditar = null;

foreach ($productos as $p) {
    if ($p['id'] === $editarId) {
        $productoEditar = $p;
        break;
    }
}

$totalProductos = count($productos);
$totalUnidades = array_sum(array_column($productos, 'stock'));
$bajoStock = count(array_filter(
    $productos,
    fn($p) => $p['stock'] <= $p['minimo']
));
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sistema de Inventario</title>
<style>
* { box-sizing: border-box; }
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f1f5f9;
    color: #1e293b;
}
header {
    background: #1e3a8a;
    color: white;
    padding: 25px;
    text-align: center;
}
header h1 { margin: 0 0 8px; }
.contenedor {
    max-width: 1100px;
    margin: 30px auto;
    padding: 0 18px;
}
.resumen {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 15px;
    margin-bottom: 25px;
}
.tarjeta, .panel {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 8px #00000010;
}
.tarjeta h3 { margin: 0 0 10px; color: #64748b; }
.numero { font-size: 30px; font-weight: bold; }
.alerta-numero { color: #dc2626; }
.panel { margin-bottom: 25px; }
input {
    width: 100%;
    padding: 11px;
    margin: 7px 0 15px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
}
button, .boton {
    display: inline-block;
    padding: 10px 15px;
    border: 0;
    border-radius: 6px;
    background: #2563eb;
    color: white;
    text-decoration: none;
    cursor: pointer;
}
.eliminar { background: #dc2626; }
.cancelar { background: #64748b; }
table { width: 100%; border-collapse: collapse; }
th, td {
    padding: 12px;
    border-bottom: 1px solid #e2e8f0;
    text-align: left;
}
th { background: #f8fafc; }
.estado {
    padding: 5px 9px;
    border-radius: 20px;
    font-size: 13px;
}
.bajo { background: #fee2e2; color: #991b1b; }
.normal { background: #dcfce7; color: #166534; }
.mensaje {
    padding: 12px;
    margin-bottom: 20px;
    background: #dbeafe;
    border-radius: 6px;
}
.tabla-contenedor { overflow-x: auto; }
.acciones { display: flex; gap: 7px; }
@media(max-width:600px) {
    .acciones { flex-direction: column; }
}
</style>
</head>
<body>

<header>
    <h1>Sistema de Inventario</h1>
    <p>Administración y control de productos</p>
</header>

<main class="contenedor">

<?php if ($mensaje): ?>
<div class="mensaje"><?= escapar($mensaje) ?></div>
<?php endif; ?>

<section class="resumen">
    <div class="tarjeta">
        <h3>Productos registrados</h3>
        <div class="numero"><?= $totalProductos ?></div>
    </div>
    <div class="tarjeta">
        <h3>Unidades disponibles</h3>
        <div class="numero"><?= $totalUnidades ?></div>
    </div>
    <div class="tarjeta">
        <h3>Productos con bajo stock</h3>
        <div class="numero alerta-numero"><?= $bajoStock ?></div>
    </div>
</section>

<section class="panel">
    <h2><?= $productoEditar ? 'Editar producto' : 'Agregar producto' ?></h2>

    <form method="POST">
        <input type="hidden" name="csrf" value="<?= escapar($_SESSION['csrf']) ?>">
        <input type="hidden" name="accion"
               value="<?= $productoEditar ? 'editar' : 'agregar' ?>">

        <?php if ($productoEditar): ?>
        <input type="hidden" name="id" value="<?= escapar($productoEditar['id']) ?>">
        <?php endif; ?>

        <label>Nombre del producto</label>
        <input type="text" name="nombre" required maxlength="100"
               value="<?= escapar($productoEditar['nombre'] ?? '') ?>">

        <label>Categoría</label>
        <input type="text" name="categoria" required maxlength="80"
               value="<?= escapar($productoEditar['categoria'] ?? '') ?>">

        <label>Cantidad en stock</label>
        <input type="number" name="stock" min="0" required
               value="<?= escapar($productoEditar['stock'] ?? '0') ?>">

        <label>Stock mínimo para alerta</label>
        <input type="number" name="minimo" min="0" required
               value="<?= escapar($productoEditar['minimo'] ?? '5') ?>">

        <button type="submit">
            <?= $productoEditar ? 'Guardar cambios' : 'Agregar producto' ?>
        </button>

        <?php if ($productoEditar): ?>
        <a class="boton cancelar" href="index.php">Cancelar</a>
        <?php endif; ?>
    </form>
</section>

<section class="panel">
    <h2>Inventario de productos</h2>

    <div class="tabla-contenedor">
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Stock</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($productos)): ?>
            <tr><td colspan="5">No hay productos registrados.</td></tr>
        <?php else: ?>
            <?php foreach ($productos as $p): ?>
            <tr>
                <td><?= escapar($p['nombre']) ?></td>
                <td><?= escapar($p['categoria']) ?></td>
                <td><?= escapar($p['stock']) ?></td>
                <td>
                    <?php if ($p['stock'] <= $p['minimo']): ?>
                        <span class="estado bajo">Bajo stock</span>
                    <?php else: ?>
                        <span class="estado normal">Stock normal</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="acciones">
                        <a class="boton" href="?editar=<?= urlencode($p['id']) ?>">
                            Editar
                        </a>
                        <form method="POST" onsubmit="return confirm('¿Eliminar este producto?')">
                            <input type="hidden" name="csrf"
                                   value="<?= escapar($_SESSION['csrf']) ?>">
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id" value="<?= escapar($p['id']) ?>">
                            <button class="eliminar" type="submit">Eliminar</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</section>

</main>
</body>
</html>

