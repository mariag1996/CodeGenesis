<?php
// --- Conexión a la base de datos ---
// Se incluye el archivo db.php, que contiene la función getDB()
// para obtener la conexión a la base de datos.
require_once __DIR__ . '/db.php';
// Se incluye procesar.php para manejo de sesion de adscripto
require __DIR__.'/procesar.php'; 

// --- Obtención del parámetro 'usuario' desde la URL ---
// Si se recibe el parámetro por GET, se convierte a entero para evitar inyecciones o texto malicioso.
// Si no existe, se asigna el valor 0.
$usuario = isset($_GET['usuario']) ? (int) $_GET['usuario'] : 0;


// --- Validación del parámetro ---
// Si el valor del usuario no es válido (0 o negativo), 
// el script se detiene y muestra un mensaje de error.
if ($usuario <= 0) {
    die("Usuario inválido.");
}

// --- Eliminación del alumno ---
// Se prepara una consulta SQL segura usando prepared statements para prevenir inyecciones SQL.
$stmt = getDB()->prepare("DELETE FROM alumno WHERE usuario = ?");
// Vincula el parámetro con el marcador ?
// 'i' indica que el tipo de dato es entero (integer).
$stmt->bind_param("i", $usuario);
// Ejecuta la consulta (borra el alumno con ese 'usuario')
$stmt->execute();
// Cierra el statement para liberar recursos
$stmt->close();

// --- Registro en el historial ---
// Incluye el archivo procesar_historial.php que contiene la función procesarHistorial()
// para registrar las acciones realizadas en el sistema por el adscripto (registro, borrar, edición.).
 require_once __DIR__ . '/procesar_historial.php';
// Llama a la función para guardar el evento en el historial:
//   - 'alumno' → tabla afectada
//   - 'borrar' → tipo de acción
//   - $usuario → ID (PK) del alumno afectado
procesarHistorial('alumno', 'borrar', $usuario);

// --- Redirección final ---
// Una vez eliminado el registro y registrado el historial,
// redirige al listado principal de alumnos.
header("Location: alumnos_registro.php");
exit;
?>