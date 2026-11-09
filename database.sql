-- 1. Crear y seleccionar la base de datos
CREATE DATABASE IF NOT EXISTS universidad_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE universidad_db;

-- 2. Tabla 1: Estudiantes
CREATE TABLE IF NOT EXISTS estudiantes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  correo VARCHAR(150) NOT NULL UNIQUE,
  programa VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- 3. Tabla 2: Cursos / Asignaturas
CREATE TABLE IF NOT EXISTS cursos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(20) NOT NULL UNIQUE,
  nombre VARCHAR(100) NOT NULL,
  creditos INT NOT NULL,
  profesor VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- 4. Tabla 3: Matrículas (Tabla de unión relacional con llaves foráneas)
CREATE TABLE IF NOT EXISTS matriculas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  estudiante_id INT NOT NULL,
  curso_id INT NOT NULL,
  semestre VARCHAR(20) NOT NULL,
  fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_matricula_estudiante FOREIGN KEY (estudiante_id)
    REFERENCES estudiantes(id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_matricula_curso FOREIGN KEY (curso_id)
    REFERENCES cursos(id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT uq_estudiante_curso_semestre UNIQUE (estudiante_id, curso_id, semestre)
) ENGINE=InnoDB;

-- 5. Datos iniciales para pruebas
INSERT INTO estudiantes (nombre, correo, programa) VALUES
('Ana Torres', 'ana.torres@lasalle.edu.co', 'Ingeniería de Sistemas'),
('Luis Pérez', 'luis.perez@lasalle.edu.co', 'Ingeniería Industrial'),
('Camila Rincón', 'camila.rincon@lasalle.edu.co', 'Ingeniería de Alimentos')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

INSERT INTO cursos (codigo, nombre, creditos, profesor) VALUES
('BD101', 'Bases de Datos', 3, 'Gabriel Barrera'),
('PROG202', 'Programación Web', 4, 'Carlos Gómez'),
('CALC101', 'Cálculo Diferencial', 4, 'Martha Duarte')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

INSERT INTO matriculas (estudiante_id, curso_id, semestre) VALUES
(1, 1, '2026-1'),
(1, 2, '2026-1'),
(2, 1, '2026-1'),
(3, 3, '2026-1')
ON DUPLICATE KEY UPDATE semestre = VALUES(semestre);

-- 6. Crear usuario con privilegios mínimos para la aplicación
CREATE USER IF NOT EXISTS 'app_lasalle'@'localhost'
IDENTIFIED BY 'Cambiar_Esta_Clave_2026!';

GRANT SELECT, INSERT ON universidad_db.*
TO 'app_lasalle'@'localhost';

FLUSH PRIVILEGES;

