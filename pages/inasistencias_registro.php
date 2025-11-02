<?php
require_once __DIR__ . '/db.php';
require __DIR__.'/procesar.php';


$db = getDB();






$dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
$grupo = $_GET['grupo'] ?? '';
$msg = '';


if (!$grupo) {
    echo "<p>Grupo no especificado.</p>";
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inasistencias_datos = $_POST['inasistencias'] ?? [];
    $aulas_datos = $_POST['aula'] ?? [];
    $fecha_actual = date('Y-m-d');


     $huboCambios = false; //para saber si hubo inserciones o actualizaciones


    for ($hora = 1; $hora <= 11; $hora++) {
        foreach ($dias as $dia) {
            $inasistencia = isset($inasistencias_datos[$hora][$dia]) ? 1 : 0;
            $id_aula = $aulas_datos[$hora][$dia] ?? null;


            if ($id_aula || $inasistencia) {
                $stmt = $db->prepare("SELECT id_inasistencia FROM inasistencia WHERE grupo = ? AND dia = ? AND hora = ?");
                $stmt->bind_param("ssi", $grupo, $dia, $hora);
                $stmt->execute();
                $result = $stmt->get_result();
                $existe = $result->fetch_assoc();
                $stmt->close();


                if ($existe) {
                    $stmt = $db->prepare("UPDATE inasistencia SET id_aula = ?, fecha = ?, inasistencia = ?, fecha_editado = CURRENT_TIMESTAMP WHERE id_inasistencia = ?");
                    $stmt->bind_param("ssii", $id_aula, $fecha_actual, $inasistencia, $existe['id_inasistencia']);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    $stmt = $db->prepare("INSERT INTO inasistencia (grupo, dia, hora, fecha, id_aula, inasistencia) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssisii", $grupo, $dia, $hora, $fecha_actual, $id_aula, $inasistencia);
                    $stmt->execute();


                    $id_nuevo = $db->insert_id;
                    $stmt->close();
                    $huboCambios = true;
                }
            }
        }
    }


     //  Solo registra una vez en historial, al final
    if ($huboCambios) {
        require_once __DIR__ . '/procesar_historial.php';
        procesarHistorial('inasistencia', 'registro', $id_nuevo ?? 0);
   
}


$msg = "Inasistencias y aulas registradas correctamente.";
}


// Agregar esto para asignaturas inactivas: $asignaturas = $db->query("SELECT id_asignatura, nombre FROM asignatura WHERE activo = 1")->fetch_all(MYSQLI_ASSOC);
$asignaturas = $db->query("SELECT id_asignatura, nombre FROM asignatura")->fetch_all(MYSQLI_ASSOC);
$aulas = $db->query("SELECT id_aula, nombre FROM aula WHERE activo = 1")->fetch_all(MYSQLI_ASSOC);


$horario = [];
$inasistencias_existentes = [];


for ($hora = 1; $hora <= 11; $hora++) {
    foreach ($dias as $dia) {
        // Horario
        $stmt = $db->prepare("SELECT id_asignatura, hora_inicio, hora_fin FROM horario WHERE grupo = ? AND dia = ? AND hora = ?");
        $stmt->bind_param("ssi", $grupo, $dia, $hora);
        $stmt->execute();
        $result = $stmt->get_result();
        $horario[$hora][$dia] = $result->fetch_assoc();
        $stmt->close();


        // Inasistencia
        $stmt = $db->prepare("SELECT inasistencia FROM inasistencia WHERE grupo = ? AND dia = ? AND hora = ?");
        $stmt->bind_param("ssi", $grupo, $dia, $hora);
        $stmt->execute();
        $registro = $result->fetch_assoc();
        $inasistencias_existentes[$hora][$dia] = isset($registro['inasistencia']) ? (int)$registro['inasistencia'] : 0;
        $stmt->close();
    }
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
    <h1 class="titulo">Registrar Inasistencias para el grupo: <?= htmlspecialchars($grupo) ?></h1>
    </div>


<section class="contenedor_formulario-inaistencias">
   
    <form method="POST"  class="formulario-horario">
        <input type="hidden" name="grupo" value="<?= htmlspecialchars($grupo) ?>">


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
                            <?= $hora ?>°
                            <br>
                            <?php
                                $inicio = $horario[$hora]['Lunes']['hora_inicio'] ?? '';
                                $fin = $horario[$hora]['Lunes']['hora_fin'] ?? '';
                            ?>
                            <input type="time" value="<?= $inicio ?>" disabled>
                            <input type="time" value="<?= $fin ?>" disabled>
                        </th>
                        <?php foreach ($dias as $dia):
                            $celda = $horario[$hora][$dia] ?? null;
                            $id_asignatura = $celda['id_asignatura'] ?? '';
                            $nombre_asignatura = '';
                            foreach ($asignaturas as $a) {
                                if ($a['id_asignatura'] == $id_asignatura) {
                                    $nombre_asignatura = $a['nombre'];
                                    break;
                                }
                            }
                            $clase = $inasistencias_existentes[$hora][$dia] === 1 ? 'rojo' : '';
                        ?>
                        <td class="dia <?= $clase ?>">
                            <?= htmlspecialchars($nombre_asignatura) ?>
                            <br>
                            <select class="select-aula" name="aula[<?= $hora ?>][<?= $dia ?>]">
                                <option value="">Aula</option>
                                <?php foreach ($aulas as $a): ?>
                                    <option value="<?= $a['id_aula'] ?>"><?= htmlspecialchars($a['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <br>
                            <label>Inasistencia</label>
                            <input type="checkbox" name="inasistencias[<?= $hora ?>][<?= $dia ?>]" value="1">
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
        <button class="boton_guardar" type="submit">Guardar Inasistencias</button>
        </div>
        <div class="contenedor__boton_listado">
            <p><a class="boton_listado" href="inasistencias_index.php">Volver al listado</a></p>
            </div>
            </section>
            <?php if ($msg): ?>
        <div class="mensaje-exito">
            <p><?= htmlspecialchars($msg) ?></p>
        </div>
    <?php endif; ?>
    </form>
            </section>


<?php
require __DIR__.'/footer.php';  
?>
