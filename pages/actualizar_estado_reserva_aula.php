<?php
session_start();
require_once __DIR__ . '/db.php';

// --- Control de acceso ---
// Verifica si el usuario tiene rol 'adscripto'.
// Si no lo tiene, redirige al login.
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'adscripto') {
    header("Location: logeo.php");
    exit(); // Se detiene la ejecución del script tras redirigir
}

// --- Procesamiento del formulario ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_reserva = intval($_POST['id_reserva']);
    $accion = $_POST['accion'] ?? '';
    if ($id_reserva > 0 && in_array($accion, ['aceptar', 'cancelar'])) {
        $estado_nuevo = ($accion === 'aceptar') ? 'aceptada' : 'cancelada';

        try {
            $conn = getDB();
            $stmt = $conn->prepare("UPDATE reserva_aula SET estado = ? WHERE id_reserva = ?");
            $stmt->bind_param("si", $estado_nuevo, $id_reserva);
            $stmt->execute();

    // Verifica si alguna fila fue afectada (actualizada)
            if ($stmt->affected_rows > 0) {
                $_SESSION['mensaje'] = "Reserva actualizada correctamente.";
            } else {
                $_SESSION['mensaje'] = " No se pudo actualizar la reserva.";
            }
    // Captura excepciones de MySQL y guarda un mensaje seguro
        } catch (mysqli_sql_exception $e) {     
    // Si los datos son inválidos
            $_SESSION['mensaje'] = " Error: " . htmlspecialchars($e->getMessage());
        }
    } else {
        $_SESSION['mensaje'] = "Datos inválidos.";
    }

// Redirige a la página de listado de reservas de aulas
    header("Location: ver_reservas_aulas.php");
    exit();
}
?>
