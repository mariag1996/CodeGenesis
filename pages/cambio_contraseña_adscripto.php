<!-- ......Este archivo se encarga de de cambiar la contraseña del usuario adscripto........ -->
<?php 
// =====================================
//  CONEXIÓN A LA BASE DE DATOS
// ===================================== 

// Se incluye el archivo 'db.php', que contiene la función getDB()
// para obtener una conexión segura a la base de datos mediante mysqli.
require_once __DIR__ . '/db.php';
// Se incluye procesar.php para manejo de sesion de adscripto
require __DIR__.'/procesar.php'; 
// Se incluye la cabecera principal del sitio con estilos, íconos y navegación.
require __DIR__.'/header.php'; 


// =====================================
//  OBTENER USUARIO ACTUAL
// =====================================

// Se obtiene el nombre del usuario logueado desde la sesión.
$usuario = $_SESSION['usuario'];

// Variable para mostrar mensajes de éxito o error.
$mensaje = "";
// Conexión a la base de datos.
$db = getDB();

// =====================================
//  MANEJO DEL FORMULARIO POST
// =====================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
// Se obtienen las contraseñas ingresadas por el usuario.
    $nueva = $_POST['nueva_contraseña'] ?? '';
    $confirmar = $_POST['confirmar_contraseña'] ?? '';
 
    // =====================================
    //  VALIDACIÓN DE LA CONTRASEÑA
    // =====================================

    // 1️⃣ La contraseña debe tener al menos 8 caracteres, 
    //    al menos una letra mayúscula y un número.
    if (strlen($nueva) < 8 || 
        !preg_match('/[A-Z]/', $nueva) || 
        !preg_match('/[0-9]/', $nueva)) {
        $mensaje = "La contraseña debe tener al menos 8 caracteres, una mayúscula y un número.";
       // 2️⃣ Las contraseñas deben coincidir.
    } elseif ($nueva !== $confirmar) {
        $mensaje = "Las contraseñas no coinciden.";

    // =====================================
    //  ACTUALIZAR CONTRASEÑA EN LA BASE DE DATOS
    // =====================================
    } else {
        // Hasheamos la contraseña antes de guardarla
        $hash = password_hash($nueva, PASSWORD_DEFAULT);
        // Se prepara la consulta SQL para actualizar la contraseña del usuario actual.
        $stmt = $db->prepare("UPDATE adscripto SET contraseña = ? WHERE usuario = ?");
        // Se vinculan los parámetros: hash de la contraseña (string) y usuario (int).
        $stmt->bind_param("si", $hash, $usuario);
        // Se ejecuta la consulta y se define el mensaje según el resultado.
        if ($stmt->execute()) {
            $mensaje = "Contraseña actualizada correctamente.";
        } else {
            $mensaje = "Error al actualizar la contraseña.";
        }
        // Cerramos el statement para liberar memoria.
        $stmt->close();
    }
}
?>

<!-- =====================================
TÍTULO DE LA PÁGINA
===================================== -->
<div class="contenedor_titulo">
         <h1 class="titulo" >Cambiar Contraseña</h1>
</div>

<!-- =====================================
FORMULARIO DE CAMBIO DE CONTRASEÑA
===================================== -->
        <div class="contenedor_formulario">
        <form class="formulario" method="POST" action="">
        <!-- Nueva Contraseña -->
        <div class="casilla">
        <label class="guardar_datos" for="nueva_contraseña">Nueva Contraseña:</label><br>
            <input class="campo" type="password" id="nueva_contraseña" name="nueva_contraseña" required
                   pattern="(?=.*[A-Z])(?=.*\d).{8,}"
                   title="Debe tener al menos 8 caracteres, una mayúscula y un número."><br><br>
        </div>
         <!-- Confirmar Contraseña -->
        <div class="casilla">
            <label class="guardar_datos" for="confirmar_contraseña">Confirmar Contraseña:</label><br>
            <input class="campo" type="password" id="confirmar_contraseña" name="confirmar_contraseña" required><br><br>
            </div> 

            <button class="boton_guardar" type="submit">Cambiar Contraseña</button>
        </form>
            </div>
    </div>
     <?php if ($mensaje): ?>
        <div class="mensaje-exito">
            <p style="color: <?= strpos($mensaje, '') !== false ? 'green' : 'red' ?>;">
                <?= htmlspecialchars($mensaje) ?>
            </p>
            </div>
        <?php endif; ?>
</body>
</html>