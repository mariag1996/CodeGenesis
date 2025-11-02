<?php
require_once __DIR__ . '/db.php';
require __DIR__.'/procesar.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombreRecurso = trim($_POST['nombreRecurso'] ?? '');
    $cantidad = trim($_POST['cantidad'] ?? '');

    if ($nombreRecurso !== '') {
    try {
        $stmt = getDB()->prepare("INSERT INTO recurso (nombre, cantidad) VALUES (?, ?)");
        $stmt->bind_param("si", $nombreRecurso, $cantidad); 
        $stmt->execute();

         // Obtener el ID 
            $idRecurso = $stmt->insert_id;
     

        $stmt->close();
        $msg = "Recurso guardado correctamente.";

        require_once __DIR__ . '/procesar_historial.php';
        procesarHistorial('recurso', 'registro', $idRecurso);

    } catch (mysqli_sql_exception $e) {
        $msg = "Error al insertar: " . htmlspecialchars($e->getMessage());
    }
} else {
    $msg = "Datos inválidos. Asegúrate de ingresar un nombre de recurso correcto.";
}
}
?>

<?php
require __DIR__.'/header.php'; 
?>
       <div class="contenedor_titulo">
    <h1 class="titulo">Registro de Recursos</h1>
      </div>
    <div class="contenedor_formulario">
    <form class="formulario" action="" method="POST">
     <div class="casilla">
        <label class="guardar_datos" for="recurso">Nombre del recurso:</label>
        <input class="campo" type="text" name="nombreRecurso" id="nombreRecurso" required />
    </div>
        <br>
        <div class="casilla">
        <label class="guardar_datos" for="recurso">Cantidad:</label>
        <input class="campo" type="number" name="cantidad" id="cantidad" required />
    </div>
        <button class="boton_guardar" type="submit">Guardar</button>
</form>
      </div>

 <?php if (!empty($msg)): ?>
    <div class="mensaje-exito">
    <p><strong><?= $msg ?></strong></p>
     </div>
    <?php endif; ?>

<?php
require_once __DIR__ . '/db.php';

$result = getDB()->query("SELECT id_recurso, nombre, cantidad, creado_en FROM recurso ORDER BY creado_en DESC");
$nombreRecurso = $result->fetch_all(MYSQLI_ASSOC);

$result->close();
?>

<div class="cont_titulo">
   <div class="contenedor__titulo-lista">
<h2>Recursos registrados</h2>
 </div>
</div>
 <!--  .................................. Lista de Recursos ............................ -->
    <?php if ($nombreRecurso): ?>
        <div class="contenedor_tabla_responsive">
        <table class="tabla_lista tabla" border="1" cellpadding="6" cellspacing="0">
            <thead class="tabla-cabecera">
                <tr class="tabla-fila">
                    <th class="tabla-celda">ID</th>
                    <th class="tabla-celda">Nombre</th>
                    <th class="tabla-celda">Cantidad Disponible</th>
                    <th class="tabla-celda">Creado en</th>
                    <th class="tabla-celda">Acciones</th>
    </tr>
    </thead>
    </tbody>
    <?php 
    foreach ($nombreRecurso as $a): ?>
    <tr class="tabla-fila">
        <td class="tabla-celda"><?= htmlspecialchars($a['id_recurso']) ?></td>
        <td class="tabla-celda"><?= htmlspecialchars($a['nombre']) ?></td>
        <td class="tabla-celda"><?= htmlspecialchars($a['cantidad']) ?></td>
        <td class="tabla-celda"><?= htmlspecialchars($a['creado_en']) ?></td>
        <td class="tabla-celda">
       <a href="recurso_edit.php?id_recurso=<?= urlencode($a['id_recurso']) ?>">Editar</a>
    </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    </div>
    <?php else: ?>
        <p class="mensaje_lista">No hay Recursos registrados aún.</p>
        <?php endif; ?>

        <?php
require __DIR__.'/footer.php';  
?>