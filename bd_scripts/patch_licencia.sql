CREATE TABLE IF NOT EXISTS `configuracion_sistema` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo_sistema` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Código único de esta instalación',
  `fecha_instalacion` datetime NOT NULL,
  `estado_licencia` enum('Prueba','Activado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Prueba',
  `clave_activacion` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
