<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link rel="stylesheet" href="../assets/css/registro.css"><!--Link al estilo-->
     <link  rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"> <!--Imagen de fondo-->
   <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"><!--Link a los iconos de el header-->
     <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=menu"><!--Link a los iconos de el header-->
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400..900&display=swap" rel="stylesheet">
     <link rel="icon" href="../assets/img/logo.png" type="image/png">
    </head>
<body>
    <header class="header">
      <div class="contenedor__header__texto">
      <p class="header__texto">Registros</p>
    </div>
  <!-- Contenedor de los íconos-->
       <div class="contenedor__iconos">
    <a href="registro_main.php">  <!-- Dirige el ícono de home a registro_main.php-->
      <span class="material-icons">home</span>
    </a>
      <span class="material-symbols-outlined" id="iconoMenu">menu</span>
  </div>
  <div id="menuDesplegable" class="menu-desplegable">
  <a href="cambio_contraseña_adscripto.php" class="boton-menu">Cambiar contraseña</a>
  <a href="logout.php" class="boton-menu">Cerrar sesión</a>
</div>
    </header>
    <script>
  const iconoMenu = document.getElementById('iconoMenu');
  const menuDesplegable = document.getElementById('menuDesplegable');

  iconoMenu.addEventListener('click', () => {
    menuDesplegable.style.display = 
      menuDesplegable.style.display === 'flex' ? 'none' : 'flex';
  });
  
</script>
<main>