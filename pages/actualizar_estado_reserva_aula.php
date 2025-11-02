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
// Solo se ejecuta si el método de la petición es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
// Convierte el ID de la reserva a entero para evitar inyección SQL
    $id_reserva = intval($_POST['id_reserva']);
// Obtiene la acción enviada (aceptar o cancelar). Si no existe, deja una cadena vacía
    $accion = $_POST['accion'] ?? '';
// Verifica que el ID sea válido y que la acción sea una de las permitidas
    if ($id_reserva > 0 && in_array($accion, ['aceptar', 'cancelar'])) {
// Determina el nuevo estado según la acción
        $estado_nuevo = ($accion === 'aceptar') ? 'aceptada' : 'cancelada';

        try {
    // Obtiene la conexión a la base de datos
            $conn = getDB();
    // Prepara la consulta SQL de actualización con parámetros
            $stmt = $conn->prepare("UPDATE reserva_aula SET estado = ? WHERE id_reserva = ?");
    // Vincula los parámetros: 's' = string, 'i' = integer
            $stmt->bind_param("si", $estado_nuevo, $id_reserva);
    // Ejecuta la consulta
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