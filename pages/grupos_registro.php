<?php
require_once __DIR__ . '/db.php';
require __DIR__.'/procesar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombreGrupo = trim($_POST['nombreGrupo'] ?? '');

    if ($nombreGrupo !== '') {
    try {
        $stmt = getDB()->prepare("INSERT INTO grupo (nombre) VALUES (?)");
        $stmt->bind_param("s", $nombreGrupo); 
        $stmt->execute();

    
        $stmt->close();
        $msg = "Grupo guardado correctamente.";

        require_once __DIR__ . '/procesar_historial.php';
        procesarHistorial('grupo', 'registro', $nombreGrupo);

    } catch (mysqli_sql_exception $e) {
        $msg = "Error al insertar: " . htmlspecialchars($e->getMessage());
    }
} else {
    $msg = "Datos inválidos. Asegúrate de ingresar un nombre de grupo correcto.";
}
}
?>

<?php
require __DIR__.'/header.php'; 
?>

     
        <div class="contenedor_titulo">
    <h1 class="titulo">Registro de Grupos</h1>
    </div>
    <div class="contenedor_formulario">
    <form class="formulario" action="" method="POST">
     <div class="casilla">
        <label class="grupo guardar_datos" for="grupo">Nombre del grupo:</label>
        <input class="campo" type="text" name="nombreGrupo" id="nombreGrupo" required />
    </div>
        <br>
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

$result = getDB()->query("SELECT nombre, creado_en, activo FROM grupo ORDER BY creado_en DESC");
$grupos = $result->fetch_all(MYSQLI_ASSOC);

$result->close();
?>

 <div class="cont_titulo">
<div class="contenedor__titulo-lista">
<h2 class="titulo-lista">Grupos registrados</h2>
</div>
</div>


  <div class="cont_checkbox">
    <div class="contenedor_checkbox">
    <label>
    <input type="checkbox" id="filtroInactivos" class="checkbox" checked>
    Mostrar alumnos con grupos inactivos
    </label>
</div>
    </div>

</div>
    <?php if ($grupos): ?>
        <div class="contenedor_tabla_responsive">
        <table class="tabla_lista tabla" border="1" cellpadding="6" cellspacing="0">
            <thead class="tabla-cabecera">
                <tr class="tabla-fila">
                    <th class="tabla-celda">Nombre</th>
                    <th class="tabla-celda">Creado en</th>
                    <th class="tabla-celda">Estado</th>
                    <th class="tabla-celda">Acciones</th>
    </tr>
    </thead>
    </tbody>
    <?php 
    foreach ($grupos as $a): ?>
     <tr class="tabla-fila <?= $a['activo'] ? '' : 'inactivo' ?>"> <!-- Agregamos la clase 'inactivo' si no está activo -->
        <td class="tabla-celda"><?= htmlspecialchars($a['nombre']) ?></td>
        <td class="tabla-celda"><?= htmlspecialchars($a['creado_en']) ?></td>
         <td class="tabla-celda"><?= $a['activo'] ? 'ACTIVO' : 'INACTIVO' ?></td>
        <td class="tabla-celda">
       <a href="grupo_edit.php?nombre=<?= urlencode($a['nombre']) ?>">Editar</a>
    </td>
    </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    </div>
    <?php else: ?>
        <p>No hay grupos registrados aún.</p>
        <?php endif; ?>
       
        <!-- Código para que se escondan las celdas inactivas-->
        <script>
document.getElementById('filtroInactivos').addEventListener('change', function () {
    const mostrar = this.checked;
    const filasInactivas = document.querySelectorAll('.tabla-fila.inactivo');

    filasInactivas.forEach(fila => {
        fila.style.display = mostrar ? '' : 'none';
    });
});
</script>

<?php
require __DIR__.'/footer.php';  
?>