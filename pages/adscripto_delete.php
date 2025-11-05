<?php
session_start();

// --- Control de expiración de sesión ---
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 900)) {
    session_unset();     
    session_destroy();   
    header("Location: logeo.php?timeout=1"); 
    exit();
}


$_SESSION['LAST_ACTIVITY'] = time(); 

// --- Control de acceso ---
// Verifica que haya un usuario logueado y que tenga el rol "director"
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'director') {
    header("Location: logeo.php");
    exit();
}
// --- Conexión a la base de datos ---
require_once __DIR__ . '/db.php';

$usuario = isset($_GET['usuario']) ? (int) $_GET['usuario'] : 0;
if ($usuario <= 0) {
    die("Usuario inválido."); 
}

// Prepara la sentencia SQL para eliminar el registro del adscripto
$stmt = getDB()->prepare("DELETE FROM adscripto WHERE usuario = ?");
$stmt->bind_param("i", $usuario);
$stmt->execute();
$stmt->close();

// Redirige de nuevo a la página de listado de adscriptos
header("Location: adscripto_registro.php");
exit;
?>
