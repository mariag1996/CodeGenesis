<!-- ......Este archivo se encarga de editar los grupos ...... -->
<?php
// =====================================
//  CONEXIÓN A LA BASE DE DATOS
// =====================================

//Incluye el archivo db.php, que contiene la función getDB() para conectarse a la base de datos.
require_once __DIR__ . '/db.php';
// Se incluye procesar.php para manejo de sesion de adscripto
require __DIR__.'/procesar.php';

// Usa el operador ternario para obtener el ID de aula desde la URL
// Si no existe el parámetro o está vacío, se asigna cadena vacía
$grupo = isset($_GET['nombre']) ? trim($_GET['nombre']) : '';

// Si no se proporcionó un ID válido, muestra un error y termina la ejecución
if ($grupo === '') {
    die("Grupo inválido.");
}
// Prepara la consulta para buscar el grupo correspondiente al ID recibido
$stmt = getDB()->prepare("SELECT nombre, activo FROM grupo WHERE nombre = ?");

//bind_param() Sirve para vincular variables PHP a esos marcadores ? en la consulta SQL preparada.
//Primer argumento "i" indica que el tipo de dato es entero (integer).
$stmt->bind_param("s", $grupo);
//“Este ? se reemplaza con la variable $id, que es un número entero."

// Ejecuta la consulta
$stmt->execute();
// Obtiene el resultado
$result = $stmt->get_result();
$grupo = $result->fetch_assoc();
// Cierra el statement para liberar recursos
$stmt->close();

// ======================
//   REGISTRO EN HISTORIAL
// ======================

// Incluye el archivo procesar_historial.php que contiene la función procesarHistorial()
// para registrar las acciones realizadas en el sistema por el adscripto (registro, borrar, edición.).
require_once __DIR__ . '/procesar_historial.php';
// Llama a la función para guardar el evento en el historial:
//   - 'grupo' → tabla afectada
//   - 'edicion' → tipo de acción
//   - $nombre → PK del grupo afectado
procesarHistorial('grupo', 'edicion', $grupo['nombre']);
// Si no se encontró ningun grupo con ese nombre, muestra un mensaje y detiene la ejecución
if (!$grupo) {
    die("Grupo no encontrado.");
}
?>
<?php
// Incluye la cabecera general del sitio (HTML, menús, etc.)
require __DIR__.'/header.php'; 
?>

    <div class="contenedor_titulo">
    <h1 class= "titulo">Editar grupo</h1>
    </div>

    <div class="contenedor_formulario">
        <!-- Formulario para editar los datos del grupo -->
    <!-- Envía los datos mediante POST a grupo_update.php -->
    <form class="formulario" method="post" action="grupo_update.php">
    <!-- Campo oculto para mantener el nombre original del grupo -->
    <input type="hidden" name="original" value="<?= htmlspecialchars($grupo['nombre']) ?>">
       <!-- Campo de texto para editar el nombre del grupo -->
    <label class="guardar_datos">Nombre del grupo:
            <input class="campo" type="text" name="nombre" required value="<?= htmlspecialchars($grupo['nombre']) ?>">
        </label><br><br>
 <!-- Checkbox para marcar si el grupo está activo -->
        <label class="guardar_datos">
    <input type="checkbox" name="activo" value="1" <?= $grupo['activo'] ? 'checked' : '' ?>>
    Grupo activo
    </label><br><br>
      <!-- Botón para guardar los cambios -->
        <button class="boton_guardar" type="submit">Actualizar</button>
    </form>
    </div>
     <!-- Botón para volver al listado de grupos -->
    <div class="contenedor__boton_listado">
    <p><a class="boton_listado" href="grupos_registro.php">Volver al listado</a></p>
    </div>

    <?php
     //linkea el footer
require __DIR__.'/footer.php';  
?>