<?php
session_start();

if (isset($_POST["clave"])) {
    $clave = htmlspecialchars($_POST["clave"]);

    // Comprobar si la clave existe en el archivo
    $archivo = "usuarios.txt";
    $usuarios = file($archivo, FILE_IGNORE_NEW_LINES);
    $claveValida = false;

    foreach ($usuarios as $usuario) {
        if (strpos($usuario, "Clave: $clave") !== false) {
            $claveValida = true;
            $_SESSION["usuario"] = $clave;
            header("Location: publicar.php");
            exit;
        }
    }

    if (!$claveValida) {
        // Mostrar el alert si la clave es incorrecta
        echo "<script>alert('La clave no existe.'); window.history.back();</script>";
        exit;
    }
}
?>
