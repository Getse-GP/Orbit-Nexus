<?php
session_start();

$archivoPublicaciones = "publicaciones.txt";
$archivoComentarios = "comentarios.txt";
$archivoLikes = "likes.txt";

// Verificar si el usuario está logueado
if (!isset($_SESSION["usuario"])) {
    echo "<p>Debes iniciar sesión primero.</p>";
    exit;
}

// Procesar likes
if (isset($_POST['like'])) {
    $publicacionId = htmlspecialchars($_POST['publicacion_id']);
    $usuario = $_SESSION["usuario"];

    // Registrar el like en el archivo
    $registroLike = "Publicacion: $publicacionId | Usuario: $usuario\n";
    file_put_contents($archivoLikes, $registroLike, FILE_APPEND);

    header("Location: publicar.php");
    exit;
}

// Procesar comentarios
if (isset($_POST['comentario'])) {
    $publicacionId = htmlspecialchars($_POST['publicacion_id']);
    $comentario = htmlspecialchars($_POST['comentario']);
    $usuario = $_SESSION["usuario"];

    // Registrar el comentario en el archivo
    $registroComentario = "Publicacion: $publicacionId | Usuario: $usuario | Comentario: $comentario\n";
    file_put_contents($archivoComentarios, $registroComentario, FILE_APPEND);

    header("Location: publicar.php");
    exit;
}

// Procesar nuevas publicaciones
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['texto'])) {
    $texto = htmlspecialchars($_POST['texto']);
    $imagen = "";

    // Procesar imagen si se subió
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $rutaDestino = "uploads/" . basename($_FILES['imagen']['name']);
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino)) {
            $imagen = $rutaDestino;
        }
    }

    // Obtener el nombre del usuario actual
    $usuario = $_SESSION["usuario"];
    $registro = "Nombre: $usuario | Texto: $texto";
    if (!empty($imagen)) {
        $registro .= " | Imagen: $imagen";
    }
    $registro .= "\n";

    // Guardar la publicación en el archivo
    file_put_contents($archivoPublicaciones, $registro, FILE_APPEND);

    // Redirigir para evitar reenvío del formulario
    header("Location: publicar.php");
    exit;
}

// Leer publicaciones
$publicaciones = [];
if (file_exists($archivoPublicaciones)) {
    $contenido = file($archivoPublicaciones, FILE_IGNORE_NEW_LINES);
    foreach ($contenido as $id => $linea) {
        $datos = explode(" | ", $linea);
        $nombre = str_replace("Nombre: ", "", $datos[0] ?? "");
        $texto = "";
        $imagen = "";
        $video = "";

        foreach ($datos as $dato) {
            if (strpos($dato, "Texto:") === 0) {
                $texto = str_replace("Texto: ", "", $dato);
            } elseif (strpos($dato, "Imagen:") === 0) {
                $imagen = str_replace("Imagen: ", "", $dato);
            } elseif (strpos($dato, "Video:") === 0) {
                $video = str_replace("Video: ", "", $dato);
            }
        }

        $publicaciones[] = [
            'id' => $id,
            'nombre' => $nombre,
            'texto' => $texto,
            'imagen' => $imagen,
            'video' => $video,
        ];
    }
}
?>


<?php
// session_start();

$archivoPublicaciones = "publicaciones.txt";
$archivoUsuarios = "usuarios.txt";

// Verificar si el usuario está logueado
if (!isset($_SESSION["usuario"])) {
    echo "<p>Debes iniciar sesión primero.</p>";
    exit;
}

// Leer usuarios y mapear claves a nombres
$mapaUsuarios = [];
if (file_exists($archivoUsuarios)) {
    $contenidoUsuarios = file($archivoUsuarios, FILE_IGNORE_NEW_LINES);
    foreach ($contenidoUsuarios as $usuario) {
        preg_match('/Clave: (\d+)/', $usuario, $claveMatch);
        preg_match('/Nombre: ([^|]+)/', $usuario, $nombreMatch);
        if (isset($claveMatch[1]) && isset($nombreMatch[1])) {
            $clave = trim($claveMatch[1]);
            $nombre = trim($nombreMatch[1]);
            $mapaUsuarios[$clave] = $nombre;
        }
    }
}

// Leer publicaciones
$publicaciones = [];
if (file_exists($archivoPublicaciones)) {
    $contenidoPublicaciones = file($archivoPublicaciones, FILE_IGNORE_NEW_LINES);
    foreach ($contenidoPublicaciones as $id => $linea) {
        $datos = explode(" | ", $linea);
        $claveUsuario = str_replace("Nombre: ", "", $datos[0] ?? "");
        $texto = $imagen = "";
        foreach ($datos as $dato) {
            if (strpos($dato, "Texto:") === 0) {
                $texto = str_replace("Texto: ", "", $dato);
            } elseif (strpos($dato, "Imagen:") === 0) {
                $imagen = str_replace("Imagen: ", "", $dato);
            }
        }
        $publicaciones[] = [
            'id' => $id,
            'claveUsuario' => $claveUsuario,
            'texto' => $texto,
            'imagen' => $imagen,
        ];
    }
}

