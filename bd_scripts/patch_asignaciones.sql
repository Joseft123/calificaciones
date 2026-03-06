-- Script para agregar la relación entre docentes, materias y grupos

USE sistema_escolar;

-- Tabla para asignar un docente a una materia en un nivel, grado y grupo específico
CREATE TABLE IF NOT EXISTS `docente_materia_grupo` (
    `id_asignacion` int NOT NULL AUTO_INCREMENT,
    `id_docente` int NOT NULL,
    `id_materia` int NOT NULL,
    `nivel` enum(
        'Primaria',
        'Secundaria',
        'Preparatoria'
    ) COLLATE utf8mb4_unicode_ci NOT NULL,
    `grado` int NOT NULL,
    `grupo` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
    PRIMARY KEY (`id_asignacion`),
    KEY `id_docente` (`id_docente`),
    KEY `id_materia` (`id_materia`)
) ENGINE = MyISAM DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Insertar una clase de prueba para el docente test (DOC-01: Profesor Prueba)
-- Si sabemos que DOC-01 tiene id_docente = 1 y la materia "ESPAÑOL" tiene id_materia = 1
INSERT INTO
    `docente_materia_grupo` (
        `id_docente`,
        `id_materia`,
        `nivel`,
        `grado`,
        `grupo`
    )
VALUES (1, 1, 'Primaria', 1, 'A');