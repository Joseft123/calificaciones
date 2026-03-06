-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 26-02-2026 a las 02:08:06
-- Versión del servidor: 8.4.7
-- Versión de PHP: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

START TRANSACTION;

SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */
;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */
;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */
;
/*!40101 SET NAMES utf8mb4 */
;

--
-- Base de datos: `sistema_escolar`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alumnos`
--

DROP TABLE IF EXISTS `alumnos`;

CREATE TABLE IF NOT EXISTS `alumnos` (
    `id_alumno` int NOT NULL AUTO_INCREMENT,
    `matricula` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
    `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
    `apellidos` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
    `nivel` enum(
        'Primaria',
        'Secundaria',
        'Preparatoria'
    ) COLLATE utf8mb4_unicode_ci NOT NULL,
    `grado` int NOT NULL,
    `grupo` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
    PRIMARY KEY (`id_alumno`),
    UNIQUE KEY `matricula` (`matricula`)
) ENGINE = MyISAM AUTO_INCREMENT = 2 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `alumnos`
--

INSERT INTO
    `alumnos` (
        `id_alumno`,
        `matricula`,
        `nombre`,
        `apellidos`,
        `nivel`,
        `grado`,
        `grupo`
    )
VALUES (
        1,
        'MAT-01',
        'Jose Fabian',
        'Muñozcano Guzman',
        'Primaria',
        1,
        'A'
    );

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `calificaciones`
--

DROP TABLE IF EXISTS `calificaciones`;

CREATE TABLE IF NOT EXISTS `calificaciones` (
    `id_calificacion` int NOT NULL AUTO_INCREMENT,
    `id_alumno` int NOT NULL,
    `id_materia` int NOT NULL,
    `periodo` int NOT NULL COMMENT 'Ejemplo: 1 para primer bloque/bimestre',
    `calificacion` decimal(4, 2) NOT NULL,
    `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_calificacion`),
    KEY `id_alumno` (`id_alumno`),
    KEY `id_materia` (`id_materia`)
) ENGINE = MyISAM AUTO_INCREMENT = 2 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `calificaciones`
--

INSERT INTO
    `calificaciones` (
        `id_calificacion`,
        `id_alumno`,
        `id_materia`,
        `periodo`,
        `calificacion`,
        `fecha_registro`
    )
VALUES (
        1,
        1,
        1,
        1,
        10.00,
        '2026-02-25 15:07:30'
    );

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materias`
--

DROP TABLE IF EXISTS `materias`;

CREATE TABLE IF NOT EXISTS `materias` (
    `id_materia` int NOT NULL AUTO_INCREMENT,
    `clave_materia` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
    `nombre_materia` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    `nivel` enum(
        'Primaria',
        'Secundaria',
        'Preparatoria'
    ) COLLATE utf8mb4_unicode_ci NOT NULL,
    `grado` int NOT NULL,
    PRIMARY KEY (`id_materia`),
    UNIQUE KEY `clave_materia` (`clave_materia`)
) ENGINE = MyISAM AUTO_INCREMENT = 2 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `materias`
--

INSERT INTO
    `materias` (
        `id_materia`,
        `clave_materia`,
        `nombre_materia`,
        `nivel`,
        `grado`
    )
VALUES (
        1,
        'MAT-01',
        'ESPAÑOL',
        'Primaria',
        1
    );

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;

CREATE TABLE IF NOT EXISTS `usuarios` (
    `id_usuario` int NOT NULL AUTO_INCREMENT,
    `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    `correo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `rol` enum(
        'Director',
        'Cobranza',
        'Coordinador',
        'Docente'
    ) COLLATE utf8mb4_unicode_ci NOT NULL,
    PRIMARY KEY (`id_usuario`),
    UNIQUE KEY `correo` (`correo`)
) ENGINE = MyISAM AUTO_INCREMENT = 2 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO
    `usuarios` (
        `id_usuario`,
        `nombre`,
        `correo`,
        `password`,
        `rol`
    )
VALUES (
        1,
        'Admin',
        'admin@escuela.com',
        '$2y$10$oelsTfH8fW99V1bSOdv5CuViZSflmQu/zG/xLAP3c9t3pH66EE7d6',
        'Director'
    );

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `docentes`
--

DROP TABLE IF EXISTS `docentes`;

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
) ENGINE = MyISAM DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

INSERT INTO
    `docentes` (
        `nomina`,
        `nombre`,
        `apellidos`,
        `correo`,
        `password`
    )
VALUES (
        'DOC-01',
        'Profesor',
        'Prueba',
        'docente@escuela.com',
        '$2y$10$oelsTfH8fW99V1bSOdv5CuViZSflmQu/zG/xLAP3c9t3pH66EE7d6'
    );

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `docente_materia_grupo`
--

DROP TABLE IF EXISTS `docente_materia_grupo`;

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

INSERT INTO
    `docente_materia_grupo` (
        `id_docente`,
        `id_materia`,
        `nivel`,
        `grado`,
        `grupo`
    )
VALUES (1, 1, 'Primaria', 1, 'A');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencias`
--

DROP TABLE IF EXISTS `asistencias`;

CREATE TABLE IF NOT EXISTS `asistencias` (
    `id_asistencia` INT AUTO_INCREMENT PRIMARY KEY,
    `id_alumno` INT NOT null,
    `id_materia` INT NOT null,
    `id_docente` INT NOT null,
    `fecha` DATE NOT null,
    `estado` ENUM(
        'Presente',
        'Falta',
        'Retardo'
    ) NOT null DEFAULT 'Presente',
    FOREIGN KEY (`id_alumno`) REFERENCES `alumnos` (`id_alumno`) ON DELETE CASCADE,
    FOREIGN KEY (`id_materia`) REFERENCES `materias` (`id_materia`) ON DELETE CASCADE,
    FOREIGN KEY (`id_docente`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
    UNIQUE KEY (
        `id_alumno`,
        `id_materia`,
        `id_docente`,
        `fecha`
    )
) ENGINE = MyISAM DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */
;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */
;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */
;