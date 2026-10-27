<?php
require_once __DIR__ . '/../controller/functions.php';

// Ambil data awal saat pertama kali halaman dimuat
$initialData = getLiveVotingStats();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Live Count Hasil Voting Harapan Competition 2026</title>
  <link rel="stylesheet" href="../src/output.css" />
  <style>
    .bar-fill {
      transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .rank-card {
      transition: transform 0.2s ease, border-color 0.2s ease, background-color 0.3s ease;
    }
    .vote-bump {
      animation: voteBump 0.8s ease;
    }
    @keyframes voteBump {
      0% { transform: scale(1); background-color: #FEF3C7; }
      50% { transform: scale(1.02); background-color: #FDE68A; }
      100% { transform: scale(1); background-color: white; }
    }
    .pulse-dot {
      display: inline-block;
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background-color: #16A34A;
      box-shadow: 0 0 0 0 rgba(22, 163, 74, 0.7);
      animation: pulseGreen 1.8s infinite;
    }
    @keyframes pulseGreen {
      0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(22, 163, 74, 0.7); }
      70% { transform: scale(1); box-shadow: 0 0 0 7px rgba(22, 163, 74, 0); }
      100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(22, 163, 74, 0); }
    }
  </style>
</head>
<body class="page-wrapper" id="pageBody">

<?php include __DIR__ . '/../src/components/navbar.php'; ?>

<main class="page-content" style="padding-bottom:3rem;">
  <div style="width:100%; max-width:960px; margin-inline:auto; display:flex; flex-direction:column; gap:20px;">

    <!-- ── TOP BAR HEADER & STATUS ── -->
    <div class="card" style="padding:18px 20px;">
      <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
        <div>
          <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
            <span class="pulse-dot"></span>
            <span style="font-size:0.75rem; font-weight:700; color:#15803D; text-transform:uppercase; letter-spacing:0.06em;">
              Live View Real-Time
            </span>
            <span style="font-size:0.75rem; color:#A8A29E;">&bull;</span>
            <span id="connStatus" style="font-size:0.75rem; color:#57534E;">Terhubung</span>
            <span style="font-size:0.75rem; color:#A8A29E;">&bull;</span>
            <span id="timeText" style="font-size:0.75rem; color:#57534E; font-family:monospace;"><?= htmlspecialchars($initialData['timestamp']) ?></span>
          </div>
          <h1 style="font-size:1.45rem; font-weight:800; color:#1C1917; margin:0; letter-spacing:-0.02em;">
            Perolehan Suara Pentas Seni Harapan Competition 2026
          </h1>
        </div>

        <div style="display:flex; align-items:center; gap:8px;">
          <button
            type="button"
            onclick="toggleFullscreen()"
            id="btnFullscreen"
            class="btn-secondary"
            style="height:36px; padding-inline:12px; font-size:0.8rem; display:flex; align-items:center; gap:6px;">
            <span class="material-symbols-rounded" style="font-size:1rem;">fullscreen</span>
            <span>Layar Penuh</span>
          </button>
        </div>
      </div>

      <!-- ── STATS SUMMARY METRIC ROW ── -->
      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:12px; margin-top:16px; padding-top:16px; border-top:1px solid #E7E5E4;">
        
        <div style="background:#F5F5F4; padding:12px 14px; border-radius:8px;">
          <div style="font-size:0.72rem; font-weight:600; color:#78716C; text-transform:uppercase; letter-spacing:0.04em;">Total Suara Masuk</div>
          <div style="font-size:1.5rem; font-weight:800; color:#0D1B45; margin-top:2px;">
            <span id="statTotalSuara"><?= number_format($initialData['total_suara'], 0, ',', '.') ?></span>
            <span style="font-size:0.85rem; font-weight:600; color:#57534E;">suara</span>
          </div>
        </div>

        <div style="background:#F5F5F4; padding:12px 14px; border-radius:8px;">
          <div style="font-size:0.72rem; font-weight:600; color:#78716C; text-transform:uppercase; letter-spacing:0.04em;">Partisipasi Siswa</div>
          <div style="font-size:1.5rem; font-weight:800; color:#0D1B45; margin-top:2px;">
            <span id="statSiswaVote"><?= number_format($initialData['total_sudah_vote'], 0, ',', '.') ?></span>
            <span style="font-size:0.85rem; font-weight:600; color:#57534E;">/ <?= number_format($initialData['total_siswa'], 0, ',', '.') ?></span>
            <span id="statPersen" style="font-size:0.85rem; font-weight:700; color:#15803D; margin-left:4px;">(<?= $initialData['persen_partisipasi'] ?>%)</span>
          </div>
        </div>

        <div style="background:#F5F5F4; padding:12px 14px; border-radius:8px;">
          <div style="font-size:0.72rem; font-weight:600; color:#78716C; text-transform:uppercase; letter-spacing:0.04em;">Pemimpin Suara Kelas X</div>
          <div id="leaderXText" style="font-size:0.95rem; font-weight:700; color:#1C1917; margin-top:6px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
            <?= !empty($initialData['kelas_x'][0]) ? htmlspecialchars($initialData['kelas_x'][0]['nama_kelas']) . ' (' . $initialData['kelas_x'][0]['total_votes'] . ')' : '-' ?>
          </div>
        </div>

        <div style="background:#F5F5F4; padding:12px 14px; border-radius:8px;">
          <div style="font-size:0.72rem; font-weight:600; color:#78716C; text-transform:uppercase; letter-spacing:0.04em;">Pemimpin Suara Kelas XI</div>
          <div id="leaderXIText" style="font-size:0.95rem; font-weight:700; color:#1C1917; margin-top:6px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
            <?= !empty($initialData['kelas_xi'][0]) ? htmlspecialchars($initialData['kelas_xi'][0]['nama_kelas']) . ' (' . $initialData['kelas_xi'][0]['total_votes'] . ')' : '-' ?>
          </div>
        </div>

      </div>
    </div>

    <!-- ── DUA KOLOM PANGGUNG REAL-TIME ── -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(380px, 1fr)); gap:20px;">
      
      <!-- KOLOM KELAS X -->
      <section class="card" style="padding:18px 20px;">
        <div style="display:flex; justify-content:space-between; align-items:baseline; margin-bottom:14px; padding-bottom:10px; border-bottom:2px solid #0D1B45;">
          <h2 style="font-size:1.15rem; font-weight:800; color:#1C1917; margin:0;">
            Panggung Kelas X
          </h2>
          <span style="font-size:0.8rem; font-weight:600; color:#57534E;">
            10 Penampilan
          </span>
        </div>

        <div id="listContainerX" style="display:flex; flex-direction:column; gap:10px;">
          <!-- Kartu dirender dinamis via renderList() -->
        </div>
      </section>

      <!-- KOLOM KELAS XI -->
      <section class="card" style="padding:18px 20px;">
        <div style="display:flex; justify-content:space-between; align-items:baseline; margin-bottom:14px; padding-bottom:2px; border-bottom:2px solid #0D1B45;">
          <h2 style="font-size:1.15rem; font-weight:800; color:#1C1917; margin:0;">
            Panggung Kelas XI
          </h2>
          <span style="font-size:0.8rem; font-weight:600; color:#57534E;">
            9 Penampilan
          </span>
        </div>

        <div id="listContainerXI" style="display:flex; flex-direction:column; gap:10px;">
          <!-- Kartu dirender dinamis via renderList() -->
        </div>
      </section>

    </div>

  </div>
