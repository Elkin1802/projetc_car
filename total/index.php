<?php
include_once('./conexion/conexion.php');
$objeto = new Conexion();
$conexion = $objeto->conectar();

// Función para obtener el total de una tabla
function getTotal($conexion, $tabla)
{
    $sql = "SELECT SUM(valor) as total FROM $tabla";
    $res = $conexion->query($sql);
    return $res->fetch_assoc()['total'] ?? 0;
}

// Totales
$totalNomina = getTotal($conexion, 'nomina');
$totalLiquidacion = getTotal($conexion, 'liquidacion');
$totalPrestamos = getTotal($conexion, 'prestamos');
$totalGastos = getTotal($conexion, 'gastos');
$totalAbonos = getTotal($conexion, 'abonos');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="./src/output.css" rel="stylesheet">
    <link href="./src/style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>


    <title>Document</title>
</head>

<body>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6 p-4">
        <!-- Nómina -->
        <div class="bg-blue-500 text-white rounded-lg p-6 shadow-lg">
            <h2 class="text-lg font-semibold">Nómina</h2>
            <p class="text-2xl mt-2"><?php echo number_format($totalNomina, 0, ',', '.'); ?></p>
        </div>

        <!-- Liquidación -->
        <div class="bg-green-500 text-white rounded-lg p-6 shadow-lg">
            <h2 class="text-lg font-semibold">Liquidación</h2>
            <p class="text-2xl mt-2"><?php echo number_format($totalLiquidacion, 0, ',', '.'); ?></p>
        </div>

        <!-- Préstamos -->
        <div class="bg-yellow-500 text-white rounded-lg p-6 shadow-lg">
            <h2 class="text-lg font-semibold">Préstamos</h2>
            <p class="text-2xl mt-2"><?php echo number_format($totalPrestamos, 0, ',', '.'); ?></p>
        </div>

        <!-- Gastos -->
        <div class="bg-red-500 text-white rounded-lg p-6 shadow-lg">
            <h2 class="text-lg font-semibold">Gastos</h2>
            <p class="text-2xl mt-2"><?php echo number_format($totalGastos, 0, ',', '.'); ?></p>
        </div>

        <!-- Abonos -->
        <div class="bg-purple-500 text-white rounded-lg p-6 shadow-lg">
            <h2 class="text-lg font-semibold">Abonos</h2>
            <p class="text-2xl mt-2"><?php echo number_format($totalAbonos, 0, ',', '.'); ?></p>
        </div>

        <!-- Total -->
        <div class="bg-gray-500 text-white rounded-lg p-6 shadow-lg">
            <h2 class="text-lg font-semibold">Total</h2>
            <p class="text-2xl mt-2"><?php echo number_format($totalNomina - $totalLiquidacion - $totalPrestamos - $totalGastos - $totalAbonos, 0, ',', '.'); ?></p>
        </div>
    </div>



    <script src="../path/to/flowbite/dist/flowbite.min.js"></script>
</body>

</html>