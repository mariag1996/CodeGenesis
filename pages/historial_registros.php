<?php
require_once __DIR__ . '/db.php';
require __DIR__ . '/procesar.php'; // si necesitás sesión o autenticación

// Consulta el historial ordenado del más reciente al más antiguo
$stmt = getDB()->prepare("
      SELECT 
        h.id,
        h.tabla_modificada,
        h.tipo_accion,
        h.id_registro,
        h.usuario_id,
        CONCAT(a.nombre, ' ', a.apellido) AS nombre_adscripto,
        h.fecha
    FROM historial_registros h
    LEFT JOIN adscripto a ON h.usuario_id = a.usuario
    ORDER BY h.fecha DESC
");
$stmt->execute();
$result = $stmt->get_result();
$historial = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/**
 * Función auxiliar que devuelve un nombre legible del registro
 */
function obtenerNombreRegistro($tabla, $id_registro) {
    $db = getDB();

    switch ($tabla) {
        case 'alumno':
        case 'docente':
        case 'adscripto':
            $campo_id = 'usuario';
            $campo_nombre = "CONCAT(nombre, ' ', apellido)";
            break;

        case 'asignatura':
            $campo_id = 'id_asignatura';
            $campo_nombre = 'nombre';
            break;

        case 'aula':
            $campo_id = 'id_aula';
            $campo_nombre = 'nombre';
            break;

        case 'grupo':
            $campo_id = 'nombre'; // grupo usa el nombre como PK
            $campo_nombre = 'nombre';
            break;

        case 'horario':
            $campo_id = 'id_horario';
            $campo_nombre = "CONCAT(grupo)";
            break;

        case 'inasistencia':
            $campo_id = 'id_inasistencia';
            $campo_nombre = "CONCAT(grupo)";
            break;

        case 'noticias':
            $campo_id = 'id';
            $campo_nombre = 'titulo';
            break;

        case 'recurso':
            $campo_id = 'id_recurso';
            $campo_nombre = 'nombre';
            break;

        default:
            return 'Desconocido';
    }

    // Consulta dinámica y segura
    $stmt = $db->prepare("SELECT $campo_nombre AS nombre FROM $tabla WHERE $campo_id = ?");
    if (!$stmt) return 'Error SQL';

    $stmt->bind_param("s", $id_registro);
    $stmt->execute();
    $resultado = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $resultado['nombre'] ?? 'No encontrado';
}
?>

?>

<?php require __DIR__ . '/header.php'; ?>

<div class="contenedor_titulo">
    <h1 class="titulo">Historial de Registros</h1>
</div>

<div class="contenedor_tabla_responsive">
    <?php if (!empty($historial)): ?>
        <div class="contenedor_tabla_responsive">
         <table class="tabla_lista tabla" border="1" cellpadding="6" cellspacing="0">
            <thead class="tabla-cabecera">
                <tr class="tabla-fila">
                     <th>ID</th>
                    <th>Tabla</th>
                    <th>Acción</th>
                    <th>Registro</th>
                    <th>Adscripto</th>
                    <th>Fecha y hora</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($historial as $h): ?>
                    <?php $nombreRegistro = obtenerNombreRegistro($h['tabla_modificada'], $h['id_registro']); ?>
                    <tr class="tabla-fila">
                        <td class="tabla-celda"><?= htmlspecialchars($h['id']) ?></td>
                        <td class="tabla-celda"><?= htmlspecialchars($h['tabla_modificada']) ?></td>
                        <td class="tabla-celda"><?= htmlspecialchars($h['tipo_accion']) ?></td>
                        <td class="tabla-celda"><?= htmlspecialchars($nombreRegistro) ?></td>
                        <td class="tabla-celda">
                            <?= htmlspecialchars($h['nombre_adscripto'] ?? 'Desconocido') ?>
                            (ID: <?= htmlspecialchars($h['usuario_id']) ?>)
                        </td>
                        <td class="tabla-celda"><?= htmlspecialchars($h['fecha']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
                </div>
    <?php else: ?>
        <p>No hay registros en el historial aún.</p>
    <?php endif; ?>
</div>


<?php require __DIR__ . '/footer.php'; ?>