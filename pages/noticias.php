<?php

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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../assets/css/registro.css"><!--Link al estilo-->
     <link  rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"> <!--Imagen de fondo-->
   <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"><!--Link a los iconos de el header-->
     <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=menu"><!--Link a los iconos de el header-->
</head>
<body>
     <header class="header">
  <!-- Contenedor de los íconos-->
       <div class="contenedor__iconos">
    <a href="registro_main.php">  <!-- Dirige el ícono de home a registro_main.php-->
      <span class="material-icons">home</span>
    </a>
    </header>


<div class="contenedor_titulo">
    <h1 class="titulo">Noticias</h1>
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
                            <p class="fecha__noticia">Publicado el <?= date('d/m/Y H:i', strtotime($noticia['fecha_publicacion'])) ?></p>
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
                        </form>
                </article>
                <hr>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
</body>
</html>