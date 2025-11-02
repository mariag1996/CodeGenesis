<?php
// noticias_update.php
require __DIR__ . '/header.php';
require __DIR__.'/procesar.php';

$mensaje = '';

// Verifica que el formulario haya sido enviado por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Conexión a la BD
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

        // Obtener datos del formulario
        $id = $_POST['id'] ?? null;
        $titulo = $_POST['titulo'] ?? '';
        $contenido = $_POST['contenido'] ?? '';

        if (!$id || !is_numeric($id) || empty($titulo) || empty($contenido)) {
            throw new Exception('Datos inválidos.');
        }

        $pdo->beginTransaction();

        // Verificar si se subió una nueva imagen
        if (isset($_FILES['miArchivo']) && $_FILES['miArchivo']['error'] === UPLOAD_ERR_OK) {
            $tmp = $_FILES['miArchivo']['tmp_name'];
            $imgInfo = @getimagesize($tmp);
            if ($imgInfo === false) {
                throw new Exception('El archivo no es una imagen válida.');
            }

            $imgType = $imgInfo[2];
            $extension = image_type_to_extension($imgType, false);
            if ($extension === 'jpg') $extension = 'jpeg';

            $permitidas = ['jpeg', 'png', 'gif', 'webp'];
            if (!in_array($extension, $permitidas, true)) {
                throw new Exception('Formato de imagen no permitido.');
            }

            $uploadDir = __DIR__ . '/uploads/img';
            $publicBase = 'uploads/img/';
            $filename = bin2hex(random_bytes(8)) . '_' . time() . '.' . ($extension === 'jpeg' ? 'jpg' : $extension);
            $destPath = $uploadDir . '/' . $filename;

            if (!move_uploaded_file($tmp, $destPath)) {
                throw new Exception('No se pudo mover el archivo al directorio final.');
            }

            $relativeUrl = $publicBase . $filename;

            // Guardar la nueva imagen en la tabla de imágenes
            $stmt = $pdo->prepare('INSERT INTO imagenes (url) VALUES (?)');
            $stmt->execute([$relativeUrl]);
            $nuevaImagenId = $pdo->lastInsertId();

            // Actualizar la noticia con nueva imagen
            $stmt2 = $pdo->prepare('UPDATE noticias SET titulo = ?, contenido = ?, imagen_id = ? WHERE id = ?');
            $stmt2->execute([$titulo, $contenido, $nuevaImagenId, $id]);
        } else {
            // Si no hay nueva imagen, solo actualizamos título y contenido
            $stmt = $pdo->prepare('UPDATE noticias SET titulo = ?, contenido = ? WHERE id = ?');
            $stmt->execute([$titulo, $contenido, $id]);
        }

        $pdo->commit();

        require_once __DIR__ . '/procesar_historial.php';
        procesarHistorial('noticias', 'edicion', $id);

        $mensaje = '<div style="color: green;">Noticia actualizada correctamente.</div>';
    } catch (Throwable $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if (isset($destPath) && file_exists($destPath)) {
            @unlink($destPath);
        }
        $mensaje = '<div style="color: red;">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
} else {
    $mensaje = '<div style="color: red;">Acceso inválido.</div>';
}
?>

<div class="contenedor_titulo">
    <h1 class="titulo">Resultado de la Actualización</h1>
</div>

<div class="contenedor_boton">
    
<div class="contenedor_mensaje">
<?= $mensaje ?>
</div>
    <br>
    <div class="contenedor__boton_listado">
        <p><a class="boton_listado" href="noticias_ver.php">Volver al listado</a></p>
    </div>
</div>

</body>
</html>