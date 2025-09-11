<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link href="./src/style.css" rel="stylesheet">
  <link href="./src/output.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>


</head>

<body>



  <?php include './header/header.php'; ?>



  <div class=" grid grid-cols-2">
    <div>

      <?php include './total/index.php'; ?>

    </div>
    <div class="max-w-sm w-full bg-white rounded-lg shadow-sm dark:bg-gray-800 p-4 md:p-6 mx-auto">

      <div class="flex justify-between items-start w-full">
        <div class="flex-col items-center">
          <div class="flex items-center mb-1">
            <h5 class="text-xl font-bold leading-none text-gray-900 dark:text-white me-1">Diagrama</h5>
          </div>
          <div id="dateRangeDropdown" class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-80 lg:w-96 dark:bg-gray-700 dark:divide-gray-600">
            <div class="p-3" aria-labelledby="dateRangeButton">
              <div date-rangepicker datepicker-autohide class="flex items-center">
                <div class="relative">
                  <input name="start" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Start date">
                </div>
                <div class="relative">
                  <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                    </svg>
                  </div>
                  <input name="end" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="End date">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Line Chart -->
      <div class="py-6" id="pie-chart"></div>

    </div>

  </div>

  <script>
    window.chartData = {
      nomina: <?php echo floatval($totalNomina); ?>,
      liquidacion: <?php echo floatval($totalLiquidacion); ?>,
      prestamos: <?php echo floatval($totalPrestamos); ?>,
      gastos: <?php echo floatval($totalGastos); ?>,
      abonos: <?php echo floatval($totalAbonos); ?>
    };
    window.totalGeneral = <?php echo floatval($totalNomina + $totalLiquidacion + $totalPrestamos + $totalGastos + $totalAbonos); ?>;
  </script>


  <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.46.0/dist/apexcharts.min.js"></script>

  <script src="../path/to/flowbite/dist/flowbite.min.js"></script>

  <script src="./js/script.js"></script>

</body>

</html>