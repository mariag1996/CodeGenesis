<?php

session_start();

// --- Control de expiración de sesión ---
// Si el usuario estuvo inactivo más de 15 minutos (900 segundos), se destruye la sesión y redirige al login.
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 900)) {
    session_unset();     // Limpia variables de sesión
    session_destroy();   // Destruye la sesión
    header("Location: logeo.php?timeout=1");
    exit();
}

// Actualiza el tiempo de la última actividad
$_SESSION['LAST_ACTIVITY'] = time(); 

// --- Control de acceso ---
// Verifica que haya un usuario logueado y que sea "director"
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'director') {
    header("Location: logeo.php");
    exit();
}


/* Conexión a base de datos */
require_once __DIR__ . '/db.php';

// Obtiene el parámetro usuario desde la URL (?usuario=...), lo convierte a entero
$usuario = isset($_GET['usuario']) ? (int) $_GET['usuario'] : 0;

//Si el valor de usuario es menor o igual a 0, se detiene el script mostrando un mensaje de error.
if ($usuario <= 0) {
    die("Usuario inválido.");
}

// --- Consulta del adscripto a editar ---
$stmt = getDB()->prepare("SELECT usuario, nombre, apellido FROM adscripto WHERE usuario = ?");

//bind_param() Sirve para vincular variables PHP a esos marcadores ? en la consulta SQL preparada.
//Primer argumento "i" indica que el tipo de dato es entero (integer).
$stmt->bind_param("i", $usuario);
//“Este ? se reemplaza con la variable $id, que es un número entero."

$stmt->execute();
$result = $stmt->get_result();
$adscripto = $result->fetch_assoc();
$stmt->close();


//Si no se encuentra el adscripto, se muestra un mensaje y se detiene el script.
if (!$adscripto) {
    die("Adscripto no encontrado.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Adscriptos</title>
</head>
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
      <p class="header__texto">Registro</p>
    </div>
  <!-- Contenedor de los íconos-->
      <div class="contenedor__iconos">
        <a href="adscripto_registro.php">  <!-- Dirige el ícono de home a registro_main.php-->
      <span class="material-icons">home</span>
    </a>
      <span class="material-symbols-outlined" id="iconoMenu">menu</span>
  </div>
  <div id="menuDesplegable" class="menu-desplegable">
  <a href="logout.php" class="boton-menu">Cerrar sesión</a>
</div>
    </header>
    <script>
  const iconoMenu = document.getElementById('iconoMenu');
  const menuDesplegable = document.getElementById('menuDesplegable');

// Cuando haces clic en el ícono del menú...
  iconoMenu.addEventListener('click', () => {
// Si el menú está visible (display:flex), lo oculta; si está oculto (none), lo muestra.
    menuDesplegable.style.display = 
      menuDesplegable.style.display === 'flex' ? 'none' : 'flex';
  });

 
</script>

     <div class="contenedor_titulo">
    <h1 class= "titulo">Editar adscripto</h1>
</div>

    <div class="contenedor_formulario">
        <!-- El formulario envía los datos a docente_update.php mediante POST.-->
    <form class="formulario" method="post" action="adscripto_update.php">
        <!--El campo usuario se envía oculto (type="hidden") para identificar al docente en la actualización.-->
        <input  type="hidden" name="usuario" value="<?= (int) $adscripto['usuario'] ?>">

        <!--Se usa htmlspecialchars() para evitar problemas de seguridad-->
        <label class="guardar_datos">Nombre:
            <input class="campo" type="text" pattern="[A-Za-zÁáÉéÍíÓóÚúÑñ\s]+"
       title="Solo se permiten letras y espacios" id="nombreAdscripto" name="nombre" required value="<?= htmlspecialchars($adscripto['nombre']) ?>">
        </label><br><br>

        <label class="guardar_datos">Apellido:
            <input class="campo" type="texto" pattern="[A-Za-zÁáÉéÍíÓóÚúÑñ\s]+"
       title="Solo se permiten letras y espacios" id="apellidoAdscripto" name="apellido" required value="<?= htmlspecialchars($adscripto['apellido']) ?>">
        </label><br><br>

        <button class="boton_guardar" type="submit">Actualizar</button>
    </form>
</div>
    <div class="contenedor__boton_listado">
        <!--Botón para volver al listado:-->
    <p><a class="boton_listado" href="adscripto_registro.php">Volver al listado</a></p>
</div>
</main>
</body>
</html>

<script src="validaciones.js"></script>
    <?php
require __DIR__.'/footer.php';  
?>