<!-- ......Este archivo se encarga de procesar el formulario de edición (asignatura_edit.php)........ -->
<?php

// Se incluye el archivo 'db.php', que contiene la función getDB()
require_once __DIR__ . '/db.php';

//Si el script no fue accedido mediante POST (es decir, desde un formulario).
//  Por ejemplo, si alguien lo abre directamente en el navegador
// redirige al listado de asignaturas y termina la ejecución.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: asignaturas_registro.php");
    exit;
}

//Se obtienen los datos enviados por el formulario
// y se usa trim() para eliminar espacios en blanco al inicio y al final.
$id_asignatura = (int)($_POST['id_asignatura'] ?? 0); // ID de la asignatura (entero)
$nombre = trim($_POST['nombre'] ?? ''); // Nombre de la asignatura
// Si el checkbox "activo" está marcado, su valor será 1; si no, 0
$activo = isset($_POST['activo']) ? 1 : 0;

// Validación básica:
// - El ID debe ser mayor que 0 (asignatura válida)
// - El nombre no puede estar vacío
if ($id_asignatura <= 0 || $nombre === '') {
    die("Datos inválidos.");
}

try {
    // Se prepara una consulta SQL segura para actualizar los datos de la asignatura
    // Los signos ? son marcadores de posición que luego se reemplazan con los valores reales
    $stmt = getDB()->prepare("UPDATE asignatura SET nombre = ?, activo = ?  WHERE id_asignatura = ?");
    // "sii" indica los tipos de datos: string (s), integer (i), integer (i)
    $stmt->bind_param("sii", $nombre,  $activo, $id_asignatura);
    $stmt->execute();
    $stmt->close();

    // Si todo salió bien, redirige de nuevo a la página principal de asignaturas
    header("Location: asignaturas_registro.php");
    exit;
} catch (mysqli_sql_exception $e) {
    // Si ocurre un error con la base de datos (por ejemplo, conexión o SQL inválido),
    // se muestra un mensaje seguro (evitando inyección con htmlspecialchars)
    echo "Error al actualizar: " . htmlspecialchars($e->getMessage());
}
