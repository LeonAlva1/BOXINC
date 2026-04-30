<?php
// articulos.php — ABM de artículos
// Tabla: articulo (id_articulo, nom_articulo, precio, id_fabricacion)
// Relación: articulo → fabricacion

require_once "conexion.php";

$accion = isset($_GET['accion']) ? $_GET['accion'] : 'lista';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$msg    = '';
$tipo   = '';

// ===== GUARDAR =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom    = $BD->real_escape_string(trim($_POST['nom_articulo']));
    $precio = (float)$_POST['precio'];
    $id_fab = (int)$_POST['id_fabricacion'];

    if (empty($nom)) {
        $msg  = "El nombre del artículo es obligatorio.";
        $tipo = "error";
    } elseif ($precio < 0) {
        $msg  = "El precio no puede ser negativo.";
        $tipo = "error";
    } else {
        $id_fab_sql = ($id_fab > 0) ? $id_fab : 'NULL';

        if (isset($_POST['id_articulo']) && (int)$_POST['id_articulo'] > 0) {
            $idE = (int)$_POST['id_articulo'];
            $BD->query("UPDATE articulo SET nom_articulo='$nom', precio=$precio, id_fabricacion=$id_fab_sql WHERE id_articulo=$idE");
            $msg  = "Artículo actualizado correctamente.";
            $tipo = "exito";
        } else {
            $BD->query("INSERT INTO articulo (nom_articulo, precio, id_fabricacion) VALUES ('$nom', $precio, $id_fab_sql)");
            $msg  = "Artículo creado correctamente.";
            $tipo = "exito";
        }
        $accion = 'lista';
    }
}

// ===== ELIMINAR =====
if ($accion === 'eliminar' && $id > 0) {
    // Verificamos si tiene órdenes asociadas
    $check = $BD->query("SELECT COUNT(*) AS c FROM orden WHERE id_articulo=$id")->fetch_assoc();
    if ($check['c'] > 0) {
        $msg  = "No se puede eliminar: este artículo tiene órdenes registradas.";
        $tipo = "error";
    } else {
        $BD->query("DELETE FROM articulo WHERE id_articulo=$id");
        $msg  = "Artículo eliminado.";
        $tipo = "exito";
    }
    $accion = 'lista';
}

// ===== CARGAR PARA EDITAR =====
$editar = null;
if ($accion === 'editar' && $id > 0) {
    $editar = $BD->query("SELECT * FROM articulo WHERE id_articulo=$id")->fetch_assoc();
}

// ===== DATOS PARA EL SELECT DE FABRICACIONES =====
$fabricaciones = $BD->query("SELECT * FROM fabricacion ORDER BY fecha_fab DESC");

