<?php
include_once('../../conexion/conexion.php');

// Mostrar errores para depurar
error_reporting(E_ALL);
ini_set('display_errors', 1);

$swalMessage = '';

// Crear conexión
$objeto = new Conexion();
$conexion = $objeto->conectar();


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['fecha'], $_POST['motivo'], $_FILES['comprobante'])) {

        $fecha = $_POST['fecha'];
        $motivo = $_POST['motivo'];
        $comprobantePath = '';

        // Manejo de archivo
        if ($_FILES['comprobante']['error'] === UPLOAD_ERR_OK) {
            $uploadsDir = "../../invoices/uploads/"; // carpeta junto a table.php

            // Crear carpeta uploads si no existe
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0777, true);
            }

            // Buscar cuántos archivos hay en la carpeta para generar el consecutivo
            $archivos = glob($uploadsDir . "comprobante-*.{jpg,jpeg,png,gif,pdf}", GLOB_BRACE);
            $numero = count($archivos) + 1;

            // Detectar la extensión original
            $extension = pathinfo($_FILES['comprobante']['name'], PATHINFO_EXTENSION);

            // Nuevo nombre
            $nombreArchivo = "comprobante-" . $numero . "." . $extension;

            $rutaDestino = $uploadsDir . $nombreArchivo;

            if (move_uploaded_file($_FILES['comprobante']['tmp_name'], $rutaDestino)) {
                // Guardar ruta relativa desde table.php
                $comprobantePath = "uploads/" . $nombreArchivo;
            } else {
                echo "<script>alert('Error al mover el archivo.');</script>";
                exit();
            }
        } else {
            echo "<script>alert('Error al subir el archivo.');</script>";
            exit();
        }


        // Insert en la base de datos
        $stmt = $conexion->prepare("INSERT INTO factura (fecha, motivo, comprobante) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $fecha, $motivo, $comprobantePath);

        if ($stmt->execute()) {
            $swalMessage = "
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Éxito',
                        text: 'Factura registrada correctamente',
                        icon: 'success',
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        window.location.href = '../../invoices/table.php';
                    });
                });
            </script>";
        } else {
            $swalMessage = "
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudo registrar la factura',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                });
            </script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Faltan datos en el formulario.');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Factura</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="./src/output.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
</head>

<body class="bg-gray-100">

    <a href="../../invoices/index.php" class="m-4 inline-block">
        <svg class="w-10 h-10 bg-red-100 rounded-full border-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12l4-4m-4 4 4 4" />
        </svg>
    </a>

    <div class="flex justify-center items-center h-screen">
        <form enctype="multipart/form-data" method="POST" action="<?= $_SERVER['PHP_SELF'] ?>" class="max-w-sm w-full p-6 bg-white rounded-lg shadow-xl">

            <!-- Fecha -->
            <div class="relative z-0 w-full mb-5 group">
                <input type="date" name="fecha" id="fecha" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer" required />
                <label for="fecha" class="absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6 peer-focus:text-blue-600">Fecha</label>
            </div>

            <!-- Comprobante -->
            <div class="relative z-0 w-full mb-5 group">
                <input type="file" name="comprobante" id="comprobante" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer" accept="image/*" required />
                <label for="comprobante" class="absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6 peer-focus:text-blue-600">Comprobante</label>
            </div>

            <!-- Motivo -->
            <div class="relative z-0 w-full mb-5 group">
                <textarea name="motivo" id="motivo" rows="4" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none resize-none focus:outline-none focus:ring-0 focus:border-blue-600 peer" required></textarea>
                <label for="motivo" class="absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6 peer-focus:text-blue-600">Motivo</label>
            </div>

            <!-- Botón -->
            <button type="submit" class="cursor-pointer w-full bg-blue-700 hover:bg-blue-800 text-white font-medium rounded-lg text-sm px-5 py-2.5 text-center">Registrar Factura</button>
        </form>
    </div>

    <?php if (!empty($swalMessage)) echo $swalMessage; ?>

</body>

</html>