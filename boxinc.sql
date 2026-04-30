SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

USE `boxinc`;

CREATE TABLE `fabricacion` (
  `id_fabricacion` int(11) NOT NULL AUTO_INCREMENT,
  `Vencimiento` date DEFAULT NULL,
  `fecha_fab` date DEFAULT NULL,
  PRIMARY KEY (`id_fabricacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `ubicacion` (
  `id_ubicacion` int(11) NOT NULL AUTO_INCREMENT,
  `localidad` varchar(50) DEFAULT NULL,
  `ciudad` varchar(50) DEFAULT NULL,
  `cod_postal` varchar(10) DEFAULT NULL,
  `Direccion` varchar(100) DEFAULT NULL,
  `nom_ubicacion` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_ubicacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `cliente` (
  `id_cliente` int(11) NOT NULL AUTO_INCREMENT,
  `nom_cliente` varchar(50) DEFAULT NULL,
  `id_ubicacion` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_cliente`),
  KEY `id_ubicacion` (`id_ubicacion`),
  CONSTRAINT `cliente_ibfk_1` FOREIGN KEY (`id_ubicacion`) REFERENCES `ubicacion` (`id_ubicacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `articulo` (
  `id_articulo` int(11) NOT NULL AUTO_INCREMENT,
  `nom_articulo` varchar(50) DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `id_fabricacion` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_articulo`),
  KEY `id_fabricacion` (`id_fabricacion`),
  CONSTRAINT `articulo_ibfk_1` FOREIGN KEY (`id_fabricacion`) REFERENCES `fabricacion` (`id_fabricacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `orden` (
  `id_orden` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` date DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `id_articulo` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_orden`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_articulo` (`id_articulo`),
  CONSTRAINT `orden_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`),
  CONSTRAINT `orden_ibfk_2` FOREIGN KEY (`id_articulo`) REFERENCES `articulo` (`id_articulo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;
