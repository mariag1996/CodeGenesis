<!-- ......Este archivo se encarga de procesar el formulario de edición (grupo_edit.php)........ -->
<?php
// =====================================
//  CONEXIÓN A LA BASE DE DATOS
// =====================================

// Se incluye el archivo 'db.php', que contiene la función getDB()
// para obtener una conexión segura a la base de datos mediante mysqli.
require_once __DIR__ . '/db.php';

// =====================================
//  VERIFICAR MÉTODO DE ACCESO
// =====================================

//Si el script no fue accedido mediante POST (es decir, desde un formulario).
//  Por ejemplo, si alguien lo abre directamente en el navegador
// redirige al listado de aulas y termina la ejecución.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: grupos_registro.php");
    exit;
}

// =====================================
//  OBTENCIÓN Y VALIDACIÓN DE DATOS
// =====================================

//Se obtienen los datos enviados por el formulario
// y se usa trim() para eliminar espacios en blanco al inicio y al final.
$nombreNuevo = trim($_POST['nombre'] ?? '');
$nombreOriginal = trim($_POST['original'] ?? '');
// Si el checkbox "activo" está marcado, su valor será 1; si no, 0
$activo = isset($_POST['activo']) ? 1 : 0;

// Validación básica:
// - Los nombres no pueden estar vacíos
if ($nombreNuevo === '' || $nombreOriginal === '') {
    die("Datos inválidos.");
}

// =====================================
//  ACTUALIZAR GRUPO EN LA BASE DE DATOS
// =====================================

try {
    // Se prepara una consulta SQL segura para actualizar los datos del grupo
    // Los signos ? son marcadores de posición que luego se reemplazan con los valores reales
    $stmt = getDB()->prepare("UPDATE grupo SET nombre = ?, activo = ? WHERE nombre = ?");
    // "sis" indica los tipos de datos: string (s), integer (i), string (s)
    $stmt->bind_param("sis", $nombreNuevo, $activo, $nombreOriginal);
    // Se ejecuta la actualización
    $stmt->execute();
    // Se cierra el statement para liberar recursos
    $stmt->close();

// =====================================
//  REDIRECCIÓN POST-ACTUALIZACIÓN
// =====================================

 // Si todo salió bien, redirige de nuevo a la página principal de grupos
    header("Location: grupos_registro.php");
    exit;
} catch (mysqli_sql_exception $e) {
    // Si ocurre un error con la base de datos (por ejemplo, conexión o SQL inválido),
    // se muestra un mensaje seguro (evitando inyección con htmlspecialchars)
    echo "Error al actualizar: " . htmlspecialchars($e->getMessage());
}

