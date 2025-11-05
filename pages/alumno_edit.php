<?php
require_once __DIR__ . '/db.php';
require __DIR__.'/procesar.php'; 

// --- Obtención de grupos disponibles ---
// Se declara un array vacío para almacenar los grupos.
$grupos = [];
try {
// Se consulta la tabla "grupo" para obtener todos los nombres de los grupos activos (activo = 1)
// y se ordenan alfabéticamente por nombre.
    $result = getDB()->query("SELECT nombre FROM grupo WHERE activo = 1 ORDER BY nombre");
// Si la consulta fue exitosa, se recorren los resultados y se guardan en el array $grupos.
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $grupos[] = $row;
        }
    }
} catch (mysqli_sql_exception $e) {
// Si ocurre un error (por ejemplo, problema de conexión o SQL incorrecta),
// el script se detiene mostrando un mensaje seguro.
    die("Error al obtener grupos: " . htmlspecialchars($e->getMessage()));
}


// --- Obtención del parámetro 'usuario' desde la URL ---
$usuario = isset($_GET['usuario']) ? (int) $_GET['usuario'] : 0;
// Si el valor de usuario no es válido (0 o negativo), el script se detiene.
if ($usuario <= 0) {
    die("Usuario inválido.");
}

// --- Consulta de los datos del alumno ---
// Se prepara una consulta segura para obtener nombre y apellido del alumno.
$stmt = getDB()->prepare("SELECT usuario, nombre, apellido FROM alumno WHERE usuario = ?");
//Se usa bind_param para evitar inyecciones SQL.
//Es i porque es int.
$stmt->bind_param("i", $usuario);
//Se ejecuta la consulta y se guarda el resultado en $alumno.
$stmt->execute();
// Se obtienen los resultados como un array asociativo.
$result = $stmt->get_result();
$alumno = $result->fetch_assoc();
$stmt->close();

// --- Registro en el historial ---
// Incluye el archivo procesar_historial.php que contiene la función procesarHistorial()
// para registrar las acciones realizadas en el sistema por el adscripto (registro, borrar, edición.).
require_once __DIR__ . '/procesar_historial.php';
// Llama a la función para guardar el evento en el historial:
//   - 'alumno' → tabla afectada
//   - 'edicion' → tipo de acción
//   - $usuario → ID (PK) del alumno afectado
procesarHistorial('alumno', 'edicion', $usuario);

// --- Verificación final ---
// Si no se encontró el alumno (por ejemplo, si el usuario no existe en la base),
// se muestra un mensaje de error y se detiene el script.
if (!$alumno) {
    die("Alumno no encontrado.");
}
?>

<?php
// Se incluye el encabezado común de la página (menú, logo, etc.)
require __DIR__.'/header.php'; 
?>

<div class="contenedor_titulo">
<h1 class= "titulo">Editar alumno</h1>
</div>

   <div class="contenedor_formulario">
    <!-- Formulario para actualizar los datos del alumno -->
    <!-- El formulario envía los datos por POST al archivo alumno_update.php. -->
    <form class="formulario" method="post" action="alumno_update.php">
    <!-- Muestra un select con los nombres de los grupos obtenidos de la base de datos. -->
      <select id="grupo" name="grupo" required>
    <option value="">Seleccione...</option>
    <?php foreach ($grupos as $grupo) { ?>
         <!-- htmlspecialchars() evita inyecciones XSS en el HTML -->
        <option value="<?= htmlspecialchars($grupo['nombre']) ?>"><?= htmlspecialchars($grupo['nombre']) ?></option>
    <?php } ?>
</select>
        <br><br>
        <!-- Campo oculto con el ID del alumno (para identificar qué registro actualizar) -->
        <!-- Se incluye el usuario como campo oculto para saber a quién se está editando. -->
        <input type="hidden" name="usuario" value="<?= (int) $alumno['usuario'] ?>">
            <!-- Campo de nombre del alumno -->
        <label class="guardar_datos">Nombre:
            <input class="campo" type="text" pattern="[A-Za-zÁáÉéÍíÓóÚúÑñ\s]+" 
       title="Solo se permiten letras y espacios" name="nombre"  id="nombreAlumno" required value="<?= htmlspecialchars($alumno['nombre']) ?>">
        </label><br><br>
        <!-- Campo de apellido -->
        <label class="guardar_datos">Apellido:
            <input class="campo" type="text" pattern="[A-Za-zÁáÉéÍíÓóÚúÑñ\s]+" 
       title="Solo se permiten letras y espacios" name="apellido" id="apellidoAlumno" required value="<?= htmlspecialchars($alumno['apellido']) ?>">
        </label><br><br>
         <!-- Botón para enviar el formulario -->
        <button class="boton_guardar" type="submit">Actualizar</button>
    </form>
    </div>
    <!-- Botón para volver al listado de alumnos -->
    <div class="contenedor__boton_listado">
    <p><a class="boton_listado" href="alumnos_registro.php">Volver al listado</a></p>
    </div>
    
  <?php
  // Se incluye el pie de página general del sistema
require __DIR__.'/footer.php';  
?>
<!-- Archivo JS con validaciones adicionales 
 (para que solo se pueda ingresar texto en el nombre y apellido)-->
<script src="validaciones.js"></script>
