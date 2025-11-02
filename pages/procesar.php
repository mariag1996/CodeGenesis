
<!-- Sesion adscriptos-->

<?php
session_start();

// Verifica si la sesión ha expirado (15 minutos = 900 segundos)
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 900)) {
    session_unset();
    session_destroy();
    header("Location: logeo.php?timeout=1");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();

// Validación para adscriptos
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'adscripto') {
    header("Location: logeo.php");
    exit();
}
?>