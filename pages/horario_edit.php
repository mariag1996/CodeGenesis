<?php
require_once __DIR__ . '/db.php';
require __DIR__.'/procesar.php';




$db = getDB();
$id_horario = isset($_GET['id_horario']) ? (int) $_GET['id_horario'] : 0;


// Obtener grupo
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


// Obtener horarios del grupo
$stmt = $db->prepare("SELECT id_horario, dia, hora, id_asignatura, hora_inicio, hora_fin FROM horario WHERE grupo = ?");
$stmt->bind_param("s", $grupo);
$stmt->execute();
$horarios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();


   require_once __DIR__ . '/procesar_historial.php';
    if (!empty($horarios)) {
    procesarHistorial('horario', 'edicion', $horarios[0]['id_horario']);
}


// Mapear horarios por hora y día
$horario_map = [];
foreach ($horarios as $h) {
    $horario_map[$h['hora']]['inicio'] = $h['hora_inicio'];
    $horario_map[$h['hora']]['fin'] = $h['hora_fin'];
    $horario_map[$h['hora']][$h['dia']] = $h['id_asignatura'];
}


// Obtener asignaturas
$asignaturas = $db->query("SELECT id_asignatura, nombre FROM asignatura WHERE activo = 1 ORDER BY nombre ASC")->fetch_all(MYSQLI_ASSOC);


// Mensaje de confirmación
$mensaje_confirmacion = '';


// Procesar edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $horas = $_POST['horario'];


    // Eliminar horarios anteriores
    $stmt = $db->prepare("DELETE FROM horario WHERE grupo = ?");
    $stmt->bind_param("s", $grupo);
    $stmt->execute();
    $stmt->close();


    // Insertar nuevos valores
    foreach ($horas as $hora => $info) {
        $inicio = $info['inicio'];
        $fin = $info['fin'];


        foreach (['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'] as $dia) {
            if (!empty($info[$dia])) {
                $id_asignatura = $info[$dia];
                $stmt = $db->prepare("INSERT INTO horario (grupo, dia, hora, id_asignatura, hora_inicio, hora_fin) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssiiss", $grupo, $dia, $hora, $id_asignatura, $inicio, $fin);
                $stmt->execute();
                $stmt->close();
            }
        }
    }


    $mensaje_confirmacion = '<div class="mensaje-exito"><strong>Horario actualizado correctamente.</strong></div>';
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


<main>
     <div class="contenedor_titulo">
        <h1 class="titulo">Editar horario del grupo <?= htmlspecialchars($grupo) ?></h1>
        </div>
   
        <section class="contenedor-tabla">
       
        <form method="POST" class="formulario-horario">
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
                    <?php for ($h = 1; $h <= 11; $h++): ?>
                        <tr class="fila-horario">
                            <th class="hora">
                                <?= $h ?>°<br>
                                <input type="time" name="horario[<?= $h ?>][inicio]" value="<?= $horario_map[$h]['inicio'] ?? '' ?>" class="input-hora">
                                <input type="time" name="horario[<?= $h ?>][fin]" value="<?= $horario_map[$h]['fin'] ?? '' ?>" class="input-hora">
                            </th>
                            <?php foreach (['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'] as $dia): ?>
                                <td class="dia">
                                    <select name="horario[<?= $h ?>][<?= $dia ?>]" class="select-asignatura">
                                        <option value="">Asignatura</option>
                                        <?php foreach ($asignaturas as $a): ?>
                                            <option value="<?= $a['id_asignatura'] ?>"
                                                <?= (isset($horario_map[$h][$dia]) && $horario_map[$h][$dia] == $a['id_asignatura']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($a['nombre']) ?>
                                            </option>
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


            <section class="seccion-boton">
            <div class="contenedor__boton_guardar"></div>
            <button class="boton_guardar" type="submit">Actualizar</button>
             </div>
             <div class="contenedor__boton_listado">
        <p><a class="boton_listado" href="horarios_registro.php">Volver al listado</a></p>
    </div>
                 </section>


            <?php if (!empty($mensaje_confirmacion)): ?>
                <?= $mensaje_confirmacion ?>
            <?php endif; ?>
        </form>
    </section>
<?php
require __DIR__.'/footer.php';  
?>

