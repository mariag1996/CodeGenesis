<!-- Pagina principal de los usuarios docentes (cuando inician sesion se redirige a esta) -->
<?php
session_start();

// --- Control de expiración de sesión ---
// Si el usuario estuvo inactivo más de 15 minutos (900 segundos), se destruye la sesión y redirige al login.
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 900)) {
    session_unset();     // Limpia variables de sesión
    session_destroy();   // Destruye la sesión
    header("Location: logeo.php?timeout=1");  // Redirige al login
    exit();
}
// Actualiza el tiempo de la última actividad
$_SESSION['LAST_ACTIVITY'] = time(); 

// --- Control de acceso ---
// Solo los usuarios con rol 'docente' pueden acceder.
// Si no existe la variable de sesión 'usuario' o el rol no es 'docente',
// redirige al formulario de login.
if (!isset($_SESSION['docente']) || $_SESSION['rol'] !== 'docente') {
    header("Location: logeo.php"); // Redirige al login
    exit();
}

//Conecta a la base de datos
require_once __DIR__ . '/db.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../assets/css/registro.css"><!--Link al estilo-->
     <link  rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"> <!--Imagen de fondo-->
     <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=menu"><!--Link a los iconos de el header-->
    <link rel="icon" href="../assets/img/logo.png" type="image/png">
    </head>
<body>
<header class="header">
    
   <!-- Ícono del menú desplegable -->
      <div class="contenedor__iconos">
      <span class="material-symbols-outlined" id="iconoMenu">menu</span>
  </div>
  <!-- Menú desplegable -->
  <div id="menuDesplegable" class="menu-desplegable">
  <a href="cambio_contraseña_docente.php" class="boton-menu">Cambiar contraseña</a>
  <a href="logout.php" class="boton-menu">Cerrar sesión</a>
</div>
    </header>
    <!-- Script para abrir/cerrar menú -->
    <script>
  const iconoMenu = document.getElementById('iconoMenu');
  const menuDesplegable = document.getElementById('menuDesplegable');

  iconoMenu.addEventListener('click', () => {
    menuDesplegable.style.display = 
      menuDesplegable.style.display === 'flex' ? 'none' : 'flex';
  });
</script>

<!-- Título de la página -->
<div class="contenedor_titulo">
<h1 class="titulo">Reservas</h1>
</div>
<!-- Tarjetas -->
  <section class="seccion__tarjetas__reservas">
  <div class="contenedor__tarjetas__reservas">
    <div class="tarjeta_reserva">
      <a href="reservas_aula.php">Reservar aulas</a>
    </div>
    <div class="tarjeta_reserva">
      <a href="reservas_recurso.php">Reservar recursos</a>
    </div>
  </div>
</section>

<?php
// Linkea el footer
require __DIR__.'/footer.php';  
?>