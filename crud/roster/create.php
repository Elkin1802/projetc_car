<?php

include_once('../../conexion/conexion.php');

// Mostrar errores para poder depurar
error_reporting(E_ALL);
ini_set('display_errors', 1);


// Crear conexión
$objeto = new Conexion();
$conexion = $objeto->conectar();

$swalMessage = ''; // ← Aquí almacenamos el mensaje JS a mostrar

// Update pass

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (
        isset($_POST['fecha']) &&
        isset($_POST['valor']) &&
        isset($_POST['deuda']) &&
        isset($_POST['valor_deuda']) &&
        isset($_POST['motivo'])

    ) {
        $fecha = $_POST['fecha'];
        $valor = $_POST['valor'];
        $deuda = $_POST['deuda'];
        $valor_deuda = $_POST['valor_deuda'];
        $motivo = $_POST['motivo'];


        $stmt = $conexion->prepare("INSERT INTO nomina (fecha, valor, deuda, valor_deuda, motivo) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("siiss", $fecha, $valor, $deuda, $valor_deuda, $motivo);

        if ($stmt->execute()) {
            $swalMessage = "
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            title: 'Éxito',
            text: 'Datos creados correctamente',
            icon: 'success',
            confirmButtonText: 'Aceptar'
        }).then(() => {
            setTimeout(function() {
                window.location.href = '../../roster/table.php';
            }, 500); // 500ms para asegurar que la alerta se muestre
        });
    });
</script>";
        } else {
            $swalMessage = "
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            title: 'Error',
            text: 'No se pudo crear el préstamo',
            icon: 'error',
            confirmButtonText: 'Aceptar'
        }).then(() => {
            setTimeout(function() {
                window.location.href = '../../loans/table.php';
            }, 500); // 500ms para asegurar que la alerta se muestre
        });
    });
</script>";
        }

        $stmt->close();
        echo $swalMessage;
    } else {
        $swalMessage = "
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            title: 'Error',
            text: 'Faltan datos en el formulario',
            icon: 'error',
            confirmButtonText: 'Aceptar'
        }).then(() => {
            setTimeout(function() {
            }, 500); // 500ms para asegurar que la alerta se muestre
        });
    });
</script>";
    }
}


?>

<!DOCTYPE html>
<html lang="en">

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

<body>

    <a href="../../roster/index.php" class=""><svg class="w-10 h-10 bg-red-100 rounded-full border-4 m-4 " aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12l4-4m-4 4 4 4" />
        </svg>
    </a>

    <div class="flex justify-center items-center h-screen">
        <form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>" class="max-w-sm w-full mx-auto p-6 bg-white rounded-lg shadow-xl shadow-red-500/50">

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
                    <input type="number" name="valor_deuda" id="valor_deuda" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer" <?php if ($deuda == 1) echo 'required'; ?> />
                    <label for="valor_deuda" class="absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6 peer-focus:text-blue-600">Valor a pagar</label>
                </div>

            </div>

            <!-- Motivo -->
            <div class="relative z-0 w-full mb-5 group">
                <textarea name="motivo" id="motivo" rows="4" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none resize-none focus:outline-none focus:ring-0 focus:border-blue-600 peer" <?php if ($deuda == 1) echo 'required'; ?>></textarea>
                <label for="motivo" class="absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6 peer-focus:text-blue-600">Motivo</label>
            </div>

            <!-- Botón -->
            <button type="submit" class="cursor-pointer w-full bg-blue-700 hover:bg-blue-800 text-white font-medium rounded-lg text-sm px-5 py-2.5 text-center">Registrar Datos</button>
    </div>

    </form>
    </div>

    <?php if (!empty($swalMessage)) echo $swalMessage; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deudaSelect = document.getElementById('deuda');
            const valorDeudaInput = document.getElementById('valor_deuda');
            const motivoTextArea = document.getElementById('motivo');

            deudaSelect.addEventListener('change', function() {
                if (this.value === "1") {
                    valorDeudaInput.required = true;
                    motivoTextArea.required = true;
                } else {
                    valorDeudaInput.required = false;
                    valorDeudaInput.value = "";
                    motivoTextArea.required = false;
                    motivoTextArea.value = "";
                }
            });
        });
    </script>

</body>

</html>