document.addEventListener("DOMContentLoaded", function() {
  function porcentaje(valor) {
    return parseFloat(((valor / window.totalGeneral) * 100).toFixed(2));
  }

  const seriesData = [
    porcentaje(window.chartData.nomina),
    porcentaje(window.chartData.liquidacion),
    porcentaje(window.chartData.prestamos),
    porcentaje(window.chartData.gastos),
    porcentaje(window.chartData.abonos)
  ];

  const labels = ["Nómina", "Liquidación", "Préstamos", "Gastos", "Abonos"];

  const options = {
    series: seriesData,
    chart: {
      type: 'pie',
      height: 420
    },
    labels: labels,
    colors: ["#1C64F2", "#16BDCA", "#9061F9", "#98B9AB", "#3F3047"],

    // Mostrar solo el porcentaje en cada porción
    dataLabels: {
      enabled: true,
      formatter: function(val) {
        return val + '%';
      },
      style: {
        fontSize: '14px',
        fontWeight: 'bold'
      },
      dropShadow: { enabled: false }
    },

    // Tooltip muestra nombre + valor real en pesos
    tooltip: {
      enabled: true,
      y: {
        formatter: function(val, { seriesIndex }) {
          const valorReal = [
            window.chartData.nomina,
            window.chartData.liquidacion,
            window.chartData.prestamos,
            window.chartData.gastos,
            window.chartData.abonos
          ][seriesIndex];
          return `${new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0
          }).format(valorReal)}`;
        }
      }
    },

    legend: {
      position: 'bottom'
    }
  };

  const chart = new ApexCharts(document.querySelector("#pie-chart"), options);
  chart.render();
});
