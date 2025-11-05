<?php

session_start();

// --- Control de expiración de sesión ---
// Si el usuario estuvo inactivo más de 15 minutos (900 segundos), se destruye la sesión y redirige al login.
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 900)) {
    session_unset();     
    session_destroy();  
    header("Location: logeo.php?timeout=1"); 
    exit(); 
}


$_SESSION['LAST_ACTIVITY'] = time(); 

// --- Control de acceso ---

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'director') {
    header("Location: logeo.php"); 
    exit(); 
}

require_once __DIR__ . '/db.php';
 // Variable para almacenar mensajes de error o éxito
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //Obtiene los datos del formulario y elimina espacios en blanco.
    $nombreAdscripto = trim($_POST['nombreAdscripto'] ?? '');
    $apellidoAdscripto = trim($_POST['apellidoAdscripto'] ?? '');
    $usuarioAdscripto = trim($_POST['usuarioAdscripto'] ?? '');
    $contraseniaAdscripto = trim($_POST['contraseniaAdscripto'] ?? '');
    $repetirContrasenia = trim($_POST['repetircontrasenia'] ?? '');

    // Verifica que las contraseñas coincidan
    if ($contraseniaAdscripto !== $repetirContrasenia) {
        $msg = "Las contraseñas no coinciden.";
    }

    // Verifica que los demás campos no estén vacíos
    elseif ($nombreAdscripto === '' || $apellidoAdscripto === '' || $usuarioAdscripto === '' || $contraseniaAdscripto === '') {
        $msg = "Datos inválidos. Asegúrate de ingresar los datos correctamente.";
    } 
    
    // Si todo está correcto, procede a guardar el registro
    else {
    // Hashea la contraseña de forma segura antes de guardarla en la base de datos
        $hash = password_hash($contraseniaAdscripto, PASSWORD_DEFAULT);
        try {
            // Prepara la consulta SQL para evitar inyecciones
            $stmt = getDB()->prepare("INSERT INTO adscripto (nombre, apellido, usuario, contraseña) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssis", $nombreAdscripto, $apellidoAdscripto, $usuarioAdscripto, $hash);
           //Si todo está correcto, se ejecuta.
            $stmt->execute();
            $stmt->close();
            $msg = "Adscripto guardado correctamente.";
            // Si ocurre un error al insertar se captura y se muestra en un mensaje
        } catch (mysqli_sql_exception $e) {
            $msg = "Error al insertar: " . htmlspecialchars($e->getMessage());
}
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Adscriptos</title>
    <link rel="stylesheet" href="../assets/css/registro.css"><!--Link al estilo-->
    <link  rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"> <!--Imagen de fondo-->
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
      <span class="material-symbols-outlined" id="iconoMenu">menu</span>
  </div>
  <div id="menuDesplegable" class="menu-desplegable">
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

    <div class="contenedor_titulo">
    <h1 class="titulo">Registro de Adscriptos</h1>
    </div>

   <div class="contenedor_formulario">
    <form class="formulario" action="" method="POST">
    
    <div class="casilla">
        <label class="guardar_datos" for="nombreAdscripto">Nombre:</label>
        <input class="campo" type="text" pattern="[A-Za-zÁáÉéÍíÓóÚúÑñ\s]+"
       title="Solo se permiten letras y espacios" id="nombreAdscripto" name="nombreAdscripto" required/>
    </div>
    
    <div class="casilla">
        <label class="guardar_datos" for="apellidoAdscripto">Apellido:</label>
        <input class="campo" type="text" pattern="[A-Za-zÁáÉéÍíÓóÚúÑñ\s]+"
       title="Solo se permiten letras y espacios" id="apellidoAdscripto" name="apellidoAdscripto" required/>
    </div>

<!-- inputmode="numeric" muestra teclado numérico en móviles-->
<!-- maxlength="8" limita la entrada a 8 caracteres-->
<!-- pattern="\d{1,8}": asegura que solo se ingresen entre 1 y 8 dígitos-->
    <div class="casilla">
        <label class="guardar_datos" for="usuarioAdscripto">Usuario (Cédula):</label>
        <input class="campo" type="text" inputmode="numeric" maxlength="8" pattern="\d{1,8}" id="usuarioAdscripto" name="usuarioAdscripto" required/>
    </div>

    <div class="casilla">
        <label class="guardar_datos" for="contraseniaAdscripto">Contraseña:</label>
        <input class="campo" type="password" id="contraseniaAdscripto" name="contraseniaAdscripto" required/>
    </div>

    <div class="casilla">
        <label class="guardar_datos" for="repetircontrasenia">Repetir Contraseña:</label>
        <input class="campo" type="password" id="repetircontrasenia" name="repetircontrasenia" required/>
    </div>
    
    <button class="boton_guardar" type="submit">Guardar</button>
    <!--Muestra el mensaje generado por el bloque PHP (error o éxito).-->
    </form>
    </div>


    <?php if ($msg): ?>
        <div class="mensaje-exito">
        <p><?= htmlspecialchars($msg) ?></p>
        </div>
    <?php endif; ?>
 
 <?php

$result = getDB()->query("SELECT usuario, nombre, apellido, creado_en FROM adscripto ORDER BY apellido DESC");
$adscriptos = $result->fetch_all(MYSQLI_ASSOC);

$result->close();
?>

<div class="cont_titulo">
<div class="contenedor__titulo-lista">
    <h2 class="titulo-lista">Adscriptos Registrados</h2>
    </div>
    </div>

    <!-- Muestra los datos en una tabla -->
    <?php if ($adscriptos): ?>
    <div class="contenedor_tabla_responsive">
        <table class="tabla_lista tabla" border="1" cellpadding="6" cellspacing="0">
            <thead class="tabla-cabecera">
                <tr class="tabla-fila">
                    <th class="tabla-celda">Usuario</th>
                    <th class="tabla-celda">Nombre</th>
                    <th class="tabla-celda" >Apellido</th>
                    <th class="tabla-celda">Creado en</th>
                    <th class="tabla-celda">Acciones</th>
    </tr>
    </thead>
    <tbody>
    <?php 
    foreach ($adscriptos as $a): ?>
    <tr class="tabla-fila">
        <td class="tabla-celda"><?= (int) $a['usuario'] ?></td>
        <td class="tabla-celda"><?= htmlspecialchars($a['nombre']) ?></td>
        <td class="tabla-celda"><?= htmlspecialchars($a['apellido']) ?></td>
        <td class="tabla-celda"><?= htmlspecialchars($a['creado_en']) ?></td>
        <td class="tabla-celda">
        <!-- Cada fila de alumno tiene un enlace para editar sus datos, pasando el usuario (PK) como parámetro.-->
        <div class="botones_acciones">
        <a href="adscripto_edit.php?usuario=<?= (int) $a['usuario'] ?>">Editar</a>
        <a href="adscripto_delete.php?usuario=<?= (int) $a['usuario'] ?>" onclick="return confirm('¿Estás seguro de que deseas borrar este adscripto?');">Borrar</a>

        <form action="reseteo_contraseña_adscripto.php" method="POST" style="display:inline;" onsubmit="return confirm('¿Deseas resetear la contraseña de este adscripto?');">
        <input type="hidden" name="usuario" value="<?= (int) $a['usuario'] ?>">
        <button class="boton_resetear" type="submit">Resetear contraseña</button>
    </form>
    </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    </div>
    <?php else: ?>
        <p>No hay adscriptos registrados aún.</p>
        <?php endif; ?>
</body>
</html>
<script src="validaciones.js"></script>
<?php
require __DIR__.'/footer.php';  
?>
