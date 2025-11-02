<?php
require_once __DIR__ . '/db.php';
require __DIR__.'/procesar.php';

$db = getDB();

$id_horario = isset($_GET['id_horario']) ? (int) $_GET['id_horario'] : 0;

// Obtener el grupo correspondiente
$stmt = $db->prepare("SELECT grupo FROM horario WHERE id_horario = ?");
$stmt->bind_param("i", $id_horario);
$stmt->execute();
$result = $stmt->get_result();
$grupo = $result->fetch_assoc()['grupo'] ?? null;
$stmt->close();

if (!$grupo) {
    echo "Horario no encontrado.";
    exit;
}

// Obtener todos los horarios del grupo
$stmt = $db->prepare("SELECT dia, hora, id_asignatura, hora_inicio, hora_fin FROM horario WHERE grupo = ? ORDER BY hora ASC");
$stmt->bind_param("s", $grupo);
$stmt->execute();
$horarios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Obtener nombres de asignaturas
$asignaturas = $db->query("SELECT id_asignatura, nombre FROM asignatura")->fetch_all(MYSQLI_ASSOC);
$mapa_asignaturas = [];
foreach ($asignaturas as $a) {
    $mapa_asignaturas[$a['id_asignatura']] = $a['nombre'];
}
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
     <link rel="icon" href="../assets/img/logo.png" type="image/png">
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
  <a href="login.html" class="boton-menu" onclick="cerrarSesion()">Cerrar sesión</a>
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
<h2 class="titulo" >Horario del grupo <?= htmlspecialchars($grupo) ?></h2>
</div>

<section class="horario-ver">

    <table class="horario-ver__tabla" border="1" cellpadding="6" cellspacing="0">
        <thead class="horario-ver__cabecera" >
            <tr class="horario-ver__fila">
                <th class="horario-ver__columna">Hora</th>
                <th class="horario-ver__columna">Lunes</th>
                <th class="horario-ver__columna">Martes</th>
                <th class="horario-ver__columna">Miércoles</th>
                <th class="horario-ver__columna">Jueves</th>
                <th class="horario-ver__columna">Viernes</th>
            </tr>
        </thead>
        <tbody class="horario-ver__cuerpo">
            <?php
            for ($h = 1; $h <= 11; $h++) {
                // Filtrar por hora
                $por_hora = array_filter($horarios, fn($r) => $r['hora'] == $h);
                if (!$por_hora) continue;

                // Obtener inicio y fin (puede ser cualquiera del array)
                $fila = reset($por_hora);
                echo "<tr>";
               echo "<td>
        {$h}°<br>
        <small> {$fila['hora_inicio']}</small><br>
        <small> {$fila['hora_fin']}</small>
      </td>";

                foreach (['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'] as $dia) {
                    $asignatura = '';
                    foreach ($por_hora as $r) {
                        if ($r['dia'] === $dia) {
                            $asignatura = $mapa_asignaturas[$r['id_asignatura']] ?? '—';
                            break;
                        }
                    }
                    echo "<td>" . htmlspecialchars($asignatura ?: '—') . "</td>";
                }

                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
    
</section>
         <section class="seccion-boton">
         <div class="contenedor__boton_listado">
    <p><a class="boton_listado" href="horarios_registro.php">Volver al listado</a></p>
    </div>
        </section>
<?php
require __DIR__.'/footer.php';  
?>