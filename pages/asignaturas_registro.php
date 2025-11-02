<?php
// =====================================
//  CONEXIÓN A LA BASE DE DATOS
// ===================================== 

// Se incluye el archivo 'db.php', que contiene la función getDB()
// para obtener una conexión segura a la base de datos mediante mysqli.
require_once __DIR__ . '/db.php';
// Se incluye procesar.php para manejo de sesion de adscripto
require __DIR__.'/procesar.php'; 

// =====================================
//  VERIFICAR MÉTODO DE ACCESO
// =====================================

// Este bloque solo se ejecuta si la página fue accedida por POST,
// es decir, si el usuario envió el formulario de registro de aula.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombreAsignatura = trim($_POST['nombreAsignatura'] ?? '');

    // Se valida que el campo no esté vacío.
    if ($nombreAsignatura !== '') {
    try {
        // =====================================
        //  INSERCIÓN EN LA BASE DE DATOS
        // =====================================
        // Se prepara una consulta SQL segura con marcadores (?) 
        // para evitar inyección de SQL.
        $stmt = getDB()->prepare("INSERT INTO asignatura (nombre) VALUES (?)");
        // Se asocia la variable $nombreAsignatura al marcador “?”.
        // “s” indica que el valor es de tipo string.
        $stmt->bind_param("s", $nombreAsignatura); 
        // Se ejecuta la consulta.
        $stmt->execute();

        // Después de ejecutar, obtenemos el ID de la nueva asignatura insertada.
        $idAsignatura = $stmt->insert_id;
        // Cerramos el statement para liberar memoria.
        $stmt->close();
        // Mensaje de éxito para mostrar en pantalla.
        $msg = "Asignatura guardada correctamente.";

        // =====================================
        //  REGISTRO EN HISTORIAL
        // =====================================

        // Incluye el archivo procesar_historial.php que contiene la función procesarHistorial()
        // para registrar las acciones realizadas en el sistema por el adscripto (registro, borrar, edición.).
        require_once __DIR__ . '/procesar_historial.php';
        // Llama a la función para guardar el evento en el historial:
        //   - 'asignatura' → tabla afectada
        //   - 'registro' → tipo de acción
        //   - $idAsignatura → ID (PK) de la asignatura afectada
        procesarHistorial('asignatura', 'registro',  $idAsignatura);

    } catch (mysqli_sql_exception $e) {
        // Si ocurre un error de base de datos, se captura y se muestra
        // de forma segura (sin exponer detalles del sistema).
        $msg = "Error al insertar: " . htmlspecialchars($e->getMessage());
    }
} else {
    // Si el nombre está vacío, se informa un mensaje de error.
    $msg = "Datos inválidos. Asegúrate de ingresar un nombre de grupo correcto.";
}
}
?>

<?php
// Se incluye la cabecera principal del sitio con estilos, íconos y navegación.
require __DIR__.'/header.php'; 
?>

<!-- =====================================
 SECCIÓN: FORMULARIO DE REGISTRO
 ===================================== -->
    <main>
     <div class="contenedor_titulo">
     <h1 class="titulo">Registro de Asignaturas</h1>
    </div>
    <div class="contenedor_formulario">
         <!-- Formulario que envía los datos a esta misma página (action="") -->
    <form class="formulario" action="" method="POST">
         
         <div class="casilla">
        <label class="guardar_datos" for="nombreAsignatura">Nombre de asignatura:</label>
        <input class="campo" type="text" id="nombreAsignatura" name="nombreAsignatura" required />
        </div>
        <br>
        <button class="boton_guardar" type="submit">Guardar</button>
        </form>
        </div>

        <main>

    <?php if (!empty($msg)): ?>
        <!-- Si hay un mensaje (de éxito o error), se muestra dentro de un recuadro -->
        <div class="mensaje-exito">
    <p><strong><?= $msg ?></strong></p>
    </div>
    <?php endif; ?>

     <?php
// =====================================
//  CONSULTA DE ASIGNATURAS REGISTRADAS
// =====================================

// Se realiza una consulta a la tabla “asignatura”
// para listar todas las asignaturas, con su fecha de creación y estado.
require_once __DIR__ . '/db.php';

$result = getDB()->query("SELECT id_asignatura, nombre, creado_en, activo FROM asignatura ORDER BY creado_en DESC");
// Se obtiene un arreglo asociativo con todas las filas encontradas.
$asignaturas = $result->fetch_all(MYSQLI_ASSOC);
// Se cierra el resultado para liberar recursos.
$result->close();
?>

<!-- =====================================
SECCIÓN: LISTADO DE ASIGNATURAS
===================================== -->
 <div class="cont_titulo">
<div class="contenedor__titulo-lista">
    <h2 class="titulo-lista">Asignaturas registradas</h2>
    </div>
    </div>

<!-- Filtro para mostrar/ocultar las asignaturas inactivas -->
  <div class="cont_checkbox">
    <div class="contenedor_checkbox">
    <label>
    <input type="checkbox" id="filtroInactivos" class="checkbox" checked>
    Mostrar asignaturas inactivos
    </label>
</div>
    </div>

    <!-- Si existen asignaturas, se muestran en una tabla -->
    <?php if ($asignaturas): ?>
        <div class="contenedor_tabla_responsive">
        <table  class="tabla_lista tabla" border="1" cellpadding="6" cellspacing="0">
            <thead class="tabla-cabecera">
                <tr class="tabla-fila">
                    <th class="tabla-celda">ID</th>
                    <th class="tabla-celda">Nombre</th>
                    <th class="tabla-celda">Creado en</th>
                    <th class="tabla-celda">Estado</th>
                    <th class="tabla-celda">Acciones</th>
    </tr>
    </thead>
    </tbody>
    <?php 
    foreach ($asignaturas as $a): ?>
     <tr class="tabla-fila <?= $a['activo'] ? '' : 'inactivo' ?>"> <!-- Agregamos la clase 'inactivo' si no está activo -->
        <td class="tabla-celda"><?= (int) $a['id_asignatura'] ?></td>
        <td class="tabla-celda"><?= htmlspecialchars($a['nombre']) ?></td>
        <td class="tabla-celda"><?= htmlspecialchars($a['creado_en']) ?></td>
        <td class="tabla-celda"><?= $a['activo'] ? 'ACTIVO' : 'INACTIVO' ?></td>
        <td class="tabla-celda">
       <!-- Enlace para editar -->
        <a href="asignatura_edit.php?id_asignatura=<?= (int) $a['id_asignatura'] ?>">Editar</a>
    </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    </div>
    <?php else: ?>
        <!-- Si no hay asignaturas en la base de datos -->
        <p>No hay asignaturas registradas aún.</p>
        <?php endif; ?>
    </main>
    <!-- =====================================
     SCRIPT: FILTRO DE ASIGNATURAS INACTIVAS
     ===================================== -->
         <!-- Código para que se escondan las celdas inactivas-->
        <script>
document.getElementById('filtroInactivos').addEventListener('change', function () {
    const mostrar = this.checked;
    const filasInactivas = document.querySelectorAll('.tabla-fila.inactivo');

    // Si el checkbox está marcado, se muestran todas las filas.
    // Si está desmarcado, se ocultan las asignaturas inactivas.
    filasInactivas.forEach(fila => {
        fila.style.display = mostrar ? '' : 'none';
    });
});
</script>
<?php
// Se incluye el footer
require __DIR__.'/footer.php';  
?>