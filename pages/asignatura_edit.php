<!-- ......Este archivo se encarga de editar las asignaturas ...... -->
<?php

//Incluye el archivo db.php, que contiene la función getDB() para conectarse a la base de datos.
require_once __DIR__ . '/db.php';
// Se incluye procesar.php para manejo de sesion de adscripto
require __DIR__.'/procesar.php'; 

$asignaturas = isset($_GET['id_asignatura']) ? trim($_GET['id_asignatura']) : '';
// Si no se proporcionó un ID válido, muestra un error y termina la ejecución
if ($asignaturas === '') {
    die("Asignatura inválida.");
}

// Prepara la consulta para buscar la asignatura correspondiente al ID recibido
$stmt = getDB()->prepare("SELECT nombre, id_asignatura, activo FROM asignatura WHERE id_asignatura = ?");
$stmt->bind_param("i", $asignaturas);
$stmt->execute();
$result = $stmt->get_result();
$asignaturas = $result->fetch_assoc();
$stmt->close();

// Incluye el archivo procesar_historial.php que contiene la función procesarHistorial()
// para registrar las acciones realizadas en el sistema por el adscripto (registro, borrar, edición.).
require_once __DIR__ . '/procesar_historial.php';
// Llama a la función para guardar el evento en el historial:
//   - 'asignatura' → tabla afectada
//   - 'edicion' → tipo de acción
//   - $id_asignatura → ID (PK) de la asignatura afectada
procesarHistorial('asignatura', 'edicion', $asignaturas['id_asignatura']);

// Si no se encontró ninguna asignatura con ese ID, muestra un mensaje y detiene la ejecución
if (!$asignaturas) {
    die("Asignatura no encontrada.");
}
?>

<?php
// Incluye la cabecera general del sitio (HTML, menús, etc.)
require __DIR__.'/header.php'; 
?>

     <div class="contenedor_titulo">
    <h1 class= "titulo">Editar asignatura</h1>
    </div>
     <div class="contenedor_formulario">
    <!-- Formulario para editar los datos de la asignatura -->
    <!-- Envía los datos mediante POST a asignatura_update.php -->
   <form class="formulario" method="post" action="asignatura_update.php">
    <!-- Campo oculto para mantener el ID de la asignatura -->
    <input type="hidden" name="id_asignatura" value="<?= (int) $asignaturas['id_asignatura'] ?>">
    <!-- Campo de texto para editar el nombre de la asignatura -->
        <label class="guardar_datos">Nombre del aula:
            <input class="campo" type="text" name="nombre" required value="<?= htmlspecialchars($asignaturas['nombre']) ?>">
        </label><br><br>
          <!-- Checkbox para marcar si la asignatura está activa -->
        <label class="guardar_datos">
        <input type="checkbox" name="activo" value="1" <?= $asignaturas['activo'] ? 'checked' : '' ?>>
        Asignatura activa
        </label><br><br>
         <!-- Botón para guardar los cambios -->
        <button class="boton_guardar" type="submit">Actualizar</button>
    </form>
    </div>
    <!-- Botón para volver al listado de asignaturas -->
<div class="contenedor__boton_listado">
    <p><a class="boton_listado" href="asignaturas_registro.php">Volver al listado</a></p>
    </div>

    <?php
    //linkea el footer
require __DIR__.'/footer.php';  
?>
