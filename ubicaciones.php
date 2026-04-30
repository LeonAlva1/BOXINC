<?php
// ubicaciones.php — ABM de ubicaciones
// Tablas que usa: ubicacion (id_ubicacion, localidad, ciudad, cod_postal, Direccion, nom_ubicacion)

require_once "conexion.php";

// Leemos la acción de la URL (?accion=nuevo, ?accion=editar&id=1, ?accion=eliminar&id=1)
$accion = isset($_GET['accion']) ? $_GET['accion'] : 'lista';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$msg    = '';       // mensaje de resultado
$tipo   = '';       // tipo de mensaje: exito / error / aviso

// ===== GUARDAR (INSERT o UPDATE) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Limpiamos los datos del formulario
    $nom      = $BD->real_escape_string(trim($_POST['nom_ubicacion']));
    $localidad= $BD->real_escape_string(trim($_POST['localidad']));
    $ciudad   = $BD->real_escape_string(trim($_POST['ciudad']));
    $postal   = $BD->real_escape_string(trim($_POST['cod_postal']));
    $dir      = $BD->real_escape_string(trim($_POST['Direccion']));

    // Validación básica: el nombre es obligatorio
    if (empty($nom)) {
        $msg  = "El nombre de la ubicación es obligatorio.";
        $tipo = "error";
    } else {
        // Si viene un id_ubicacion en el formulario = actualizar
        if (isset($_POST['id_ubicacion']) && (int)$_POST['id_ubicacion'] > 0) {
            $idE = (int)$_POST['id_ubicacion'];
            $BD->query("UPDATE ubicacion SET nom_ubicacion='$nom', localidad='$localidad', ciudad='$ciudad', cod_postal='$postal', Direccion='$dir' WHERE id_ubicacion=$idE");
            $msg  = "Ubicación actualizada correctamente.";
            $tipo = "exito";
        } else {
            // Si no hay id = insertar nuevo registro
            $BD->query("INSERT INTO ubicacion (nom_ubicacion, localidad, ciudad, cod_postal, Direccion) VALUES ('$nom', '$localidad', '$ciudad', '$postal', '$dir')");
            $msg  = "Ubicación creada correctamente.";
            $tipo = "exito";
        }
        $accion = 'lista'; // volvemos al listado
    }
}

// ===== ELIMINAR =====
if ($accion === 'eliminar' && $id > 0) {
    // Verificamos si hay clientes usando esta ubicación
    $check = $BD->query("SELECT COUNT(*) AS c FROM cliente WHERE id_ubicacion=$id")->fetch_assoc();
    if ($check['c'] > 0) {
        $msg  = "No se puede eliminar: hay clientes que usan esta ubicación.";
        $tipo = "error";
    } else {
        $BD->query("DELETE FROM ubicacion WHERE id_ubicacion=$id");
        $msg  = "Ubicación eliminada.";
        $tipo = "exito";
    }
    $accion = 'lista';
}

// ===== CARGAR DATOS PARA EDITAR =====
$editar = null;
if ($accion === 'editar' && $id > 0) {
    $editar = $BD->query("SELECT * FROM ubicacion WHERE id_ubicacion=$id")->fetch_assoc();
}

// ===== TRAER TODAS LAS UBICACIONES PARA EL LISTADO =====
$ubicaciones = $BD->query("SELECT * FROM ubicacion ORDER BY nom_ubicacion");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>BOXINC — Ubicaciones</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>

<header>
    <a href="index.php" class="logo">BOX<span>INC</span></a>
    <nav>
        <a href="ubicaciones.php" class="activo">Ubicaciones</a>
        <a href="fabricaciones.php">Fabricaciones</a>
        <a href="clientes.php">Clientes</a>
        <a href="articulos.php">Artículos</a>
        <a href="ordenes.php">Órdenes</a>
    </nav>
</header>

<div class="banner">
    <h1>Ubicaciones</h1>
    <p>BOXINC · Gestión de localidades y direcciones</p>
</div>

