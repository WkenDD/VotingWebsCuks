<?php
require_once __DIR__ . '/../controller/functions.php';

$alert = null;
$voteCompleted = $_SESSION['vote_completed'] ?? null;
$voter = $_SESSION['voter'] ?? null;

if (!$voter && !$voteCompleted) {
    header("Location: verifikasi.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submitVoting'])) {
    if (!$voter) {
        $alert = ['type' => 'error', 'msg' => 'Sesi pemilih telah berakhir. Silakan verifikasi ulang.'];
    } else {
        $penampilanIds = $_POST['pilihan_penampilan'] ?? [];
        if (!is_array($penampilanIds) || count($penampilanIds) === 0) {
            $alert = ['type' => 'error', 'msg' => 'Silakan pilih minimal 1 penampilan di Kelas X dan 1 di Kelas XI.'];
        } else {
            $result = submitVote($voter['id_siswa'], $penampilanIds);
            if ($result['status'] === 'success') {
                $voteCompleted = $_SESSION['vote_completed'];
                $voter = null;
            } else {
                $alert = ['type' => 'error', 'msg' => $result['message']];
            }
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'finish') {
    unset($_SESSION['vote_completed'], $_SESSION['voter']);
    header("Location: verifikasi.php");
    exit;
}

$groupedPenampilan = [];
if (!$voteCompleted) {
    foreach (getActivePenampilan() as $item) {
        $key = ($item['angkatan'] == '10') ? 'Kelas X' : 'Kelas XI';
        $groupedPenampilan[$key][] = $item;
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Bilik Suara Harapan Competition 2026</title>
  <link rel="stylesheet" href="../src/output.css" />
</head>
<body class="page-wrapper" style="padding-bottom: <?= !$voteCompleted ? '90px' : '0' ?>;">

<?php include __DIR__ . '/../src/components/navbar.php'; ?>

<main class="page-content">
  <div style="width:100%; max-width:900px; margin-inline:auto; display:flex; flex-direction:column; gap:24px;">

  <?php if ($voteCompleted): ?>
  <!-- ── SUCCESS SCREEN ── -->
  <div class="card" style="max-width:500px; width:100%; margin:2rem auto; overflow:hidden;">
    <div style="background:#0D1B45; color:white; padding:2rem 1.5rem; text-align:center;">
      <h1 style="font-size:1.4rem; font-weight:700; color:white; margin:0 0 6px;">Suara Berhasil Dicatat</h1>
      <p style="font-size:0.875rem; color:rgba(255,255,255,0.7); margin:0;">
        Terima kasih telah berpartisipasi dalam voting Harapan Competition 2026.
      </p>
    </div>

    <div style="padding:1.5rem; display:flex; flex-direction:column; gap:16px;">
      <div>
        <p style="font-size:0.8rem; font-weight:700; color:#57534E; margin-bottom:8px;">Pilihan Kamu:</p>
        <div style="display:flex; flex-direction:column; gap:8px;">
          <?php foreach ($voteCompleted['chosen'] as $i => $choice): ?>
            <div style="padding:10px 12px; background:#F5F5F4; border-radius:6px; font-size:0.9rem; font-weight:600; color:#1C1917;">
              <?= ($i + 1) ?>. <?= htmlspecialchars($choice) ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <p style="font-size:0.75rem; color:#78716C;">
        Waktu vote: <?= htmlspecialchars($voteCompleted['timestamp']) ?>. Setiap siswa hanya dapat memilih satu kali.
      </p>

      <a href="votingSection.php?action=finish" class="btn-primary" style="text-decoration:none;">
        Selesai
      </a>
    </div>
  </div>

  <?php else: ?>
  <!-- ── VOTER SUMMARY HEADER ── -->
  <div class="card" style="padding:18px 20px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px;">
      <div>
        <h1 style="font-size:1.35rem; font-weight:700; color:#1C1917; margin:0 0 4px;">Pilih Penampilan Terbaik</h1>
        <p style="font-size:0.875rem; color:#57534E; margin:0;">
          Ketentuan: Wajib memilih minimal 1 dan maksimal 2 penampilan dari Kelas X, serta minimal 1 dan maksimal 2 penampilan dari Kelas XI.
        </p>
      </div>

      <div style="display:flex; align-items:center; gap:8px; font-size:0.8125rem; color:#44403C; background:#F5F5F4; padding:8px 12px; border-radius:6px; border:1px solid #E7E5E4;">
        <span>Pemilih: <strong><?= htmlspecialchars($voter['nama']) ?></strong></span>
        <span>&bull;</span>
        <span>NIS: <strong><?= htmlspecialchars($voter['nis']) ?></strong></span>
        <span>&bull;</span>
        <span>Kelas: <strong><?= htmlspecialchars($voter['kelas']) ?></strong></span>
      </div>
    </div>
  </div>

  <?php if ($alert): ?>
    <div class="alert alert-error">
      <?= htmlspecialchars($alert['msg']) ?>
    </div>
  <?php endif; ?>

  <!-- ── VOTING FORM ── -->
  <form id="formVoting" action="votingSection.php" method="POST" style="display:flex; flex-direction:column; gap:28px;">

    <?php foreach ($groupedPenampilan as $groupTitle => $items): ?>
      <?php
        $isX = ($groupTitle === 'Kelas X');
        $angkatanCode = $isX ? '10' : '11';
        $subId = $isX ? 'subX' : 'subXI';
      ?>
      <section>
        <!-- Header Tanpa Background Stabilo -->
        <div style="margin-bottom:12px; padding-bottom:8px; border-bottom:1.5px solid #E7E5E4; display:flex; justify-content:space-between; align-items:baseline; flex-wrap:wrap; gap:8px;">
          <h2 style="font-size:1.2rem; font-weight:700; color:#1C1917; margin:0;">
            Penampilan <?= htmlspecialchars($groupTitle) ?>
          </h2>
          <span id="<?= $subId ?>" style="font-size:0.8125rem; font-weight:600; color:#57534E;">
            0 dari 2 dipilih (wajib pilih minimal 1)
          </span>
        </div>

        <!-- Grid Kartu Penampilan -->
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:10px;">
          <?php foreach ($items as $p): ?>
            <label
              for="p_<?= $p['id_penampilan'] ?>"
              class="vote-card"
              data-angkatan="<?= $p['angkatan'] ?>">
              <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px;">
                <div style="flex:1;">
                  <div style="font-size:0.8125rem; font-weight:700; color:#0D1B45; margin-bottom:2px;">
                    <?= htmlspecialchars($p['nama_kelas']) ?>
                  </div>
                  <div style="font-size:1rem; font-weight:700; color:#1C1917; line-height:1.35;">
                    <?= htmlspecialchars($p['nama_penampilan']) ?>
                  </div>
                </div>
                <input
                  type="checkbox"
                  id="p_<?= $p['id_penampilan'] ?>"
                  name="pilihan_penampilan[]"
                  value="<?= $p['id_penampilan'] ?>"
                  data-angkatan="<?= $p['angkatan'] ?>"
                  class="vote-checkbox"
                  style="width:20px; height:20px; cursor:pointer; accent-color:#0D1B45; margin-top:2px; flex-shrink:0;"
                />
              </div>
            </label>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endforeach; ?>

    <input type="hidden" name="submitVoting" value="1" />
  </form>

  <!-- ── BOTTOM STICKY BAR TANPA NEON ── -->
  <div class="bottom-dock">
    <div class="dock-inner">
      <div style="display:flex; flex-direction:column; gap:2px;">
        <div style="font-size:0.875rem; color:#1C1917;">
          Kelas X: <strong id="countX">0</strong>/2 &nbsp;&bull;&nbsp;
          Kelas XI: <strong id="countXI">0</strong>/2 &nbsp;&bull;&nbsp;
          Total: <strong id="countTotal">0</strong>/4
        </div>
        <div id="helperText" style="font-size:0.75rem; color:#57534E;">
          Pilih minimal 1 penampilan di Kelas X dan 1 di Kelas XI.
        </div>
      </div>

      <button
        type="button"
        id="btnSubmit"
        disabled
        class="dock-btn">
        Kirim Suara
      </button>
    </div>
  </div>

  <?php endif; ?>

  </div>
</main>

<?php include __DIR__ . '/../src/components/footer.php'; ?>

<?php if (!$voteCompleted): ?>
<script>
(function() {
  const checkboxes = document.querySelectorAll('.vote-checkbox');
  const countXText = document.getElementById('countX');
  const countXIText = document.getElementById('countXI');
  const countTotalText = document.getElementById('countTotal');
  const subXText = document.getElementById('subX');
  const subXIText = document.getElementById('subXI');
  const btnSubmit = document.getElementById('btnSubmit');
  const helperText = document.getElementById('helperText');
  const form = document.getElementById('formVoting');

  function update() {
    const checkedX = document.querySelectorAll('.vote-checkbox[data-angkatan="10"]:checked');
    const checkedXI = document.querySelectorAll('.vote-checkbox[data-angkatan="11"]:checked');
    const cx = checkedX.length;
    const cxi = checkedXI.length;
    const total = cx + cxi;

    if (countXText) countXText.textContent = cx;
    if (countXIText) countXIText.textContent = cxi;
    if (countTotalText) countTotalText.textContent = total;

    if (subXText) {
      if (cx === 0) {
        subXText.textContent = '0 dari 2 dipilih (wajib pilih minimal 1)';
        subXText.style.color = '#57534E';
      } else if (cx === 1) {
        subXText.textContent = '1 dari 2 dipilih (bisa tambah 1 lagi)';
        subXText.style.color = '#15803D';
      } else {
        subXText.textContent = '2 dari 2 dipilih (maksimal tercapai)';
        subXText.style.color = '#0D1B45';
      }
    }

    if (subXIText) {
      if (cxi === 0) {
        subXIText.textContent = '0 dari 2 dipilih (wajib pilih minimal 1)';
        subXIText.style.color = '#57534E';
      } else if (cxi === 1) {
        subXIText.textContent = '1 dari 2 dipilih (bisa tambah 1 lagi)';
        subXIText.style.color = '#15803D';
      } else {
        subXIText.textContent = '2 dari 2 dipilih (maksimal tercapai)';
        subXIText.style.color = '#0D1B45';
      }
    }

    // Update style card & disable checkbox sisa jika sudah 2
    checkboxes.forEach(cb => {
      const card = cb.closest('.vote-card');
      const isX = (cb.dataset.angkatan === '10');
      const curCount = isX ? cx : cxi;

      if (cb.checked) {
        card.classList.add('vote-card-selected');
        card.classList.remove('vote-card-disabled');
        cb.disabled = false;
      } else if (curCount >= 2) {
        card.classList.remove('vote-card-selected');
        card.classList.add('vote-card-disabled');
        cb.disabled = true;
      } else {
        card.classList.remove('vote-card-selected', 'vote-card-disabled');
        cb.disabled = false;
      }
    });

    // Validasi tombol kirim
    const ready = (cx >= 1 && cx <= 2 && cxi >= 1 && cxi <= 2);
    if (btnSubmit) {
      btnSubmit.disabled = !ready;
    }

    if (helperText) {
      if (ready) {
        helperText.textContent = 'Syarat terpenuhi. Total ' + total + ' penampilan siap dikirim.';
        helperText.style.color = '#15803D';
      } else if (cx === 0 && cxi === 0) {
        helperText.textContent = 'Pilih minimal 1 penampilan di Kelas X dan 1 di Kelas XI.';
        helperText.style.color = '#57534E';
      } else if (cx === 0) {
        helperText.textContent = 'Belum memilih penampilan untuk Kelas X (wajib minimal 1).';
        helperText.style.color = '#B91C1C';
      } else if (cxi === 0) {
        helperText.textContent = 'Belum memilih penampilan untuk Kelas XI (wajib minimal 1).';
        helperText.style.color = '#B91C1C';
      }
    }
  }

  // Gunakan event change bawaan checkbox
  checkboxes.forEach(cb => {
    cb.addEventListener('change', update);
  });

  if (btnSubmit && form) {
    btnSubmit.addEventListener('click', function() {
      const cx = document.querySelectorAll('.vote-checkbox[data-angkatan="10"]:checked').length;
      const cxi = document.querySelectorAll('.vote-checkbox[data-angkatan="11"]:checked').length;
      if (cx < 1 || cx > 2 || cxi < 1 || cxi > 2) {
        alert('Wajib memilih minimal 1 dan maksimal 2 penampilan per angkatan.');
        return;
      }

      const ok = confirm('Konfirmasi pilihan:\n\n• Kelas X: ' + cx + ' penampilan\n• Kelas XI: ' + cxi + ' penampilan\nTotal: ' + (cx + cxi) + ' suara.\n\nKirim suara sekarang?');
      if (ok) {
        form.submit();
      }
    });
  }

  update();
})();
</script>
<?php endif; ?>

</body>
</html>
