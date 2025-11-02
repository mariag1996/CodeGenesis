<?php
require __DIR__ . '/header.php';
require __DIR__.'/procesar.php'; 

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

$pdo = new PDO($dsn, $user, $pass, $options);

// Consulta para obtener noticias con sus imágenes
$stmt = $pdo->query("SELECT noticias.id, titulo, contenido, fecha_publicacion, imagenes.url AS imagen_url FROM noticias LEFT JOIN imagenes ON noticias.imagen_id = imagenes.id ORDER BY fecha_publicacion DESC");

$noticias = $stmt->fetchAll();
?>

<div class="contenedor_titulo">
    <h1 class="titulo">Noticias Publicadas</h1>
</div>

<div class="contenedor__boton_listado">
        <p><a class="boton_listado" href="noticias_registro.php">Volver a registro de noticias</a></p>
    </div>


<section class="seccion_noticias">
    <div class="contenedor_noticias">
    <?php if (empty($noticias)): ?>
        <p>No hay noticias disponibles.</p>
    <?php else: ?>
        <?php foreach ($noticias as $noticia): ?>
            <article class="noticia">
                <div class="cont__contenedor__fecha__noticia">
                <div class="contenedor__fecha__noticia">
                 <p class="fecha__noticia">Publicado el <?= date('d M Y, H:i', strtotime($noticia['fecha_publicacion'])) ?></p>
                </div>
                 </div>
                <h2 class="noticia__titulo"><?= htmlspecialchars($noticia['titulo']) ?></h2>
             
                <div class="contenedor__noticia__imagen">
                <?php if ($noticia['imagen_url']): ?>
                    <img class="noticia__imagen" src="<?= htmlspecialchars($noticia['imagen_url']) ?>" alt="Imagen" style="max-width:400px;">
                
                    <?php endif; ?>
                    </div>
                <p class="noticia__contenido">
                    <?= nl2br(htmlspecialchars($noticia['contenido'])) ?>
                </p>
               <div class="botones_noticias">
                <a class="boton__noticia__editar" href="noticias_edit.php?id=<?= $noticia['id'] ?>">Editar</a>
                <form action="noticias_delete.php" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas borrar esta noticia?');">
                <input type="hidden" name="id" value="<?= $noticia['id'] ?>">
                <button type="submit" class="boton__noticia__borrar">Borrar</button>
                </form>
                </div>
            </article>
            <hr>
        <?php endforeach; ?>
    <?php endif; ?>
            </div>
        </section>

        <?php
require __DIR__.'/footer.php';  
?>