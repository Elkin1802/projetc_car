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
$consulta = "SELECT * FROM prestamos";
$resultado = $conexion->query($consulta);

$loans = [];
if ($resultado && $resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $loans[] = $row;
    }
}

// Update loans

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (
        isset($_POST['fecha']) &&
        isset($_POST['valor']) &&
        isset($_POST['responsable']) &&
        isset($_POST['status']) &&
        isset($_POST['abono_pago']) &&
        isset($_POST['motivo']) &&
        isset($_POST['id_prestamos']) // importante
    ) {
        $fecha = $_POST['fecha'];
        $valor = $_POST['valor'];
        $responsable = $_POST['responsable'];
        $status = $_POST['status'];
        $abono_pago = $_POST['abono_pago'];
        $motivo = $_POST['motivo'];
        $id_prestamos = $_POST['id_prestamos'];

        // Asegúrate de validar/sanitizar aquí si es necesario

        $stmt = $conexion->prepare("UPDATE prestamos SET fecha=?, valor=?, responsable=?, status=?, abono_pago=?, motivo=? WHERE id_prestamos=?");
        $stmt->bind_param("sissisi", $fecha, $valor, $responsable, $status, $abono_pago, $motivo, $id_prestamos);

        if ($stmt->execute()) {
            $swalMessage = "
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Éxito',
                        text: 'Datos actualizados correctamente',
                        icon: 'success',
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                         window.location.href = 'table.php'; // redirige después

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
        echo "<script>alert('Faltan datos en el formulario.');</script>";
    }
}


// Delete loans

