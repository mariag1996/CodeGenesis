<?php
// --- Conexión y configuración base ---
// Se incluye el archivo db.php, que contiene la función getDB()
// usada para obtener la conexión a la base de datos.
require_once __DIR__ . '/db.php';
// Se incluye procesar.php para manejo de sesion de adscripto
require __DIR__.'/procesar.php';

// Usa el operador ternario para verificar si se recibió el parámetro usuario por GET. Si no, se asigna 0.
$usuario = isset($_GET['usuario']) ? (int) $_GET['usuario'] : 0;

//Si el valor de usuario es menor o igual a 0, se detiene el script mostrando un mensaje de error.
if ($usuario <= 0) {
    die("Usuario inválido.");
}

// --- Consulta de los datos del docente ---
// Se prepara una consulta segura para obtener nombre y apellido del docente.
$stmt = getDB()->prepare("SELECT usuario, nombre, apellido FROM docente WHERE usuario = ?");

//bind_param() Sirve para vincular variables PHP a esos marcadores ? en la consulta SQL preparada.
//Primer argumento "i" indica que el tipo de dato es entero (integer).
$stmt->bind_param("i", $usuario);
//“Este ? se reemplaza con la variable $id, que es un número entero."
//Se ejecuta la consulta y se guarda el resultado en $docente.
$stmt->execute();
// Se obtienen los resultados como un array asociativo.
$result = $stmt->get_result();
$docente = $result->fetch_assoc();
// Se cierra el statement para liberar recursos.
$stmt->close();

// --- Registro en el historial ---
// Incluye el archivo procesar_historial.php que contiene la función procesarHistorial()
// para registrar las acciones realizadas en el sistema por el adscripto (registro, borrar, edición.).
require_once __DIR__ . '/procesar_historial.php';
// Llama a la función para guardar el evento en el historial:
//   - 'docente' → tabla afectada
//   - 'edicion' → tipo de acción
//   - $usuario → ID (PK) del docente afectado
procesarHistorial('docente', 'edicion', $usuario);

// --- Verificación final ---
// Si no se encontró el docente (por ejemplo, si el usuario no existe en la base),
// se muestra un mensaje de error y se detiene el script.
if (!$docente) {
    die("Docente no encontrado.");
}
?>


<!-- Se conecta el header-->
<?php
require __DIR__.'/header.php'; 
?>

     <div class="contenedor_titulo">
    <h1 class= "titulo">Editar docente</h1>
</div>

    <div class="contenedor_formulario">
      <!-- Formulario para actualizar los datos del docente -->
    <!-- El formulario envía los datos por POST al archivo docente_update.php. -->
    <form class="formulario" method="post" action="docente_update.php">
         <!-- Campo oculto con el ID del docente (para identificar qué registro actualizar) -->
        <!-- Se incluye el usuario como campo oculto para saber a quién se está editando. -->
        <input  type="hidden" name="usuario" value="<?= (int) $docente['usuario'] ?>">

        <!--Se usa htmlspecialchars() para evitar problemas de seguridad-->
        <!-- htmlspecialchars() evita inyecciones XSS en el HTML -->

          <!-- Campo de nombre del docente -->
        <label class="guardar_datos">Nombre:
            <input class="campo" type="text"  pattern="[A-Za-zÁáÉéÍíÓóÚúÑñ\s]+" 
       title="Solo se permiten letras y espacios" id="nombreDocente" name="nombre" required value="<?= htmlspecialchars($docente['nombre']) ?>">
        </label><br><br>
        <!-- Campo de apellido -->
        <label class="guardar_datos">Apellido:
            <input class="campo" type="texto" pattern="[A-Za-zÁáÉéÍíÓóÚúÑñ\s]+" 
       title="Solo se permiten letras y espacios" id="apellidoDocente" name="apellido" required value="<?= htmlspecialchars($docente['apellido']) ?>">
        </label><br><br>
        <!-- Botón para enviar el formulario -->
        <button class="boton_guardar" type="submit">Actualizar</button>
    </form>
</div>
    <div class="contenedor__boton_listado">
        <!-- Botón para volver al listado de docentes -->
    <p><a class="boton_listado" href="docentes_registro.php">Volver al listado</a></p>
</div>
<?php
// Se incluye el pie de página general del sistema
require __DIR__.'/footer.php';  
?>
<!-- Archivo JS con validaciones adicionales 
 (para que solo se pueda ingresar texto en el nombre y apellido)-->
<script src="validaciones.js"></script>