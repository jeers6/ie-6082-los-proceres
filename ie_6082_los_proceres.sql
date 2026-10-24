-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 24-10-2026 a las 06:11:27
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `ie_6082_los_proceres`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `avisos`
--

CREATE TABLE `avisos` (
  `id` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `contenido` text NOT NULL,
  `autor` varchar(100) NOT NULL,
  `tipo` enum('general','urgente','evento') DEFAULT 'general',
  `fecha_publicacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `avisos`
--

INSERT INTO `avisos` (`id`, `titulo`, `contenido`, `autor`, `tipo`, `fecha_publicacion`) VALUES
(1, 'Inicio de Trimestre II', 'Se da inicio al II Trimestre con todas las actividades académicas programadas. Todos los estudiantes deben estar al día con sus tareas.', 'Dirección Académica', 'general', '2026-10-24 03:59:41'),
(2, 'Semana de Logros 2026', 'Próximamente se realizará la Semana de Logros con concursos académicos, culturales y deportivos. Prepárate para participar.', 'Coordinación Académica', 'evento', '2026-10-24 03:59:41'),
(3, 'Mantenimiento del Campus Virtual', 'Se realizará mantenimiento programado este fin de semana. Se recomienda guardar su progreso con anticipación.', 'Soporte TI - Desarrollador Web', 'urgente', '2026-10-24 03:59:41');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cursos`
--

CREATE TABLE `cursos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `icono` varchar(50) DEFAULT '?',
  `descripcion` text DEFAULT NULL,
  `profesor` varchar(150) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cursos`
--

INSERT INTO `cursos` (`id`, `nombre`, `codigo`, `icono`, `descripcion`, `profesor`, `fecha_creacion`) VALUES
(1, 'Matemática', 'MAT-01', '🧮', 'Álgebra, Geometría, Razones y Proporciones. Desarrollo del pensamiento lógico-matemático.', 'Prof. Miguel Torres', '2026-10-24 03:59:41'),
(2, 'Comunicación', 'COM-01', '📝', 'Comprensión lectora, redacción, gramática, literatura peruana y expresión oral.', 'Prof. Ana Quispe', '2026-10-24 03:59:41'),
(3, 'Ciencia y Tecnología', 'CYT-01', '🔬', 'Biología, Física y Química con enfoque práctico e investigativo.', 'Prof. Roberto Chávez', '2026-10-24 03:59:41'),
(4, 'Ciencias Sociales', 'CSS-01', '🌍', 'Historia del Perú, Geografía, Realidad Nacional y Formación Ciudadana.', 'Prof. Luisa Mendoza', '2026-10-24 03:59:41'),
(5, 'Inglés', 'ING-01', '🌐', 'Grammar, Vocabulary, Reading, Listening y Speaking con enfoque comunicativo.', 'Prof. Daniel Herrera', '2026-10-24 03:59:41');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entregas_tareas`
--

CREATE TABLE `entregas_tareas` (
  `id` int(11) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `id_estudiante` int(11) NOT NULL,
  `titulo_tarea` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_asignacion` date NOT NULL,
  `fecha_limite` date NOT NULL,
  `archivo_entregado` varchar(255) DEFAULT NULL,
  `estado` enum('pendiente','entregado','calificado') DEFAULT 'pendiente',
  `calificacion` decimal(4,1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `dni` varchar(20) NOT NULL,
  `rol` enum('estudiante','profesor','desarrollador') NOT NULL DEFAULT 'estudiante',
  `foto` varchar(255) DEFAULT NULL,
  `nivel_xp` int(11) DEFAULT 0,
  `racha_dias` int(11) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `password`, `nombre`, `apellido`, `dni`, `rol`, `foto`, `nivel_xp`, `racha_dias`, `fecha_creacion`) VALUES
(1, 'jjauyajesus', '605jeers6', 'Jesus', 'Jauya', '60469739', 'desarrollador', 'foto.jpg', 9999, 365, '2026-10-24 03:59:41'),
(2, 'alumno1', '123456', 'Carlos', 'Ramirez', '70859641', 'estudiante', NULL, 150, 5, '2026-10-24 03:59:41'),
(3, 'mlopez', '123456', 'María', 'Lopez', '71234567', 'estudiante', NULL, 80, 3, '2026-10-24 03:59:41'),
(4, 'jperez', '123456', 'Juan', 'Pérez', '72345678', 'estudiante', NULL, 65, 2, '2026-10-24 03:59:41');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `avisos`
--
ALTER TABLE `avisos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indices de la tabla `entregas_tareas`
--
ALTER TABLE `entregas_tareas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_curso` (`id_curso`),
  ADD KEY `id_estudiante` (`id_estudiante`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD UNIQUE KEY `dni` (`dni`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `avisos`
--
ALTER TABLE `avisos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `cursos`
--
ALTER TABLE `cursos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `entregas_tareas`
--
ALTER TABLE `entregas_tareas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `entregas_tareas`
--
ALTER TABLE `entregas_tareas`
  ADD CONSTRAINT `entregas_tareas_ibfk_1` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `entregas_tareas_ibfk_2` FOREIGN KEY (`id_estudiante`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
