<?php
require_once __DIR__ . '/db.php';
require __DIR__.'/procesar.php';

$db = getDB();



// Obtener todos los grupos con horarios registrados
$result = $db->query("SELECT DISTINCT grupo FROM horario ORDER BY grupo ASC");
$grupos = $result->fetch_all(MYSQLI_ASSOC);
$result->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../assets/css/tablas.css">
   <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
     <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=menu" >
     <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400..900&display=swap" rel="stylesheet"> 
    </head>
<body>
<header class="header">
  <div class="contenedor__header__texto">
      <p class="header__texto">Registros</p>
    </div>
       <div class="contenedor__iconos">
    <a href="registro_main.php">
      <span class="material-icons">home</span>
    </a>
      <span class="material-symbols-outlined" id="iconoMenu">menu</span>
  </div>
  <div id="menuDesplegable" class="menu-desplegable">
  <a href="cambio-contra-adscrip.php" class="boton-menu">Cambiar contraseña</a>
  <a href="login.php" class="boton-menu" onclick="cerrarSesion()">Cerrar sesión</a>
</div>
    </header>
    <script>
  const iconoMenu = document.getElementById('iconoMenu');
  const menuDesplegable = document.getElementById('menuDesplegable');

  iconoMenu.addEventListener('click', () => {
    menuDesplegable.style.display = 
      menuDesplegable.style.display === 'flex' ? 'none' : 'flex';
  });

  function cerrarSesion() {
    //Agregar lógica para cerrar sesión en PHP
    alert("Sesión cerrada");
  }
</script>

<div class="contenedor_titulo">
      <h2 class="titulo">Inasistencias por Grupo</h2>
        </div>

  <main class="contenedor">
   
    <?php if ($grupos): ?>
       <div class="contenedor_tabla_responsive">
      <table class="tabla_inasistencias" border="1" cellpadding="6" cellspacing="0">
        <thead class="cabecera">
          <tr class="cabecera__fila">
            <th class="fila">Grupo</th>
            <th class="fila">Ver tabla actual</th>
            <th class="fila">Fecha de actualización</th>
            <th class="fila">Acciones</th>
          </tr>
        </thead>
        <tbody>
  <?php foreach ($grupos as $g): 
    $grupo = $g['grupo'];

    // Obtener la fecha de la última actualización para el grupo
      $stmt = $db->prepare("SELECT fecha_editado FROM inasistencia WHERE grupo = ? ORDER BY fecha_editado DESC LIMIT 1");
      $stmt->bind_param("s", $grupo);
      $stmt->execute();
      $result = $stmt->get_result();
      $fecha_editado = $result->fetch_assoc();
      $stmt->close();
      ?>
    
     <tr>
      <td><?= htmlspecialchars($grupo) ?></td>
                <td>
                    <div class="contenedor_botones">
                    <a href="inasistencias_ver.php?grupo=<?= urlencode($grupo) ?>">Ver</a>
                    </div>
                </td>
                <td>
                    <?php if ($fecha_editado): ?>
                        <span><?= $fecha_editado['fecha_editado'] ?></span>
                    <?php else: ?>
                        <span>No disponible</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php
                    // Verificar si ya hay inasistencias registradas
                    $stmt = $db->prepare("SELECT id_inasistencia FROM inasistencia WHERE grupo = ? LIMIT 1");
                    $stmt->bind_param("s", $grupo);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $inasistencia = $result->fetch_assoc();
                    $stmt->close();
                    ?>
                    <?php if ($inasistencia): ?>
                        <div class="contenedor_botones">
                            <a href="inasistencias_edit.php?id_inasistencia=<?= $inasistencia['id_inasistencia'] ?>">Editar</a>
                             <a href="inasistencias_delete.php?grupo=<?= urlencode($grupo) ?>" onclick="return confirm('¿Estás seguro de que deseas borrar esta tabla de inasistencias para el grupo <?= htmlspecialchars($grupo) ?>?');">Borrar</a>
                          </div>
                    <?php else: ?>
                        <div class="contenedor_botones">
                            <a href="inasistencias_registro.php?grupo=<?= urlencode($grupo) ?>">Crear</a>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        </table>
                    </div>
    <?php else: ?>
        <p>No hay horarios registrados aún.</p>
    <?php endif; ?>
<?php
require __DIR__.'/footer.php';  
?>