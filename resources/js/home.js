var series = {
  monthDataSeries1: {
    prices: [
      142.5, 144.1, 143.8, 146.2, 148.0, 147.3, 149.9, 152.4, 151.0, 153.75,
      155.2, 154.9, 157.8, 160.1, 159.4, 162.0, 164.5, 163.2, 166.8, 168.9,
    ],
    dates: [
      "13 Nov 2017",
      "14 Nov 2017",
      "15 Nov 2017",
      "16 Nov 2017",
      "17 Nov 2017",
      "20 Nov 2017",
      "21 Nov 2017",
      "22 Nov 2017",
      "23 Nov 2017",
      "24 Nov 2017",
      "27 Nov 2017",
      "28 Nov 2017",
      "29 Nov 2017",
      "30 Nov 2017",
      "01 Dec 2017",
      "04 Dec 2017",
      "05 Dec 2017",
      "06 Dec 2017",
      "07 Dec 2017",
      "08 Dec 2017",
    ],
  },
};

// 2. Tu configuración original funcionará sin errores
var options = {
  series: [
    {
      name: "Archivos",
      data: series.monthDataSeries1.prices,
    },
  ],
  chart: {
    type: "area",
    height: 350,
    // 1. Cambia la tipografía de TODA la gráfica (títulos, tooltips, ejes)
    fontFamily: "Inter, sans-serif", // Reemplaza 'Inter' por el nombre exacto de tu fuente
    zoom: {
      enabled: false,
    },
  },
  // 1. Agrega este bloque para eliminar TODAS las líneas horizontales de fondo
  grid: {
    show: false,
  },
  dataLabels: {
    enabled: false,
  },
  colors: ["#052659"],
  stroke: {
    curve: "straight",
  },
  title: {
    text: "",
    align: "left",
  },
  labels: series.monthDataSeries1.dates.map(function (d) {
    return d.replace("2017", "2025");
  }),
  xaxis: {
    type: "datetime",
    labels: {
      show: false,
    },
    axisBorder: {
      show: false,
    },
    axisTicks: {
      show: false,
    },
  },
  yaxis: {
    labels: {
      show: false,
    },
  },
};

var chart = new ApexCharts(document.querySelector("#chart"), options);
chart.render();
