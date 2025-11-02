<?php
require_once __DIR__ . '/config.php'; // Trae las constantes DB_HOST, DB_USER, etc.

// Activamos los reportes de errores de mysqli para que lance excepciones
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function getDB(): mysqli {
    static $conn = null; // Guardamos la conexión para reutilizarla
    // Si ya existe una conexión válida, la devolvemos
    if ($conn instanceof mysqli) {
        return $conn;
    }
 // Creamos la conexión
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset(DB_CHARSET);
    // Verificamos errores de conexión
  if ($conn->connect_error) {
        die('Error de conexión a la base de datos: ' . $conn->connect_error);
    }

    return $conn;
}

?>