<div class="contenedor">

    <!-- Mensaje de resultado -->
    <?php if ($msg): ?>
    <div class="mensaje <?= $tipo ?>"><?= $msg ?></div>
    <?php endif; ?>

    <!-- Barra superior con botón "Nueva ubicación" -->
    <div class="barra-seccion">
        <h3>Lista de ubicaciones</h3>
        <a href="ubicaciones.php?accion=nuevo" class="btn btn-principal">+ Nueva ubicación</a>
    </div>

    <!-- ===== FORMULARIO (se muestra al crear o editar) ===== -->
    <?php if ($accion === 'nuevo' || $accion === 'editar'): ?>
    <div class="tarjeta">
        <h2><?= $accion === 'editar' ? 'Editar ubicación' : 'Nueva ubicación' ?></h2>

        <form method="POST" action="ubicaciones.php">
            <!-- Si estamos editando, enviamos el id oculto -->
            <?php if ($editar): ?>
            <input type="hidden" name="id_ubicacion" value="<?= $editar['id_ubicacion'] ?>">
            <?php endif; ?>

            <div class="campo">
                <label>Nombre de la ubicación *</label>
                <input type="text" name="nom_ubicacion" placeholder="Ej: Sede Central"
                    value="<?= htmlspecialchars($editar['nom_ubicacion'] ?? '') ?>" required>
            </div>

            <div class="fila-campos">
                <div class="campo">
                    <label>Localidad</label>
                    <input type="text" name="localidad" placeholder="Ej: Palermo"
                        value="<?= htmlspecialchars($editar['localidad'] ?? '') ?>">
                </div>
                <div class="campo">
                    <label>Ciudad</label>
                    <input type="text" name="ciudad" placeholder="Ej: Buenos Aires"
                        value="<?= htmlspecialchars($editar['ciudad'] ?? '') ?>">
                </div>
                <div class="campo">
                    <label>Código postal</label>
                    <input type="text" name="cod_postal" placeholder="Ej: 1425"
                        value="<?= htmlspecialchars($editar['cod_postal'] ?? '') ?>">
                </div>
            </div>

            <div class="campo">
                <label>Dirección</label>
                <input type="text" name="Direccion" placeholder="Ej: Av. Corrientes 1234"
                    value="<?= htmlspecialchars($editar['Direccion'] ?? '') ?>">
            </div>

            <div class="botones">
                <button type="submit" class="btn btn-principal">
                    <?= $accion === 'editar' ? '💾 Guardar cambios' : '✔ Crear ubicación' ?>
                </button>
                <a href="ubicaciones.php" class="btn btn-borde">Cancelar</a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- ===== TABLA DE UBICACIONES ===== -->
    <div class="tabla-contenedor">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Localidad</th>
                    <th>Ciudad</th>
                    <th>Cod. Postal</th>
                    <th>Dirección</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($ubicaciones->num_rows === 0): ?>
                <tr>
                    <td colspan="7" class="sin-datos">No hay ubicaciones registradas todavía.</td>
                </tr>
                <?php else: ?>
                <?php while ($u = $ubicaciones->fetch_assoc()): ?>
                <tr>
                    <td><?= $u['id_ubicacion'] ?></td>
                    <td><strong><?= htmlspecialchars($u['nom_ubicacion']) ?></strong></td>
                    <td><?= htmlspecialchars($u['localidad'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($u['ciudad'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($u['cod_postal'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($u['Direccion'] ?? '—') ?></td>
                    <td>
                        <!-- Botón editar -->
                        <a href="ubicaciones.php?accion=editar&id=<?= $u['id_ubicacion'] ?>" class="btn btn-secundario btn-chico">✏ Editar</a>
                        <!-- Botón eliminar con confirmación -->
                        <a href="ubicaciones.php?accion=eliminar&id=<?= $u['id_ubicacion'] ?>"
                           class="btn btn-principal btn-chico"
                           onclick="return confirm('¿Seguro que querés eliminar esta ubicación?')">
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
