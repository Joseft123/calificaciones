USE sistema_escolar;

CREATE TABLE IF NOT EXISTS `docentes` (
  `id_docente` int NOT NULL AUTO_INCREMENT,
  `nomina` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `correo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_docente`),
  UNIQUE KEY `nomina` (`nomina`),
  UNIQUE KEY `correo` (`correo`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `docentes` (`nomina`, `nombre`, `apellidos`, `correo`, `password`) VALUES
('DOC-01', 'Profesor', 'Prueba', 'docente@escuela.com', '$2y$10$oelsTfH8fW99V1bSOdv5CuViZSflmQu/zG/xLAP3c9t3pH66EE7d6');

-- Opcional: Para evitar conflictos futuros, puedes borrar a los docentes de la tabla usuarios:
-- DELETE FROM `usuarios` WHERE `rol` = 'Docente';
