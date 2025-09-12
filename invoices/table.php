<?php

include_once('../conexion/conexion.php');

// Mostrar errores para poder depurar
error_reporting(E_ALL);
ini_set('display_errors', 1);


// Crear conexión
$objeto = new Conexion();
$conexion = $objeto->conectar();

$swalMessage = ''; // ← Aquí almacenamos el mensaje JS a mostrar

// Consulta todos los préstamos
$consulta = "SELECT * FROM factura";
$resultado = $conexion->query($consulta);

$facturas = [];
if ($resultado && $resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $facturas[] = $row;
    }
}

// Update facturas

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (
        isset($_POST['fecha']) &&
        isset($_POST['motivo']) &&
        isset($_POST['comprobante']) &&
        isset($_POST['id_factura']) // importante
    ) {
        $fecha = $_POST['fecha'];
        $motivo = $_POST['motivo'];
        $comprobante = $_POST['comprobante'];
        $id_factura = $_POST['id_factura'];

        // Asegúrate de validar/sanitizar aquí si es necesario

        $stmt = $conexion->prepare("UPDATE factura SET fecha=?, motivo=?, comprobante=? WHERE id_factura=?");
        $stmt->bind_param("sssi", $fecha, $motivo, $comprobante, $id_factura);


        if ($stmt->execute()) {
            $swalMessage = "
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Éxito',
                text: 'Datos actualizados correctamente',
                icon: 'success',
            }).then(() => {
                // Espera 5 segundos más antes de redirigir
                setTimeout(() => {
                    window.location.href = 'table.php';
                }, 2000); 
            });
        });
    </script>";
        } else {
            $swalMessage = "
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudo actualizar los datos',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                });
            </script>";
        }

        $stmt->close();
        echo $swalMessage;
    } else {
        // Manejar el caso en que faltan datos
        $swalMessage = "
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudo actualizar los datos',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                });
            </script>";
    }
}



// Delete facturas

