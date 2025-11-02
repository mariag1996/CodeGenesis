<!-- ......Este archivo se encarga de cancelar las reservas de recursos hechas por los docentes........ -->
<?php
// =====================================
//  INICIO DE SESIÓN Y CONEXIÓN A DB
// =====================================
session_start();
require_once __DIR__ . '/db.php';

// --- Control de acceso ---
// Solo los usuarios con rol 'docente' pueden acceder.
// Si no existe la variable de sesión 'usuario' o el rol no es 'docente',
// redirige al formulario de login.
if (!isset($_SESSION['docente']) || $_SESSION['rol'] !== 'docente') {
    header("Location: logeo.php");
    exit();
}

// =====================================
//  PROCESAR CANCELACIÓN DE RESERVA
// =====================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_reserva'])) {
    $id_reserva = intval($_POST['id_reserva']); // Convertir a entero por seguridad
    $id_docente = $_SESSION['docente'];
    $conn = getDB(); // Obtener conexión a DB

    // Verifica que la reserva pertenezca al docente
    $check = $conn->prepare("SELECT id_reserva FROM reserva_recurso WHERE id_reserva = ? AND id_docente = ?");
    $check->bind_param("ii", $id_reserva, $id_docente);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows === 1) {
        // =====================================
        //  CANCELAR RESERVA
        // =====================================
        // Cambia el estado a "cancelada"
        $update = $conn->prepare("UPDATE reserva_recurso SET estado = 'cancelada' WHERE id_reserva = ?");
        $update->bind_param("i", $id_reserva);
        if ($update->execute()) {
        // Redirigir con mensaje de éxito
            header("Location: reservas_recurso.php?msg=cancelada");
            exit();
        } else {
        // No tiene permiso para cancelar esta reserva
            echo "<p>Error al cancelar la reserva: " . htmlspecialchars($update->error) . "</p>";
        }
    } else {
        echo "<p style='color:red;'>No tienes permiso para cancelar esta reserva.</p>";
    }
} else {
    // Si no viene por POST o no hay id_reserva
    header("Location: reservas_recurso.php");
    exit();
}
?>