// Procesar búsqueda
$resultadosBusqueda = [];
if (isset($_POST["buscar"])) {
    $criterio = htmlspecialchars($_POST["criterio"]);
    foreach ($publicaciones as $publicacion) {
        $claveUsuario = $publicacion['claveUsuario'];
        $nombreUsuario = $mapaUsuarios[$claveUsuario] ?? "Desconocido";

        // Buscar por clave o nombre
        if (strpos($nombreUsuario, $criterio) !== false || strpos($claveUsuario, $criterio) !== false) {
            $resultadosBusqueda[] = $publicacion;
        }
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar y Publicar</title>
    <style>
        /* Estilos generales */
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #c7ebff;
        }
        textarea {
            width: 100%;
            padding: -5px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            min-height: 100px;
        }

        
        /* Header estilizado */
        header {
            background-color: #3facd9;
    color: white;
    padding: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    width: 95%;
    position: fixed;
    top: 0;
    left: 3%;
    z-index: 1000;
    }

        header h1 {
            margin: 0;
            font-size: 24px;
        }
        h2 {
            color: #333;
            margin: 20px 0;
        }
        .buscar-container {
            margin-top: 10px;
        }

        .buscar-container form {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .buscar-container input[type="text"] {
            padding: 10px;
            width: 300px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        .buscar-container button {
            background-color: #2d9dcc;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .buscar-container button:hover {
            background-color: #2179a4;
        }

        /* Formulario de publicaciones */
        .form-container {
            background-color: white;
            padding: 40px;
            margin: 20px auto;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 50%;
        }

        .form-container h2 {
            color: #333;
        }

        .form-container textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            min-height: 100px;
        }

        .form-container input[type="file"],
        .form-container input[type="submit"] {
            margin-bottom: 10px;
            font-size: 16px;
        }


        input[type="file"] {
            margin-bottom: 10px;
            font-size: 16px;
        }
 
        .form-container input[type="submit"] {
            background-color: #3facd9;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 100%;
        }

        .form-container input[type="submit"]:hover {
            background-color: #2d9dcc;
        }

        .publicacion {
            background-color: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 50%;
            margin-bottom: 20px;
        }

        .publicacion h3 {
            margin: 0;
            color: #2c3e50;
        }

        .textoPublicacion {
            margin: 10px 0;
            color: #34495e;
        }

        .imagenPublicacion {
            max-width: 100%;
            border-radius: 8px;
        }

        .cerrarSesionForm {
    margin-left: 20px;
}

.cerrarSesionBtn {
    background-color: #e74c3c;
    color: white;
    border: none;
    padding: 10px 20px;
    font-size: 16px;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.cerrarSesionBtn:hover {
    background-color: #c0392b;
}

    </style>
</head>
<body>
    <!-- Header con el formulario de búsqueda -->
    <header>
    <h1>Orbit Nexus</h1>
    <div class="buscar-container">
        <form method="post" action="">
            <input type="text" name="criterio" placeholder="Buscar por nombre o clave" required>
            <button type="submit" name="buscar">Buscar</button>
        </form>
    </div>
    <form action="logout.php" method="post" class="cerrarSesionForm">
        <button type="submit" class="cerrarSesionBtn">Cerrar sesión</button>
    </form>
</header>

    <!-- Formulario para nuevas publicaciones -->
    <div class="form-container">
        <h2>¿En qué estás pensando?</h2>
        <form action="publicar.php" method="post" enctype="multipart/form-data">
            <textarea name="texto" placeholder="Cuéntanos" required></textarea><br>
            <input type="file" name="imagen"><br>
            <input type="submit" value="Publicar">
        </form>
    </div>

    <?php if (isset($_POST["buscar"])): ?>
        <div class="form-container">
            <h2>Resultados de la búsqueda:</h2>
            <?php if (empty($resultadosBusqueda)): ?>
                <p>No se encontraron publicaciones para el usuario.</p>
            <?php else: ?>
                <?php foreach ($resultadosBusqueda as $publicacion): ?>
                    <div class="publicacion">
                        <?php
                        $claveUsuario = $publicacion['claveUsuario'];
                        $nombreUsuario = $mapaUsuarios[$claveUsuario] ?? "Desconocido";
                        ?>
                        <p><strong><?= $nombreUsuario ?></strong> dijo:</p>
                        <p><?= htmlspecialchars($publicacion['texto']) ?></p>
                        <?php if (!empty($publicacion['imagen'])): ?>
                            <img src="<?= htmlspecialchars($publicacion['imagen']) ?>" alt="Imagen" style="max-width:100%;">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</body>
</html>
    <?php
$archivoPublicaciones = "publicaciones.txt";
$archivoUsuarios = "usuarios.txt";
$archivoLikes = "likes.txt";
$archivoComentarios = "comentarios.txt";

if (file_exists($archivoPublicaciones) && file_exists($archivoUsuarios)) {
    $contenidoPublicaciones = file($archivoPublicaciones, FILE_IGNORE_NEW_LINES);
    $contenidoUsuarios = file($archivoUsuarios, FILE_IGNORE_NEW_LINES);

    if (!empty($contenidoPublicaciones)) {
        echo "<h2>Publicaciones:</h2>";

        // Crear un mapa de usuarios para una búsqueda más eficiente
        $mapaUsuarios = [];
        foreach ($contenidoUsuarios as $usuario) {
            // Extraer clave y nombre
            preg_match('/Clave: (\d+)/', $usuario, $claveMatch);
            preg_match('/Nombre: ([^|]+)/', $usuario, $nombreMatch);

            if (isset($claveMatch[1]) && isset($nombreMatch[1])) {
                $clave = trim($claveMatch[1]);
                $nombre = trim($nombreMatch[1]);
                $mapaUsuarios[$clave] = $nombre; // Guardar en el mapa
            }
        }

        // Mostrar las publicaciones
        foreach ($contenidoPublicaciones as $linea) {
            // Dividir la línea de la publicación por el delimitador "|"
            $datos = explode(" | ", $linea);

            // Obtener la clave del usuario
            preg_match('/Nombre: (\d+)/', $datos[0] ?? "", $claveMatch);
            $claveUsuario = $claveMatch[1] ?? "";
            $texto = "";
            $imagen = "";

            // Extraer contenido de texto, imagen y video
            foreach ($datos as $dato) {
                if (strpos($dato, "Texto:") === 0) {
                    $texto = str_replace("Texto: ", "", $dato);
                } elseif (strpos($dato, "Imagen:") === 0) {
                    $imagen = str_replace("Imagen: ", "", $dato);
                }
            }

            $nombreUsuario = $mapaUsuarios[$claveUsuario] ?? "Desconocido";

            echo "<div class='publicacion'>";
            echo "<p class='nombreUsuario'><strong>$nombreUsuario</strong> dijo:</p>";

            // Mostrar texto, imagen si están presentes
            if (!empty($texto)) {
                echo "<p class='textoPublicacion'>$texto</p>";
            }
            if (!empty($imagen)) {
                echo "<img src='$imagen' alt='Imagen de la publicación' class='imagenPublicacion'><br><br>";
            }

            // Botón de Like
            echo '<form method="post">
                    <input type="hidden" name="publicacion_id" value="' . $claveUsuario . '">
                    <button type="submit" name="like">👍 Me gusta</button>
                  </form>';

            // Mostrar contador de likes
            $likes = 0;
            if (file_exists($archivoLikes)) {
                $contenidoLikes = file($archivoLikes, FILE_IGNORE_NEW_LINES);
                foreach ($contenidoLikes as $linea) {
                    if (strpos($linea, "Publicacion: " . $claveUsuario) !== false) {
                        $likes++;
                    }
                }
            }
            echo "<p>$likes Me gusta</p>";

            // Formulario de comentarios
            echo '<form method="post">
                    <input type="hidden" name="publicacion_id" value="' . $claveUsuario . '">
                    <textarea name="comentario" placeholder="Escribe un comentario..." required></textarea>
                    <button type="submit">Comentar</button>
                  </form>';

            // Mostrar comentarios
            echo "<div class='comentarios'>";
            echo "<h4>Comentarios:</h4>";

            if (file_exists($archivoComentarios)) {
                $contenidoComentarios = file($archivoComentarios, FILE_IGNORE_NEW_LINES);
                foreach ($contenidoComentarios as $linea) {
                    if (strpos($linea, "Publicacion: " . $claveUsuario) !== false) {
                        // Extraer la clave del usuario y el comentario
                        preg_match('/Usuario: (\d+)/', $linea, $usuarioMatch);
                        preg_match('/Comentario: (.+)/', $linea, $comentarioMatch);

                        $claveComentario = $usuarioMatch[1] ?? "";
                        $comentario = $comentarioMatch[1] ?? "";

                        // Obtener el nombre del usuario del mapa
                        $nombreComentario = $mapaUsuarios[$claveComentario] ?? "Desconocido";

                        echo "<p><strong>$nombreComentario:</strong> $comentario</p>";
                    }
                }
            }
            echo "</div>"; // Fin de los comentarios
            echo "</div><hr>"; // Línea separadora
        }
    } else {
        echo "<p>No hay publicaciones.</p>";
    }
} else {
    echo "<p>No se puede leer los archivos.</p>";
}

// Agregar botón de cerrar sesión
// echo '<form action="logout.php" method="post" class="cerrarSesionForm">';
// echo '<input type="submit" value="Cerrar sesión" class="cerrarSesionBtn">';
// echo '</form>';
?>






</body>
</html>
