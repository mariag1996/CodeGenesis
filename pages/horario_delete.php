<?php
require_once __DIR__ . '/db.php';

$grupo = isset($_GET['grupo']) ? $_GET['grupo'] : '';

if (empty($grupo)) {
    die("Grupo inválido.");
}

$stmt = getDB()->prepare("DELETE FROM horario WHERE grupo = ?");
$stmt->bind_param("s", $grupo);  // "s" porque es una cadena (string)
$stmt->execute();
$stmt->close();

require_once __DIR__ . '/procesar_historial.php';
procesarHistorial('horario', 'borrar', $grupo);


// Redirige de nuevo al listado
header("Location: horarios_registro.php");
exit;
?>