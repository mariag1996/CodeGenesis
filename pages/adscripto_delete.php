<?php
session_start();

// --- Control de expiración de sesión ---
// Verifica si la sesión ha expirado (15 minutos = 900 segundos)
// Si existe la marca de última actividad y han pasado más de 900 segundos (15 minutos)...
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 900)) {
    session_unset();     // Limpia variables de sesión
    session_destroy();   // Destruye la sesión
    header("Location: login.html?timeout=1"); // Redirige con un indicador de expiración
    exit();
}

// Actualiza el tiempo de última actividad a "ahora"
$_SESSION['LAST_ACTIVITY'] = time(); // Actualiza el tiempo de la última actividad

// --- Control de acceso ---
// Verifica que haya un usuario logueado y que tenga el rol "director"
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'director') {
    header("Location: logeo.php");
    exit();
}
// --- Conexión a la base de datos ---
require_once __DIR__ . '/db.php';

// Obtiene el parámetro "usuario" de la URL (GET) y lo convierte a entero
$usuario = isset($_GET['usuario']) ? (int) $_GET['usuario'] : 0;
// Verifica que el valor sea válido (>0)
if ($usuario <= 0) {
    die("Usuario inválido."); // Detiene la ejecución si el parámetro no es correcto
}

// Prepara la sentencia SQL para eliminar el registro del adscripto
$stmt = getDB()->prepare("DELETE FROM adscripto WHERE usuario = ?");
// Asocia el valor del parámetro (entero)
$stmt->bind_param("i", $usuario);
// Ejecuta la sentencia
$stmt->execute();
// Cierra el statement (libera recursos)
$stmt->close();

// Redirige de nuevo a la página de listado de adscriptos
header("Location: adscripto_registro.php");
exit;
?>