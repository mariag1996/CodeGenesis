<!-- ......Este archivo se encarga de procesar el formulario de edición (alumno_edit.php)........ -->

<?php

require_once __DIR__ . '/db.php';

//Si el script no fue accedido mediante POST (es decir, desde un formulario).
//  Por ejemplo, si alguien lo abre directamente en el navegador
// redirige al listado de alumnos y termina la ejecución.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: alumnos_registro.php");
    exit;
}

//Se obtienen los datos enviados por el formulario
// y se usa trim() para eliminar espacios en blanco al inicio y al final.
$grupo = trim($_POST['grupo'] ?? ''); // Nombre del grupo asignado al alumno
$usuario = (int)($_POST['usuario'] ?? 0); // ID o cédula del alumno (entero)
$nombre = trim($_POST['nombre'] ?? '');   // Nombre del alumno
$apellido  = trim($_POST['apellido'] ?? '');  // Apellido del alumno

// --- Validaciones previas ---
// Se verifica que:
//  El usuario sea mayor que 0 (evita errores o IDs vacíos)
//  Los campos de texto no estén vacíos
// Si alguna de las condiciones falla, el script se detiene mostrando un mensaje de error.
if ($usuario <= 0 || $nombre === ' ' ||  $apellido === ' '|| $grupo === ' ') {
    die("Datos inválidos.");
}

try {
    //Se prepara una consulta SQL segura para actualizar los datos del alumno.
    $stmt = getDB()->prepare("UPDATE alumno SET nombre = ?, apellido = ?,  nombre_grupo = ? WHERE usuario = ?");
    $stmt->bind_param("sssi", $nombre, $apellido, $grupo, $usuario); 
    $stmt->execute();
    $stmt->close();

    // Si la ejecución fue correcta, se redirige al listado de alumnos.
    header("Location: alumnos_registro.php");
    exit;
     // --- Manejo de errores SQL ---
    // Si ocurre una excepción (por ejemplo, error de conexión o restricción),
    // se muestra un mensaje seguro al usuario. htmlspecialchars() evita que
    // se muestren caracteres peligrosos o se inyecte código.
} catch (mysqli_sql_exception $e) {
    echo "Error al actualizar: " . htmlspecialchars($e->getMessage());
}
