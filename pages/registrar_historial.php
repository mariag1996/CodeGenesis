<?php
function registrarHistorial($tabla, $accion, $id_registro, $usuario_id = null) {
    require_once __DIR__ . '/db.php'; 
    $conexion = getDB();

    $stmt = $conexion->prepare("
        INSERT INTO historial_registros (tabla_modificada, tipo_accion, id_registro, usuario_id)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("ssii", $tabla, $accion, $id_registro, $usuario_id);
    $stmt->execute();
    $stmt->close();
}
?>