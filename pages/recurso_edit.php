<?php
require_once __DIR__ . '/db.php';
require __DIR__.'/procesar.php'; 

// Operador ternario ? :
// (condición) ? valor_si_true : valor_si_false;
$recursos = isset($_GET['id_recurso']) ? trim($_GET['id_recurso']) : '';
//Si $_GET['id'] no existe, se asigna 0 a $id.

if ($recursos === '') {
    die("Recurso inválido.");
}

$stmt = getDB()->prepare("SELECT nombre, id_recurso, cantidad FROM recurso WHERE id_recurso = ?");

//bind_param() Sirve para vincular variables PHP a esos marcadores ? en la consulta SQL preparada.
//Primer argumento "i" indica que el tipo de dato es entero (integer).
$stmt->bind_param("i", $recursos);
//“Este ? se reemplaza con la variable $id, que es un número entero."

$stmt->execute();
$result = $stmt->get_result();
$recursos = $result->fetch_assoc();
$stmt->close();

require_once __DIR__ . '/procesar_historial.php';
procesarHistorial('recurso', 'edicion', $recursos['id_recurso']);

if (!$recursos) {
    die("Recurso no encontrado.");
}
?>

<?php
require __DIR__.'/header.php'; 
?>

     <div class="contenedor_titulo">
    <h1 class= "titulo">Editar recurso</h1>
    </div>
     <div class="contenedor_formulario">
   <form class="formulario" method="post" action="recurso_update.php">

    <input type="hidden" name="id_recurso" value="<?= (int) $recursos['id_recurso'] ?>">
        <label class="guardar_datos">Nombre del recurso:
            <input class="campo" type="text" name="nombre" required value="<?= htmlspecialchars($recursos['nombre']) ?>">
        </label><br><br>
    
       <div class="casilla">
        <label class="guardar_datos" for="recurso">Cantidad:</label>
        <input class="campo" type="number" name="cantidad" id="cantidad" required value="<?= htmlspecialchars($recursos['cantidad']) ?>" />
    </div>

        <button class="boton_guardar" type="submit">Actualizar</button>
    </form>
    </div>
<div class="contenedor__boton_listado">
    <p><a class="boton_listado" href="recursos_registro.php">Volver al listado</a></p>
    </div>

    <?php
require __DIR__.'/footer.php';  
?>