if (isset($_GET['delete'])) {
    $idToDelete = intval($_GET['delete']);

    $stmt = $conexion->prepare("DELETE FROM factura WHERE id_factura = ?");
    $stmt->bind_param("i", $idToDelete);

    if ($stmt->execute()) {
        echo json_encode(["status" => "ok"]);
    } else {
        echo json_encode(["status" => "error"]);
    }

    $stmt->close();
    exit();
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Listado de Préstamos</title>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <!-- Tailwind y Flowbite -->
    <link href="./src/output.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
</head>

<body class="bg-gray-100">

    <a href="./index.php" class=""><svg class="w-10 h-10 bg-red-100 rounded-full border-4  m-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12l4-4m-4 4 4 4" />
        </svg>
    </a>

    <div class="relative overflow-x-auto rounded-2xl shadow-2xl shadow-blue-500/50 m-4 bg-white">

        <table class="w-full text-sm text-left text-gray-700">
            <thead class="text-xs uppercase bg-gray-50">
                <tr>
                    <th class="px-6 py-3">Fecha</th>
                    <th class="px-6 py-3">Comprobante</th>
                    <th class="px-6 py-3">Motivo</th>
                    <th class="px-6 py-3">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($facturas)): ?>
                    <?php foreach ($facturas as $factura): ?>
                        <tr class="bg-white border-b hover:bg-gray-50">

                            <td class="px-6 py-4 font-semibold"><?php echo htmlspecialchars($factura['fecha']); ?></td>
                            <td class="px-6 py-4">
                                <?php
                                $rutaImagen = $factura['comprobante'];
                                if (!empty($rutaImagen) && file_exists(__DIR__ . '/' . $rutaImagen)): ?>
                                    <a href="<?= htmlspecialchars($rutaImagen) ?>" target="_blank">
                                        <img src="<?= htmlspecialchars($rutaImagen) ?>" alt="Comprobante" class="w-20 h-20 object-cover rounded">
                                    </a>
                                <?php else: ?>
                                    No hay imagen
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-4"><?php echo htmlspecialchars($factura['motivo']); ?></td>
                            <td class="px-6 py-4 flex space-x-0.5">
                                <a href="#" class="flex justify-start items-start"
                                    data-modal-target="editUserModal"
                                    data-modal-show="editUserModal"
                                    class="text-blue-600 hover:underline"
                                    data-id="<?php echo $factura['id_factura']; ?>"
                                    data-fecha="<?php echo isset($factura['fecha']) ? $factura['fecha'] : ''; ?>"
                                    data-motivo="<?php echo isset($factura['motivo']) ? $factura['motivo'] : ''; ?>"
                                    data-comprobante="<?php echo isset($factura['comprobante']) ? $factura['comprobante'] : ''; ?>">
                                    <svg class="w-6 h-6 text-green-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="square" stroke-linejoin="round" stroke-width="2" d="M7 19H5a1 1 0 0 1-1-1v-1a3 3 0 0 1 3-3h1m4-6a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm7.441 1.559a1.907 1.907 0 0 1 0 2.698l-6.069 6.069L10 19l.674-3.372 6.07-6.07a1.907 1.907 0 0 1 2.697 0Z" />
                                    </svg>

                                </a>
                                <a href="#"
                                    class="text-red-600 hover:underline ml-2 btn-delete"
                                    data-id="<?php echo $factura['id_factura']; ?>">
                                    <svg class="w-6 h-6 text-red-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No hay préstamos registrados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div id="pagination" class="flex justify-center mt-4 mb-4"></div>
    </div>

    <!-- Modal Visual (sin funcionalidad PHP) -->
    <div id="editUserModal" tabindex="-1" aria-hidden="true" class="fixed inset-0 z-50 hidden items-center justify-center p-4 overflow-auto">
        <div class="relative max-h-full">
            <form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>" class="w-full p-6 bg-white rounded-lg shadow-xl shadow-red-500/50">
                <input type="hidden" name="id_factura" id="id_factura">

                <!-- Fecha -->
                <div class="relative z-0 w-full mb-5 group">
                    <input type="date" name="fecha" id="fecha" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer" required />
                    <label for="fecha" class="absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6 peer-focus:text-blue-600">Fecha</label>
                </div>

                <!-- Comprobante -->
                <div class="relative z-0 w-full mb-5 group">
                    <input type="file" name="comprobante" id="comprobante" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer" required />
                    <label for="comprobante" class="absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6 peer-focus:text-blue-600">Comprobante</label>
                </div>

                <div class="mb-4">
                    <label class="text-sm text-gray-500">Comprobante actual:</label>
                    <img id="currentComprobante" src="" alt="Comprobante" class="w-32 h-32 object-cover rounded mt-2">
                </div>
                <!-- Motivo factura -->
                <div class="relative z-0 w-full mb-5 group">
                    <textarea name="motivo" id="motivo" rows="4" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none resize-none focus:outline-none focus:ring-0 focus:border-blue-600 peer" required></textarea>
                    <label for="motivo" class="absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6 peer-focus:text-blue-600">Motivo</label>
                </div>

                <!-- Botón -->
                <button type="submit" class="cursor-pointer w-full bg-blue-700 hover:bg-blue-800 text-white font-medium rounded-lg text-sm px-5 py-2.5 text-center">Actualizar Datos</button>
            </form>
        </div>
    </div>

    <?php
    // Muestra el mensaje si existe
    if (!empty($swalMessage)) {
        echo $swalMessage;
    }
    ?>

    <!-- Pagination -->

    <script src="../js/pagination.js"></script>

    <!-- Delete alert and button -->

    <script src="../js/delete.js"></script>

    <!-- Modal Edit -->

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editButtons = document.querySelectorAll('[data-modal-target="editUserModal"]');

            editButtons.forEach(button => {
                button.addEventListener('click', () => {
                    document.getElementById('id_factura').value = button.getAttribute('data-id');
                    document.getElementById('fecha').value = button.getAttribute('data-fecha');
                    document.getElementById('motivo').value = button.getAttribute('data-motivo');

                    // Ajustar la ruta relativa para mostrar la imagen correctamente
                    const comprobantePath = 'crud/invoices/' + button.getAttribute('data-comprobante');
                    document.getElementById('currentComprobante').src = comprobantePath;

                    // El input file no se puede rellenar por seguridad, se deja vacío
                    document.getElementById('comprobante').value = '';
                });
            });
        });
    </script>



</body>

</html>