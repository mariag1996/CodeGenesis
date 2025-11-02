<?php

// Conexión a la base de datos
require_once __DIR__ . '/db.php';

$conexion = getDB(); 



// Obtener datos del formulario
$usuario = isset($_POST['usuario']) ? $_POST['usuario'] : null;
$contraseña = isset($_POST['contraseña']) ? $_POST['contraseña'] : null;

session_start();
$_SESSION['LAST_ACTIVITY'] = time(); // tiempo actual del login


//  Verificación de usuario director
if ($usuario === 'code2025' && $contraseña === 'codegenesis') {
    $_SESSION['usuario'] = $usuario;
    $_SESSION['rol'] = 'director';
    header("Location: adscripto_registro.php");
    exit();
}

function login($conexion, $tabla, $usuario, $contraseña) {
    $query = "SELECT * FROM $tabla WHERE usuario = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();
        if (password_verify($contraseña, $fila['contraseña'])) {
            return $fila;
        }
    }

    return null;
}

// Buscar en tabla alumnos
if ($fila = login($conexion, 'alumno', $usuario, $contraseña)) {
    $_SESSION['usuario'] = $usuario;
    $_SESSION['rol'] = 'alumno';
    $_SESSION['grupo'] = $fila['nombre_grupo'];
    header("Location: alumnos.php");
    exit();
}

// Buscar en tabla docente
if ($fila = login($conexion, 'docente', $usuario, $contraseña)) {
    $_SESSION['usuario'] = $usuario;
    $_SESSION['docente'] = $usuario;
    $_SESSION['rol'] = 'docente';
    header("Location: docentes.php");
    exit();
}

// Buscar en tabla adscripto
if ($fila = login($conexion, 'adscripto', $usuario, $contraseña)) {
    $_SESSION['usuario'] = $usuario;
    $_SESSION['adscripto'] = $usuario;
    $_SESSION['rol'] = 'adscripto';
    header("Location: registro_main.php");
    exit();
}

// Si no se encontró en ninguna tabla
header("Location: logeo.php?error=1");
exit();

$conexion->close();
?>