</main>

<?php include __DIR__ . '/../src/components/footer.php'; ?>

<script>
// Data awal dari PHP
let currentStats = <?= json_encode($initialData) ?>;
let previousVotes = {};

// Helper escape HTML
function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

// Render list kartu untuk satu angkatan
function renderStageList(containerId, items, maxVotes, totalSuara) {
  const container = document.getElementById(containerId);
  if (!container) return;

  // Urutkan item berdasarkan total_votes DESC
  const sorted = [...items].sort((a, b) => b.total_votes - a.total_votes || a.nama_kelas.localeCompare(b.nama_kelas));

  let html = '';
  sorted.forEach((item, index) => {
    const id = item.id_penampilan;
    const votes = Number(item.total_votes) || 0;
    
    // Deteksi jika ada kenaikan suara untuk animasi bump
    const isBumped = (previousVotes[id] !== undefined && votes > previousVotes[id]);
    previousVotes[id] = votes;

    // Hitung persentase bar
    const barPercent = maxVotes > 0 ? Math.round((votes / maxVotes) * 100) : 0;
    const sharePercent = totalSuara > 0 ? ((votes / totalSuara) * 100).toFixed(1) : '0.0';

    const rankNumber = index + 1;
    const isLeader = (rankNumber === 1 && votes > 0);

    html += `
      <div 
        id="itemCard_${id}" 
        class="rank-card ${isBumped ? 'vote-bump' : ''}" 
        style="padding:12px 14px; background:white; border:1.5px solid ${isLeader ? '#0D1B45' : '#E7E5E4'}; border-radius:8px; display:flex; flex-direction:column; gap:8px;">
        
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:10px;">
          <div style="display:flex; align-items:flex-start; gap:8px; min-width:0;">
            <span style="font-size:0.75rem; font-weight:800; color:${isLeader ? '#0D1B45' : '#78716C'}; min-width:22px; padding-top:2px;">
              #${rankNumber}
            </span>
            <div style="min-width:0;">
              <div style="font-size:0.78rem; font-weight:700; color:#0D1B45; line-height:1.2;">
                ${escapeHtml(item.nama_kelas)}
              </div>
              <div style="font-size:0.95rem; font-weight:700; color:#1C1917; line-height:1.3; margin-top:2px;">
                ${escapeHtml(item.nama_penampilan)}
              </div>
            </div>
          </div>

          <div style="text-align:right; flex-shrink:0;">
            <div style="font-size:1.2rem; font-weight:800; color:#0D1B45; line-height:1;">
              ${votes.toLocaleString('id-ID')}
            </div>
            <div style="font-size:0.7rem; font-weight:600; color:#78716C; margin-top:2px;">
              ${sharePercent}%
            </div>
          </div>
        </div>

        <!-- Progress Bar -->
        <div style="width:100%; height:6px; background:#F5F5F4; border-radius:3px; overflow:hidden;">
          <div class="bar-fill" style="width:${barPercent}%; height:100%; background:${isLeader ? '#0D1B45' : '#3B82F6'}; border-radius:3px;"></div>
        </div>
      </div>
    `;
  });

  container.innerHTML = html;
}

