<!-- ......Este archivo se encarga de editar las aulas ...... -->
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
$aulas = isset($_GET['id_aula']) ? trim($_GET['id_aula']) : '';
// Si no se proporcionó un ID válido, muestra un error y termina la ejecución
if ($aulas === '') {
    die("Aula inválida.");
}

// Prepara la consulta para buscar el aula correspondiente al ID recibido
$stmt = getDB()->prepare("SELECT nombre, id_aula, activo FROM aula WHERE id_aula = ?");

//bind_param() Sirve para vincular variables PHP a esos marcadores ? en la consulta SQL preparada.
//Primer argumento "i" indica que el tipo de dato es entero (integer).
$stmt->bind_param("s", $aulas);
//“Este ? se reemplaza con la variable $id, que es un número entero."

// Ejecuta la consulta
$stmt->execute();
// Obtiene el resultado
$result = $stmt->get_result();
$aulas = $result->fetch_assoc();
// Cierra el statement para liberar recursos
$stmt->close();

// ======================
//   REGISTRO EN HISTORIAL
// ======================

// Incluye el archivo procesar_historial.php que contiene la función procesarHistorial()
// para registrar las acciones realizadas en el sistema por el adscripto (registro, borrar, edición.).
require_once __DIR__ . '/procesar_historial.php';
// Llama a la función para guardar el evento en el historial:
//   - 'asignatura' → tabla afectada
//   - 'edicion' → tipo de acción
//   - $id_aula → ID (PK) del aula afectada
procesarHistorial('aula', 'edicion', $aulas['id_aula']);
// Si no se encontró ningun aula con ese ID, muestra un mensaje y detiene la ejecución
if (!$aulas) {
    die("Aula no encontrada.");
}
?>
<?php
// Incluye la cabecera general del sitio (HTML, menús, etc.)
require __DIR__.'/header.php'; 
?>

    <div class="contenedor_titulo">
    <h1 class= "titulo">Editar aulas</h1>
    </div>

    <div class="contenedor_formulario">
        <!-- Formulario para editar los datos del aula -->
    <!-- Envía los datos mediante POST a aula_update.php -->
    <form class="formulario" method="post" action="aula_update.php">
    <!-- Campo oculto para mantener el ID del aula -->
    <input type="hidden" name="id_aula" value="<?= (int) $aulas['id_aula'] ?>">
    <!-- Campo de texto para editar el nombre del aula -->
        <label class="guardar_datos">Nombre del aula:
            <input class="campo" type="text" name="nombre" required value="<?= htmlspecialchars($aulas['nombre']) ?>">
        </label><br><br>
        <!-- Checkbox para marcar si el aula está activa -->
        <label class="guardar_datos">
        <input type="checkbox" name="activo" value="1" <?= $aulas['activo'] ? 'checked' : '' ?>>
        Aula activa
        </label><br><br>
        <!-- Botón para guardar los cambios -->
    <button class="boton_guardar" type="submit">Actualizar</button>
    </form>
    </div>
     <!-- Botón para volver al listado de aulas -->
    <div class="contenedor__boton_listado">
    <p><a class="boton_listado" href="aulas_registro.php">Volver al listado</a></p>
    </div>

    <?php
    //linkea el footer
require __DIR__.'/footer.php';  
?>