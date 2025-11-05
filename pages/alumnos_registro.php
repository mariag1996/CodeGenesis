<!-- ......Este archivo se encarga de registrar a los alumnos ...... -->
<?php

// --- Conexión a la base de datos ---
require_once __DIR__ . '/db.php';
// Se incluye procesar.php para manejo de sesion de adscripto
require __DIR__.'/procesar.php'; 

// Variable para mostrar mensajes de error o éxito en pantalla
$msg = '';

//Obtiene los nombres de los grupos desde la tabla grupo.
//Los guarda en el arreglo $grupos para usarlos en el formulario.
$grupos = [];
try {
    //Solo se traen los grupos que están activos (activo = 1)
    //  y los ordena alfabéticamente
    $result = getDB()->query("SELECT nombre FROM grupo WHERE activo = 1 ORDER BY nombre");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $grupos[] = $row;
        }
    }
// Si ocurre un error en la consulta, se guarda el mensaje en $msg
} catch (mysqli_sql_exception $e) {
    $msg = "Error al obtener grupos: " . htmlspecialchars($e->getMessage());
}

// --- Procesamiento del formulario de registro ---
//Verifica si el formulario fue enviado (método POST).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //Recoge los datos del formulario usando $_POST, aplicando trim() para eliminar espacios.
    $nombreGrupo = trim($_POST['nombreGrupo'] ?? '');
    $nombreAlumno = trim($_POST['nombreAlumno'] ?? '');
    $apellidoAlumno = trim($_POST['apellidoAlumno'] ?? '');
    $usuarioAlumno = trim($_POST['usuarioAlumno'] ?? '');
    $contraseniaAlumno = trim($_POST['contraseniaAlumno'] ?? '');
    $repetirContrasenia = trim($_POST['repetircontrasenia'] ?? '');
    
    // Se usará para guardar la contraseña encriptada
 $hash = ''; 

   // --- Validaciones ---
    // Verifica que las contraseñas coincidan
    if ($contraseniaAlumno !== $repetirContrasenia) {
        $msg = "Las contraseñas no coinciden.";
    }

      // Verifica que los demás campos no estén vacíos
    elseif ($nombreGrupo === '' || $nombreAlumno === '' || $apellidoAlumno === '' || $usuarioAlumno === '' ) {
      //Si hay errores, los guarda en $msg.
        $msg = "Datos inválidos. Asegúrate de ingresar los datos correctamente.";
    } 
    
    // Si las contraseñas coinciden y los campos son válidos...
    else {
        //Hashea la contraseña
        $hash = password_hash($contraseniaAlumno, PASSWORD_DEFAULT);
        try {
              // --- Inserta los datos del nuevo alumno en la base de datos ---
            $stmt = getDB()->prepare("INSERT INTO alumno (nombre, apellido, usuario, contraseña, nombre_grupo) VALUES (?, ?, ?, ?, ?)");
            // Se vinculan los valores a la consulta (ssiss = string, string, int, string, string)
            $stmt->bind_param("ssiss", $nombreAlumno, $apellidoAlumno, $usuarioAlumno, $hash, $nombreGrupo);
            $stmt->execute();
            $stmt->close();
            // Mensaje de éxito
            $msg = "Alumno guardado correctamente.";

            // --- Registro en el historial ---
            // Incluye el archivo procesar_historial.php que contiene la función procesarHistorial()
            // para registrar las acciones realizadas en el sistema por el adscripto (registro, borrar, edición.).
            require_once __DIR__ . '/procesar_historial.php';
            // Llama a la función para guardar el evento en el historial:
            //   - 'alumno' → tabla afectada
            //   - 'registro' → tipo de acción
            //   - $usuarioAlumno → ID (PK) del alumno afectado
            procesarHistorial('alumno', 'registro', $usuarioAlumno);

        } catch (mysqli_sql_exception $e) {
            // Muestra un error si la inserción falla (por ejemplo, usuario duplicado)
            $msg = "Error al insertar: " . htmlspecialchars($e->getMessage());
}
    }
}

?>
<?php
require __DIR__.'/header.php'; // Incluye el encabezado común del sitio (HTML + navegación)
?>
       
    <div class="contenedor_titulo">
    <h1 class="titulo" >Registrar alumno</h1>
    </div>
    
   <div class="contenedor_formulario">
    <form class="formulario" action="" method="POST">
    
    <div class="casilla">
        <label for="grupo">Selecciona un grupo:</label>
       
       <select id="nombreGrupo" name="nombreGrupo" required>
    <option value="">Seleccione...</option>
    <?php foreach ($grupos as $grupo) { ?>
        <option value="<?= htmlspecialchars($grupo['nombre']) ?>"><?= htmlspecialchars($grupo['nombre']) ?></option>
    <?php } ?>
</select>
    </div>
    
    <div class="casilla">
        <label class="guardar_datos" for="nombreAlumno">Nombre:</label>
        <input class="campo" type="text" pattern="[A-Za-zÁáÉéÍíÓóÚúÑñ\s]+" 
       title="Solo se permiten letras y espacios" id="nombreAlumno" name="nombreAlumno" required/>
    </div>
    
    <div class="casilla">
        <label class="guardar_datos" for="apellidoAlumno">Apellido:</label>
        <input class="campo" type="text" pattern="[A-Za-zÁáÉéÍíÓóÚúÑñ\s]+"
       title="Solo se permiten letras y espacios" id="apellidoAlumno" name="apellidoAlumno" required/>
    </div>

