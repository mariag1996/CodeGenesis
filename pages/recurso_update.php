<?php
require_once __DIR__ . '/db.php';



if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: recurso_registro.php");
    exit;
}

$id_recurso = (int)($_POST['id_recurso'] ?? 0);
$nombre = trim($_POST['nombre'] ?? '');
$cantidad = $_POST['cantidad'] ?? '';

if ($id_recurso <= 0 || $cantidad === '') {
    die("Datos inválidos.");
}

try {
    $stmt = getDB()->prepare("UPDATE recurso SET nombre = ?, cantidad = ?  WHERE id_recurso = ?");
    $stmt->bind_param("sii", $nombre,  $cantidad, $id_recurso);
    $stmt->execute();
    $stmt->close();

    header("Location: recursos_registro.php");
    exit;
} catch (mysqli_sql_exception $e) {
    echo "Error al actualizar: " . htmlspecialchars($e->getMessage());
}
