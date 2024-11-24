<?php
if (isset($_POST["nombre"]) && isset($_POST["edad"]) && isset($_POST["sexo"]) && isset($_POST["clave"])) {
    $nombre = htmlspecialchars($_POST["nombre"]);
    $edad = htmlspecialchars($_POST["edad"]);
    $sexo = htmlspecialchars($_POST["sexo"]);
    $clave = htmlspecialchars($_POST["clave"]);

    // Comprobar si la clave ya existe
    $archivo = "usuarios.txt";
    $usuarios = file($archivo, FILE_IGNORE_NEW_LINES);
    foreach ($usuarios as $usuario) {
        if (strpos($usuario, "Clave: $clave") !== false) {
            echo "<script>alert('Error: La clave ya existe.'); window.location.href='registro.html';</script>";
            exit;
        }
    }

    // Guardar usuario en archivo
    $datosAGuardar = "Nombre: $nombre | Edad: $edad | Sexo: $sexo | Clave: $clave\n";
    file_put_contents($archivo, $datosAGuardar, FILE_APPEND);

    // Redirigir a login.html después de un registro exitoso
    header("Location: login.html");
    exit; // Asegura que el script termine después de la redirección

} else {
    echo "<p>Error: Faltan datos.</p>";
}
?>
