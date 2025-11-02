<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
     <link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&icon_names=newspaper" />
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"> <!--Imagen de fondo-->
     
     <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Gabriela&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Domine:wght@400..700&display=swap" rel="stylesheet">
<link rel="icon" href="../assets/img/logo.png" type="image/png">
</head>
<body>
     <header class="header">
      <img class="logo" src="../assets/img/logo.png">
  <h1 class="header__texto">Sistema de Gestión de Recursos y Espacios Institucionales</h1>
  <div class="contenedor__iconos">
    <a href="noticias.php">
      <span class="material-symbols-outlined">newspaper</span>
      </a>
  </div>
</header>
<?php if (isset($_GET['error']) && $_GET['error'] == 1): ?>
  <div class="error_logeo">
    <p class="texto__error_logeo"; text-align: center;">Usuario o contraseña incorrectos.</p>
  </div>
<?php endif; ?>
<main class="contenedor__login">
    <form class="inicioSesion" method="post" action="login.php">

        <label class="formulario" for="usuario">Cédula:</label>
        <input class="campo" type="text"  maxlength="8"  pattern="^[A-Za-z0-9]{1,8}$" id="usuario" name="usuario" required />
        <br>
        <label class="formulario" for="contraseña">Contraseña:</label>
        <input class="campo" type="password" id="contraseña" name="contraseña" required />
        
        <div class="botonInicioSesion">
        <button class="boton_entrar" type="submit">Iniciar Sesión</button>
        </div>
        </form>
      </main>

   <footer class="footer">
    <div class="footer__contenedor-lista">
    <ul class="contenedor-integrantes">
        <li class="contenedor-integrantes__lista">Evelyn Gonzalez</li>
        <li class="contenedor-integrantes__lista">María Gómez</li>
        <li class="contenedor-integrantes__lista">César Curbelo</li>
    </ul>
    </div>
   <div class="footer__redes">
    <a href="https://www.facebook.com/itsutupaysandu/?locale=es_LA" target="_blank">
      <img class="icono__redes" src="../assets/img/facebook.svg" alt="Facebook">
    </a>
    <a href="https://www.instagram.com/its_utu.paysandu/?hl=es" target="_blank">
      <img class="icono__redes" src="../assets/img/instagram.svg" alt="Instagram">
    </a>
</footer>
</body>
</html>