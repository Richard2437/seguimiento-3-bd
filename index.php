<?php
require __DIR__ . '/conexion.php';

// 1. Consulta con INNER JOIN a las 3 tablas (Matrículas, Estudiantes, Cursos)
$sqlMatriculas = '
    SELECT 
        m.id AS matricula_id,
        e.nombre AS estudiante_nombre,
        e.correo AS estudiante_correo,
        e.programa AS estudiante_programa,
        c.codigo AS curso_codigo,
        c.nombre AS curso_nombre,
        c.creditos AS curso_creditos,
        c.profesor AS curso_profesor,
        m.semestre,
        m.fecha_registro
    FROM matriculas m
    INNER JOIN estudiantes e ON m.estudiante_id = e.id
    INNER JOIN cursos c ON m.curso_id = c.id
    ORDER BY m.id DESC
';
$matriculas = $conexion->query($sqlMatriculas)->fetchAll();

// 2. Consultas para llenar los <select> del formulario de matrícula
$estudiantes = $conexion->query('SELECT id, nombre, correo, programa FROM estudiantes ORDER BY nombre ASC')->fetchAll();
$cursos = $conexion->query('SELECT id, codigo, nombre, creditos, profesor FROM cursos ORDER BY nombre ASC')->fetchAll();

// Manejo de mensajes por URL
$mensaje = $_GET['msg'] ?? '';
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistema de Matrículas - Universidad de La Salle</title>
  <style>
    :root {
      --primary: #003366;
      --secondary: #0077b6;
      --accent: #009688;
      --light-bg: #f4f6f9;
      --card-bg: #ffffff;
      --text: #2d3748;
      --border: #e2e8f0;
      --success: #28a745;
      --danger: #dc3545;
    }
    * { box-sizing: border-box; }
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: var(--light-bg);
      color: var(--text);
      margin: 0;
      padding: 20px;
    }
    .container {
      max-width: 1100px;
      margin: 0 auto;
    }
    header {
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      color: white;
      padding: 24px;
      border-radius: 12px;
      margin-bottom: 25px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    header h1 { margin: 0 0 6px 0; font-size: 1.8rem; }
    header p { margin: 0; opacity: 0.9; font-size: 0.95rem; }
    
    .badge-info {
      display: inline-block;
      background: rgba(255,255,255,0.2);
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 0.85rem;
      margin-top: 8px;
    }

    .alert {
      padding: 14px 18px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-weight: 500;
    }
    .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

    .grid-forms {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 20px;
      margin-bottom: 30px;
    }
    @media (max-width: 850px) {
      .grid-forms { grid-template-columns: 1fr; }
    }

    .card {
      background: var(--card-bg);
      border-radius: 10px;
      padding: 22px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.06);
      border: 1px solid var(--border);
    }
    .card h2 {
      margin-top: 0;
      font-size: 1.25rem;
      color: var(--primary);
      border-bottom: 2px solid var(--border);
      padding-bottom: 10px;
    }

    label {
      display: block;
      margin-top: 12px;
      margin-bottom: 4px;
      font-weight: 600;
      font-size: 0.9rem;
    }
    input, select {
      width: 100%;
      padding: 10px;
      border: 1px solid #cbd5e0;
      border-radius: 6px;
      font-size: 0.95rem;
      background: #fafbfc;
      transition: border-color 0.2s;
    }
    input:focus, select:focus {
      outline: none;
      border-color: var(--secondary);
      background: #fff;
    }

    button {
      margin-top: 18px;
      background-color: var(--primary);
      color: white;
      border: none;
      padding: 11px 20px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 0.95rem;
      font-weight: 600;
      width: 100%;
      transition: background-color 0.2s;
    }
    button:hover { background-color: var(--secondary); }
    button.btn-accent { background-color: var(--accent); }
    button.btn-accent:hover { background-color: #00796b; }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
      background: white;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    th, td {
      padding: 12px 14px;
      text-align: left;
      font-size: 0.9rem;
    }
    th {
      background-color: var(--primary);
      color: white;
      font-weight: 600;
    }
    tr:nth-child(even) { background-color: #f8fafc; }
    tr:hover { background-color: #edf2f7; }
    .badge {
      display: inline-block;
      padding: 3px 8px;
      border-radius: 12px;
      font-size: 0.78rem;
      font-weight: bold;
      background: #e2e8f0;
      color: #334155;
    }
    .badge-code { background: #e0f2fe; color: #0369a1; }
    
    details {
      margin-top: 15px;
      background: #f8fafc;
      padding: 10px 14px;
      border-radius: 6px;
      border: 1px dashed #cbd5e0;
    }
    details summary {
      cursor: pointer;
      font-weight: 600;
      color: var(--secondary);
    }
  </style>
</head>
<body>
  <div class="container">
    <header>
      <h1>Sistema Académico - Universidad de La Salle</h1>
      <p>Gestión de Matrículas con Modelo Relacional de 3 Tablas (MySQL + PHP + PDO)</p>
      <span class="badge-info">Tablas: <code>estudiantes</code> + <code>cursos</code> + <code>matriculas</code> (INNER JOIN)</span>
      <span class="badge-info" style="background: rgba(255,255,255,0.3); margin-left: 8px;">Motor activo: <strong><?= htmlspecialchars($motorBD ?? 'MySQL', ENT_QUOTES, 'UTF-8') ?></strong></span>

    </header>

    <?php if ($mensaje === 'matricula_ok'): ?>
      <div class="alert alert-success">Matrícula registrada exitosamente en la base de datos.</div>
    <?php elseif ($mensaje === 'estudiante_ok'): ?>
      <div class="alert alert-success">Estudiante agregado correctamente a la tabla <code>estudiantes</code>.</div>
    <?php elseif ($mensaje === 'curso_ok'): ?>
      <div class="alert alert-success">Curso agregado correctamente a la tabla <code>cursos</code>.</div>
    <?php elseif ($error !== ''): ?>
      <div class="alert alert-danger"><strong>Error:</strong> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="grid-forms">
      <!-- FORMULARIO PRINCIPAL: MATRÍCULAS (UNE LAS 3 TABLAS) -->
      <div class="card">
        <h2>Registrar Nueva Matrícula</h2>
        <form action="guardar.php" method="post">
          <input type="hidden" name="accion" value="guardar_matricula">

          <label for="estudiante_id">Estudiante (Tabla: <code>estudiantes</code>):</label>
          <select name="estudiante_id" id="estudiante_id" required>
            <option value="">-- Seleccione un estudiante --</option>
            <?php foreach ($estudiantes as $est): ?>
              <option value="<?= (int)$est['id'] ?>">
                <?= htmlspecialchars($est['nombre'] . ' (' . $est['programa'] . ')', ENT_QUOTES, 'UTF-8') ?>
              </option>
            <?php endforeach; ?>
          </select>

          <label for="curso_id">Curso / Asignatura (Tabla: <code>cursos</code>):</label>
          <select name="curso_id" id="curso_id" required>
            <option value="">-- Seleccione un curso --</option>
            <?php foreach ($cursos as $cur): ?>
              <option value="<?= (int)$cur['id'] ?>">
                <?= htmlspecialchars($cur['codigo'] . ' - ' . $cur['nombre'] . ' (' . $cur['creditos'] . ' créd.)', ENT_QUOTES, 'UTF-8') ?>
              </option>
            <?php endforeach; ?>
          </select>

          <label for="semestre">Semestre / Periodo Académico:</label>
          <input type="text" name="semestre" id="semestre" value="2026-1" required maxlength="20" placeholder="Ej: 2026-1">

          <button type="submit">Guardar Matrícula</button>
        </form>
      </div>

      <!-- FORMULARIOS SECUNDARIOS PLEGABLES PARA AGREGAR NUEVAS ENTIDADES -->
      <div class="card">
        <h2>Gestión de Tablas Base</h2>
        <p style="font-size: 0.85rem; color: #64748b; margin-top: 0;">
          Agrega nuevos registros a las tablas individuales para utilizarlos en las matrículas:
        </p>

        <!-- Agregar Estudiante -->
        <details>
          <summary>+ Agregar Nuevo Estudiante</summary>
          <form action="guardar.php" method="post" style="margin-top: 10px;">
            <input type="hidden" name="accion" value="guardar_estudiante">
            
            <label>Nombre Completo:</label>
            <input type="text" name="nombre" required maxlength="100" placeholder="Ej: Juan Silva">

            <label>Correo Electrónico:</label>
            <input type="email" name="correo" required maxlength="150" placeholder="juan.silva@lasalle.edu.co">

            <label>Programa Académico:</label>
            <input type="text" name="programa" required maxlength="100" placeholder="Ej: Ingeniería de Sistemas">

            <button type="submit" class="btn-accent">Guardar Estudiante</button>
          </form>
        </details>

        <!-- Agregar Curso -->
        <details style="margin-top: 12px;">
          <summary>+ Agregar Nuevo Curso</summary>
          <form action="guardar.php" method="post" style="margin-top: 10px;">
            <input type="hidden" name="accion" value="guardar_curso">
            
            <label>Código del Curso:</label>
            <input type="text" name="codigo" required maxlength="20" placeholder="Ej: MAT301">

            <label>Nombre de la Materia:</label>
            <input type="text" name="nombre_curso" required maxlength="100" placeholder="Ej: Estructura de Datos">

            <label>Créditos Académicos:</label>
            <input type="number" name="creditos" min="1" max="10" value="3" required>

            <label>Docente Asignado:</label>
            <input type="text" name="profesor" required maxlength="100" placeholder="Ej: Gabriel Barrera">

            <button type="submit" class="btn-accent">Guardar Curso</button>
          </form>
        </details>
      </div>
    </div>

    <!-- LISTADO GENERAL CON INNER JOIN DE LAS 3 TABLAS -->
    <div class="card">
      <h2>Listado General de Matrículas (INNER JOIN de 3 Tablas)</h2>
      <p style="font-size: 0.88rem; color: #64748b; margin-top: -5px;">
        Esta vista combina en tiempo real datos de <code>matriculas</code>, <code>estudiantes</code> y <code>cursos</code>.
      </p>

      <?php if (empty($matriculas)): ?>
        <p>No hay matrículas registradas aún en el sistema.</p>
      <?php else: ?>
        <div style="overflow-x: auto;">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Estudiante</th>
                <th>Correo</th>
                <th>Programa</th>
                <th>Código</th>
                <th>Asignatura</th>
                <th>Créditos</th>
                <th>Docente</th>
                <th>Semestre</th>
                <th>Fecha Registro</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($matriculas as $fila): ?>
                <tr>
                  <td><strong><?= (int)$fila['matricula_id'] ?></strong></td>
                  <td><?= htmlspecialchars($fila['estudiante_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($fila['estudiante_correo'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($fila['estudiante_programa'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><span class="badge badge-code"><?= htmlspecialchars($fila['curso_codigo'], ENT_QUOTES, 'UTF-8') ?></span></td>
                  <td><?= htmlspecialchars($fila['curso_nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><span class="badge"><?= (int)$fila['curso_creditos'] ?> cred.</span></td>
                  <td><?= htmlspecialchars($fila['curso_profesor'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><span class="badge"><?= htmlspecialchars($fila['semestre'], ENT_QUOTES, 'UTF-8') ?></span></td>
                  <td><small><?= htmlspecialchars($fila['fecha_registro'], ENT_QUOTES, 'UTF-8') ?></small></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>

