<?php
require_once __DIR__ . '/db.php';
?>
<?php
require __DIR__.'/header.php'; 
?>


<?php
require __DIR__.'/procesar.php'; 
?>


<section class="seccion-tarjetas">
     <div class="contenedor-tarjetas">
      <div class="tarjeta"><a class="tarjeta__texto" href="grupos_registro.php">Registrar grupos</a></div>
      <div class="tarjeta"><a class="tarjeta__texto" href="alumnos_registro.php">Registrar alumnos</a></div>
      <div class="tarjeta"><a class="tarjeta__texto" href="docentes_registro.php">Registrar docentes</a></div>
      <div class="tarjeta"><a class="tarjeta__texto" href="asignaturas_registro.php">Registrar asignaturas</a></div>
      <div class="tarjeta"><a class="tarjeta__texto" href="aulas_registro.php">Registrar aulas</a></div>
      <div class="tarjeta"><a class="tarjeta__texto" href="horarios_registro.php">Registrar horarios</a></div>
      <div class="tarjeta"><a class="tarjeta__texto" href="inasistencias_index.php">Registrar inasistencias</a></div>
      <div class="tarjeta"><a class="tarjeta__texto" href="recursos_registro.php">Registrar recursos</a></div>
      <div class="tarjeta"><a class="tarjeta__texto"href="noticias_registro.php">Registrar noticias</a></div>
      <div class="tarjeta"><a class="tarjeta__texto" href="ver_reservas.php">Ver reservaciones</a></div>
      <div class="tarjeta"><a class="tarjeta__texto" href="historial_registros.php">Ver el historal de registros</a></div>
    </div>
</section>

<?php
require __DIR__.'/footer.php';  
?>