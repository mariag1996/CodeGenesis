<?php
require_once __DIR__ . '/db.php';


require __DIR__.'/procesar.php';


require __DIR__.'/header.php';
?>

<?php
// Obtener todas las reservas
$reservas = [];
try {
    $query = "
    SELECT r.id_reserva, r.fecha, r.hora_inicio, r.hora_fin, r.creado_en, r.estado,
    a.nombre AS nombre_recurso,
    asig.nombre AS nombre_asignatura,
    CONCAT(d.nombre, ' ', d.apellido) AS nombre_docente
    FROM reserva_recurso r
    JOIN recurso a ON r.id_recurso = a.id_recurso
    JOIN asignatura asig ON r.id_asignatura = asig.id_asignatura
    JOIN docente d ON r.id_docente = d.usuario
    ORDER BY r.creado_en DESC
    ";
    $result = getDB()->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $reservas[] = $row;
        }
    }
} catch (mysqli_sql_exception $e) {
    $msg = "Error al obtener reservas: " . htmlspecialchars($e->getMessage());
}
?>






<div class="contenedor_titulo">
    <h1 class="titulo">Reservas de Recursos</h1>
</div>


<?php if (!empty($reservas)): ?>
    <div class="contenedor_tabla_responsive">
    <table class="tabla_lista tabla" border="1" cellpadding="6" cellspacing="0">
        <thead class="tabla-cabecera">
            <tr class="tabla-fila">
                <th class="tabla-celda">ID</th>
                <th class="tabla-celda">Recurso</th>
                <th class="tabla-celda">Asignatura</th>
                <th class="tabla-celda">Docente</th>
                <th class="tabla-celda">Fecha</th>
                <th class="tabla-celda">Hora de inicio</th>
                <th class="tabla-celda">Hora fin</th>
                <th class="tabla-celda">Creado en</th>
                <th class="tabla-celda">Estado</th>
                <th class="tabla-celda">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reservas as $r): ?>
            <tr class="tabla-fila">
                <td class="tabla-celda"><?= (int) $r['id_reserva'] ?></td>
                <td class="tabla-celda"><?= htmlspecialchars($r['nombre_recurso']) ?></td>
                <td class="tabla-celda"><?= htmlspecialchars($r['nombre_asignatura']) ?></td>
                <td class="tabla-celda"><?= htmlspecialchars($r['nombre_docente']) ?></td>
                <td class="tabla-celda"><?= htmlspecialchars($r['fecha']) ?></td>
                <td class="tabla-celda"><?= htmlspecialchars($r['hora_inicio']) ?></td>
                <td class="tabla-celda"><?= htmlspecialchars($r['hora_fin']) ?></td>
                <td class="tabla-celda"><?= htmlspecialchars($r['creado_en']) ?></td>
                <td class="tabla-celda"><?= htmlspecialchars($r['estado']) ?></td>
                <td class="tabla-celda">
                <?php if ($r['estado'] === 'vigente'): ?>
    <form method="POST" action="actualizar_estado_reserva_recurso.php" style="display:inline;">
        <input type="hidden" name="id_reserva" value="<?= $r['id_reserva'] ?>">
        <input type="hidden" name="accion" value="aceptar">
        <button type="submit" class="boton_aceptar">Aceptar</button>
    </form>
    <form method="POST" action="actualizar_estado_reserva_recurso.php" style="display:inline;">
        <input type="hidden" name="id_reserva" value="<?= $r['id_reserva'] ?>">
        <input type="hidden" name="accion" value="cancelar">
        <button type="submit" class="boton_cancelar">Cancelar</button>
    </form>
<?php else: ?>
    <span>Sin acción</span>
<?php endif; ?>
</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
<?php else: ?>
    <p>No hay reservas registradas.</p>
<?php endif; ?>

<?php
require __DIR__.'/footer.php';  
?>
