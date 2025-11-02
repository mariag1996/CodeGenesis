<?php
require_once __DIR__ . '/db.php';

require __DIR__.'/procesar.php'; 

require __DIR__.'/header.php'; 
?>

<div class="contenedor_titulo">
    <h1 class="titulo" >Ver Reservas</h1>
    </div>

    <section class="seccion__tarjetas__reservas">
     <div class="contenedor__tarjetas__reservas">
      <div class="tarjeta"><a class="tarjeta__texto" href="ver_reservas_aulas.php">Ver reservas de Aulas</a></div>
      <div class="tarjeta"><a class="tarjeta__texto" href="ver_reservas_recursos.php">Ver reservas de Recursos</a></div>
    </div>
</section>


<?php
require __DIR__.'/footer.php';  
?>