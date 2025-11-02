<?php
//Incluye el archivo db.php, que contiene la función getDB() para conectarse a la base de datos.
require_once __DIR__ . '/db.php';

//Si el script no fue accedido mediante POST (por ejemplo, si alguien lo abre directamente en el navegador), 
// redirige al listado de alumnos y termina la ejecución.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: adscripto_registro.php");
    exit;
}

//Se obtienen los datos enviados por el formulario.
//trim() elimina espacios en blanco.
$usuario = (int)($_POST['usuario'] ?? 0);
$nombre = trim($_POST['nombre'] ?? '');
$apellido  = trim($_POST['apellido'] ?? '');

//Verifica que el usuario sea válido (> 0).
//Verifica que los campos no estén vacíos
if ($usuario <= 0 || $nombre === ' ' ||  $apellido === ' ') {
    die("Datos inválidos.");
}

try {
    //Se prepara una consulta SQL segura para actualizar los datos del alumno.
    //"sssi" indica los tipos de datos: string, string, string, integer.
    $stmt = getDB()->prepare("UPDATE adscripto SET nombre = ?, apellido = ? WHERE usuario = ?");
    $stmt->bind_param("ssi", $nombre, $apellido, $usuario);
   //Se ejecuta la consulta y se cierra el statement.
    $stmt->execute();
    $stmt->close();

    //Después de actualizar, redirige al listado de alumnos.
    header("Location: adscripto_registro.php");
    exit;
    //Si ocurre un error en la base de datos, se muestra un mensaje seguro con htmlspecialchars() para evitar inyecciones en la salida.
} catch (mysqli_sql_exception $e) {
    echo "Error al actualizar: " . htmlspecialchars($e->getMessage());
}
