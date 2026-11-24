import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;
Alpine.plugin(collapse);

// Couleur de marque courante (variables CSS) pour les graphiques.
window.brandColor = (alpha = 1) => {
  const v = getComputedStyle(document.documentElement).getPropertyValue('--brand').trim() || '30 34 53';
  return `rgb(${v} / ${alpha})`;
};

// Graphique déclaratif : <canvas x-data="chart({type, labels, datasets})">
Alpine.data('chart', (config) => ({
  init() {
    const datasets = (config.datasets || []).map((d, i) => ({
      borderColor: i === 0 ? window.brandColor() : '#9CA3AF',
      backgroundColor: config.type === 'line' ? window.brandColor(0.12) : (i === 0 ? window.brandColor(0.85) : '#D1D5DB'),
      fill: config.type === 'line',
      tension: 0.3,
      borderWidth: 2,
      pointRadius: 2,
      ...d,
    }));
    new Chart(this.$el, {
      type: config.type || 'line',
      data: { labels: config.labels || [], datasets },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: datasets.length > 1 || config.type === 'doughnut' } },
        scales: config.type === 'doughnut' ? {} : { y: { beginAtZero: true, ticks: { precision: 0 } } },
        ...(config.options || {}),
      },
    });
  },
}));

// Thème clair / sombre mémorisé par navigateur.
Alpine.store('theme', {
  dark: document.documentElement.classList.contains('dark'),
  toggle() {
    this.dark = !this.dark;
    document.documentElement.classList.toggle('dark', this.dark);
    try { localStorage.setItem('theme', this.dark ? 'dark' : 'light'); } catch (e) {}
  },
});

Alpine.start();

// Tableaux sur mobile : chaque ligne devient une carte. On recopie l'intitulé de la colonne
// dans data-label pour l'afficher à gauche de chaque valeur (voir .table-stacked dans app.css).
const stackTables = () => document.querySelectorAll('table.table:not(.table-stacked)').forEach((t) => {
  const heads = [...t.querySelectorAll('thead th')].map((th) => th.textContent.trim());
  t.querySelectorAll('tbody tr').forEach((tr) => {
    let col = 0;
    [...tr.children].forEach((td) => {
      const span = td.colSpan || 1;
      if (span === 1 && heads[col] && !td.hasAttribute('data-label')) td.dataset.label = heads[col];
      col += span;
    });
  });
  t.classList.add('table-stacked');
});
document.addEventListener('DOMContentLoaded', stackTables);
