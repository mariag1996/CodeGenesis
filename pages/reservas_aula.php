<?php
session_start();


// Verifica si la sesión ha expirado (15 minutos = 900 segundos)
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 900)) {
    session_unset();     // Limpia variables de sesión
    session_destroy();   // Destruye la sesión
    header("Location: logeo.php?timeout=1");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time(); // Actualiza el tiempo de la última actividad




if (!isset($_SESSION['docente']) || $_SESSION['rol'] !== 'docente') {
    header("Location: logeo.php");
    exit();
}
$id_docente = $_SESSION['docente'];  // <- ahora está siempre definida


//Conexión a la bases de datos
require_once __DIR__ . '/db.php';


$aulas = [];
try {
    //Esto hace que solo se traigan las aulas que están activas (activo = 1).
   $result = getDB()->query("SELECT id_aula, nombre FROM aula WHERE activo = 1 ORDER BY nombre");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $aulas[] = $row;
        }
    }
    //Si hay un error, lo captura y guarda el mensaje en $msg.
} catch (mysqli_sql_exception $e) {
    $msg = "Error al obtener aulas: " . htmlspecialchars($e->getMessage());
}


$asignaturas = [];
try {
    //Esto hace que solo se traigan las asignaturas que están activas (activo = 1).
   $result = getDB()->query("SELECT id_asignatura, nombre FROM asignatura WHERE activo = 1 ORDER BY nombre");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $asignaturas[] = $row;
        }
    }
    //Si hay un error, lo captura y guarda el mensaje en $msg.
} catch (mysqli_sql_exception $e) {
    $msg = "Error al obtener aulas: " . htmlspecialchars($e->getMessage());
}


$mensaje = ''; //  Variable para guardar el mensaje


//Verifica si el formulario fue enviado (método POST).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //Recoge los datos del formulario usando $_POST, aplicando trim() para eliminar espacios.
   $id_docente = $_SESSION['docente']; // ID del docente desde la sesión
  //intval se usa para asegurarse de que el valor sea un número entero antes de guardarlo
  //Evitar errores si el valor no es numérico y previene inyecciones SQL
   $id_aula = intval($_POST['id_aula']);
   $fecha = $_POST['fecha'];
   $hora_inicio = $_POST['hora_inicio'];
   $hora_fin = $_POST['hora_fin'];
   $id_asignatura = $_POST['id_asignatura'];




  // --- CHEQUEO DE DISPONIBILIDAD ---
    $conn = getDB();
    $check = $conn->prepare("
        SELECT COUNT(*) AS existe
        FROM reserva_aula
        WHERE id_aula = ?
          AND fecha = ?
          AND estado != 'cancelada'
          AND (
              (? < hora_fin AND ? > hora_inicio)
          )
    ");


    $check->bind_param("isss", $id_aula, $fecha, $hora_inicio, $hora_fin);
    $check->execute();
    $resultado = $check->get_result()->fetch_assoc();


    if ($resultado['existe'] > 0) {
         $mensaje = "<p style='color:red; margin-top:10px;'>⚠️ El aula ya está reservada para ese horario.</p>";
    } else {
   
        $stmt = $conn->prepare("
            INSERT INTO reserva_aula (id_asignatura, id_aula, id_docente, fecha, hora_inicio, hora_fin, estado)
            VALUES (?, ?, ?, ?, ?, ?, 'vigente')
        ");
        $stmt->bind_param("iiisss", $id_asignatura, $id_aula, $id_docente, $fecha, $hora_inicio, $hora_fin);


        if ($stmt->execute()) {
              $mensaje = "<p style='color:green; margin-top:10px;'>✅ Reserva guardada exitosamente.</p>";
        } else {
            $mensaje = "<p style='color:red; margin-top:10px;'>Error al guardar la reserva: " . htmlspecialchars($stmt->error) . "</p>";
        }
    }
}






?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../assets/css/registro.css"><!--Link al estilo-->
     <link  rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"> <!--Imagen de fondo-->
   <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"><!--Link a los iconos de el header-->
     <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=menu"><!--Link a los iconos de el header-->
    <link rel="icon" href="../assets/img/logo.png" type="image/png">
    </head>
<body>
<header class="header">
   
  <!-- Contenedor de los íconos-->
      <div class="contenedor__iconos">
    <a href="docentes.php">  <!-- Dirige el ícono de home a registro_main.php-->
      <span class="material-icons">home</span>
    </a>
      <span class="material-symbols-outlined" id="iconoMenu">menu</span>
  </div>
  <div id="menuDesplegable" class="menu-desplegable">
  <a href="cambio-contra-doc.php" class="boton-menu">Cambiar contraseña</a>
  <a href="logout.php" class="boton-menu" onclick="cerrarSesion()">Cerrar sesión</a>
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
  }
