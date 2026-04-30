<?php
// ordenes.php — ABM de órdenes
// Tabla: orden (id_orden, fecha, cantidad, id_cliente, id_articulo)
// Nota: la tabla "orden" tiene la cantidad directamente en la fila
// (a diferencia de otros sistemas que usan una tabla detalle separada)

require_once "conexion.php";

$accion = isset($_GET['accion']) ? $_GET['accion'] : 'lista';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$msg    = '';
$tipo   = '';

// Cantidad mínima según regla de negocio
define('MIN_CAJAS', 10);

// ===== GUARDAR =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha      = $BD->real_escape_string($_POST['fecha']);
    $cantidad   = (int)$_POST['cantidad'];
    $id_cliente = (int)$_POST['id_cliente'];
    $id_articulo= (int)$_POST['id_articulo'];

    // Validaciones
    $errores = [];
    if (empty($fecha))         $errores[] = "La fecha es obligatoria.";
    if ($id_cliente <= 0)      $errores[] = "Seleccioná un cliente.";
    if ($id_articulo <= 0)     $errores[] = "Seleccioná un artículo.";
    if ($cantidad < MIN_CAJAS) $errores[] = "La cantidad mínima es " . MIN_CAJAS . " cajas.";

    if (!empty($errores)) {
        $msg  = implode('<br>', $errores);
        $tipo = "error";
        // Mantenemos la acción para que el formulario quede visible
        $accion = isset($_POST['id_orden']) && (int)$_POST['id_orden'] > 0 ? 'editar' : 'nuevo';
    } else {
        if (isset($_POST['id_orden']) && (int)$_POST['id_orden'] > 0) {
            $idE = (int)$_POST['id_orden'];
            $BD->query("UPDATE orden SET fecha='$fecha', cantidad=$cantidad, id_cliente=$id_cliente, id_articulo=$id_articulo WHERE id_orden=$idE");
            $msg  = "Orden actualizada correctamente.";
            $tipo = "exito";
        } else {
            $BD->query("INSERT INTO orden (fecha, cantidad, id_cliente, id_articulo) VALUES ('$fecha', $cantidad, $id_cliente, $id_articulo)");
            $msg  = "Orden registrada correctamente.";
            $tipo = "exito";
        }
        $accion = 'lista';
    }
}

// ===== ELIMINAR =====
if ($accion === 'eliminar' && $id > 0) {
    $BD->query("DELETE FROM orden WHERE id_orden=$id");
    $msg  = "Orden eliminada.";
    $tipo = "exito";
    $accion = 'lista';
}

// ===== CARGAR PARA EDITAR =====
$editar = null;
if ($accion === 'editar' && $id > 0) {
    $editar = $BD->query("SELECT * FROM orden WHERE id_orden=$id")->fetch_assoc();
}

// ===== DATOS PARA SELECTS =====
$clientes  = $BD->query("SELECT * FROM cliente ORDER BY nom_cliente");
$articulos = $BD->query("SELECT a.*, f.Vencimiento FROM articulo a LEFT JOIN fabricacion f ON a.id_fabricacion = f.id_fabricacion ORDER BY a.nom_articulo");

