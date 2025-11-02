<?php
require_once __DIR__ . '/registrar_historial.php';

function procesarHistorial($tabla, $accion, $id_registro) {
    // Inicia sesión si no está iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // ID del adscripto logueado (desde $_SESSION)
    $usuario_id = $_SESSION['usuario'] ?? 0;

    // Llama a la función que guarda el registro en la tabla historial_registros
    registrarHistorial($tabla, $accion, $id_registro, $usuario_id);
}
?>