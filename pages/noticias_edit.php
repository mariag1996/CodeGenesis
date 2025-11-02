<?php
require __DIR__ . '/header.php';
require __DIR__.'/procesar.php';

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

$pdo = new PDO($dsn, $user, $pass, $options);

// Verificamos que venga un ID por GET
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    exit('ID de noticia inválido.');
}

$id = (int)$_GET['id'];

// Obtener la noticia
$stmt = $pdo->prepare('
    SELECT noticias.*, imagenes.url AS imagen_url 
    FROM noticias 
    LEFT JOIN imagenes ON noticias.imagen_id = imagenes.id 
    WHERE noticias.id = ?
');
$stmt->execute([$id]);
$noticia = $stmt->fetch();

if (!$noticia) {
    exit('Noticia no encontrada.');
}
?>

<div class="contenedor_titulo">
    <h1 class="titulo">Editar Noticia</h1>
</div>

<div class="contenedor__boton_listado">
    <p><a class="boton_listado" href="noticias_ver.php">Ver noticias registradas</a></p>
</div>

<main class="contenedor_formulario">
    <form class="formulario" action="noticias_update.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= htmlspecialchars($noticia['id']) ?>">

        <label class="titulo-noticia guardar_datos" for="titulo">Título:</label>
        <input class="campo" type="text" name="titulo" id="titulo" value="<?= htmlspecialchars($noticia['titulo']) ?>" required>
        <br>

        <label class="guardar_datos" for="contenido">Contenido:</label><br>
        <textarea class="campo casilla" id="contenido" name="contenido" rows="10" cols="50" required><?= htmlspecialchars($noticia['contenido']) ?></textarea><br><br>

        <label class="guardar_datos">Imagen actual:</label><br>
        <?php if ($noticia['imagen_url']): ?>
            <img src="<?= htmlspecialchars($noticia['imagen_url']) ?>" alt="Imagen actual" style="max-width:200px;"><br>
        <?php else: ?>
            <p>No hay imagen.</p>
        <?php endif; ?>

        <label class="guardar_datos">Cambiar imagen (opcional):</label>
        <input class="subir-fotos" type="file" name="miArchivo"><br><br>

        <button class="boton_guardar" type="submit">Guardar Cambios</button>
    </form>

    <?php
require __DIR__.'/footer.php';  
?>