// ===== LISTADO (con JOIN para mostrar fechas de fabricacion) =====
$articulos = $BD->query("
    SELECT a.*, f.fecha_fab, f.Vencimiento
    FROM articulo a
    LEFT JOIN fabricacion f ON a.id_fabricacion = f.id_fabricacion
    ORDER BY a.nom_articulo
");

$hoy = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>BOXINC — Artículos</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>

<header>
    <a href="index.php" class="logo">BOX<span>INC</span></a>
    <nav>
        <a href="ubicaciones.php">Ubicaciones</a>
        <a href="fabricaciones.php">Fabricaciones</a>
        <a href="clientes.php">Clientes</a>
        <a href="articulos.php" class="activo">Artículos</a>
        <a href="ordenes.php">Órdenes</a>
    </nav>
</header>

<div class="banner">
    <h1>Artículos</h1>
    <p>BOXINC · Catálogo de medicamentos e insumos ortopédicos</p>
</div>

<div class="contenedor">

    <?php if ($msg): ?>
    <div class="mensaje <?= $tipo ?>"><?= $msg ?></div>
    <?php endif; ?>

    <div class="barra-seccion">
        <h3>Lista de artículos</h3>
        <a href="articulos.php?accion=nuevo" class="btn btn-principal">+ Nuevo artículo</a>
    </div>

    <!-- ===== FORMULARIO ===== -->
    <?php if ($accion === 'nuevo' || $accion === 'editar'): ?>
    <div class="tarjeta">
        <h2><?= $accion === 'editar' ? 'Editar artículo' : 'Nuevo artículo' ?></h2>

        <form method="POST" action="articulos.php">
            <?php if ($editar): ?>
            <input type="hidden" name="id_articulo" value="<?= $editar['id_articulo'] ?>">
            <?php endif; ?>

            <div class="fila-campos">
                <div class="campo" style="flex: 2;">
                    <label>Nombre del artículo *</label>
                    <input type="text" name="nom_articulo" placeholder="Ej: Ibuprofeno 400mg"
                        value="<?= htmlspecialchars($editar['nom_articulo'] ?? '') ?>" required>
                </div>

                <div class="campo">
                    <label>Precio por caja ($) *</label>
                    <input type="number" name="precio" step="0.01" min="0" placeholder="0.00"
                        value="<?= $editar['precio'] ?? '' ?>" required>
                </div>
            </div>

            <div class="campo">
                <label>Lote de fabricación</label>
                <select name="id_fabricacion">
                    <option value="0">— Sin lote asignado —</option>
                    <?php while ($f = $fabricaciones->fetch_assoc()): ?>
                    <option value="<?= $f['id_fabricacion'] ?>"
                        <?= (isset($editar) && $editar['id_fabricacion'] == $f['id_fabricacion']) ? 'selected' : '' ?>>
                        Lote #<?= $f['id_fabricacion'] ?>
                        · Fabricado: <?= $f['fecha_fab'] ?? '—' ?>
                        · Vence: <?= $f['Vencimiento'] ?? '—' ?>
                    </option>
                    <?php endwhile; ?>
                </select>
                <small style="color:#888; font-size:12px;">
                    ¿No existe el lote? <a href="fabricaciones.php?accion=nuevo">Crear fabricación</a>
                </small>
            </div>

            <div class="botones">
                <button type="submit" class="btn btn-principal">
                    <?= $accion === 'editar' ? '💾 Guardar cambios' : '✔ Crear artículo' ?>
                </button>
                <a href="articulos.php" class="btn btn-borde">Cancelar</a>
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
                    <th>Nombre del artículo</th>
                    <th>Precio / caja</th>
                    <th>Fecha fab.</th>
                    <th>Vencimiento</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($articulos->num_rows === 0): ?>
                <tr>
                    <td colspan="6" class="sin-datos">No hay artículos registrados todavía.</td>
                </tr>
                <?php else: ?>
                <?php while ($a = $articulos->fetch_assoc()): ?>
                <tr>
                    <td><?= $a['id_articulo'] ?></td>
                    <td><strong><?= htmlspecialchars($a['nom_articulo']) ?></strong></td>
                    <td>$<?= number_format($a['precio'], 2, ',', '.') ?></td>
                    <td><?= $a['fecha_fab'] ?? '—' ?></td>
                    <td>
                        <?php
                        // Mostramos el vencimiento con color según si venció o no
                        $vto = $a['Vencimiento'];
                        if (!$vto) {
                            echo '—';
                        } elseif ($vto < $hoy) {
                            echo '<span class="badge badge-rojo">Vencido · ' . $vto . '</span>';
                        } else {
                            echo '<span class="badge badge-verde">' . $vto . '</span>';
                        }
                        ?>
                    </td>
                    <td>
                        <a href="articulos.php?accion=editar&id=<?= $a['id_articulo'] ?>" class="btn btn-secundario btn-chico">✏ Editar</a>
                        <a href="articulos.php?accion=eliminar&id=<?= $a['id_articulo'] ?>"
                           class="btn btn-principal btn-chico"
                           onclick="return confirm('¿Eliminar este artículo?')">
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
