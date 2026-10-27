<?php
require_once __DIR__ . '/../controller/functions.php';

$step = 1;
$alert = null;
$nisInput = '';
$namaInput = '';
$kelasInput = '';
$classList = getAllClasses();

if (!empty($_SESSION['voter'])) {
    header("Location: votingSection.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'check_nis') {
        $nisInput = trim($_POST['nis'] ?? '');
        if ($nisInput === '') {
            $alert = ['type' => 'error', 'msg' => 'Silakan masukkan NIS terlebih dahulu.'];
        } else {
            $check = checkNis($nisInput);
            if ($check['status'] === 'success') {
                $step = 2;
                $alert = ['type' => 'success', 'msg' => 'NIS terdaftar. Silakan masukkan nama lengkap dan pilih kelas Anda.'];
            } elseif ($check['status'] === 'already_voted') {
                $alert = ['type' => 'warning', 'msg' => $check['message']];
            } else {
                $alert = ['type' => 'error', 'msg' => $check['message']];
            }
        }
    } elseif ($action === 'verify_student') {
        $nisInput  = trim($_POST['nis']   ?? '');
        $namaInput = trim($_POST['nama']  ?? '');
        $kelasInput = trim($_POST['kelas'] ?? '');

        if (!$nisInput || !$namaInput || !$kelasInput) {
            $step = 2;
            $alert = ['type' => 'error', 'msg' => 'Semua kolom wajib diisi.'];
        } else {
            $verify = verifyStudent($nisInput, $namaInput, $kelasInput);
            if ($verify['status'] === 'verified') {
                header("Location: votingSection.php");
                exit;
            } else {
                $step = 2;
                $alert = [
                    'type' => 'error',
                    'msg'  => $verify['message']
                ];
            }
        }
    } elseif ($action === 'reset_step') {
        $step = 1;
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Verifikasi Pemilih</title>
  <link rel="stylesheet" href="../src/output.css" />
</head>
<body class="page-wrapper">

<?php include __DIR__ . '/../src/components/navbar.php'; ?>

<main class="page-content">
  <div style="width:100%; max-width:440px; margin-inline:auto; display:flex; flex-direction:column; gap:16px;">

    <div>
      <h1 style="font-size:1.45rem; font-weight:700; color:#1C1917; margin:0 0 4px;">Verifikasi Pemilih</h1>
      <p style="font-size:0.875rem; color:#57534E; margin:0;">
        Pentas Seni Harapan Competition 2026 SMKS Wira Harapan
      </p>
    </div>


    <?php if ($alert): ?>
      <?php
        $alertClass = match($alert['type']) {
          'error'   => 'alert-error',
          'success' => 'alert-success',
          'warning' => 'alert-warning',
          default   => 'alert-error'
        };
      ?>
      <div class="alert <?= $alertClass ?>">
        <div><?= htmlspecialchars($alert['msg']) ?></div>
      </div>
    <?php endif; ?>

    <!-- Form Card -->
    <div class="card" style="padding:20px;">
      <?php if ($step === 1): ?>
        <!-- ── LANGKAH 1: INPUT NIS ── -->
        <h2 style="font-size:1.1rem; font-weight:700; color:#1C1917; margin:0 0 6px;">Langkah 1: Masukkan NIS</h2>
        <p style="font-size:0.8125rem; color:#57534E; margin:0 0 16px;">
          Masukkan Nomor Induk Siswa yang tertera pada kartu pelajar atau data presensi kelas.
        </p>

        <form action="verifikasi.php" method="POST" style="display:flex; flex-direction:column; gap:16px;">
          <input type="hidden" name="action" value="check_nis" />

          <div>
            <label for="nis" class="field-label">Nomor Induk Siswa (NIS)</label>
            <input
              type="text"
              id="nis"
              name="nis"
              value="<?= htmlspecialchars($nisInput) ?>"
              placeholder="Contoh: 8355"
              class="field-input"
              autofocus
              required
            />
          </div>

          <button type="submit" class="btn-primary">
            Lanjut
          </button>
        </form>

      <?php else: ?>
        <!-- ── LANGKAH 2: VERIFIKASI NAMA & KELAS ── -->
        <h2 style="font-size:1.1rem; font-weight:700; color:#1C1917; margin:0 0 6px;">Langkah 2: Konfirmasi Nama &amp; Kelas</h2>
        <p style="font-size:0.8125rem; color:#57534E; margin:0 0 14px;">
          NIS: <strong><?= htmlspecialchars($nisInput) ?></strong> &bull;
          <a href="verifikasi.php" style="color:#0D1B45; text-decoration:underline;">Ganti NIS</a>
        </p>

        <form action="verifikasi.php" method="POST" style="display:flex; flex-direction:column; gap:16px;">
          <input type="hidden" name="action" value="verify_student" />
          <input type="hidden" name="nis" value="<?= htmlspecialchars($nisInput) ?>" />

          <div>
            <label for="nama" class="field-label">Nama Lengkap Siswa</label>
            <input
              type="text"
              id="nama"
              name="nama"
              value="<?= htmlspecialchars($namaInput) ?>"
              placeholder="Masukkan nama lengkap..."
              class="field-input"
              autofocus
              required
            />
            <p style="font-size:0.75rem; color:#78716C; margin-top:4px;">
              Bebas menggunakan huruf kecil atau kapital, asalkan ejaan nama sesuai data sekolah.
            </p>
          </div>

          <div>
            <label for="kelas" class="field-label">Kelas</label>
            <select id="kelas" name="kelas" class="field-select" required>
              <option value="">-- Pilih Kelas --</option>
              <?php foreach ($classList as $cls): ?>
                <option value="<?= htmlspecialchars($cls) ?>" <?= $kelasInput === $cls ? 'selected' : '' ?>>
                  <?= htmlspecialchars($cls) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div style="display:flex; gap:8px;">
            <button type="submit" name="action" value="reset_step" class="btn-secondary" style="width:auto;">
              Kembali
            </button>
            <button type="submit" class="btn-primary" style="flex:1;">
              Masuk ke Bilik Suara
            </button>
          </div>
        </form>
      <?php endif; ?>
    </div>

  </div>
</main>

<?php include __DIR__ . '/../src/components/footer.php'; ?>
</body>
</html>
