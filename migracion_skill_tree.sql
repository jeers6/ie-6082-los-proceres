-- =========================================================
-- MIGRACIÓN: Árbol de Habilidades + Bolso de Puntos
-- I.E. 6082 Los Próceres
-- Ejecutar en phpMyAdmin (pestaña SQL) sobre la BD ie_6082_los_proceres.
-- Es incremental: NO borra usuarios, cursos ni avisos existentes.
-- Se puede ejecutar más de una vez sin errores.
-- =========================================================

-- 1) Saldo del Bolso en cada usuario
ALTER TABLE `usuarios`
  ADD COLUMN IF NOT EXISTS `bolso_puntos` INT(11) NOT NULL DEFAULT 0 AFTER `racha_dias`;

-- 2) Progreso del estudiante en cada rama del árbol
--    rama: algebra | calculo | geometria | aritmetica | desafio | canje
--    niveles_total: 5 en ramas negras y canje; 10-15 en desafío (se fija con el dado)
--    consolidado: 0 = punto ganado pero aún no pasó al Bolso; 1 = ya está en el Bolso
CREATE TABLE IF NOT EXISTS `progreso_arbol` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_estudiante` INT(11) NOT NULL,
  `id_curso` INT(11) NOT NULL,
  `rama` VARCHAR(20) NOT NULL,
  `nivel_actual` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `niveles_total` TINYINT UNSIGNED DEFAULT NULL,
  `completado` TINYINT(1) NOT NULL DEFAULT 0,
  `puntos_obtenidos` INT(11) NOT NULL DEFAULT 0,
  `consolidado` TINYINT(1) NOT NULL DEFAULT 0,
  `fecha_actualizacion` TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_progreso` (`id_estudiante`,`id_curso`,`rama`),
  KEY `id_curso` (`id_curso`),
  CONSTRAINT `progreso_arbol_ibfk_1` FOREIGN KEY (`id_estudiante`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `progreso_arbol_ibfk_2` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3) Solicitudes para aplicar puntos a un examen (las aprueba profesor o desarrollador)
CREATE TABLE IF NOT EXISTS `solicitudes_puntos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_estudiante` INT(11) NOT NULL,
  `id_curso` INT(11) NOT NULL,
  `tipo_examen` ENUM('parcial','final') NOT NULL,
  `nota_actual` DECIMAL(4,1) NOT NULL,
  `puntos_aplicados` INT(11) NOT NULL,
  `nota_resultante` DECIMAL(4,1) NOT NULL,
  `estado` ENUM('pendiente','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
  `fecha_solicitud` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  `fecha_resolucion` TIMESTAMP NULL DEFAULT NULL,
  `resuelta_por` INT(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_estudiante` (`id_estudiante`),
  KEY `id_curso` (`id_curso`),
  CONSTRAINT `solicitudes_puntos_ibfk_1` FOREIGN KEY (`id_estudiante`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `solicitudes_puntos_ibfk_2` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4) Respaldo / auditoría del saldo: cada movimiento del Bolso queda registrado
CREATE TABLE IF NOT EXISTS `bolso_historial` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_estudiante` INT(11) NOT NULL,
  `movimiento` INT(11) NOT NULL,
  `saldo_resultante` INT(11) NOT NULL,
  `motivo` VARCHAR(150) NOT NULL,
  `fecha` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_estudiante` (`id_estudiante`),
  CONSTRAINT `bolso_historial_ibfk_1` FOREIGN KEY (`id_estudiante`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
