<?php

// Pruebas automatizadas del sistema de inventario

function cargarProductos($archivo) {
    if (!file_exists($archivo)) return [];
    $datos = json_decode(file_get_contents($archivo), true);
    return is_array($datos) ? $datos : [];
}

function guardarProductos($archivo, $productos) {
    $json = json_encode(
        $productos,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    );

    return file_put_contents($archivo, $json, LOCK_EX) !== false;
}

$archivo = tempnam(sys_get_temp_dir(), 'inventario_test_');

$pruebas = 0;
$aprobadas = 0;

function verificar($nombre, $resultado) {
    global $pruebas, $aprobadas;

    $pruebas++;

    if ($resultado) {
        echo "[OK] $nombre\n";
        $aprobadas++;
    } else {
        echo "[ERROR] $nombre\n";
    }
}

// Prueba 1: inventario inicialmente vacío
$productos = cargarProductos($archivo);
verificar("Inventario inicial vacío", $productos === []);

// Prueba 2: guardar un producto
$productos[] = [
    "id" => 1,
    "nombre" => "Producto de prueba",
    "categoria" => "General",
    "stock" => 3,
    "minimo" => 5
];

verificar("Guardar producto", guardarProductos($archivo, $productos));

// Prueba 3: cargar producto guardado
$cargados = cargarProductos($archivo);
verificar("Cargar producto guardado", count($cargados) === 1);

// Prueba 4: detectar stock bajo
$bajoStock = $cargados[0]["stock"] <= $cargados[0]["minimo"];
verificar("Detectar producto con bajo stock", $bajoStock);

// Prueba 5: actualizar producto
$cargados[0]["stock"] = 10;
guardarProductos($archivo, $cargados);
$actualizados = cargarProductos($archivo);

verificar("Actualizar stock del producto", $actualizados[0]["stock"] === 10);

// Prueba 6: eliminar producto
$actualizados = [];
guardarProductos($archivo, $actualizados);
$final = cargarProductos($archivo);

verificar("Eliminar producto", $final === []);

// Resumen
echo "\nResultado: $aprobadas de $pruebas pruebas aprobadas.\n";

unlink($archivo);

if ($aprobadas !== $pruebas) {
    exit(1);
}

echo "Todas las pruebas finalizaron correctamente.\n";