// Update seluruh tampilan berdasarkan data terbaru
function updateDashboard(data) {
  if (!data) return;

  // 1. Update text angka metrik
  const statTotalSuara = document.getElementById('statTotalSuara');
  const statSiswaVote = document.getElementById('statSiswaVote');
  const statPersen = document.getElementById('statPersen');
  const timeText = document.getElementById('timeText');

  if (statTotalSuara) statTotalSuara.textContent = Number(data.total_suara || 0).toLocaleString('id-ID');
  if (statSiswaVote) statSiswaVote.textContent = Number(data.total_sudah_vote || 0).toLocaleString('id-ID');
  if (statPersen) statPersen.textContent = `(${data.persen_partisipasi || 0}%)`;
  if (timeText && data.timestamp) timeText.textContent = data.timestamp;

  // 2. Pemimpin suara
  const leaderXText = document.getElementById('leaderXText');
  const leaderXIText = document.getElementById('leaderXIText');

  if (leaderXText && data.kelas_x && data.kelas_x.length > 0) {
    const topX = [...data.kelas_x].sort((a,b) => b.total_votes - a.total_votes)[0];
    leaderXText.textContent = `${topX.nama_kelas} (${topX.total_votes} suara)`;
  }
  if (leaderXIText && data.kelas_xi && data.kelas_xi.length > 0) {
    const topXI = [...data.kelas_xi].sort((a,b) => b.total_votes - a.total_votes)[0];
    leaderXIText.textContent = `${topXI.nama_kelas} (${topXI.total_votes} suara)`;
  }

  // 3. Render daftar kartu
  renderStageList('listContainerX', data.kelas_x || [], data.max_votes_x || 0, data.total_suara || 0);
  renderStageList('listContainerXI', data.kelas_xi || [], data.max_votes_xi || 0, data.total_suara || 0);
}

