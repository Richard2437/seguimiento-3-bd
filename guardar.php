<?php
// ==============================================================
// Inserción segura con PDO y sentencias preparadas
// ==============================================================

require __DIR__ . '/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido.');
}

$accion = $_POST['accion'] ?? '';

// --------------------------------------------------------------
// CASO 1: INSERTAR MATRÍCULA (Relaciona las tablas)
// --------------------------------------------------------------
if ($accion === 'guardar_matricula') {
    $estudiante_id = filter_input(INPUT_POST, 'estudiante_id', FILTER_VALIDATE_INT);
    $curso_id      = filter_input(INPUT_POST, 'curso_id', FILTER_VALIDATE_INT);
    $semestre      = trim($_POST['semestre'] ?? '');

    if (!$estudiante_id || !$curso_id || $semestre === '' || strlen($semestre) > 20) {
        header('Location: index.php?error=' . urlencode('Datos de matrícula incompletos o inválidos.'));
        exit;
    }

    try {
        $sql = 'INSERT INTO matriculas (estudiante_id, curso_id, semestre) 
                VALUES (:estudiante_id, :curso_id, :semestre)';
        $sentencia = $conexion->prepare($sql);
        $sentencia->execute([
            ':estudiante_id' => $estudiante_id,
            ':curso_id'      => $curso_id,
            ':semestre'      => $semestre
        ]);

        header('Location: index.php?msg=matricula_ok', true, 303);
        exit;
    } catch (PDOException $e) {
        error_log('Error SQL al matricular: ' . $e->getMessage());
        $msgError = 'No fue posible registrar la matrícula.';
        if ($e->getCode() == 23000) {
            $msgError = 'Este estudiante ya se encuentra matriculado en esta asignatura durante el semestre indicado.';
        }
        header('Location: index.php?error=' . urlencode($msgError));
        exit;
    }
}

// --------------------------------------------------------------
// CASO 2: INSERTAR NUEVO ESTUDIANTE (Tabla estudiantes)
// --------------------------------------------------------------
if ($accion === 'guardar_estudiante') {
    $nombre   = trim($_POST['nombre'] ?? '');
    $correo   = trim($_POST['correo'] ?? '');
    $programa = trim($_POST['programa'] ?? '');

    if (
        $nombre === '' || $programa === '' ||
        !filter_var($correo, FILTER_VALIDATE_EMAIL) ||
        strlen($nombre) > 100 || strlen($correo) > 150 || strlen($programa) > 100
    ) {
        header('Location: index.php?error=' . urlencode('Datos de estudiante inválidos. Verifique el correo y los campos.'));
        exit;
    }

    try {
        $sql = 'INSERT INTO estudiantes (nombre, correo, programa) 
                VALUES (:nombre, :correo, :programa)';
        $sentencia = $conexion->prepare($sql);
        $sentencia->execute([
            ':nombre'   => $nombre,
            ':correo'   => $correo,
            ':programa' => $programa
        ]);

        header('Location: index.php?msg=estudiante_ok', true, 303);
        exit;
    } catch (PDOException $e) {
        error_log('Error SQL al insertar estudiante: ' . $e->getMessage());
        $msgError = 'No se pudo guardar el estudiante.';
        if ($e->getCode() == 23000) {
            $msgError = 'El correo electrónico ya se encuentra registrado.';
        }
        header('Location: index.php?error=' . urlencode($msgError));
        exit;
    }
}

// --------------------------------------------------------------
// CASO 3: INSERTAR NUEVO CURSO (Tabla cursos)
// --------------------------------------------------------------
if ($accion === 'guardar_curso') {
    $codigo       = trim($_POST['codigo'] ?? '');
    $nombre_curso = trim($_POST['nombre_curso'] ?? '');
    $creditos     = filter_input(INPUT_POST, 'creditos', FILTER_VALIDATE_INT);
    $profesor     = trim($_POST['profesor'] ?? '');

    if (
        $codigo === '' || $nombre_curso === '' || $profesor === '' ||
        !$creditos || $creditos < 1 || $creditos > 10 ||
        strlen($codigo) > 20 || strlen($nombre_curso) > 100 || strlen($profesor) > 100
    ) {
        header('Location: index.php?error=' . urlencode('Datos de curso inválidos. Revise los campos numéricos y de texto.'));
        exit;
    }

    try {
        $sql = 'INSERT INTO cursos (codigo, nombre, creditos, profesor) 
                VALUES (:codigo, :nombre, :creditos, :profesor)';
        $sentencia = $conexion->prepare($sql);
        $sentencia->execute([
            ':codigo'   => $codigo,
            ':nombre'   => $nombre_curso,
            ':creditos' => $creditos,
            ':profesor' => $profesor
        ]);

        header('Location: index.php?msg=curso_ok', true, 303);
        exit;
    } catch (PDOException $e) {
        error_log('Error SQL al insertar curso: ' . $e->getMessage());
        $msgError = 'No se pudo guardar el curso.';
        if ($e->getCode() == 23000) {
            $msgError = 'El código del curso ya existe.';
        }
        header('Location: index.php?error=' . urlencode($msgError));
        exit;
    }
}

// Acción desconocida
http_response_code(400);
exit('Acción no reconocida.');

