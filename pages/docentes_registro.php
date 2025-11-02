<!-- ......Este archivo se encarga de registrar a los docentes ...... -->

<?php
// =====================================
//  CONEXIÓN A LA BASE DE DATOS
// ===================================== 

// --- Conexión a la base de datos ---
// Se incluye el archivo 'db.php', que contiene la función getDB()
// para obtener una conexión segura a la base de datos mediante mysqli.
require_once __DIR__ . '/db.php';
// Se incluye procesar.php para manejo de sesion de adscripto
require __DIR__.'/procesar.php';

$msg = ''; // Variable para almacenar mensajes de error o éxito
$hash = ''; // Inicialización de la variable $hash

// --- Procesamiento del formulario de registro ---
//Verifica si el formulario fue enviado (método POST).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //Recoge los datos del formulario usando $_POST, aplicando trim() para eliminar espacios.
    $nombreDocente = trim($_POST['nombreDocente'] ?? '');
    $apellidoDocente = trim($_POST['apellidoDocente'] ?? '');
    $usuarioDocente = trim($_POST['usuarioDocente'] ?? '');
    $contraseniaDocente = trim($_POST['contraseniaDocente'] ?? '');
    $repetirContrasenia = trim($_POST['repetircontrasenia'] ?? '');

     // --- Validaciones ---
    // Verifica que las contraseñas coincidan
    if ($contraseniaDocente !== $repetirContrasenia) {
        $msg = "Las contraseñas no coinciden.";
    }
    // Verifica que los demás campos no estén vacíos
    elseif ($nombreDocente === '' || $apellidoDocente === '' || $usuarioDocente === '') {
        $msg = "Datos inválidos. Asegúrate de ingresar los datos correctamente.";
    } 
    // Si las contraseñas coinciden y los demás campos están completos
    else {
        //Hashea la contraseña
        $hash = password_hash($contraseniaDocente, PASSWORD_DEFAULT);
        try {
            // --- Inserta los datos del nuevo docente en la base de datos ---
            $stmt = getDB()->prepare("INSERT INTO docente (nombre, apellido, usuario, contraseña) VALUES (?, ?, ?, ?)");
            // Se vinculan los valores a la consulta (ssiss = string, string, int, string)
            $stmt->bind_param("ssis", $nombreDocente, $apellidoDocente, $usuarioDocente, $hash);
            $stmt->execute();
            $stmt->close();
            // Mensaje de éxito
            $msg = "Docente guardado correctamente.";

             // ======================
            //   REGISTRO EN HISTORIAL
            // ======================

            // --- Registro en el historial ---
            // Incluye el archivo procesar_historial.php que contiene la función procesarHistorial()
            // para registrar las acciones realizadas en el sistema por el adscripto (registro, borrar, edición.).
            require_once __DIR__ . '/procesar_historial.php';
            // Llama a la función para guardar el evento en el historial:
            //   - 'docente' → tabla afectada
            //   - 'registro' → tipo de acción
            //   - $usuario → ID (PK) del docente afectado
            procesarHistorial('docente', 'registro', $usuarioDocente);

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
    <h1 class="titulo">Registro de Docentes</h1>
    </div>
   <div class="contenedor_formulario">
    <form class="formulario" action="" method="POST">
    

    <div class="casilla">
        <label class="guardar_datos" for="nombreDocente">Nombre:</label>
        <input class="campo" type="text" pattern="[A-Za-zÁáÉéÍíÓóÚúÑñ\s]+" 
       title="Solo se permiten letras y espacios" id="nombreDocente" name="nombreDocente" required/>
    </div>
    
    <div class="casilla">
        <label class="guardar_datos" for="apellidoDocente">Apellido:</label>
        <input class="campo" type="text" pattern="[A-Za-zÁáÉéÍíÓóÚúÑñ\s]+" 
       title="Solo se permiten letras y espacios" id="apellidoDocente" name="apellidoDocente" required/>
    </div>


<!-- inputmode="numeric" muestra teclado numérico en móviles-->
<!-- maxlength="8" limita la entrada a 8 caracteres-->
<!-- pattern="\d{1,8}": asegura que solo se ingresen entre 1 y 8 dígitos-->
    <div class="casilla">
        <label class="guardar_datos" for="usuarioDocente">Usuario (Cédula):</label>
        <input class="campo" type="text" inputmode="numeric" maxlength="8" pattern="\d{1,8}" id="usuarioDocente" name="usuarioDocente" required/>
    </div>

    <div class="casilla">
        <label class="guardar_datos" for="contraseniaDocente">Contraseña (Cédula):</label>
        <input class="campo" type="password" id="contraseniaDocente" name="contraseniaDocente" required/>
    </div>

    <div class="casilla">
        <label class="guardar_datos" for="repetircontrasenia">Repetir Contraseña:</label>
        <input  class="campo" type="password" id="repetircontrasenia" name="repetircontrasenia" required/>
    </div>
    
    <div class="contenedor__boton">
    <button class="boton_guardar" type="submit">Guardar</button>
    </div>
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

//Consulta todos los docentes registrados y los ordena por fecha de creación en orden descendente.
$result = getDB()->query("SELECT usuario, nombre, apellido, creado_en FROM docente ORDER BY creado_en DESC");
$docentes = $result->fetch_all(MYSQLI_ASSOC);

$result->close();
?>

<div class="cont_titulo">
  <div class="contenedor__titulo-lista">
    <h2 class="titulo-lista">Docentes registrados</h2>
    </div>
    </div>

    <!-- Muestra los datos en una tabla -->
    <?php if ($docentes): ?>
        <div class="contenedor_tabla_responsive">
        <table class="tabla_lista tabla" border="1" cellpadding="6" cellspacing="0">
            <thead class="tabla-cabecera">
                <tr class="tabla-fila">
                    <th class="tabla-celda">Usuario</th>
                    <th class="tabla-celda">Nombre</th>
                    <th class="tabla-celda">Apellido</th>
                    <th class="tabla-celda">Creado en</th>
                    <th class="tabla-celda">Acciones</th>
    </tr>
    </thead>

    <!-- Muestra los datos en una tabla -->
    <?php 
    foreach ($docentes as $a): ?>
    <tr class="tabla-fila">
        <td class="tabla-celda"><?= (int) $a['usuario'] ?></td>
        <td class="tabla-celda"><?= htmlspecialchars($a['nombre']) ?></td>
        <td class="tabla-celda"><?= htmlspecialchars($a['apellido']) ?></td>
        <td class="tabla-celda"><?= htmlspecialchars($a['creado_en']) ?></td>
        <td class="tabla-celda">
            <!-- Cada fila de docente tiene un enlace para editar sus datos, 
         pasando el usuario (PK) como parámetro.-->
        <div class="botones_acciones">
        <a href="docente_edit.php?usuario=<?= (int) $a['usuario'] ?>">Editar</a>
        <a href="docente_delete.php?usuario=<?= (int) $a['usuario'] ?>" onclick="return confirm('¿Estás seguro de que deseas borrar este docente?');">Borrar</a>
         
        <form action="reseteo_contraseña_docente.php" method="POST" style="display:inline;" onsubmit="return confirm('¿Deseas resetear la contraseña de este docente?');">
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
    <?php else: ?>
        <p>No hay docentes registrados aún.</p>
        <?php endif; ?>
  
<?php
//linkea el footer
require __DIR__.'/footer.php';  
?>
<!-- linkea las validaciones 
(hace que solo se puedan ingresar letras en los campos de nombre y apellido)-->
<script src="validaciones.js"></script>