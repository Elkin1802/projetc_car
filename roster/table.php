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
$consulta = "SELECT * FROM nomina";
$resultado = $conexion->query($consulta);

$roster = [];
if ($resultado && $resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $roster[] = $row;
    }
}

// Update roster

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (
        isset($_POST['fecha']) &&
        isset($_POST['valor']) &&
        isset($_POST['deuda']) &&
        isset($_POST['valor_deuda']) &&
        isset($_POST['motivo']) &&
        isset($_POST['id_nomina']) // importante
    ) {
        $fecha = $_POST['fecha'];
        $valor = $_POST['valor'];
        $deuda = $_POST['deuda'];
        $valor_deuda = $_POST['valor_deuda'];
        $motivo = $_POST['motivo'];
        $id_nomina = $_POST['id_nomina'];

        // Asegúrate de validar/sanitizar aquí si es necesario

        $stmt = $conexion->prepare("UPDATE nomina SET fecha=?, valor=?, deuda=?, valor_deuda=?, motivo=? WHERE id_nomina=?");
        $stmt->bind_param("siiisi", $fecha, $valor, $deuda, $valor_deuda, $motivo, $id_nomina);



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



// Delete roster

if (isset($_GET['delete'])) {
    $idToDelete = intval($_GET['delete']);

    $stmt = $conexion->prepare("DELETE FROM nomina WHERE id_nomina = ?");
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
                    <th class="px-6 py-3">Valor</th>
                    <th class="px-6 py-3">Deuda</th>
                    <th class="px-6 py-3">Valor a pagar</th>
                    <th class="px-6 py-3">Motivo</th>
                    <th class="px-6 py-3">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($roster)): ?>
                    <?php foreach ($roster as $loan): ?>
                        <tr class="bg-white border-b hover:bg-gray-50">

                            <td class="px-6 py-4 font-semibold"><?php echo htmlspecialchars($loan['fecha']); ?></td>
                            <td class="px-6 py-4"><?php echo number_format($loan['valor'], 0, ',', '.'); ?></td>
                            <td class="px-6 py-4 flex items-center">
                                <div class="h-2.5 w-2.5 rounded-full <?php echo $loan['deuda'] == 0 ? 'bg-green-500' : 'bg-red-500'; ?> mr-2"></div>
                                <?php echo $loan['deuda'] == 0 ? 'Pagado' : 'Debe'; ?>
                            </td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($loan['valor_deuda']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($loan['motivo']); ?></td>
                            <td class="px-6 py-4 flex space-x-0.5">
                                <a href="#" class="flex justify-start items-start"
                                    data-modal-target="editUserModal"
                                    data-modal-show="editUserModal"
                                    class="text-blue-600 hover:underline"
                                    data-id="<?php echo $loan['id_nomina']; ?>"
                                    data-fecha="<?php echo isset($loan['fecha']) ? $loan['fecha'] : ''; ?>"
                                    data-valor="<?php echo isset($loan['valor']) ? $loan['valor'] : ''; ?>"
                                    data-deuda="<?php echo isset($loan['deuda']) ? $loan['deuda'] : ''; ?>"
                                    data-valor_deuda="<?php echo isset($loan['valor_deuda']) ? $loan['valor_deuda'] : ''; ?>"
                                    data-motivo="<?php echo isset($loan['motivo']) ? htmlspecialchars($loan['motivo'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                    <svg class="w-6 h-6 text-green-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="square" stroke-linejoin="round" stroke-width="2" d="M7 19H5a1 1 0 0 1-1-1v-1a3 3 0 0 1 3-3h1m4-6a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm7.441 1.559a1.907 1.907 0 0 1 0 2.698l-6.069 6.069L10 19l.674-3.372 6.07-6.07a1.907 1.907 0 0 1 2.697 0Z" />
                                    </svg>

                                </a>
                                <a href="#"
                                    class="text-red-600 hover:underline ml-2 btn-delete"
                                    data-id="<?php echo $loan['id_nomina']; ?>">
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
        <div class="relative max-h-full w-96">
            <form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>" class="w-full p-6 bg-white rounded-lg shadow-xl shadow-red-500/50">
                <input type="hidden" name="id_nomina" id="id_nomina">

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

                <div class="grid md:grid-cols-2 md:gap-6">
                    <!-- Deuda -->
                    <div class="relative z-0 w-full mb-5 group">
                        <select name="deuda" id="deuda" class="block appearance-none w-full bg-transparent text-sm text-gray-900 border-0 border-b-2 border-gray-300 px-0 py-2.5 peer focus:outline-none focus:ring-0 focus:border-blue-600" required>
                            <option value="" disabled selected hidden></option>
                            <option value="0">No</option>
                            <option value="1">Si</option>
                        </select>
                        <label for="deuda" class="absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6 peer-focus:text-blue-600">Deuda</label>
                    </div>

                    <!-- Valor deuda -->
                    <div class="relative z-0 w-full mb-5 group">
                        <input type="number" name="valor_deuda" id="valor_deuda" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer" required />
                        <label for="valor_deuda" class="absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6 peer-focus:text-blue-600">Valor a pagar</label>
                    </div>

                </div>

                <!-- Motivo -->
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
            const deudaSelect = document.getElementById('deuda');
            const valorDeudaInput = document.getElementById('valor_deuda');
            const motivoTextArea = document.getElementById('motivo');

            // Función que actualiza required dinámicamente
            function updateRequiredFields() {
                if (deudaSelect.value === "1") {
                    valorDeudaInput.required = true;
                    motivoTextArea.required = true;
                } else {
                    valorDeudaInput.required = false;
                    valorDeudaInput.value = "";
                    motivoTextArea.required = false;
                    motivoTextArea.value = "";
                }
            }

            // Escuchamos cambio en el select deuda
            deudaSelect.addEventListener('change', updateRequiredFields);

            // Escuchamos clics en los botones "Editar"
            const editButtons = document.querySelectorAll('[data-modal-target="editUserModal"]');
            editButtons.forEach(button => {
                button.addEventListener('click', () => {
                    document.getElementById('id_nomina').value = button.getAttribute('data-id');
                    document.getElementById('fecha').value = button.getAttribute('data-fecha');
                    document.getElementById('valor').value = button.getAttribute('data-valor');
                    document.getElementById('deuda').value = button.getAttribute('data-deuda');
                    document.getElementById('valor_deuda').value = button.getAttribute('data-valor_deuda');
                    document.getElementById('motivo').value = button.getAttribute('data-motivo');

                    // 👇 Forzamos actualización de required según el valor cargado
                    updateRequiredFields();
                });
            });
        });
    </script>


</body>

</html>