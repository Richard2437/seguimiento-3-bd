# Guía Práctica: Sistema de Matrículas con 3 Tablas en PHP, PDO y MySQL
**Universidad de La Salle — Asignatura: Bases de Datos**  
**Docente:** Gabriel Barrera  
**Actividad:** Seguimiento 3 / Reto de Aplicación Web con 3 Tablas  

---

## 1. Arquitectura del Modelo Relacional (3 Tablas)

Para cumplir con el requerimiento de **3 tablas relacionales** bajo la Tercera Forma Normal (3FN), se implementó un sistema de **Matrículas Académicas**:

```
+--------------------+           +--------------------+
|    estudiantes     |           |       cursos       |
+--------------------+           +--------------------+
| id (PK)            |           | id (PK)            |
| nombre             |           | codigo (UNIQUE)    |
| correo (UNIQUE)    |           | nombre             |
| programa           |           | creditos           |
+---------+----------+           | profesor           |
          |                      +---------+----------+
          | 1                              | 1
          |                                |
          |        +-----------------+     |
          +------> |   matriculas    | <---+
                 N +-----------------+ N
                   | id (PK)         |
                   | estudiante_id(FK)
                   | curso_id (FK)   |
                   | semestre        |
                   | fecha_registro  |
                   +-----------------+
```

### Relaciones:
1. **`estudiantes`** (1) a **`matriculas`** (N): Un estudiante puede tener varias materias matriculadas.
2. **`cursos`** (1) a **`matriculas`** (N): Una materia puede tener múltiples estudiantes inscritos.
3. **Llave única compuesta:** `(estudiante_id, curso_id, semestre)` evita que un estudiante se matricule por duplicado en el mismo curso y periodo.

---

## 2. Paso a Paso para Desplegar el Proyecto en XAMPP

