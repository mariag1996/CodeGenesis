<?php
require __DIR__ . '/procesar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int) $_POST['id'];

    // Conexión a la base de datos
    $host = 'localhost';
    $dbname = 'codegenesis';
    $user = 'root';
    $pass = '';

    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);

        // ✅ Obtener datos de la noticia (para eliminar imagen si existe)
        $stmt = $pdo->prepare("
            SELECT noticias.imagen_id, imagenes.url AS imagen_url
            FROM noticias
            LEFT JOIN imagenes ON noticias.imagen_id = imagenes.id
            WHERE noticias.id = ?
        ");
        $stmt->execute([$id]);
        $noticia = $stmt->fetch();

        if (!$noticia) {
            throw new Exception('La noticia no existe.');
        }

        $pdo->beginTransaction();

        // ✅ Eliminar la noticia
        $stmt = $pdo->prepare("DELETE FROM noticias WHERE id = ?");
        $stmt->execute([$id]);

        // ✅ Eliminar la imagen asociada (si existe)
        if (!empty($noticia['imagen_url'])) {
            $rutaImagen = __DIR__ . '/' . $noticia['imagen_url'];
            if (file_exists($rutaImagen)) {
                @unlink($rutaImagen);
            }

            // También eliminar de la tabla `imagenes` si no se usa en otra noticia
            $stmt = $pdo->prepare("DELETE FROM imagenes WHERE id = ?");
            $stmt->execute([$noticia['imagen_id']]);
        }

        $pdo->commit();

        //  Registrar acción en historial
        require_once __DIR__ . '/procesar_historial.php';
        procesarHistorial('noticias', 'borrar', $id);

        //  Redirigir de vuelta al listado
        header('Location: noticias_ver.php');
        exit;

    } catch (Throwable $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo "<div style='color:red;text-align:center;margin-top:20px;'>Error al borrar la noticia: "
             . htmlspecialchars($e->getMessage()) . "</div>";
        echo "<p style='text-align:center;'><a href='noticias_ver.php'>← Volver al listado</a></p>";
    }
} else {
    echo "<div style='color:red;text-align:center;margin-top:20px;'>Solicitud inválida.</div>";
    echo "<p style='text-align:center;'><a href='noticias_ver.php'>← Volver</a></p>";
}