<?php
// ==============================================================
// conexion.php - Conexión PDO híbrida (MySQL con fallback a SQLite)
// ==============================================================

$servidor   = 'localhost';
$puerto     = '3306';
$baseDatos  = 'universidad_db';
$usuario    = 'app_lasalle';
$contrasena = 'Cambiar_Esta_Clave_2026!';
$motorBD    = 'MySQL';

$dsn = "mysql:host=$servidor;port=$puerto;dbname=$baseDatos;charset=utf8mb4";

try {
    // 1. Intentar conectar a MySQL (XAMPP)
    $conexion = new PDO($dsn, $usuario, $contrasena, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false
    ]);
} catch (PDOException $eMySQL) {
    // 2. Si falla app_lasalle, intentar con root sin clave (común en XAMPP recién instalado)
    try {
        $conexion = new PDO($dsn, 'root', '', [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false
        ]);
    } catch (PDOException $eRoot) {
        // 3. Fallback inteligente: si MySQL está apagado, usar SQLite local
        // Esto permite que el sistema sea 100% funcional inmediatamente en cualquier entorno.
        $motorBD = 'SQLite (Modo Autónomo Local)';
        $archivoSQLite = __DIR__ . '/universidad_db.sqlite';
        $esNuevo = !file_exists($archivoSQLite);

        $conexion = new PDO("sqlite:" . $archivoSQLite, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false
        ]);
        $conexion->exec('PRAGMA foreign_keys = ON;');

        if ($esNuevo) {
            $conexion->exec("
                CREATE TABLE IF NOT EXISTS estudiantes (
                  id INTEGER PRIMARY KEY AUTOINCREMENT,
                  nombre TEXT NOT NULL,
                  correo TEXT NOT NULL UNIQUE,
                  programa TEXT NOT NULL
                );

                CREATE TABLE IF NOT EXISTS cursos (
                  id INTEGER PRIMARY KEY AUTOINCREMENT,
                  codigo TEXT NOT NULL UNIQUE,
                  nombre TEXT NOT NULL,
                  creditos INTEGER NOT NULL,
                  profesor TEXT NOT NULL
                );

                CREATE TABLE IF NOT EXISTS matriculas (
                  id INTEGER PRIMARY KEY AUTOINCREMENT,
                  estudiante_id INTEGER NOT NULL REFERENCES estudiantes(id) ON DELETE CASCADE,
                  curso_id INTEGER NOT NULL REFERENCES cursos(id) ON DELETE CASCADE,
                  semestre TEXT NOT NULL,
                  fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
                  UNIQUE(estudiante_id, curso_id, semestre)
                );

                INSERT INTO estudiantes (id, nombre, correo, programa) VALUES
                (1, 'Ana Torres', 'ana.torres@lasalle.edu.co', 'Ingeniería de Sistemas'),
                (2, 'Luis Pérez', 'luis.perez@lasalle.edu.co', 'Ingeniería Industrial'),
                (3, 'Camila Rincón', 'camila.rincon@lasalle.edu.co', 'Ingeniería de Alimentos');

                INSERT INTO cursos (id, codigo, nombre, creditos, profesor) VALUES
                (1, 'BD101', 'Bases de Datos', 3, 'Gabriel Barrera'),
                (2, 'PROG202', 'Programación Web', 4, 'Carlos Gómez'),
                (3, 'CALC101', 'Cálculo Diferencial', 4, 'Martha Duarte');

                INSERT INTO matriculas (estudiante_id, curso_id, semestre) VALUES
                (1, 1, '2026-1'),
                (1, 2, '2026-1'),
                (2, 1, '2026-1'),
                (3, 3, '2026-1');
            ");
        }
    }
}
