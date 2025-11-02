<?php
require_once __DIR__ . '/db.php'; // Incluye el archivo de conexión a la base de datos
require __DIR__.'/procesar.php';


$db = getDB(); // Obtiene la instancia de conexión a la base de datos
 


// Consulta los grupos, asignaturas y aulas disponibles en la base de datos
$grupos = $db->query("SELECT nombre FROM grupo  ORDER BY nombre ASC")->fetch_all(MYSQLI_ASSOC);
$asignaturas = $db->query("SELECT id_asignatura, nombre FROM asignatura WHERE activo = 1 ORDER BY nombre ASC")->fetch_all(MYSQLI_ASSOC);
$aulas = $db->query("SELECT id_aula, nombre FROM aula")->fetch_all(MYSQLI_ASSOC);


$msg = ''; // Variable para mostrar mensajes al usuario


// Si el formulario fue enviado (método POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $grupo = $_POST['grupo']; // Grupo seleccionado
    $horas = $_POST['horario']; // Horario ingresado por el usuario


    // Recorre cada hora del horario
    foreach ($horas as $hora => $info) {
        $inicio = $info['inicio']; // Hora de inicio
        $fin = $info['fin'];        // Hora de fin


         // Recorre los días de la semana
        foreach (['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'] as $dia) {
            if (!empty($info[$dia])) {
                $id_asignatura = $info[$dia];
                $stmt = $db->prepare("INSERT INTO horario (grupo, dia, hora, id_asignatura, hora_inicio, hora_fin) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssiiss", $grupo, $dia, $hora, $id_asignatura, $inicio, $fin);
                $stmt->execute();


                 // Obtener el ID
            $idHorario = $stmt->insert_id;


                $stmt->close();


                   // Registrar en historial
               require_once __DIR__ . '/procesar_historial.php';
               procesarHistorial('horario', 'registro', $idHorario);
            }
        }
    }


    $msg = "Horario guardado correctamente."; // Muestra el mensaje si guardó los datos correctamente
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Horarios</title>
    <link rel="stylesheet" href="../assets/css/tablas.css">
   <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
     <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=menu" >
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400..900&display=swap" rel="stylesheet">
   
    <link rel="icon" href="../assets/img/logo.png" type="image/png"> <!-- Icono de la pestaña -->
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




    <div class="contenedor_titulo">
       <h1 class="titulo">Registrar horarios</h1>
        </div>
<!-- Muestra mensaje si se guardó el horario:-->


         <?php if ($msg): ?>
        <div class="mensaje-exito">
        <p><?= htmlspecialchars($msg)?></p>
         </div>
    <?php endif; ?>




    <section class="contenedor_formulario-horario">
       <!--Formulario para seleccionar grupo y asignar horarios:-->
   
       <form method="POST" class="formulario-horario">
       <div class="selector_grupo">
       <label class="guardar_datos label-horario" for="grupo">Selecciona un grupo:</label>
       <br>
        <select name="grupo" id="grupo" class="seleccionar-grupo" required>
    <option value="">-- Seleccionar --</option>


    <?php foreach ($grupos as $g): ?>
        <option value="<?= $g['nombre'] ?>"><?= htmlspecialchars($g['nombre']) ?></option>
    <?php endforeach; ?>
</select>
</div>
        <br><br>
        <div class="tabla_responsive">
        <table class="horarios tabla-horario">
            <thead>
            <tr class="fila-cabecera">
                <th class="hora">Hora</th>
                <th class="hora">Lunes</th>
                <th class="hora">Martes</th>
                <th class="hora">Miércoles</th>
                <th class="hora">Jueves</th>
                <th class="hora">Viernes</th>
            </tr>
            </thead>
            <tbody>


            <!-- Tabla con 11 bloques horarios y días de la semana:-->
            <?php for ($hora = 1; $hora <= 11; $hora++): ?>
                <tr class="fila-horario">
                    <th class="hora"><?= $hora ?>°
                    <br>
                      <input type="time" name="horario[<?= $hora ?>][inicio]" class="input-hora" >
                      <input type="time" name="horario[<?= $hora ?>][fin]" class="input-hora">
                </th>
                    <?php foreach (['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'] as $dia): ?>
                        <td  class="dia">
                            <select name="horario[<?= $hora ?>][<?= $dia ?>]"  class="select-asignatura">
                                <option value="">Asignatura</option>
                                <?php foreach ($asignaturas as $a): ?>
                                    <option value="<?= $a['id_asignatura'] ?>"><?= htmlspecialchars($a['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <br>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endfor; ?>
            </tbody>
        </table>
                </div>
        <br>


        <!--Botón para enviar el formulario:-->
            <div class="contenedor__boton-horario">
        <button class="boton_guardar boton-horario" type="submit">Guardar Horario</button>
        </div>
    </form>
    </section>
                               
<section>
    <?php
require_once __DIR__ . '/db.php';


$result = getDB()->query("
    SELECT MIN(id_horario) AS id_horario, grupo FROM horario GROUP BY grupo ORDER BY grupo DESC ");
$horarios = $result->fetch_all(MYSQLI_ASSOC);


$result->close();
?>




<div class="cont_titulo">
<div class="contenedor__titulo-lista">
<h2 class="titulo-lista">Horarios registrados</h2>
</div>
</div>


<!--Sección para mostrar horarios registrados-->


     <!--Muestra tabla con los horarios registrados por grupo:-->
     <?php if ($horarios): ?>
        <div class="contenedor_tabla_responsive">
        <table class="tabla_lista tabla" border="1" cellpadding="6" cellspacing="0">
            <thead  class="tabla-cabecera">
                <tr class="tabla-fila">
                    <th class="tabla-celda">ID</th>
                    <th class="tabla-celda">Grupo</th>
                    <th class="tabla-celda">Ver horario</th>
                    <th class="tabla-celda">Acciones</th>


    </tr>
    </thead>
     <tbody class="tabla__cuerpo">
      <?php
    foreach ($horarios as $a): ?>
    <tr class="tabla-fila">
        <td class="tabla-celda"><?= (int) $a['id_horario'] ?></td>
        <td class="tabla-celda"><?= htmlspecialchars($a['grupo']) ?></td>
        <td class="tabla-celda">
            <a href="horario_ver.php?id_horario=<?= (int) $a['id_horario'] ?>">Ver horario</a>
    </td>
    <td class="tabla-celda">
        <div class="botones_acciones">
            <a href="horario_edit.php?id_horario=<?= (int) $a['id_horario'] ?>">Editar</a>
            <a href="horario_delete.php?grupo=<?= urlencode($a['grupo']) ?>" onclick="return confirm('¿Estás seguro de que deseas borrar esta tabla de horarios?');">Borrar</a>
            </div>
    </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    </div>
    <?php else: ?>
        <p class="mensaje-sin-horarios">No hay horarios registrados aún.</p>
        <?php endif; ?>
<?php
require __DIR__.'/footer.php';  
?>
