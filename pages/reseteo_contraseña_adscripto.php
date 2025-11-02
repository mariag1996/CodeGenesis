<?php
require_once __DIR__ . '/db.php';

session_start();

// Verifica si la sesión ha expirado (15 minutos = 900 segundos)
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 900)) {
    session_unset();     // Limpia variables de sesión
    session_destroy();   // Destruye la sesión
    header("Location: login.html?timeout=1");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time(); // Actualiza el tiempo de la última actividad


if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'director') {
    header("Location: login.html");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuario'])) {
    $usuario = (int) $_POST['usuario'];

    // La nueva contraseña será igual al usuario (cédula)
    $nueva_contraseña = password_hash($usuario, PASSWORD_DEFAULT);

    $conexion = getDB();
    $stmt = $conexion->prepare("UPDATE adscripto SET contraseña = ? WHERE usuario = ?");
    $stmt->bind_param("si", $nueva_contraseña, $usuario);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header("Location: adscripto_registro.php");
        exit();
    } else {
        echo "Error: No se pudo actualizar la contraseña.";
    }

    $stmt->close();
    $conexion->close();
} else {
    echo "Petición inválida.";
}
?>