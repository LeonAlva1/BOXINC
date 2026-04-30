<?php
// fabricaciones.php — ABM de fabricaciones
// Tabla: fabricacion (id_fabricacion, fecha_fab, Vencimiento)

require_once "conexion.php";

$accion = isset($_GET['accion']) ? $_GET['accion'] : 'lista';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$msg    = '';
$tipo   = '';

// ===== GUARDAR =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha_fab   = $BD->real_escape_string($_POST['fecha_fab']);
    $vencimiento = $BD->real_escape_string($_POST['Vencimiento']);

    // Validamos que las fechas no estén vacías
    if (empty($fecha_fab) || empty($vencimiento)) {
        $msg  = "Ambas fechas son obligatorias.";
        $tipo = "error";
    } elseif ($vencimiento <= $fecha_fab) {
        // El vencimiento tiene que ser posterior a la fabricación
        $msg  = "La fecha de vencimiento debe ser posterior a la fecha de fabricación.";
        $tipo = "error";
    } else {
        if (isset($_POST['id_fabricacion']) && (int)$_POST['id_fabricacion'] > 0) {
            $idE = (int)$_POST['id_fabricacion'];
            $BD->query("UPDATE fabricacion SET fecha_fab='$fecha_fab', Vencimiento='$vencimiento' WHERE id_fabricacion=$idE");
            $msg  = "Fabricación actualizada.";
            $tipo = "exito";
        } else {
            $BD->query("INSERT INTO fabricacion (fecha_fab, Vencimiento) VALUES ('$fecha_fab', '$vencimiento')");
            $msg  = "Fabricación registrada correctamente.";
            $tipo = "exito";
        }
        $accion = 'lista';
    }
}

// ===== ELIMINAR =====
if ($accion === 'eliminar' && $id > 0) {
    // Verificamos si hay artículos usando este lote
    $check = $BD->query("SELECT COUNT(*) AS c FROM articulo WHERE id_fabricacion=$id")->fetch_assoc();
    if ($check['c'] > 0) {
        $msg  = "No se puede eliminar: hay artículos que usan este lote de fabricación.";
        $tipo = "error";
    } else {
        $BD->query("DELETE FROM fabricacion WHERE id_fabricacion=$id");
        $msg  = "Fabricación eliminada.";
        $tipo = "exito";
    }
    $accion = 'lista';
}

// ===== CARGAR PARA EDITAR =====
$editar = null;
if ($accion === 'editar' && $id > 0) {
    $editar = $BD->query("SELECT * FROM fabricacion WHERE id_fabricacion=$id")->fetch_assoc();
}

// ===== LISTADO =====
$fabricaciones = $BD->query("SELECT * FROM fabricacion ORDER BY fecha_fab DESC");
$hoy = date('Y-m-d'); // fecha actual para comparar vencimientos
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>BOXINC — Fabricaciones</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>

<header>
    <a href="index.php" class="logo">BOX<span>INC</span></a>
    <nav>
        <a href="ubicaciones.php">Ubicaciones</a>
        <a href="fabricaciones.php" class="activo">Fabricaciones</a>
        <a href="clientes.php">Clientes</a>
        <a href="articulos.php">Artículos</a>
        <a href="ordenes.php">Órdenes</a>
    </nav>
</header>

<div class="banner">
    <h1>Fabricaciones</h1>
    <p>BOXINC · Lotes de producción y fechas de vencimiento</p>
</div>

<div class="contenedor">

    <?php if ($msg): ?>
    <div class="mensaje <?= $tipo ?>"><?= $msg ?></div>
    <?php endif; ?>

    <div class="barra-seccion">
        <h3>Lista de fabricaciones</h3>
        <a href="fabricaciones.php?accion=nuevo" class="btn btn-principal">+ Nueva fabricación</a>
    </div>

    <!-- ===== FORMULARIO ===== -->
    <?php if ($accion === 'nuevo' || $accion === 'editar'): ?>
    <div class="tarjeta">
        <h2><?= $accion === 'editar' ? 'Editar fabricación' : 'Nueva fabricación' ?></h2>

        <form method="POST" action="fabricaciones.php">
            <?php if ($editar): ?>
            <input type="hidden" name="id_fabricacion" value="<?= $editar['id_fabricacion'] ?>">
            <?php endif; ?>

            <div class="fila-campos">
                <div class="campo">
                    <label>Fecha de fabricación *</label>
                    <input type="date" name="fecha_fab"
                        value="<?= $editar['fecha_fab'] ?? '' ?>" required>
                </div>
                <div class="campo">
                    <label>Fecha de vencimiento *</label>
                    <input type="date" name="Vencimiento"
                        value="<?= $editar['Vencimiento'] ?? '' ?>" required>
                </div>
            </div>

            <div class="botones">
                <button type="submit" class="btn btn-principal">
                    <?= $accion === 'editar' ? '💾 Guardar cambios' : '✔ Registrar lote' ?>
                </button>
                <a href="fabricaciones.php" class="btn btn-borde">Cancelar</a>
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
                    <th>Fecha de fabricación</th>
                    <th>Vencimiento</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($fabricaciones->num_rows === 0): ?>
                <tr>
                    <td colspan="5" class="sin-datos">No hay fabricaciones registradas todavía.</td>
                </tr>
                <?php else: ?>
                <?php while ($f = $fabricaciones->fetch_assoc()): ?>
                <tr>
                    <td><?= $f['id_fabricacion'] ?></td>
                    <td><?= $f['fecha_fab'] ?? '—' ?></td>
                    <td><?= $f['Vencimiento'] ?? '—' ?></td>
                    <td>
                        <?php
                        // Mostramos si el lote está vigente o vencido
                        if (empty($f['Vencimiento'])) {
                            echo '—';
                        } elseif ($f['Vencimiento'] < $hoy) {
                            echo '<span class="badge badge-rojo">Vencido</span>';
                        } else {
                            echo '<span class="badge badge-verde">Vigente</span>';
                        }
                        ?>
                    </td>
                    <td>
                        <a href="fabricaciones.php?accion=editar&id=<?= $f['id_fabricacion'] ?>" class="btn btn-secundario btn-chico">✏ Editar</a>
                        <a href="fabricaciones.php?accion=eliminar&id=<?= $f['id_fabricacion'] ?>"
                           class="btn btn-principal btn-chico"
                           onclick="return confirm('¿Eliminar este lote de fabricación?')">
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
