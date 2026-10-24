-- Base de datos para I.E. 6082 Los Próceres
CREATE DATABASE IF NOT EXISTS ie_6082_los_proceres CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE ie_6082_los_proceres;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    dni VARCHAR(20) NOT NULL UNIQUE,
    rol ENUM('estudiante', 'profesor') NOT NULL DEFAULT 'estudiante',
    nivel ENUM('primaria', 'secundaria') NOT NULL,
    grado_seccion VARCHAR(50) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Usuario de prueba: Estudiante Jesus Jauya
-- Usuario: jjauya (inicial del nombre + apellido)
-- Contraseña: 60469739 (DNI)
INSERT INTO usuarios (usuario, password, nombre, apellido, dni, rol, nivel, grado_seccion) VALUES
('jjauya', '60469739', 'Jesus', 'Jauya', '60469739', 'estudiante', 'secundaria', '3° A');