</script>


<div class="contenedor_titulo">
<h1 class="titulo">Reservas de Aulas</h1>
</div>
<div class="contenedor_formulario">
<form class="formulario" method="POST" action="">
 
<div class="casilla">
  <label for="id_asignatura">Asignatura:</label>
  <select name="id_asignatura" required>
    <option value="">Seleccione...</option>
    <?php foreach ($asignaturas as $asignatura): ?>
      <option value="<?= $asignatura['id_asignatura'] ?>"><?= htmlspecialchars($asignatura['nombre']) ?></option>
    <?php endforeach; ?>
  </select>
 </div>




<div class="casilla">
  <label for="id_aula">Aula:</label>
  <select name="id_aula" required>
    <option value="">Seleccione...</option>
    <?php foreach ($aulas as $aula): ?>
      <option value="<?= $aula['id_aula'] ?>"><?= htmlspecialchars($aula['nombre']) ?></option>
    <?php endforeach; ?>
  </select>
 </div>


 <div class="casilla">
  <label class="guardar_datos" for="fecha">Fecha:</label>
  <input class="campo" type="date" name="fecha" required>
  </div>


   <div class="casilla">
  <label class="guardar_datos" for="hora_inicio">Hora de inicio:</label>
  <input class="campo" type="time" name="hora_inicio" required>
   </div>


   <div class="casilla">
  <label class="guardar_datos" for="hora_fin">Hora de fin:</label>
  <input class="campo" type="time" name="hora_fin" required>
   </div>


  <button class="boton_guardar" type="submit">Reservar</button>
  <?php if (!empty($mensaje)) echo $mensaje; ?>
</form>
    </div>




<div class="cont_titulo">
  <div class="contenedor__titulo-lista">
    <h2 class="titulo-lista">Lista de Reservas de Aulas</h2>
    </div>
    </div>


  <?php
$reservas = [];
try {
    $query = "
         SELECT r.id_reserva, r.fecha, r.hora_inicio, r.hora_fin, r.creado_en,
           r.estado,
           a.nombre AS nombre_aula,
           asig.nombre AS nombre_asignatura
    FROM reserva_aula r
    JOIN aula a ON r.id_aula = a.id_aula
    JOIN asignatura asig ON r.id_asignatura = asig.id_asignatura
    WHERE r.id_docente = $id_docente
    ORDER BY r.creado_en DESC
    ";
    $result = getDB()->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $reservas[] = $row;
        }
    }
} catch (mysqli_sql_exception $e) {
    $msg = "Error al obtener reservas: " . htmlspecialchars($e->getMessage());
}


?>




    <!-- Muestra los datos en una tabla -->
  <?php if ($reservas): ?>
      <div class="contenedor_tabla_responsive">
<table class="tabla_lista tabla" border="1" cellpadding="6" cellspacing="0">
    <thead class="tabla-cabecera">
        <tr class="tabla-fila">
            <th class="tabla-celda">ID</th>
            <th class="tabla-celda">Aula</th>
            <th class="tabla-celda">Asignatura</th>
            <th class="tabla-celda">Fecha</th>
            <th class="tabla-celda">Hora de inicio</th>
            <th class="tabla-celda">Hora fin</th>
            <th class="tabla-celda">Creado en</th>
            <th class="tabla-celda">Estado</th>
            <th class="tabla-celda">Cancelar</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($reservas as $r): ?>
        <tr class="tabla-fila">
            <td class="tabla-celda"><?= (int) $r['id_reserva'] ?></td>
            <td class="tabla-celda"><?= htmlspecialchars($r['nombre_aula']) ?></td>
            <td class="tabla-celda"><?= htmlspecialchars($r['nombre_asignatura']) ?></td>
            <td class="tabla-celda"><?= htmlspecialchars($r['fecha']) ?></td>
            <td class="tabla-celda"><?= htmlspecialchars($r['hora_inicio']) ?></td>
            <td class="tabla-celda"><?= htmlspecialchars($r['hora_fin']) ?></td>
            <td class="tabla-celda"><?= htmlspecialchars($r['creado_en']) ?></td>
           <td class="tabla-celda"><?= htmlspecialchars($r['estado']) ?></td>
            <td class="tabla-celda">
            <?php if ($r['estado'] === 'vigente'): ?>
            <form method="POST" action="cancelar_reserva_aula.php">
            <input type="hidden" name="id_reserva" value="<?= $r['id_reserva']?>">
            <button type="submit" class="boton_cancelar">Cancelar</button>
            </form>
            <?php else: ?>
            <span>Sin acción</span>
    <?php endif; ?>
</td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
            </div>
<?php else: ?>
    <p>No hay reservas registradas aún.</p>
<?php endif; ?>
     


<?php
require __DIR__.'/footer.php';  
?>
