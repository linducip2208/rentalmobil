<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Widget Booking — Rental Mobil</title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',system-ui,-apple-system,sans-serif; }
  body { background:#f5f5f4; padding:16px; }
  .widget-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; flex-wrap:wrap; gap:10px; }
  .widget-title { font-size:17px; font-weight:800; color:#1c1917; }
  .filters { display:flex; gap:8px; flex-wrap:wrap; }
  select, input { padding:9px 12px; border:1.5px solid #d6d3d1; border-radius:10px; font-size:13px; background:#fff; min-height:38px; }
  .cards { display:grid; grid-template-columns:repeat(auto-fill,minmax(230px,1fr)); gap:12px; }
  .card { background:#fff; border-radius:14px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.06); transition:transform .25s, box-shadow .25s; }
  .card:hover { transform:translateY(-4px); box-shadow:0 14px 28px -10px rgba(0,0,0,.15); }
  .card-img { height:120px; background:linear-gradient(135deg,#1e40af,#3730a3); display:flex; align-items:center; justify-content:center; color:#fff; font-size:38px; }
  .card-img img { width:100%; height:100%; object-fit:cover; }
  .card-body { padding:12px 14px 14px; }
  .card-name { font-size:14px; font-weight:700; color:#1c1917; margin-bottom:2px; }
  .card-meta { font-size:11.5px; color:#78716c; margin-bottom:8px; }
  .card-price { font-size:16px; font-weight:800; color:#1d4ed8; }
  .card-price small { font-size:11px; font-weight:500; color:#78716c; }
  .btn-book { display:block; width:100%; margin-top:10px; padding:9px 0; text-align:center; background:linear-gradient(135deg,#2563eb,#7c3aed); color:#fff; border:none; border-radius:9px; font-weight:700; font-size:13px; cursor:pointer; text-decoration:none; transition:transform .2s, box-shadow .2s; min-height:38px; line-height:20px; }
  .btn-book:hover { transform:translateY(-1px); box-shadow:0 8px 18px -6px rgba(37,99,235,.45); }
  .powered { text-align:center; font-size:10.5px; color:#a8a29e; margin-top:14px; }
  @media (max-width:640px) { .cards { grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); } }
</style>
</head>
<body>
<div class="widget-header">
  <div class="widget-title">Cek Ketersediaan</div>
  <div class="filters">
    <select id="location"><option value="">Semua Lokasi</option></select>
    <input type="date" id="start">
    <input type="date" id="end">
    <select id="category"><option value="">Semua Tipe</option></select>
  </div>
</div>

<div class="cards" id="cards">
  <div style="grid-column:1/-1;text-align:center;color:#a8a29e;padding:30px">Pilih tanggal sewa untuk melihat unit tersedia…</div>
</div>

<div class="powered">Powered by <strong>RentalMobil</strong> · <a href="/" target="_blank" rel="noopener" style="color:#2563eb">Buka situs lengkap →</a></div>

<script>
(function () {
  var base = document.currentScript ? new URL(document.currentScript.src).origin : window.location.origin;
  var $ = function (id) { return document.getElementById(id); };
  var today = new Date().toISOString().slice(0, 10);
  var tomorrow = new Date(Date.now() + 864e5).toISOString().slice(0, 10);
  $('start').value = today; $('end').value = tomorrow;

  fetch(base + '/api/public/meta').then(function (r) { return r.json(); }).then(function (meta) {
    (meta.locations || []).forEach(function (l) {
      var o = document.createElement('option'); o.value = l.id; o.textContent = l.name; $('location').appendChild(o);
    });
    (meta.categories || []).forEach(function (c) {
      var o = document.createElement('option'); o.value = c.id; o.textContent = c.name; $('category').appendChild(o);
    });
    load();
  }).catch(load);

  ['location', 'category'].forEach(function (id) { $(id).addEventListener('change', load); });
  ['start', 'end'].forEach(function (id) { $(id).addEventListener('change', load); });

  var timer;
  function load() {
    clearTimeout(timer);
    timer = setTimeout(function () {
      var s = $('start').value || today, e = $('end').value || tomorrow;
      if (new Date(e) <= new Date(s)) e = s; // fallback
      var qs = '?start_date=' + s + '&end_date=' + e +
        ($('location').value ? '&location_id=' + $('location').value : '') +
        ($('category').value ? '&category_id=' + $('category').value : '');
      $('cards').innerHTML = '<div style="grid-column:1/-1;text-align:center;color:#a8a29e;padding:24px">Memuat…</div>';
      fetch(base + '/api/public/availability' + qs)
        .then(function (r) { return r.json(); })
        .then(render);
    }, 250);
  }

  function render(json) {
    var items = json.data || [];
    if (!items.length) {
      $('cards').innerHTML = '<div style="grid-column:1/-1;text-align:center;color:#a8a29e;padding:30px">Tidak ada unit tersedia untuk tanggal ini.</div>';
      return;
    }
    $('cards').innerHTML = items.map(function (v) {
      var price = 'Rp' + Number(v.total).toLocaleString('id-ID');
      var daily = 'Rp' + Number(v.daily_rate).toLocaleString('id-ID') + '/hari';
      var img = v.photo_url ? '<img src="' + v.photo_url + '" alt="' + v.name + '" loading="lazy">' : '';
      return '<div class="card">' +
        '<div class="card-img">' + img + '</div>' +
        '<div class="card-body">' +
          '<div class="card-name">' + esc(v.name) + '</div>' +
          '<div class="card-meta">' + esc(v.category || '') + ' · ' + (v.transmission || '-') + ' · ' + (v.seat_count || '-') + ' kursi</div>' +
          '<div class="card-price">' + price + ' <small>(' + daily + ')</small></div>' +
          '<a class="btn-book" href="' + v.booking_url + '" target="_blank" rel="noopener">Booking Sekarang</a>' +
        '</div>' +
      '</div>';
    }).join('');
  }

  function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }

  // Expose untuk integrasi lanjutan oleh mitra.
  window.RentalMobilWidget = { reload: load };
})();
</script>
</body>
</html>
