<?php
require __DIR__.'/header.php';
require __DIR__.'/procesar.php';


// Si el formulario se ha enviado (método POST)
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

        $inputName  = 'miArchivo';
        $uploadDir = __DIR__ . '/uploads/img';
        $publicBase = 'uploads/img/';

        if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('No se recibió archivo o hubo un error de subida.');
        }

        $tmp = $_FILES[$inputName]['tmp_name'];
        $imgInfo = @getimagesize($tmp);
        if ($imgInfo === false) {
            throw new Exception('El archivo no es una imagen válida.');
        }

        $imgType = $imgInfo[2];
        $extension = image_type_to_extension($imgType, false);
        if ($extension === 'jpg') $extension = 'jpeg';

        $permitidas = ['jpeg','png','gif','webp'];
        if (!in_array($extension, $permitidas, true)) {
            throw new Exception('Formato de imagen no permitido.');
        }

        $filename = bin2hex(random_bytes(8)) . '_' . time() . '.' . ($extension === 'jpeg' ? 'jpg' : $extension);
        $destPath = $uploadDir . '/' . $filename;

        if (!move_uploaded_file($tmp, $destPath)) {
            throw new Exception('No se pudo mover el archivo al directorio final.');
        }

        $relativeUrl = $publicBase . $filename;
        $publicUrl = $relativeUrl;

        $pdo->beginTransaction();

        $stmt = $pdo->prepare('INSERT INTO imagenes (url) VALUES (?)');
        $stmt->execute([$publicUrl]);
        $imagenId = $pdo->lastInsertId();

        $titulo = $_POST['titulo'] ?? '';
        $contenido = $_POST['contenido'] ?? '';

        if (empty($titulo) || empty($contenido)) {
            throw new Exception('Faltan datos del formulario.');
        }

        $stmt2 = $pdo->prepare('INSERT INTO noticias (titulo, contenido, imagen_id) VALUES (?, ?, ?)');
        $stmt2->execute([$titulo, $contenido, $imagenId]);

        $noticiaId = $pdo->lastInsertId();

        $pdo->commit();

        //  Registrar acción en el historial
        require_once __DIR__ . '/procesar_historial.php';
        procesarHistorial('noticias', 'registro', $noticiaId);  


    $mensaje = '<div style="color: green;">Noticia registrada correctamente.</div>';

    } catch (Throwable $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if (isset($destPath) && file_exists($destPath)) {
            @unlink($destPath);
        }
        $mensaje = '<div style="color: red;">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}
?>

<div class="contenedor_titulo">
    <h1 class="titulo">Registro de Noticias</h1>
</div>
<div class="contenedor__boton_listado">
    <p><a class="boton_listado" href="noticias_ver.php">Ver noticias registradas</a></p>
</div>

<main class="contenedor_formulario">

    <form class="formulario" action="" method="post" enctype="multipart/form-data">
        <label class="titulo-noticia guardar_datos" for="titulo">Título de la noticia:</label>
        <input class="campo" type="text" name="titulo" id="titulo" required />
        <br>
        <label class="guardar_datos">Selecciona archivo:
            <input class="subir-fotos" type="file" name="miArchivo" required>
        </label>
        <br>
        <label class="guardar_datos" for="contenido">Contenido:</label><br>
        <textarea class="campo" id="contenido" name="contenido" rows="10" cols="50" required></textarea><br><br>
        <button class="boton_guardar" type="submit">Subir</button>
        <?php if (!empty($mensaje)) echo $mensaje; ?>
    </form>

    <?php
require __DIR__.'/footer.php';  
?>