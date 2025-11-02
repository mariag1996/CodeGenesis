<!-- ......Este archivo se encarga de procesar el formulario de edición (aula_edit.php)........ -->
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
    header("Location: aulas_registro.php");
    exit;
}

// =====================================
//  OBTENCIÓN Y VALIDACIÓN DE DATOS
// =====================================

//Se obtienen los datos enviados por el formulario
// y se usa trim() para eliminar espacios en blanco al inicio y al final.
$id_aula = (int)($_POST['id_aula'] ?? 0); // ID del aula (entero)
$nombre = trim($_POST['nombre'] ?? ''); // Nombre del aula
// Si el checkbox "activo" está marcado, su valor será 1; si no, 0
$activo = isset($_POST['activo']) ? 1 : 0;

// Validación básica:
// - El ID debe ser mayor que 0 (aulaválida)
// - El nombre no puede estar vacío
if ($id_aula <= 0 || $nombre === ' ') {
    die("Datos inválidos.");
}

// =====================================
//  ACTUALIZAR AULA EN LA BASE DE DATOS
// =====================================

try {
    // Se prepara una consulta SQL segura para actualizar los datos del aula
    // Los signos ? son marcadores de posición que luego se reemplazan con los valores reales
    $stmt = getDB()->prepare("UPDATE aula SET nombre = ?, activo = ? WHERE id_aula = ?");
    // "sii" indica los tipos de datos: string (s), integer (i), integer (i)
    $stmt->bind_param("sii", $nombre, $activo, $id_aula);
    // Se ejecuta la actualización
    $stmt->execute();
    // Se cierra el statement para liberar recursos
    $stmt->close();

// =====================================
//  REDIRECCIÓN POST-ACTUALIZACIÓN
// =====================================

 // Si todo salió bien, redirige de nuevo a la página principal de aulas
    header("Location: aulas_registro.php");
    exit;
} catch (mysqli_sql_exception $e) {
    // Si ocurre un error con la base de datos (por ejemplo, conexión o SQL inválido),
    // se muestra un mensaje seguro (evitando inyección con htmlspecialchars)
    echo "Error al actualizar: " . htmlspecialchars($e->getMessage());
}