// ── REAL-TIME ENGINE: SSE (Server-Sent Events) + POLLING FALLBACK ──
let sseSource = null;
let pollTimer = null;

function startRealTimeEngine() {
  const connStatus = document.getElementById('connStatus');

  // Cek apakah browser mendukung EventSource
  if (typeof(EventSource) !== "undefined") {
    try {
      sseSource = new EventSource('liveStream.php?stream=1');

      sseSource.addEventListener('voteUpdate', function(e) {
        try {
          const freshData = JSON.parse(e.data);
          updateDashboard(freshData);
          if (connStatus) connStatus.textContent = "Live Stream Aktif";
        } catch(err) {
          console.error("Gagal parse SSE data:", err);
        }
      });

      sseSource.onopen = function() {
        if (connStatus) connStatus.textContent = "Live Stream Aktif";
      };

      sseSource.onerror = function() {
        if (connStatus) connStatus.textContent = "Sinkronisasi...";
        // Jika SSE tertutup/terputus sementara, fallback polling otomatis aktif
        fetchJsonFallback();
      };
    } catch(e) {
      console.warn("EventSource init error, menggunakan polling:", e);
      startPollingOnly();
    }
  } else {
    startPollingOnly();
  }

  // Polling pengaman tiap 3 detik untuk memastikan data selalu sinkron
  if (!pollTimer) {
    pollTimer = setInterval(fetchJsonFallback, 3000);
  }
}

function fetchJsonFallback() {
  fetch('liveStream.php')
    .then(r => r.json())
    .then(data => {
      updateDashboard(data);
    })
    .catch(err => {
      console.error("Polling error:", err);
    });
}

function startPollingOnly() {
  const connStatus = document.getElementById('connStatus');
  if (connStatus) connStatus.textContent = "Live Polling Aktif";
  fetchJsonFallback();
}

// Toggle Fullscreen untuk proyektor
function toggleFullscreen() {
  const elem = document.documentElement;
  const btn = document.getElementById('btnFullscreen');
  if (!document.fullscreenElement) {
    if (elem.requestFullscreen) {
      elem.requestFullscreen();
    } else if (elem.webkitRequestFullscreen) {
      elem.webkitRequestFullscreen();
    }
    if (btn) btn.innerHTML = '<span class="material-symbols-rounded" style="font-size:1rem;">fullscreen_exit</span><span>Keluar Layar Penuh</span>';
  } else {
    if (document.exitFullscreen) {
      document.exitFullscreen();
    }
    if (btn) btn.innerHTML = '<span class="material-symbols-rounded" style="font-size:1rem;">fullscreen</span><span>Layar Penuh</span>';
  }
}

document.addEventListener('fullscreenchange', () => {
  const btn = document.getElementById('btnFullscreen');
  if (!document.fullscreenElement && btn) {
    btn.innerHTML = '<span class="material-symbols-rounded" style="font-size:1rem;">fullscreen</span><span>Layar Penuh</span>';
  }
});

// Jalankan rendering awal dan hubungkan real-time engine
document.addEventListener('DOMContentLoaded', () => {
  updateDashboard(currentStats);
  startRealTimeEngine();
});
</script>

</body>
</html>