### Paso 1: Importar la Base de Datos en phpMyAdmin
1. Inicie **Apache** y **MySQL** desde el **XAMPP Control Panel**.
2. Abra su navegador en: `http://localhost/phpmyadmin/`.
3. Vaya a la pestaña **SQL**.
4. Copie y pegue todo el contenido de [`database.sql`](file:///c:/Users/DELL/Desktop/DB/seguimiento_3/database.sql) y haga clic en **Continuar**.
   - Esto creará la base de datos `universidad_db`.
   - Creará las tablas `estudiantes`, `cursos` y `matriculas`.
   - Insertará datos de prueba.
   - Creará el usuario `app_lasalle` con contraseña `Cambiar_Esta_Clave_2026!`.

### Paso 2: Ubicar los Archivos en `htdocs`
Copie la carpeta `seguimiento_3` dentro de la carpeta pública de Apache:
- **Ruta destino:** `C:\xampp\htdocs\lasalle_matriculas\`
- Archivos incluidos:
  - `conexion.php`
  - `index.php`
  - `guardar.php`
  - `database.sql`

*(O copie los 4 archivos directamente en la carpeta).*

### Paso 3: Probar en el Navegador
Abra su navegador web e ingrese a:
- Si Apache usa el puerto 80: **`http://localhost/lasalle_matriculas/`**
- Si Apache usa el puerto 8080: **`http://localhost:8080/lasalle_matriculas/`**

---

## 3. Descripción de los Archivos del Proyecto

| Archivo | Función y Características Técnicas |
|---|---|
| [`database.sql`](file:///c:/Users/DELL/Desktop/DB/seguimiento_3/database.sql) | DDL y DML: Creación de BD `utf8mb4`, 3 tablas con llaves foráneas (`ON DELETE CASCADE`), restricciones `UNIQUE`, datos iniciales y creación del usuario de mínimos privilegios `app_lasalle`. |
| [`conexion.php`](file:///c:/Users/DELL/Desktop/DB/seguimiento_3/conexion.php) | Conexión segura usando `PDO`, habilitando `ERRMODE_EXCEPTION`, `FETCH_ASSOC` y `EMULATE_PREPARES => false`. Manejo elegante de fallas de conexión con interfaz explicativa. |
| [`index.php`](file:///c:/Users/DELL/Desktop/DB/seguimiento_3/index.php) | Vista principal. Realiza una consulta con `INNER JOIN` entre las 3 tablas para listar las matrículas en una tabla protegida con `htmlspecialchars()`. Carga listas desplegables dinámicas para matricular estudiantes en cursos y cuenta con opciones para crear nuevos estudiantes y cursos. |
| [`guardar.php`](file:///c:/Users/DELL/Desktop/DB/seguimiento_3/guardar.php) | Controlador de inserciones. Implementa el patrón **Post/Redirect/Get**, validaciones del lado del servidor (tipos de datos, longitudes, filtros) y sentencias preparadas con parámetros nombrados para prevenir inyecciones SQL. Captura errores de unicidad (código 23000). |

---

## 4. Respuestas a las Preguntas de Comprobación (Sección 16 de la Guía)

1. **¿Por qué `http://localhost:3306` no es una dirección válida para abrir phpMyAdmin?**  
   Porque el puerto 3306 es el canal de transporte binario del protocolo TCP de MySQL, no un servidor web HTTP. El navegador web solo entiende HTTP/HTTPS (manejado por Apache en el puerto 80 o 8080), mientras que phpMyAdmin es una aplicación PHP servida por Apache que a su vez se conecta internamente a MySQL en el 3306.

2. **¿Cuál es la diferencia entre Apache y MySQL?**  
   - **Apache** es el servidor web que atiende peticiones HTTP de los navegadores, interpreta los scripts de PHP y devuelve código HTML/CSS/JS.  
   - **MySQL** es el Sistema Gestor de Bases de Datos Relacionales (RDBMS) encargado de almacenar, estructurar, indexar y recuperar datos de forma persistente.

3. **¿Por qué un archivo PHP no debe abrirse con doble clic?**  
   Al hacer doble clic, el sistema operativo intenta abrirlo con un navegador mediante el protocolo de archivo local (`file:///...`). Los navegadores no tienen un motor de ejecución de PHP, por lo que mostrarían el código fuente plano sin procesar. Para ejecutarse, debe solicitarse mediante HTTP a Apache (`http://localhost/...`), quien compila el PHP en el servidor y entrega solo el resultado HTML.

4. **¿Qué cambia en `conexion.php` si MySQL escucha en el puerto 3307?**  
   Se debe modificar la variable `$puerto = '3307';` y/o la cadena DSN: `"mysql:host=$servidor;port=3307;dbname=$baseDatos;charset=utf8mb4"`, asegurando que PDO se comunique con la instancia de MySQL en el canal de red correcto.

5. **¿Qué diferencia existe entre `query()`, `prepare()` y `execute()`?**  
   - `query()`: Ejecuta inmediatamente una instrucción SQL estática y devuelve el conjunto de resultados. Se usa cuando no hay datos ingresados por el usuario.  
   - `prepare()`: Envía la plantilla de la consulta al motor de base de datos para precompilarla, separando la lógica SQL de los parámetros.  
   - `execute()`: Envía únicamente los valores de los parámetros a la consulta precompilada, impidiendo de raíz la inyección SQL.

6. **¿Por qué no debemos mostrar directamente en HTML los datos de la base sin `htmlspecialchars()`?**  
   Para prevenir ataques de **Cross-Site Scripting (XSS)**. Si un usuario malintencionado inserta `<script>alert('hack')</script>` en un campo de texto y se imprime sin escapar, el navegador de otros usuarios lo interpretará y ejecutará como JavaScript. `htmlspecialchars()` convierte caracteres como `<`, `>`, `&`, `"`, `'` en sus entidades HTML correspondientes (`&lt;`, `&gt;`).

7. **¿Por qué es preferible `app_lasalle` frente a `root`?**  
   Por el **Principio de Mínimo Privilegio**. El usuario `root` tiene control absoluto sobre el servidor de base de datos (puede borrar bases de datos completas, apagar el motor o modificar usuarios). Una cuenta específica para la aplicación como `app_lasalle`, configurada solo con permisos `SELECT` e `INSERT` sobre la base de datos `universidad_db`, limita drásticamente el impacto de seguridad ante cualquier vulnerabilidad.