<!-- inputmode="numeric" muestra teclado numérico en móviles-->
<!-- maxlength="8" limita la entrada a 8 caracteres-->
<!-- pattern="\d{1,8}": asegura que solo se ingresen entre 1 y 8 dígitos-->
    <div class="casilla">
        <label class="guardar_datos" for="usuarioAlumno">Usuario (Cédula):</label>
        <input class="campo"  type="text" inputmode="numeric" maxlength="8" pattern="\d{1,8}" id="usuarioAlumno" name="usuarioAlumno" required/> <!-- inputmode="numeric" muestra teclado numérico en móviles.-->
    </div>

    <div class="casilla">
        <label class="guardar_datos" for="contraseniaAlumno">Contraseña:</label>
        <input class="campo" type="password" id="contraseniaAlumno" name="contraseniaAlumno" required/>
    </div>

    <div class="casilla">
        <label class="guardar_datos" for="repetircontrasenia">Repetir Contraseña:</label>
        <input class="campo" type="password" id="repetircontrasenia" name="repetircontrasenia" required/>
    </div>
    
    <button class="boton_guardar" type="submit">Guardar</button>
</form>
    </div>
    
 <!-- Si hay un mensaje en $msg, lo muestra en pantalla. -->
    <?php if (!empty($msg)): ?> 
        <div class="mensaje-exito">
    <p><strong><?= $msg ?></strong></p>
    </div>
    <?php endif; ?> 

    <?php
require_once __DIR__ . '/db.php';

//Consulta todos los alumnos registrados y los ordena por grupo en orden descendente.
//Unifica información de las dos tablas: alumno y grupo.
//Obtienen el campo g.activo, que permite saber si el grupo está inactivo (y asignar la clase inactiva).
//Se usa esta información para controlar la visibilidad en el checkbox con JS.
$result = getDB()->query("SELECT a.nombre_grupo, a.usuario, a.nombre, a.apellido, a.creado_en, g.activo 
                          FROM alumno a
                          JOIN grupo g ON a.nombre_grupo = g.nombre
                          ORDER BY a.nombre_grupo DESC");
$alumnos = $result->fetch_all(MYSQLI_ASSOC);

$result->close();
?>

<div class="cont_titulo">
  <div class="contenedor__titulo-lista">
    <h2 class="titulo-lista">Alumnos registrados</h2>
    </div>
    </div>

    <div class="cont_checkbox">
    <div class="contenedor_checkbox">
    <label>
    <input type="checkbox" id="filtroInactivos" class="checkbox" checked>
    Mostrar alumnos con grupos inactivos
    </label>
</div>
    </div>
    
    <!-- Muestra los datos en una tabla -->
    <?php if ($alumnos): ?>
        <div class="contenedor_tabla_responsive">
        <table class="tabla_lista tabla" border="1" cellpadding="6" cellspacing="0">
            <thead class="tabla-cabecera">
                <tr class="tabla-fila">
                    <th class="tabla-celda">Grupo</th>
                    <th class="tabla-celda">Usuario</th>
                    <th class="tabla-celda">Nombre</th>
                    <th class="tabla-celda" >Apellido</th>
                    <th class="tabla-celda">Creado en</th>
                    <th class="tabla-celda">Acciones</th>
    </tr>
    </thead>
    </tbody>
    <?php 
    foreach ($alumnos as $a): ?>
        <?php
        // Si el grupo está inactivo, asignamos la clase 'inactiva' a la fila
        $esInactivo = $a['activo'] == 0 ? 'inactiva' : '';
    ?>
    <tr class="tabla-fila <?= $esInactivo ?>">
        <td class="tabla-celda"><?= htmlspecialchars($a['nombre_grupo']) ?></td>
        <td class="tabla-celda"><?= (int) $a['usuario'] ?></td>
        <td class="tabla-celda"><?= htmlspecialchars($a['nombre']) ?></td>
        <td class="tabla-celda"><?= htmlspecialchars($a['apellido']) ?></td>
        <td class="tabla-celda"><?= htmlspecialchars($a['creado_en']) ?></td>
        <td class="tabla-celda">
        <!-- Cada fila de alumno tiene un enlace para editar sus datos, 
         pasando el usuario (PK) como parámetro.-->
       <div class="botones_acciones">
        <a href="alumno_edit.php?usuario=<?= (int) $a['usuario'] ?>">Editar</a>
        <a href="alumno_delete.php?usuario=<?= (int) $a['usuario'] ?>" onclick="return confirm('¿Estás seguro de que deseas borrar este alumno?');">Borrar</a>
        
        <form action="reseteo_contraseña_alumno.php" method="POST" style="display:inline;" onsubmit="return confirm('¿Deseas resetear la contraseña de este alumno?');">
        <input type="hidden" name="usuario" value="<?= (int) $a['usuario'] ?>">
        <button class="boton_resetear" type="submit">Resetear contraseña</button>
    </form>
        </div>
    
    </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    </div>
   <script>
    document.getElementById('filtroInactivos').addEventListener('change', function () {
        const mostrar = this.checked; // Si el checkbox está marcado, mostrar, si no, ocultar
        const filasInactivas = document.querySelectorAll('.tabla-fila.inactiva'); // Todas las filas con la clase 'inactiva'

        filasInactivas.forEach(fila => {
            fila.style.display = mostrar ? '' : 'none'; // Si mostrar es true, se muestran, si no, se ocultan
        });
    });
</script>
    <?php else: ?>
        <p>No hay alumnos registrados aún.</p>
        <?php endif; ?>
<?php
//linkea el footer
require __DIR__.'/footer.php';  
?>
<!-- linkea las validaciones 
(hace que solo se puedan ingresar letras en los campos de nombre y apellido)-->
<script src="validaciones.js"></script>
