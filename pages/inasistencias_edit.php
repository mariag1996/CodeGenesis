<?php
require_once __DIR__ . '/db.php';
require __DIR__.'/procesar.php';


$db = getDB();






$dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
$id_inasistencia = $_GET['id_inasistencia'] ?? null;


if (!$id_inasistencia) {
    echo "<p>ID de inasistencia no especificado.</p>";
    exit;
}


// Obtener datos de inasistencia
$stmt = $db->prepare("SELECT grupo FROM inasistencia WHERE id_inasistencia = ?");
$stmt->bind_param("i", $id_inasistencia);
$stmt->execute();
$inasistencia_info = $stmt->get_result()->fetch_assoc();
$stmt->close();


if (!$inasistencia_info) {
    echo "<p>No se encontró la inasistencia.</p>";
    exit;
}


$grupo = $inasistencia_info['grupo'];
$msg = '';


// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inasistencias_data = $_POST['inasistencias'] ?? [];
    $aulas_data = $_POST['aula'] ?? [];
    $huboCambios = false;


    $fecha_actual = date('Y-m-d');


    // Asegurar que se envíen también los valores no marcados
    foreach ($aulas_data as $hora => $dias_data) {
        foreach ($dias_data as $dia => $id_aula) {
            if (!isset($inasistencias_data[$hora][$dia])) {
                $inasistencias_data[$hora][$dia] = 0;
            }
        }
    }


    foreach ($inasistencias_data as $hora => $dias_data) {
        foreach ($dias_data as $dia => $inasistencia) {
            $id_aula = $aulas_data[$hora][$dia] ?? null;
            if ($id_aula === '' || $id_aula === '0') {
            $id_aula = null;
        }
            // Buscar si ya existe el registro
            $stmt = $db->prepare("SELECT id_inasistencia FROM inasistencia WHERE grupo = ? AND dia = ? AND hora = ?");
            $stmt->bind_param("ssi", $grupo, $dia, $hora);
            $stmt->execute();
            $existe = $stmt->get_result()->fetch_assoc();
            $stmt->close();


            if ($existe) {
                // ✅ Si existe → actualizar
                $stmt = $db->prepare("UPDATE inasistencia
                                      SET id_aula = ?, inasistencia = ?, fecha_editado = CURRENT_TIMESTAMP
                                      WHERE id_inasistencia = ?");
                $id_aula = ($id_aula === '') ? null : $id_aula;
                $stmt->bind_param("iii", $id_aula, $inasistencia, $existe['id_inasistencia']);
                $stmt->execute();
                $stmt->close();
                $huboCambios = true;
            } else {
                // ✅ Si no existe → insertar
                if ($id_aula !== null || $inasistencia != 0) {
                    $stmt = $db->prepare("INSERT INTO inasistencia (grupo, dia, hora, fecha, id_aula, inasistencia)
                                          VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssisii", $grupo, $dia, $hora, $fecha_actual, $id_aula, $inasistencia);
                    $stmt->execute();
                    $stmt->close();
                    $huboCambios = true;
                }
            }
        }
    }


    // ✅ Registrar solo una vez en el historial
    if ($huboCambios) {
        require_once __DIR__ . '/procesar_historial.php';
        procesarHistorial('inasistencia', 'edicion', $id_inasistencia);
    }


    $msg = "Inasistencias actualizadas correctamente.";
}
// Obtener asignaturas y aulas
// Agregar esto para asignaturas inactivas: $asignaturas = $db->query("SELECT id_asignatura, nombre FROM asignatura WHERE activo = 1")->fetch_all(MYSQLI_ASSOC);
$asignaturas = $db->query("SELECT id_asignatura, nombre FROM asignatura")->fetch_all(MYSQLI_ASSOC);
$aulas = $db->query("SELECT id_aula, nombre FROM aula WHERE activo = 1")->fetch_all(MYSQLI_ASSOC);


// Obtener horario
$horario = [];
for ($hora = 1; $hora <= 8; $hora++) {
    foreach ($dias as $dia) {
        $stmt = $db->prepare("SELECT id_asignatura, hora_inicio, hora_fin FROM horario WHERE grupo = ? AND dia = ? AND hora = ?");
        $stmt->bind_param("ssi", $grupo, $dia, $hora);
        $stmt->execute();
        $horario[$hora][$dia] = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}


// Obtener inasistencias existentes
$inasistencias = [];
$stmt = $db->prepare("SELECT * FROM inasistencia WHERE grupo = ?");
$stmt->bind_param("s", $grupo);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $inasistencias[$row['hora']][$row['dia']] = $row;
}
$stmt->close();
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
<h1>Editar Inasistencias para el grupo: <?= htmlspecialchars($grupo) ?></h1>
 </div>
<main class="contenedor">
   


    <?php if ($msg): ?>
        <div class="mensaje-exito">
        <p><?= htmlspecialchars($msg) ?></p>
          </div>
        <a href="inasistencias_index.php">← Volver al listado</a>
    <?php endif; ?>


    <form class="formulario-horario" method="POST">
         <div class="contenedor_tabla_responsive">
        <table class="tabla-horario">
            <thead>
                <tr class="fila-cabecera">
                    <th class="hora">Hora</th>
                    <?php foreach ($dias as $dia): ?>
                        <th class="dia-cabecera"><?= $dia ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php for ($hora = 1; $hora <= 11; $hora++): ?>
                    <tr>
                        <th class="hora">
                            <?= $hora ?>°<br>
                            <?php
                                $inicio = $horario[$hora]['Lunes']['hora_inicio'] ?? '';
                                $fin = $horario[$hora]['Lunes']['hora_fin'] ?? '';
                            ?>
                            <input type="time" value="<?= $inicio ?>" disabled>
                            <input type="time" value="<?= $fin ?>" disabled>
                        </th>
                        <?php foreach ($dias as $dia):
                            $celda = $horario[$hora][$dia] ?? [];
                            $id_asignatura = $celda['id_asignatura'] ?? '';
                            $nombre_asignatura = '';
                            foreach ($asignaturas as $a) {
                                if ($a['id_asignatura'] == $id_asignatura) {
                                    $nombre_asignatura = $a['nombre'];
                                    break;
                                }
                            }


                            $inasistencia = $inasistencias[$hora][$dia] ?? null;
                            $id_aula_actual = $inasistencia['id_aula'] ?? '';
                            $check = (!empty($inasistencia) && isset($inasistencia['inasistencia']) && $inasistencia['inasistencia'] == 1) ? 'checked' : '';
                        ?>
                        <td class="dia <?= $clase ?>">
                            <?= htmlspecialchars($nombre_asignatura) ?>
                            <br>
                            <select class="select-aula" name="aula[<?= $hora ?>][<?= $dia ?>]">
                                <option value="">Aula</option>
                                <?php foreach ($aulas as $a): ?>
                                    <option value="<?= $a['id_aula'] ?>" <?= $a['id_aula'] == $id_aula_actual ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($a['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <br>
                            <label>Inasistencia</label>
                            <input type="checkbox" name="inasistencias[<?= $hora ?>][<?= $dia ?>]" value="1" <?= $check ?>>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>
                                </div>
        <br>
         <section class="seccion-boton">
        <div class="contenedor__boton-horario">
        <button class="boton_guardar" type="submit">Guardar Cambios</button>
         </div>
         <div class="contenedor__boton_listado">
            <p><a class="boton_listado" href="inasistencias_index.php">Volver al listado</a></p>
            </div>
            </section>
    </form>


    <?php
require __DIR__.'/footer.php';  
?>