if (isset($_GET['delete'])) {
    $idToDelete = intval($_GET['delete']);

    $stmt = $conexion->prepare("DELETE FROM prestamos WHERE id_prestamos = ?");
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

    <div class="relative overflow-x-auto rounded-2xl shadow-2xl shadow-blue-500/50 m-4 bg-white">
        <table class="w-full text-sm text-left text-gray-700">
            <thead class="text-xs uppercase bg-gray-50">
                <tr>
                    <th class="px-6 py-3">Fecha</th>
                    <th class="px-6 py-3">Valor</th>
                    <th class="px-6 py-3">Responsable</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Abono / Pago</th>
                    <th class="px-6 py-3">Motivo Préstamo</th>
                    <th class="px-6 py-3">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($loans)): ?>
                    <?php foreach ($loans as $loan): ?>
                        <tr class="bg-white border-b hover:bg-gray-50">

                            <td class="px-6 py-4 font-semibold"><?php echo htmlspecialchars($loan['fecha']); ?></td>
                            <td class="px-6 py-4"><?php echo number_format($loan['valor'], 0, ',', '.'); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($loan['responsable']); ?></td>
                            <td class="px-6 py-4 flex items-center">
                                <div class="h-2.5 w-2.5 rounded-full <?php echo $loan['status'] == 1 ? 'bg-green-500' : 'bg-red-500'; ?> mr-2"></div>
                                <?php echo $loan['status'] == 1 ? 'Pagado' : 'Debe'; ?>
                            </td>
                            <td class="px-6 py-4"><?php echo number_format($loan['abono_pago'], 0, ',', '.') ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($loan['motivo']); ?></td>
                            <td class="px-6 py-4 flex space-x-0.5">
                                <a href="#" class="flex justify-start items-start"
                                    data-modal-target="editUserModal"
                                    data-modal-show="editUserModal"
                                    class="text-blue-600 hover:underline"
                                    data-id="<?php echo isset($loan['id']) ? $loan['id'] : ''; ?>"
                                    data-fecha="<?php echo isset($loan['fecha']) ? $loan['fecha'] : ''; ?>"
                                    data-valor="<?php echo isset($loan['valor']) ? $loan['valor'] : ''; ?>"
                                    data-responsable="<?php echo isset($loan['responsable']) ? htmlspecialchars($loan['responsable'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                                    data-status="<?php echo isset($loan['status']) ? $loan['status'] : ''; ?>"
                                    data-abono_pago="<?php echo isset($loan['abono_pago']) ? $loan['abono_pago'] : ''; ?>"
                                    data-motivo="<?php echo isset($loan['motivo']) ? htmlspecialchars($loan['motivo'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                    <svg class="w-6 h-6 text-green-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="square" stroke-linejoin="round" stroke-width="2" d="M7 19H5a1 1 0 0 1-1-1v-1a3 3 0 0 1 3-3h1m4-6a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm7.441 1.559a1.907 1.907 0 0 1 0 2.698l-6.069 6.069L10 19l.674-3.372 6.07-6.07a1.907 1.907 0 0 1 2.697 0Z" />
                                    </svg>

                                </a>
                                <a href="#"
                                    class="text-red-600 hover:underline ml-2 btn-delete"
                                    data-id="<?php echo $loan['id_prestamos']; ?>">
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
                <input type="hidden" name="id_prestamos" id="id_prestamos" value="<?= $loan['id_prestamos'] ?? '' ?>">



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

                <!-- Responsable -->
                <div class="relative z-0 w-full mb-5 group">
                    <input type="text" name="responsable" id="responsable" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer" required />
                    <label for="responsable" class="absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6 peer-focus:text-blue-600">Responsable</label>
                </div>

                <div class="grid md:grid-cols-2 md:gap-6">
                    <!-- Status -->
                    <div class="relative z-0 w-full mb-5 group">
                        <select name="status" id="status" class="block appearance-none w-full bg-transparent text-sm text-gray-900 border-0 border-b-2 border-gray-300 px-0 py-2.5 peer focus:outline-none focus:ring-0 focus:border-blue-600" required>
                            <option value="" disabled selected hidden></option>
                            <option value="0">Debe</option>
                            <option value="1">Pagado</option>
                        </select>
                        <label for="status" class="absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6 peer-focus:text-blue-600">Estado de pago</label>
                    </div>

                    <!-- Abono -->
                    <div class="relative z-0 w-full mb-5 group">
                        <input type="number" name="abono_pago" id="abono_pago" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer" required />
                        <label for="abono_pago" class="absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6 peer-focus:text-blue-600">Abono / Pagado</label>
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

    <!-- Modal Edit -->

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Escuchamos clics en los botones "Editar"
            const editButtons = document.querySelectorAll('[data-modal-target="editUserModal"]');

            editButtons.forEach(button => {
                button.addEventListener('click', () => {
                    // Obtenemos los valores de los atributos data-*
                    const fecha = button.getAttribute('data-fecha');
                    const valor = button.getAttribute('data-valor');
                    const responsable = button.getAttribute('data-responsable');
                    const status = button.getAttribute('data-status');
                    const abono = button.getAttribute('data-abono_pago');
                    const motivo = button.getAttribute('data-motivo');

                    // Rellenamos los inputs del modal
                    document.getElementById('fecha').value = fecha;
                    document.getElementById('valor').value = valor;
                    document.getElementById('responsable').value = responsable;
                    document.getElementById('status').value = status;
                    document.getElementById('abono_pago').value = abono;
                    document.getElementById('motivo').value = motivo;
                });
            });
        });
    </script>

    <!-- Delete alert and button -->

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.btn-delete');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();

                    const id = this.getAttribute('data-id');

                    Swal.fire({
                        title: '¿Estás seguro?',
                        text: 'Esta acción no se puede deshacer.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch('table.php?delete=' + id)
                                .then(response => response.json())
                                .then(data => {
                                    if (data.status === "ok") {
                                        Swal.fire({
                                            title: 'Eliminado',
                                            text: 'El préstamo ha sido eliminado correctamente.',
                                            icon: 'success',
                                            confirmButtonText: 'Aceptar'
                                        }).then(() => {
                                            location.reload(); // 🔄 recarga automáticamente
                                        });
                                    } else {
                                        Swal.fire({
                                            title: 'Error',
                                            text: 'No se pudo eliminar el préstamo.',
                                            icon: 'error',
                                            confirmButtonText: 'Aceptar'
                                        });
                                    }
                                })
                                .catch(error => {
                                    Swal.fire('Error', 'Error en la petición al servidor.', 'error');
                                });
                        }
                    });
                });
            });
        });
    </script>

</body>

</html>