<!-- ......Este archivo se encarga de cambiar la contraseña del usuario alumno........ -->
<?php
// =====================================
//  INICIO DE SESIÓN Y CONEXIÓN A DB
// =====================================
session_start();
require_once __DIR__ . '/db.php';

// --- Control de expiración de sesión ---
// Si el usuario estuvo inactivo más de 15 minutos (900 segundos), se destruye la sesión y redirige al login.
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 900)) {
    session_unset();     // Limpia variables de sesión
    session_destroy();   // Destruye la sesión
    header("Location: logeo.php?timeout=1"); // Redirige al login
    exit();
}

// Actualiza el tiempo de la última actividad
$_SESSION['LAST_ACTIVITY'] = time(); 

// --- Control de acceso ---
// Solo los usuarios con rol 'alumno' pueden acceder.
// Si no existe la variable de sesión 'usuario' o el rol no es 'alumno',
// redirige al formulario de login.
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'alumno') {
    header("Location: logeo.php");
    exit();
}
//Conecta a la base de datos
$db = getDB();
$usuario = $_SESSION['usuario']; // Cédula del alumno (entero)
$mensaje = "";

// =====================================
//  MANEJO DEL FORMULARIO POST
// =====================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Se obtienen las contraseñas ingresadas por el usuario.
    $nueva = $_POST['nueva_contraseña'] ?? '';
    $confirmar = $_POST['confirmar_contraseña'] ?? '';

    // =====================================
    //  VALIDACIÓN DE LA CONTRASEÑA
    // =====================================

    // 1️⃣ La contraseña debe tener al menos 8 caracteres, 
    //    al menos una letra mayúscula y un número.
    if (strlen($nueva) < 8 || 
        !preg_match('/[A-Z]/', $nueva) || 
        !preg_match('/[0-9]/', $nueva)) {
        $mensaje = "La contraseña debe tener al menos 8 caracteres, una mayúscula y un número.";
         // 2️⃣ Las contraseñas deben coincidir.
    } elseif ($nueva !== $confirmar) {
        $mensaje = "Las contraseñas no coinciden.";

    // =====================================
    //  ACTUALIZAR CONTRASEÑA EN LA BASE DE DATOS
    // =====================================
    } else {
        // Hasheamos la contraseña antes de guardarla
        $hash = password_hash($nueva, PASSWORD_DEFAULT);
        // Se prepara la consulta SQL para actualizar la contraseña del usuario actual.
        $stmt = $db->prepare("UPDATE alumno SET contraseña = ? WHERE usuario = ?");
        // Se vinculan los parámetros: hash de la contraseña (string) y usuario (int).
        $stmt->bind_param("si", $hash, $usuario);
        // Se ejecuta la consulta y se define el mensaje según el resultado.
        if ($stmt->execute()) {
            $mensaje = "Contraseña actualizada correctamente.";
        } else {
            $mensaje = "Error al actualizar la contraseña.";
        }
        // Cerramos el statement para liberar memoria.
        $stmt->close();
    }
}
?>

<!-- =====================================
TÍTULO DE LA PÁGINA
===================================== -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cambiar Contraseña</title>
    <link rel="stylesheet" href="../assets/css/registro.css">
    <link  rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"> <!--Imagen de fondo-->
   <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"><!--Link a los iconos de el header-->
     <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=menu"><!--Link a los iconos de el header-->
    <link rel="icon" href="../assets/img/logo.png" type="image/png">
    </head>
</head>
<body>
<header class="header">
  <!-- Contenedor de los íconos-->
       <div class="contenedor__iconos">
         <a href="alumnos.php">  <!-- Dirige el ícono de home a registro_main.php-->
      <span class="material-icons">home</span>
    </a>
      <span class="material-symbols-outlined" id="iconoMenu">menu</span>
  </div>
  <div id="menuDesplegable" class="menu-desplegable">
  <a href="cambio_contraseña_alumno.php" class="boton-menu">Cambiar contraseña</a>
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

<!-- =====================================
FORMULARIO DE CAMBIO DE CONTRASEÑA
===================================== -->
<div class="contenedor_titulo">
         <h1 class="titulo" >Cambiar Contraseña</h1>
</div>

        <div class="contenedor_formulario">
        <form class="formulario" method="POST" action="">
         <!-- Nueva Contraseña -->    
        <div class="casilla">
        <label class="guardar_datos" for="nueva_contraseña">Nueva Contraseña:</label><br>
            <input class="campo" type="password" id="nueva_contraseña" name="nueva_contraseña" required
                   pattern="(?=.*[A-Z])(?=.*\d).{8,}"
                   title="Debe tener al menos 8 caracteres, una mayúscula y un número."><br><br>
        </div>
         <!-- Confirmar Contraseña -->
        <div class="casilla">
            <label class="guardar_datos" for="confirmar_contraseña">Confirmar Contraseña:</label><br>
            <input class="campo" type="password" id="confirmar_contraseña" name="confirmar_contraseña" required><br><br>
            </div> 

            <button class="boton_guardar" type="submit">Cambiar Contraseña</button>
        </form>
            </div>
    </div>
     <?php if ($mensaje): ?>
        <div class="mensaje-exito">
            <p style="color: <?= strpos($mensaje, '') !== false ? 'green' : 'red' ?>;">
                <?= htmlspecialchars($mensaje) ?>
            </p>
            </div>
        <?php endif; ?>
</body>
</html>