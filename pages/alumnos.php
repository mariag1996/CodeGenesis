<?php
session_start();


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
    header("Location: logeo.php"); // Redirige al login
    exit();
}


//Conecta a la base de datos
require_once __DIR__ . '/db.php';
$db = getDB();


// Se obtiene el grupo actual del alumno (guardado en la sesión)
$grupo = $_SESSION['grupo'];


// --- Obtener datos del alumno ---
$usuario = $_SESSION['usuario'];  // Cédula del alumno
// Consulta preparada para evitar inyección SQL
$stmt = $db->prepare("SELECT nombre, apellido FROM alumno WHERE usuario = ?");
$stmt->bind_param("i", $usuario); // 'i' indica que es un número entero
$stmt->execute();
$result = $stmt->get_result();
$alumno = $result->fetch_assoc();
$nombreAlumno = isset($alumno) ? $alumno['nombre'] . ' ' . $alumno['apellido'] : 'Alumno';
$stmt->close();


?>


<?php
// --- Días de la semana ---
$dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
// --- Obtener asignaturas (obtiene el id y el nombre de la asignatura de la tabla asignatura) ---
$asignaturas = $db->query("SELECT id_asignatura, nombre FROM asignatura")->fetch_all(MYSQLI_ASSOC);
// --- Obtener aulas (obtiene el id y el nombre del aula de la tabla aula) ---
$aulas = $db->query("SELECT id_aula, nombre FROM aula")->fetch_all(MYSQLI_ASSOC);


// Creamos arrays asociativos para acceder rápido por ID
$nombre_asignaturas = [];
foreach ($asignaturas as $a) {
    $nombre_asignaturas[$a['id_asignatura']] = $a['nombre'];
}


$nombre_aulas = [];
foreach ($aulas as $a) {
    $nombre_aulas[$a['id_aula']] = $a['nombre'];
}


// --- Obtener horario del grupo ---
$stmt = $db->prepare("SELECT dia, hora, id_asignatura, hora_inicio, hora_fin FROM horario WHERE grupo = ? ORDER BY hora ASC");
$stmt->bind_param("s", $grupo);
$stmt->execute();
$horarios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();


// --- Obtener inasistencias del grupo ---
$stmt = $db->prepare("SELECT dia, hora, id_aula, inasistencia FROM inasistencia WHERE grupo = ?");
$stmt->bind_param("s", $grupo);
$stmt->execute();
$inasistencias = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();


// Reorganizamos las inasistencias en un array [hora][día]
$nombre_inasistencias = [];
foreach ($inasistencias as $i) {
    $nombre_inasistencias[$i['hora']][$i['dia']] = $i;
}
?>






<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
     <link rel="stylesheet" href="../assets/css/tablas.css">
    <link  rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"> <!--Imagen de fondo-->
     <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=menu"><!--Link a los iconos de el header-->
     <link rel="icon" href="../assets/img/logo.png" type="image/png">
    </head>
<body>
<header class="header">
        <!-- Muestra el nombre del alumno -->
    <div class="cont__nombre-usuario">
    <div class="nombre-usuario">
 <?= htmlspecialchars($nombreAlumno) ?>
</div>
</div>
    <!-- Ícono del menú desplegable -->
       <div class="contenedor__iconos">
      <span class="material-symbols-outlined" id="iconoMenu">menu</span>
  </div>
    <!-- Menú desplegable -->
  <div id="menuDesplegable" class="menu-desplegable">
  <a href="cambio_contraseña_alumno.php" class="boton-menu">Cambiar contraseña</a>
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
<div class="contenedor_titulo">
 <h1 class="titulo">Grupo: <?= htmlspecialchars($grupo) ?></h1>
</div>
 
<main class="contenedor">


<div class="contenedor_tabla_responsive">
<table class="inasistencias-ver__tabla" border="1" cellpadding="6" cellspacing="0">
    <thead class="inasistencias-ver__cabecera">
        <tr>
              <!-- Encabezados con los días -->
            <th>Hora</th>
            <?php foreach ($dias as $dia): ?>
                <th><?= $dia ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody class="inasistencias-ver__cuerpo">
        <!--  Se recorre cada posible hora (1 a 11) del horario -->
        <?php for ($h = 1; $h <= 11; $h++):
            $por_hora = array_filter($horarios, fn($r) => $r['hora'] == $h);
            if (!$por_hora) continue;
            $fila = reset($por_hora);
        ?>
        <tr class="inasistencias-ver__fila">
                   <!-- Columna de la hora -->
            <td class="inasistencias-ver__celda">
                <?= $h ?>°<br>
                <small><?= $fila['hora_inicio'] ?></small><br>
                <small><?= $fila['hora_fin'] ?></small>
            </td>
             <!-- Columnas de cada día -->
            <?php foreach ($dias as $dia):
             // Por defecto muestra guion si no hay asignatura
                $asignatura = '—';
                 // Busca si hay una asignatura en esa hora y día
                foreach ($por_hora as $r) {
                    if ($r['dia'] === $dia) {
                        $asignatura = $nombre_asignaturas[$r['id_asignatura']] ?? '—';
                        break;
                    }
                }
                // Verifica si hay una inasistencia registrada
                $inasistencia = $nombre_inasistencias[$h][$dia] ?? null;
                $aula = $inasistencia ? ($nombre_aulas[$inasistencia['id_aula']] ?? '') : '';
                // Si hubo inasistencia, se aplica clase CSS para marcarla
                $clase = ($inasistencia && $inasistencia['inasistencia'] == 1) ? 'inasistencia' : '';
            ?>
            <td class="inasistencias-ver__celda  <?= $clase ?>">
                <?= htmlspecialchars($asignatura) ?><br>
                <?php if ($aula): ?>
                    <hp class="aulas"><?= htmlspecialchars($aula) ?></hp>
                <?php endif; ?>
            </td>
            <?php endforeach; ?>
        </tr>
        <?php endfor; ?>
    </tbody>
</table>
                </div>
<?php
require __DIR__.'/footer.php';  
?>
