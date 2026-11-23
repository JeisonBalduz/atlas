const nodoChart = document.querySelector('#chart');

if (nodoChart && typeof window.ApexCharts !== 'undefined') {
  const series = {
    monthDataSeries1: {
      prices: [
        142.5, 144.1, 143.8, 146.2, 148.0, 147.3, 149.9, 152.4, 151.0, 153.75,
        155.2, 154.9, 157.8, 160.1, 159.4, 162.0, 164.5, 163.2, 166.8, 168.9,
      ],
      dates: [
        '13 Nov 2017', '14 Nov 2017', '15 Nov 2017', '16 Nov 2017', '17 Nov 2017',
        '20 Nov 2017', '21 Nov 2017', '22 Nov 2017', '23 Nov 2017', '24 Nov 2017',
        '27 Nov 2017', '28 Nov 2017', '29 Nov 2017', '30 Nov 2017', '01 Dec 2017',
        '04 Dec 2017', '05 Dec 2017', '06 Dec 2017', '07 Dec 2017', '08 Dec 2017',
      ],
    },
  };

  const options = {
    series: [{ name: 'Archivos', data: series.monthDataSeries1.prices }],
    chart: { type: 'area', height: 350, fontFamily: 'Inter, sans-serif', zoom: { enabled: false } },
    grid: { show: false },
    dataLabels: { enabled: false },
    colors: ['#052659'],
    stroke: { curve: 'straight' },
    title: { text: '', align: 'left' },
    labels: series.monthDataSeries1.dates.map((d) => d.replace('2017', '2025')),
    xaxis: {
      type: 'datetime',
      labels: { show: false },
      axisBorder: { show: false },
      axisTicks: { show: false },
    },
    yaxis: { labels: { show: false } },
  };

  const chart = new window.ApexCharts(nodoChart, options);
  chart.render();
}
