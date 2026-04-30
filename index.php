<?php
// index.php — Página de inicio del sistema
require_once "conexion.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BOXINC — Sistema de Gestión</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>

<!-- ENCABEZADO -->
<header>
    <a href="index.php" class="logo">BOX<span>INC</span></a>
    <nav>
        <a href="ubicaciones.php">Ubicaciones</a>
        <a href="fabricaciones.php">Fabricaciones</a>
        <a href="clientes.php">Clientes</a>
        <a href="articulos.php">Artículos</a>
        <a href="ordenes.php">Órdenes</a>
    </nav>
</header>

<!-- BANNER -->
<div class="banner">
    <h1>Panel de Administración</h1>
    <p>BOXINC · Laboratorio &amp; Insumos Ortopédicos</p>
</div>

<!-- CONTENIDO PRINCIPAL -->
<div class="contenedor">

    <!-- Aviso de regla de negocio -->
    <div class="mensaje aviso" style="margin-top: 4px;">
        ⚠️ <strong>Regla de negocio:</strong> BOXINC solo vende a hospitales, sanatorios y farmacéuticas.
        La cantidad mínima por orden es de <strong>10 cajas</strong> por producto.
    </div>

    <!-- Grilla de módulos -->
    <h2 style="font-size:15px; text-transform:uppercase; letter-spacing:2px; color:#666; margin-bottom:4px;">Módulos del sistema</h2>

    <div class="grid-modulos">

        <a href="ubicaciones.php" class="modulo-card">
            <div class="modulo-icono">📍</div>
            <div class="modulo-nombre">Ubicaciones</div>
            <div class="modulo-desc">Localidades y direcciones de los clientes.</div>
        </a>

        <a href="fabricaciones.php" class="modulo-card">
            <div class="modulo-icono">🏭</div>
            <div class="modulo-nombre">Fabricaciones</div>
            <div class="modulo-desc">Fechas de fabricación y vencimiento de lotes.</div>
        </a>

        <a href="clientes.php" class="modulo-card">
            <div class="modulo-icono">🏥</div>
            <div class="modulo-nombre">Clientes</div>
            <div class="modulo-desc">Hospitales, sanatorios y farmacéuticas registradas.</div>
        </a>

        <a href="articulos.php" class="modulo-card">
            <div class="modulo-icono">💊</div>
            <div class="modulo-nombre">Artículos</div>
            <div class="modulo-desc">Catálogo de medicamentos e insumos con precios.</div>
        </a>

        <a href="ordenes.php" class="modulo-card">
            <div class="modulo-icono">📋</div>
            <div class="modulo-nombre">Órdenes</div>
            <div class="modulo-desc">Pedidos mayoristas. Mínimo 10 cajas por artículo.</div>
        </a>

    </div>
</div>

<!-- PIE DE PÁGINA -->
<footer>
    <span>BOXINC</span> · Laboratorio &amp; Insumos Ortopédicos · Sistema Interno de Gestión
</footer>

</body>
</html>
