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

// ── Configurateur de séjour (page chambre) ─────────────────────────────────────
// Le client choisit ses dates dans un calendrier (nuits déjà réservées grisées), le nombre
// de personnes (et de chambres si l'établissement en a plusieurs du même type), et voit le
// prix se recalculer aussitôt via l'API publique price-preview.
const pad = (n) => String(n).padStart(2, '0');
const isoOf = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
const dateOf = (iso) => { const [y, m, d] = iso.split('-').map(Number); return new Date(y, m - 1, d); };
const addDays = (iso, n) => { const d = dateOf(iso); d.setDate(d.getDate() + n); return isoOf(d); };
const fcfa = (n) => `${Math.round(n).toLocaleString('fr-FR').replace(/ | /g, ' ')} FCFA`;

Alpine.data('stay', (cfg) => ({
  today: isoOf(new Date()),
  month: null,
  blocked: new Set(),
  checkIn: cfg.checkIn || null,
  checkOut: cfg.checkOut || null,
  guests: cfg.guests || 1,
  rooms: cfg.rooms || 1,
  quote: null,
  loadingQuote: false,
  error: '',
  fcfa,

  init() {
    const start = dateOf(this.checkIn || this.today);
    this.month = new Date(start.getFullYear(), start.getMonth(), 1);
    this.guests = Math.min(Math.max(1, this.guests), this.maxGuests);
    fetch(`/api/accommodations/${cfg.accId}/unavailable-dates?room_id=${cfg.roomId}`, { headers: { Accept: 'application/json' } })
      .then((r) => (r.ok ? r.json() : { dates: [] }))
      .then((j) => {
        this.blocked = new Set(j.dates || []);
        if (this.checkIn && this.checkOut && !this.rangeFree(this.checkIn, this.checkOut)) { this.checkIn = this.checkOut = null; }
        if (this.checkIn && this.checkOut) this.fetchQuote();
      })
      .catch(() => {});
  },

  get maxGuests() { return cfg.capacity * this.rooms; },
  get nights() { return this.checkIn && this.checkOut ? Math.round((dateOf(this.checkOut) - dateOf(this.checkIn)) / 86400000) : 0; },
  get ready() { return this.nights > 0 && this.quote && !this.error; },
  get monthLabel() { return this.month.toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' }); },
  get canPrev() { const t = dateOf(this.today); return this.month > new Date(t.getFullYear(), t.getMonth(), 1); },
  get continueUrl() {
    const p = new URLSearchParams({ room_id: cfg.roomId, check_in: this.checkIn || '', check_out: this.checkOut || '', guests: this.guests });
    if (this.rooms > 1) p.set('rooms_quantity', this.rooms);
    return `${cfg.bookUrl}?${p}`;
  },
  get hint() {
    if (!this.checkIn) return 'Touchez votre date d’arrivée.';
    if (!this.checkOut) return 'Touchez maintenant votre date de départ.';
    return null;
  },
  label(iso) { return dateOf(iso).toLocaleDateString('fr-FR', { weekday: 'short', day: 'numeric', month: 'short' }); },

  rangeFree(from, to) {
    for (let d = from; d < to; d = addDays(d, 1)) if (this.blocked.has(d)) return false;
    return true;
  },

  get days() {
    const first = this.month;
    const lead = (first.getDay() + 6) % 7; // semaine commençant le lundi
    const count = new Date(first.getFullYear(), first.getMonth() + 1, 0).getDate();
    const cells = [];
    for (let i = 0; i < lead; i++) cells.push({ key: `b${i}`, iso: null });
    const choosingOut = this.checkIn && !this.checkOut;
    for (let n = 1; n <= count; n++) {
      const iso = isoOf(new Date(first.getFullYear(), first.getMonth(), n));
      const past = iso < this.today;
      const taken = this.blocked.has(iso);
      let disabled = past;
      if (!past && choosingOut && iso > this.checkIn) disabled = !this.rangeFree(this.checkIn, iso);
      else if (!past) disabled = taken;
      const edge = iso === this.checkIn || iso === this.checkOut;
      const inside = this.checkIn && this.checkOut && iso > this.checkIn && iso < this.checkOut;
      cells.push({ key: iso, iso, n, disabled, taken: taken && !past, edge, inside });
    }
    return cells;
  },

  prevMonth() { if (this.canPrev) this.month = new Date(this.month.getFullYear(), this.month.getMonth() - 1, 1); },
  nextMonth() { this.month = new Date(this.month.getFullYear(), this.month.getMonth() + 1, 1); },

  pick(iso) {
    this.error = '';
    if (!this.checkIn || this.checkOut || iso <= this.checkIn) {
      if (this.blocked.has(iso)) return;
      this.checkIn = iso; this.checkOut = null; this.quote = null;
      return;
    }
    if (!this.rangeFree(this.checkIn, iso)) { this.error = 'Ces dates comprennent une nuit déjà réservée.'; return; }
    this.checkOut = iso;
    this.fetchQuote();
  },
  clear() { this.checkIn = this.checkOut = null; this.quote = null; this.error = ''; },

  setRooms(n) {
    this.rooms = Math.min(Math.max(1, n), cfg.quantity);
    this.guests = Math.min(this.guests, this.maxGuests);
    if (this.checkIn && this.checkOut) this.fetchQuote();
  },

  fetchQuote() {
    if (!(this.checkIn && this.checkOut)) return;
    this.loadingQuote = true;
    const p = new URLSearchParams({ check_in: this.checkIn, check_out: this.checkOut, room_id: cfg.roomId });
    if (this.rooms > 1) p.set('rooms_quantity', this.rooms);
    fetch(`/api/accommodations/${cfg.accId}/price-preview?${p}`, { headers: { Accept: 'application/json' } })
      .then(async (r) => {
        const j = await r.json().catch(() => ({}));
        if (!r.ok) { this.quote = null; this.error = j.message || 'Prix indisponible pour ces dates.'; return; }
        this.quote = j;
      })
      .catch(() => { this.quote = null; this.error = 'Connexion impossible. Réessayez.'; })
      .finally(() => { this.loadingQuote = false; });
  },
}));

// Démarre Alpine une fois tous les composants (chart, stay…) déclarés.
Alpine.start();
