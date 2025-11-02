<?php
require_once __DIR__ . '/db.php';
require __DIR__.'/procesar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuario'])) {
    $usuario = (int) $_POST['usuario'];

    // La nueva contraseña será igual al usuario (cédula)
    $nueva_contraseña = password_hash($usuario, PASSWORD_DEFAULT);

    $conexion = getDB();
    $stmt = $conexion->prepare("UPDATE alumno SET contraseña = ? WHERE usuario = ?");
    $stmt->bind_param("si", $nueva_contraseña, $usuario);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header("Location: alumnos_registro.php");
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