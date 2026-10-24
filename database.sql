-- Base de datos para I.E. 6082 Los Próceres - Arquitectura Rol por Rol
CREATE DATABASE IF NOT EXISTS ie_6082_los_proceres CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE ie_6082_los_proceres;

DROP TABLE IF EXISTS entregas_tareas;
DROP TABLE IF EXISTS avisos;
DROP TABLE IF EXISTS cursos;
DROP TABLE IF EXISTS usuarios;

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    dni VARCHAR(20) NOT NULL UNIQUE,
    rol ENUM('estudiante', 'profesor', 'desarrollador') NOT NULL DEFAULT 'estudiante',
    foto VARCHAR(255) DEFAULT NULL,
    nivel_xp INT DEFAULT 0,
    racha_dias INT DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Desarrollador Web Institucional
INSERT INTO usuarios (usuario, password, nombre, apellido, dni, rol, foto, nivel_xp, racha_dias) VALUES
('jjauyajesus', '605jeers6', 'Jesus', 'Jauya', '60469739', 'desarrollador', 'foto.jpg', 9999, 365);

-- Alumnos de prueba sin foto.jpg (usar iniciales/avatar por defecto)
INSERT INTO usuarios (usuario, password, nombre, apellido, dni, rol, foto, nivel_xp, racha_dias) VALUES
('alumno1', '123456', 'Carlos', 'Ramirez', '70859641', 'estudiante', NULL, 150, 5),
('mlopez', '123456', 'María', 'Lopez', '71234567', 'estudiante', NULL, 80, 3),
('jperez', '123456', 'Juan', 'Pérez', '72345678', 'estudiante', NULL, 65, 2);

-- Tabla de cursos
CREATE TABLE cursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    icono VARCHAR(50) DEFAULT '📘',
    descripcion TEXT,
    profesor VARCHAR(150) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO cursos (nombre, codigo, icono, descripcion, profesor) VALUES
('Matemática', 'MAT-01', '🧮', 'Álgebra, Geometría, Razones y Proporciones. Desarrollo del pensamiento lógico-matemático.', 'Prof. Miguel Torres'),
('Comunicación', 'COM-01', '📝', 'Comprensión lectora, redacción, gramática, literatura peruana y expresión oral.', 'Prof. Ana Quispe'),
('Ciencia y Tecnología', 'CYT-01', '🔬', 'Biología, Física y Química con enfoque práctico e investigativo.', 'Prof. Roberto Chávez'),
('Ciencias Sociales', 'CSS-01', '🌍', 'Historia del Perú, Geografía, Realidad Nacional y Formación Ciudadana.', 'Prof. Luisa Mendoza'),
('Inglés', 'ING-01', '🌐', 'Grammar, Vocabulary, Reading, Listening y Speaking con enfoque comunicativo.', 'Prof. Daniel Herrera');

-- Tabla de entregas de tareas
CREATE TABLE entregas_tareas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_curso INT NOT NULL,
    id_estudiante INT NOT NULL,
    titulo_tarea VARCHAR(200) NOT NULL,
    descripcion TEXT,
    fecha_asignacion DATE NOT NULL,
    fecha_limite DATE NOT NULL,
    archivo_entregado VARCHAR(255),
    estado ENUM('pendiente', 'entregado', 'calificado') DEFAULT 'pendiente',
    calificacion DECIMAL(4,1) DEFAULT NULL,
    FOREIGN KEY (id_curso) REFERENCES cursos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_estudiante) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de avisos
CREATE TABLE avisos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    contenido TEXT NOT NULL,
    autor VARCHAR(100) NOT NULL,
    tipo ENUM('general', 'urgente', 'evento') DEFAULT 'general',
    fecha_publicacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO avisos (titulo, contenido, autor, tipo) VALUES
('Inicio de Trimestre II', 'Se da inicio al II Trimestre con todas las actividades académicas programadas. Todos los estudiantes deben estar al día con sus tareas.', 'Dirección Académica', 'general'),
('Semana de Logros 2026', 'Próximamente se realizará la Semana de Logros con concursos académicos, culturales y deportivos. Prepárate para participar.', 'Coordinación Académica', 'evento'),
('Mantenimiento del Campus Virtual', 'Se realizará mantenimiento programado este fin de semana. Se recomienda guardar su progreso con anticipación.', 'Soporte TI - Desarrollador Web', 'urgente');
