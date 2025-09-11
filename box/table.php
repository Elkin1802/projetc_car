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
$consulta = "SELECT * FROM caja";
$resultado = $conexion->query($consulta);

$cajas = [];
if ($resultado && $resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $cajas[] = $row;
    }
}

// Update cajas

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (
        isset($_POST['fecha']) &&
        isset($_POST['tipo_dato']) &&
        isset($_POST['valor']) &&
        isset($_POST['id_caja']) // importante
    ) {
        $fecha = $_POST['fecha'];
        $tipo_dato = $_POST['tipo_dato'];
        $valor = $_POST['valor'];
        $id_caja = $_POST['id_caja'];

        // Asegúrate de validar/sanitizar aquí si es necesario

        $stmt = $conexion->prepare("UPDATE caja SET fecha=?, tipo_dato=?, valor=? WHERE id_caja=?");
        $stmt->bind_param("ssis", $fecha, $tipo_dato, $valor, $id_caja);


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



// Delete cajas

if (isset($_GET['delete'])) {
    $idToDelete = intval($_GET['delete']);

    $stmt = $conexion->prepare("DELETE FROM caja WHERE id_caja = ?");
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

    <a href="./index.php" class=""><svg class="w-10 h-10 bg-red-100 rounded-full border-4 m-4 m-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12l4-4m-4 4 4 4" />
        </svg>
    </a>

    <div class="relative overflow-x-auto rounded-2xl shadow-2xl shadow-blue-500/50 m-4 bg-white">

        <table class="w-full text-sm text-left text-gray-700">
            <thead class="text-xs uppercase bg-gray-50">
                <tr>
                    <th class="px-6 py-3">Fecha</th>
                    <th class="px-6 py-3">Tipo De Ingreso</th>
                    <th class="px-6 py-3">Valor</th>
                    <th class="px-6 py-3">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($cajas)): ?>
                    <?php foreach ($cajas as $caja): ?>
                        <tr class="bg-white border-b hover:bg-gray-50">

                            <td class="px-6 py-4 font-semibold"><?php echo htmlspecialchars($caja['fecha']); ?></td>
                            <td class="px-6 py-4 flex items-center">
                                <div class="h-2.5 w-2.5 rounded-full <?php echo $caja['tipo_dato'] == 'Anual' ? 'bg-green-500' : 'bg-blue-500'; ?> mr-2"></div>
                                <?php echo $caja['tipo_dato'] ?>
                            </td>
                            <td class="px-6 py-4"><?php echo number_format($caja['valor'], 0, ',', '.'); ?></td>
                            <td class="px-6 py-4 flex space-x-0.5">
                                <a href="#" class="flex justify-start items-start"
                                    data-modal-target="editUserModal"
                                    data-modal-show="editUserModal"
                                    class="text-blue-600 hover:underline"
                                    data-id="<?php echo $caja['id_caja']; ?>"
                                    data-fecha="<?php echo isset($caja['fecha']) ? $caja['fecha'] : ''; ?>"
                                    data-tipo_dato="<?php echo isset($caja['tipo_dato']) ? $caja['tipo_dato'] : ''; ?>"
                                    data-valor="<?php echo isset($caja['valor']) ? $caja['valor'] : ''; ?>">
                                    <svg class="w-6 h-6 text-green-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="square" stroke-linejoin="round" stroke-width="2" d="M7 19H5a1 1 0 0 1-1-1v-1a3 3 0 0 1 3-3h1m4-6a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm7.441 1.559a1.907 1.907 0 0 1 0 2.698l-6.069 6.069L10 19l.674-3.372 6.07-6.07a1.907 1.907 0 0 1 2.697 0Z" />
                                    </svg>

                                </a>
                                <a href="#"
                                    class="text-red-600 hover:underline ml-2 btn-delete"
                                    data-id="<?php echo $caja['id_caja']; ?>">
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
                <input type="hidden" name="id_caja" id="id_caja">

                <!-- Fecha -->
                <div class="relative z-0 w-full mb-5 group">
                    <input type="date" name="fecha" id="fecha" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer" required />
                    <label for="fecha" class="absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6 peer-focus:text-blue-600">Fecha</label>
                </div>

                <!-- Valor -->
                <div class="relative z-0 w-full mb-5 group">
                    <input type="number" name="valor" id="valor" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer" required />
                    <label for="valor" class="absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6 peer-focus:text-blue-600">Valor</label>
                </div>

                <!-- Dato -->
                <div class="relative z-0 w-full mb-5 group">
                    <select name="tipo_dato" id="tipo_dato" class="block appearance-none w-full bg-transparent text-sm text-gray-900 border-0 border-b-2 border-gray-300 px-0 py-2.5 peer focus:outline-none focus:ring-0 focus:border-blue-600" required>
                        <option value="" disabled selected hidden></option>
                        <option value="Anual">Anual</option>
                        <option value="Semestral">Semestral</option>
                    </select>
                    <label for="tipo_dato" class="absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6 peer-focus:text-blue-600">Estado de pago</label>
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
            // Escuchamos clics en los botones "Editar"
            const editButtons = document.querySelectorAll('[data-modal-target="editUserModal"]');

            editButtons.forEach(button => {
                button.addEventListener('click', () => {
                    document.getElementById('id_caja').value = button.getAttribute('data-id'); // 👈 AGREGA ESTO
                    document.getElementById('fecha').value = button.getAttribute('data-fecha');
                    document.getElementById('tipo_dato').value = button.getAttribute('data-tipo_dato');
                    document.getElementById('valor').value = button.getAttribute('data-valor');

                });
            });

        });
    </script>



</body>

</html>