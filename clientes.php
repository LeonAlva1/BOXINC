<?php
// clientes.php — ABM de clientes
// Tabla: cliente (id_cliente, nom_cliente, id_ubicacion)
// Relación: cliente → ubicacion

require_once "conexion.php";

$accion = isset($_GET['accion']) ? $_GET['accion'] : 'lista';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$msg    = '';
$tipo   = '';

// ===== GUARDAR =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom   = $BD->real_escape_string(trim($_POST['nom_cliente']));
    $id_ub = (int)$_POST['id_ubicacion'];

    if (empty($nom)) {
        $msg  = "El nombre del cliente es obligatorio.";
        $tipo = "error";
    } else {
        // El id_ubicacion puede ser 0 si no se seleccionó ninguna
        $id_ub_sql = ($id_ub > 0) ? $id_ub : 'NULL';

        if (isset($_POST['id_cliente']) && (int)$_POST['id_cliente'] > 0) {
            $idE = (int)$_POST['id_cliente'];
            $BD->query("UPDATE cliente SET nom_cliente='$nom', id_ubicacion=$id_ub_sql WHERE id_cliente=$idE");
            $msg  = "Cliente actualizado correctamente.";
            $tipo = "exito";
        } else {
            $BD->query("INSERT INTO cliente (nom_cliente, id_ubicacion) VALUES ('$nom', $id_ub_sql)");
            $msg  = "Cliente registrado correctamente.";
            $tipo = "exito";
        }
        $accion = 'lista';
    }
}

// ===== ELIMINAR =====
if ($accion === 'eliminar' && $id > 0) {
    // Verificamos si tiene órdenes asociadas
    $check = $BD->query("SELECT COUNT(*) AS c FROM orden WHERE id_cliente=$id")->fetch_assoc();
    if ($check['c'] > 0) {
        $msg  = "No se puede eliminar: este cliente tiene órdenes registradas.";
        $tipo = "error";
    } else {
        $BD->query("DELETE FROM cliente WHERE id_cliente=$id");
        $msg  = "Cliente eliminado.";
        $tipo = "exito";
    }
    $accion = 'lista';
}

// ===== CARGAR PARA EDITAR =====
$editar = null;
if ($accion === 'editar' && $id > 0) {
    $editar = $BD->query("SELECT * FROM cliente WHERE id_cliente=$id")->fetch_assoc();
}

// ===== DATOS PARA EL SELECT DE UBICACIONES =====
$ubicaciones = $BD->query("SELECT * FROM ubicacion ORDER BY nom_ubicacion");

// ===== LISTADO (con JOIN para mostrar nombre de ubicación y ciudad) =====
$clientes = $BD->query("
    SELECT c.*, u.nom_ubicacion, u.ciudad
    FROM cliente c
    LEFT JOIN ubicacion u ON c.id_ubicacion = u.id_ubicacion
    ORDER BY c.nom_cliente
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>BOXINC — Clientes</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>

<header>
    <a href="index.php" class="logo">BOX<span>INC</span></a>
    <nav>
        <a href="ubicaciones.php">Ubicaciones</a>
        <a href="fabricaciones.php">Fabricaciones</a>
        <a href="clientes.php" class="activo">Clientes</a>
        <a href="articulos.php">Artículos</a>
        <a href="ordenes.php">Órdenes</a>
    </nav>
</header>

<div class="banner">
    <h1>Clientes</h1>
    <p>BOXINC · Solo hospitales, sanatorios y farmacéuticas</p>
</div>

<div class="contenedor">

    <?php if ($msg): ?>
    <div class="mensaje <?= $tipo ?>"><?= $msg ?></div>
    <?php endif; ?>

    <!-- Aviso de regla de negocio -->
    <div class="mensaje aviso">
        🏥 Solo se registran <strong>personas jurídicas</strong> (hospitales, sanatorios y farmacéuticas). No se atiende a personas físicas.
    </div>

    <div class="barra-seccion">
        <h3>Lista de clientes</h3>
        <a href="clientes.php?accion=nuevo" class="btn btn-principal">+ Nuevo cliente</a>
    </div>

    <!-- ===== FORMULARIO ===== -->
    <?php if ($accion === 'nuevo' || $accion === 'editar'): ?>
    <div class="tarjeta">
        <h2><?= $accion === 'editar' ? 'Editar cliente' : 'Nuevo cliente' ?></h2>

        <form method="POST" action="clientes.php">
            <?php if ($editar): ?>
            <input type="hidden" name="id_cliente" value="<?= $editar['id_cliente'] ?>">
            <?php endif; ?>

            <div class="fila-campos">
                <div class="campo">
                    <label>Razón social / Nombre *</label>
                    <input type="text" name="nom_cliente" placeholder="Ej: Hospital Central SA"
                        value="<?= htmlspecialchars($editar['nom_cliente'] ?? '') ?>" required>
                </div>

                <div class="campo">
                    <label>Ubicación</label>
                    <select name="id_ubicacion">
                        <option value="0">— Sin ubicación asignada —</option>
                        <?php while ($u = $ubicaciones->fetch_assoc()): ?>
                        <option value="<?= $u['id_ubicacion'] ?>"
                            <?= (isset($editar) && $editar['id_ubicacion'] == $u['id_ubicacion']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['nom_ubicacion']) ?>
                            <?= $u['ciudad'] ? ' · ' . htmlspecialchars($u['ciudad']) : '' ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                    <!-- Si no hay ubicaciones, avisamos -->
                    <small style="color:#888; font-size:12px;">
                        ¿No existe la ubicación? <a href="ubicaciones.php?accion=nuevo">Crear ubicación</a>
                    </small>
                </div>
            </div>

            <div class="botones">
                <button type="submit" class="btn btn-principal">
                    <?= $accion === 'editar' ? '💾 Guardar cambios' : '✔ Registrar cliente' ?>
                </button>
                <a href="clientes.php" class="btn btn-borde">Cancelar</a>
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
                    <th>Razón Social</th>
                    <th>Ubicación</th>
                    <th>Ciudad</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($clientes->num_rows === 0): ?>
                <tr>
                    <td colspan="5" class="sin-datos">No hay clientes registrados todavía.</td>
                </tr>
                <?php else: ?>
                <?php while ($c = $clientes->fetch_assoc()): ?>
                <tr>
                    <td><?= $c['id_cliente'] ?></td>
                    <td><strong><?= htmlspecialchars($c['nom_cliente']) ?></strong></td>
                    <td><?= htmlspecialchars($c['nom_ubicacion'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($c['ciudad'] ?? '—') ?></td>
                    <td>
                        <a href="clientes.php?accion=editar&id=<?= $c['id_cliente'] ?>" class="btn btn-secundario btn-chico">✏ Editar</a>
                        <a href="clientes.php?accion=eliminar&id=<?= $c['id_cliente'] ?>"
                           class="btn btn-principal btn-chico"
                           onclick="return confirm('¿Eliminar este cliente?')">
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