// ===== LISTADO (con JOINs para mostrar nombres) =====
$ordenes = $BD->query("
    SELECT o.*, c.nom_cliente, a.nom_articulo, a.precio,
           (o.cantidad * a.precio) AS subtotal
    FROM orden o
    LEFT JOIN cliente c  ON o.id_cliente  = c.id_cliente
    LEFT JOIN articulo a ON o.id_articulo = a.id_articulo
    ORDER BY o.fecha DESC, o.id_orden DESC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>BOXINC — Órdenes</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>

<header>
    <a href="index.php" class="logo">BOX<span>INC</span></a>
    <nav>
        <a href="ubicaciones.php">Ubicaciones</a>
        <a href="fabricaciones.php">Fabricaciones</a>
        <a href="clientes.php">Clientes</a>
        <a href="articulos.php">Artículos</a>
        <a href="ordenes.php" class="activo">Órdenes</a>
    </nav>
</header>

<div class="banner">
    <h1>Órdenes de Venta</h1>
    <p>BOXINC · Mínimo <?= MIN_CAJAS ?> cajas por orden</p>
</div>

<div class="contenedor">

    <?php if ($msg): ?>
    <div class="mensaje <?= $tipo ?>"><?= nl2br($msg) ?></div>
    <?php endif; ?>

    <!-- Aviso de cantidad mínima -->
    <div class="mensaje aviso">
        📦 Regla mayorista: la cantidad mínima por orden es de <strong><?= MIN_CAJAS ?> cajas</strong>.
        Solo se atiende a clientes institucionales registrados.
    </div>

    <div class="barra-seccion">
        <h3>Lista de órdenes</h3>
        <a href="ordenes.php?accion=nuevo" class="btn btn-principal">+ Nueva orden</a>
    </div>

    <!-- ===== FORMULARIO ===== -->
    <?php if ($accion === 'nuevo' || $accion === 'editar'): ?>
    <div class="tarjeta">
        <h2><?= $accion === 'editar' ? 'Editar orden' : 'Nueva orden' ?></h2>

        <form method="POST" action="ordenes.php">
            <?php if ($editar): ?>
            <input type="hidden" name="id_orden" value="<?= $editar['id_orden'] ?>">
            <?php endif; ?>

            <div class="fila-campos">
                <!-- Fecha -->
                <div class="campo">
                    <label>Fecha *</label>
                    <input type="date" name="fecha"
                        value="<?= $editar['fecha'] ?? date('Y-m-d') ?>" required>
                </div>

                <!-- Cantidad -->
                <div class="campo">
                    <label>Cantidad de cajas * (mín. <?= MIN_CAJAS ?>)</label>
                    <input type="number" name="cantidad"
                        min="<?= MIN_CAJAS ?>"
                        value="<?= $editar['cantidad'] ?? MIN_CAJAS ?>" required>
                </div>
            </div>

            <div class="fila-campos">
                <!-- Cliente -->
                <div class="campo">
                    <label>Cliente *</label>
                    <select name="id_cliente" required>
                        <option value="">— Seleccioná un cliente —</option>
                        <?php while ($c = $clientes->fetch_assoc()): ?>
                        <option value="<?= $c['id_cliente'] ?>"
                            <?= (isset($editar) && $editar['id_cliente'] == $c['id_cliente']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['nom_cliente']) ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                    <small style="color:#888; font-size:12px;">
                        ¿No está? <a href="clientes.php?accion=nuevo">Crear cliente</a>
                    </small>
                </div>

                <!-- Artículo -->
                <div class="campo">
                    <label>Artículo *</label>
                    <select name="id_articulo" required>
                        <option value="">— Seleccioná un artículo —</option>
                        <?php while ($a = $articulos->fetch_assoc()): ?>
                        <option value="<?= $a['id_articulo'] ?>"
                            <?= (isset($editar) && $editar['id_articulo'] == $a['id_articulo']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($a['nom_articulo']) ?>
                            · $<?= number_format($a['precio'], 2, ',', '.') ?>/caja
                        </option>
                        <?php endwhile; ?>
                    </select>
                    <small style="color:#888; font-size:12px;">
                        ¿No está? <a href="articulos.php?accion=nuevo">Crear artículo</a>
                    </small>
                </div>
            </div>

            <div class="botones">
                <button type="submit" class="btn btn-principal">
                    <?= $accion === 'editar' ? '💾 Guardar cambios' : '✔ Registrar orden' ?>
                </button>
                <a href="ordenes.php" class="btn btn-borde">Cancelar</a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- ===== TABLA ===== -->
    <div class="tabla-contenedor">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Artículo</th>
                    <th>Cantidad</th>
                    <th>Precio/caja</th>
                    <th>Subtotal</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($ordenes->num_rows === 0): ?>
                <tr>
                    <td colspan="8" class="sin-datos">No hay órdenes registradas todavía.</td>
                </tr>
                <?php else: ?>
                <?php while ($o = $ordenes->fetch_assoc()): ?>
                <tr>
                    <td>#<?= $o['id_orden'] ?></td>
                    <td><?= $o['fecha'] ?></td>
                    <td><strong><?= htmlspecialchars($o['nom_cliente'] ?? '—') ?></strong></td>
                    <td><?= htmlspecialchars($o['nom_articulo'] ?? '—') ?></td>
                    <td><?= $o['cantidad'] ?> cajas</td>
                    <td>$<?= $o['precio'] ? number_format($o['precio'], 2, ',', '.') : '—' ?></td>
                    <td>
                        <?php if ($o['subtotal']): ?>
                        <strong>$<?= number_format($o['subtotal'], 2, ',', '.') ?></strong>
                        <?php else: ?>
                        —
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="ordenes.php?accion=editar&id=<?= $o['id_orden'] ?>" class="btn btn-secundario btn-chico">✏ Editar</a>
                        <a href="ordenes.php?accion=eliminar&id=<?= $o['id_orden'] ?>"
                           class="btn btn-principal btn-chico"
                           onclick="return confirm('¿Eliminar esta orden?')">
                           🗑 Eliminar
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<footer>
    <span>BOXINC</span> · Sistema Interno de Gestión
</footer>

</body>
</html>
