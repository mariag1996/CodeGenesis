<?php
require_once __DIR__ . '/db.php';
require __DIR__.'/procesar.php';

$db = getDB();

$grupo = $_GET['grupo'] ?? '';

if (!$grupo) {
    echo "Grupo no especificado.";
    exit;
}

// Eliminar las inasistencias del grupo
$stmt = $db->prepare("DELETE FROM inasistencia WHERE grupo = ?");
$stmt->bind_param("s", $grupo);
$stmt->execute();
$stmt->close();

require_once __DIR__ . '/procesar_historial.php';
procesarHistorial('inasistencia', 'borrar', $grupo);

// Redirigir de vuelta al índice
header("Location: inasistencias_index.php");
exit;