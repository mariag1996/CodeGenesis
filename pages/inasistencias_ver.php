<?php
require_once __DIR__ . '/db.php'; // Conecta con la base de datos
require __DIR__.'/procesar.php';


$db = getDB(); // Abre la conexión



$grupo = $_GET['grupo'] ?? ''; // Toma el grupo desde la URL
if (!$grupo) {
    echo "<p>Grupo no especificado.</p>";  // Si no hay grupo, muestra mensaje
    exit;
}

$dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];

// Obtener asignaturas y aulas
$asignaturas = $db->query("SELECT id_asignatura, nombre FROM asignatura")->fetch_all(MYSQLI_ASSOC);
$aulas = $db->query("SELECT id_aula, nombre FROM aula")->fetch_all(MYSQLI_ASSOC);

// Crear mapas para mostrar nombres
$nombre_asignaturas = [];
foreach ($asignaturas as $a) {
    $nombre_asignaturas[$a['id_asignatura']] = $a['nombre'];
}

$nombre_aulas = [];
foreach ($aulas as $a) {
    $nombre_aulas[$a['id_aula']] = $a['nombre'];
}

// Obtener horario del grupo
$stmt = $db->prepare("SELECT dia, hora, id_asignatura, hora_inicio, hora_fin FROM horario WHERE grupo = ? ORDER BY hora ASC");
$stmt->bind_param("s", $grupo);
$stmt->execute();
$horarios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Obtener inasistencias del grupo (incluyendo campo 'inasistencia')
$stmt = $db->prepare("SELECT dia, hora, id_aula, inasistencia FROM inasistencia WHERE grupo = ?");
$stmt->bind_param("s", $grupo);
$stmt->execute();
$inasistencias = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Organizar inasistencias por hora y día
$nombre_inasistencias = [];
foreach ($inasistencias as $i) {
    $nombre_inasistencias[$i['hora']][$i['dia']] = $i;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Inasistencias del grupo <?= htmlspecialchars($grupo) ?></title>
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
       <h2 class="titulo">Inasistencias del grupo <?= htmlspecialchars($grupo) ?></h2>
        </div>

    <main class="contenedor">
        
    <h2>Inasistencias del grupo <?= htmlspecialchars($grupo) ?></h2>

        <div class="contenedor_tabla_responsive">
        <table class="inasistencias-ver__tabla" border="1" cellpadding="6" cellspacing="0">
            <thead class="inasistencias-ver__cabecera">
                <tr>
                    <th>Hora</th>
                    <?php foreach ($dias as $dia): ?>
                        <th><?= $dia ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody class="inasistencias-ver__cuerpo">
                <?php for ($h = 1; $h <= 11; $h++): 
                    $por_hora = array_filter($horarios, fn($r) => $r['hora'] == $h);
                    if (!$por_hora) continue;

                    $fila = reset($por_hora);
                ?>
                <tr class="inasistencias-ver__fila">
                    <td class="inasistencias-ver__celda">
                        <?= $h ?>°<br>
                        <small><?= $fila['hora_inicio'] ?></small><br>
                        <small><?= $fila['hora_fin'] ?></small>
                    </td>
                    <?php foreach ($dias as $dia): 
                        $asignatura = '—';
                        foreach ($por_hora as $r) {
                            if ($r['dia'] === $dia) {
                                $asignatura = $nombre_asignaturas[$r['id_asignatura']] ?? '—';
                                break;
                            }
                        }

                        $inasistencia = $nombre_inasistencias[$h][$dia] ?? null;
                        $aula = $inasistencia ? ($nombre_aulas[$inasistencia['id_aula']] ?? '') : '';
                        $clase = ($inasistencia && $inasistencia['inasistencia'] == 1) ? 'inasistencia' : '';
                    ?>
                     <td class="inasistencias-ver__celda <?= $clase ?>">
                        <?= htmlspecialchars($asignatura) ?><br>
                        <?php if ($aula): ?>
                            <small>Aula: <?= htmlspecialchars($aula) ?></small>
                        <?php endif; ?>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>
        </div>
        <br>
         <section class="seccion-boton">
            <div class="contenedor__boton_listado">
        <p><a class="boton_listado" href="inasistencias_index.php">Volver al listado</a></p>
    </div>
        </section>
<?php
require __DIR__.'/footer.php';  
?>