<?php
require __DIR__ . '/config.php';

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$mensaje = '';
$error = '';
$editar = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $nombre = trim($_POST['nombre'] ?? '');
    $marca = trim($_POST['marca'] ?? '');
    $unidadesTexto = trim($_POST['unidades'] ?? '');
    $ubicacion = trim($_POST['ubicacion'] ?? '');

    if ($accion === 'eliminar') {
        if (!$id) {
            $error = 'El artículo seleccionado no es válido.';
        } else {
            $stmt = $pdo->prepare('DELETE FROM articulos WHERE id = ?');
            $stmt->execute([$id]);
            $mensaje = $stmt->rowCount() ? 'Artículo eliminado correctamente.' : 'No se encontró el artículo.';
        }
    } elseif ($accion === 'guardar') {
        if ($nombre === '' || $marca === '' || $ubicacion === '' || $unidadesTexto === '') {
            $error = 'Completa todos los campos.';
        } elseif (!ctype_digit($unidadesTexto) || (int)$unidadesTexto < 0) {
            $error = 'Las unidades deben ser un número entero igual o mayor que cero.';
        } elseif (mb_strlen($nombre) > 150 || mb_strlen($marca) > 100 || mb_strlen($ubicacion) > 150) {
            $error = 'Uno de los campos supera la longitud permitida.';
        } else {
            $unidades = (int)$unidadesTexto;
            if ($id) {
                $stmt = $pdo->prepare('UPDATE articulos SET nombre=?, marca=?, unidades=?, ubicacion=? WHERE id=?');
                $stmt->execute([$nombre, $marca, $unidades, $ubicacion, $id]);
                $mensaje = 'Artículo actualizado correctamente.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO articulos (nombre, marca, unidades, ubicacion) VALUES (?, ?, ?, ?)');
                $stmt->execute([$nombre, $marca, $unidades, $ubicacion]);
                $mensaje = 'Artículo registrado correctamente.';
            }
        }
    }

    if ($error !== '') {
        $editar = [
            'id' => $id ?: '',
            'nombre' => $nombre,
            'marca' => $marca,
            'unidades' => $unidadesTexto,
            'ubicacion' => $ubicacion
        ];
    }
}

if (isset($_GET['editar']) && ctype_digit((string)$_GET['editar'])) {
    $stmt = $pdo->prepare('SELECT * FROM articulos WHERE id = ?');
    $stmt->execute([(int)$_GET['editar']]);
    $editar = $stmt->fetch() ?: null;
}

$busqueda = trim($_GET['buscar'] ?? '');
if ($busqueda !== '') {
    $stmt = $pdo->prepare('SELECT * FROM articulos WHERE nombre LIKE ? OR marca LIKE ? OR ubicacion LIKE ? ORDER BY id DESC');
    $term = '%' . $busqueda . '%';
    $stmt->execute([$term, $term, $term]);
    $articulos = $stmt->fetchAll();
} else {
    $articulos = $pdo->query('SELECT * FROM articulos ORDER BY id DESC')->fetchAll();
}
$totalArticulos = (int)$pdo->query('SELECT COUNT(*) FROM articulos')->fetchColumn();
$totalUnidades = (int)$pdo->query('SELECT COALESCE(SUM(unidades),0) FROM articulos')->fetchColumn();

if (!$editar) {
    $editar = ['id'=>'', 'nombre'=>'', 'marca'=>'', 'unidades'=>'', 'ubicacion'=>''];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de artículos</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<header class="topbar">
    <div class="brand-mark">A</div>
    <div><h1>Gestión de artículos</h1><p>Inventario sencillo y organizado</p></div>
</header>
<main class="container">
    <section class="stats">
        <article class="stat"><span>Artículos registrados</span><strong><?= $totalArticulos ?></strong></article>
        <article class="stat"><span>Unidades en inventario</span><strong><?= $totalUnidades ?></strong></article>
    </section>

    <?php if ($mensaje): ?><div class="alert success"><?= h($mensaje) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>

    <div class="layout">
        <section class="panel form-panel">
            <div class="panel-heading"><h2><?= $editar['id'] ? 'Editar artículo' : 'Registrar artículo' ?></h2><p>Completa los datos del artículo.</p></div>
            <form method="post" action="index.php" autocomplete="off">
                <input type="hidden" name="accion" value="guardar">
                <input type="hidden" name="id" value="<?= h($editar['id']) ?>">
                <label for="nombre">Nombre</label>
                <input id="nombre" name="nombre" maxlength="150" required value="<?= h($editar['nombre']) ?>" placeholder="Ej. Taladro eléctrico">
                <label for="marca">Marca</label>
                <input id="marca" name="marca" maxlength="100" required value="<?= h($editar['marca']) ?>" placeholder="Ej. Bosch">
                <label for="unidades">Unidades</label>
                <input id="unidades" name="unidades" type="number" min="0" step="1" required value="<?= h($editar['unidades']) ?>" placeholder="Ej. 12">
                <label for="ubicacion">Ubicación</label>
                <input id="ubicacion" name="ubicacion" maxlength="150" required value="<?= h($editar['ubicacion']) ?>" placeholder="Ej. Bodega A, estante 2">
                <button class="btn primary full" type="submit"><?= $editar['id'] ? 'Guardar cambios' : 'Agregar artículo' ?></button>
                <?php if ($editar['id']): ?><a class="btn secondary full link-button" href="index.php">Cancelar edición</a><?php endif; ?>
            </form>
        </section>

        <section class="panel list-panel">
            <div class="panel-heading"><h2>Inventario</h2><p>Consulta, busca, modifica o elimina registros.</p></div>
            <form class="search" method="get" action="index.php">
                <input type="search" name="buscar" value="<?= h($busqueda) ?>" placeholder="Buscar por nombre, marca o ubicación" aria-label="Buscar artículos">
                <button class="btn primary" type="submit">Buscar</button>
                <?php if ($busqueda !== ''): ?><a class="btn secondary" href="index.php">Limpiar</a><?php endif; ?>
            </form>
            <?php if (!$articulos): ?>
                <div class="empty"><span>📦</span><strong>No hay artículos para mostrar</strong><p>Registra un artículo o cambia la búsqueda.</p></div>
            <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Artículo</th><th>Marca</th><th>Unidades</th><th>Ubicación</th><th>Acciones</th></tr></thead>
                    <tbody>
                    <?php foreach ($articulos as $articulo): ?>
                        <tr>
                            <td data-label="Artículo"><strong><?= h($articulo['nombre']) ?></strong><small>ID: <?= (int)$articulo['id'] ?></small></td>
                            <td data-label="Marca"><?= h($articulo['marca']) ?></td>
                            <td data-label="Unidades"><span class="quantity"><?= (int)$articulo['unidades'] ?></span></td>
                            <td data-label="Ubicación"><?= h($articulo['ubicacion']) ?></td>
                            <td data-label="Acciones"><div class="actions">
                                <a class="btn small secondary" href="index.php?editar=<?= (int)$articulo['id'] ?>">Editar</a>
                                <form method="post" onsubmit="return confirm('¿Seguro que deseas eliminar este artículo?');">
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="id" value="<?= (int)$articulo['id'] ?>">
                                    <button class="btn small danger" type="submit">Eliminar</button>
                                </form>
                            </div></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </section>
    </div>
    <footer>Gestión de artículos · PHP + MySQL</footer>
</main>
</body>
</html>
