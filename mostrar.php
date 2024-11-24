<?php
$archivoPublicaciones = "publicaciones.txt";
$archivoUsuarios = "usuarios.txt";

if (file_exists($archivoPublicaciones) && file_exists($archivoUsuarios)) {
    $contenido = file($archivoPublicaciones, FILE_IGNORE_NEW_LINES);
    $usuarios = file($archivoUsuarios, FILE_IGNORE_NEW_LINES);

    if (!empty($contenido)) {
        echo "<h2>Publicaciones:</h2>";

        // Crear un mapa de usuarios para una búsqueda más eficiente
        $mapaUsuarios = [];
        foreach ($usuarios as $usuario) {
            // Extraer clave y nombre
            preg_match('/Clave: (\d+)/', $usuario, $claveMatch);
            preg_match('/Nombre: ([^|]+)/', $usuario, $nombreMatch);

            if (isset($claveMatch[1]) && isset($nombreMatch[1])) {
                $clave = trim($claveMatch[1]);
                $nombre = trim($nombreMatch[1]);
                $mapaUsuarios[$clave] = $nombre; // Guardar en el mapa
            }
        }

        // Agrupar publicaciones por usuario y contenido
        $publicacionesAgrupadas = [];
        foreach ($contenido as $linea) {
            // Dividir la línea de la publicación por el delimitador "|"
            $datos = explode(" | ", $linea);

            // Obtener la clave del usuario
            preg_match('/Nombre: (\d+)/', $datos[0] ?? "", $matches);
            $claveUsuario = $matches[1] ?? "";

            // Extraer contenido de texto o imagen
            $texto = "";
            $imagen = "";
            foreach ($datos as $dato) {
                if (strpos($dato, "Texto:") === 0) {
                    $texto = str_replace("Texto: ", "", $dato);
                } elseif (strpos($dato, "Imagen:") === 0) {
                    $imagen = str_replace("Imagen: ", "", $dato);
                }
            }

            // Agrupar por clave de usuario
            $publicacionesAgrupadas[] = [
                'claveUsuario' => $claveUsuario,
                'texto' => $texto,
                'imagen' => $imagen,
            ];
        }

        // Mostrar publicaciones agrupadas
        foreach ($publicacionesAgrupadas as $publicacion) {
            $claveUsuario = $publicacion['claveUsuario'];
            $nombreUsuario = $mapaUsuarios[$claveUsuario] ?? "Desconocido";
            $texto = $publicacion['texto'];
            $imagen = $publicacion['imagen'];

            echo "<div class='publicacion'>";
            echo "<p class='nombreUsuario'><strong>$nombreUsuario</strong> dijo:</p>";
            if (!empty($texto)) {
                echo "<p class='textoPublicacion'>$texto</p>";
            }
            if (!empty($imagen)) {
                echo "<img src='$imagen' alt='Imagen de la publicación' class='imagenPublicacion'><br><br>";
            }
            echo "</div><hr>"; // Línea separadora
        }
    } else {
        echo "<p>No hay publicaciones.</p>";
    }
} else {
    echo "<p>No se puede leer los archivos.</p>";
}

// Agregar botón de cerrar sesión
echo '<form action="logout.php" method="post" class="cerrarSesionForm">';
echo '<input type="submit" value="Cerrar sesión" class="cerrarSesionBtn">';
echo '</form>';
?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publicaciones</title>
    <style>
        /* Estilos generales */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h2 {
            color: #333;
            margin-top: 20px;
        }

        .publicacion {
            background-color: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 60%;
            margin-bottom: 20px;
        }

        .nombreUsuario {
            font-size: 18px;
            color: #2c3e50;
        }

        .textoPublicacion {
            font-size: 16px;
            color: #34495e;
            margin: 10px 0;
        }

        .imagenPublicacion {
            max-width: 100%;
            border-radius: 8px;
        }

        hr {
            border: 1px solid #eee;
            width: 100%;
            margin-top: 20px;
        }

        /* Estilo para el botón de cerrar sesión */
        .cerrarSesionForm {
            margin-top: 30px;
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
    <!-- Aquí termina el contenido PHP, los estilos CSS van arriba -->
</body>
</html>
