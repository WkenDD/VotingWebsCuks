<?php
// Session start if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Koneksi ke database MySQL XAMPP
 */
function getDbConnection() {
    static $conn = null;
    if ($conn === null) {
        $host = 'localhost';
        $user = 'root';
        $pass = '';
        $db   = 'wira_harapan_vote';
        
        $conn = mysqli_connect($host, $user, $pass, $db);
        if (!$conn) {
            die("Koneksi Database Gagal: " . mysqli_connect_error());
        }
        mysqli_set_charset($conn, 'utf8mb4');
    }
    return $conn;
}

$conn = getDbConnection();

/**
 * Normalisasi string untuk pencocokan case-insensitive dan spasi rapi
 */
function normalizeString($str) {
    $str = mb_strtolower(trim($str), 'UTF-8');
    // Ganti spasi berlebih atau karakter aneh menjadi spasi tunggal
    $str = preg_replace('/\s+/', ' ', $str);
    return $str;
}

/**
 * Tahap 1: Cek apakah NIS/NISN terdaftar dan belum voting
 */
function checkNis($nis) {
    $conn = getDbConnection();
    $nisClean = trim($nis);

    $stmt = mysqli_prepare($conn, "SELECT id_siswa, no_absen, tingkat, kelas, nama, nis, has_voted, voted_at FROM siswa WHERE nis = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $nisClean);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $siswa = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$siswa) {
        return [
            'status' => 'not_found',
            'message' => 'NIS/NISN tidak ditemukan dalam daftar pemilih resmi.'
        ];
    }

    if ($siswa['has_voted']) {
        $waktu = $siswa['voted_at'] ? date('d-m-Y H:i', strtotime($siswa['voted_at'])) : '';
        return [
            'status' => 'already_voted',
            'message' => 'Anda sudah menggunakan hak suara Anda sebelumnya' . ($waktu ? " pada $waktu." : "."),
            'data' => $siswa
        ];
    }

    return [
        'status' => 'success',
        'message' => 'NIS/NISN valid. Silakan verifikasi nama lengkap dan kelas Anda.',
        'data' => $siswa
    ];
}

/**
 * Tahap 2: Verifikasi nama lengkap & kelas terhadap NIS
 */
function verifyStudent($nis, $namaInput, $kelasInput) {
    $nisCheck = checkNis($nis);
    if ($nisCheck['status'] !== 'success') {
        return $nisCheck;
    }

    $siswa = $nisCheck['data'];

    $normInputNama = normalizeString($namaInput);
    $normDbNama = normalizeString($siswa['nama']);

    $normInputKelas = normalizeString($kelasInput);
    $normDbKelas = normalizeString($siswa['kelas']);

    // Pencocokan nama: hiraukan huruf besar/kecil (case-insensitive)
    $namaMatch = ($normInputNama === $normDbNama);
    $kelasMatch = ($normInputKelas === $normDbKelas);

    if ($namaMatch && $kelasMatch) {
        // Simpan sesi pemilih sah
        $_SESSION['voter'] = [
            'id_siswa' => $siswa['id_siswa'],
            'nis' => $siswa['nis'],
            'nama' => $siswa['nama'],
            'kelas' => $siswa['kelas'],
            'tingkat' => $siswa['tingkat'],
            'verified_at' => time()
        ];

        return [
            'status' => 'verified',
            'message' => 'Identitas terverifikasi! Mengalihkan ke bilik voting...',
            'data' => $siswa
        ];
    } else {
        return [
            'status' => 'mismatch',
            'message' => 'Nama lengkap atau kelas tidak cocok dengan data NIS. Pastikan Anda memasukkan nama lengkap resmi dan kelas yang benar.'
        ];
    }
}

/**
 * Ambil daftar semua kelas unik untuk dropdown
 */
function getAllClasses() {
    $conn = getDbConnection();
    $sql = "SELECT DISTINCT kelas FROM siswa ORDER BY FIELD(tingkat, 'X', 'XI', 'XII'), kelas ASC";
    $result = mysqli_query($conn, $sql);
    $classes = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $classes[] = $row['kelas'];
    }
    return $classes;
}

/**
 * Ambil metadata genre, ikon, dan aksen warna untuk setiap pertunjukan festival
 */
function getPerformanceMeta($title, $kelas = '') {
    $t = mb_strtolower($title, 'UTF-8');
    
    if (strpos($t, 'band') !== false) {
        return [
            'genre' => 'Live Band',
            'icon' => 'music_note',
            'badge_bg' => 'bg-amber-100',
            'badge_text' => 'text-amber-900',
            'border_accent' => 'border-amber-300',
            'tag' => '🎸 Musik'
        ];
    } elseif (strpos($t, 'dj') !== false) {
        return [
            'genre' => 'DJ Performance',
            'icon' => 'headphones',
            'badge_bg' => 'bg-rose-100',
            'badge_text' => 'text-rose-900',
            'border_accent' => 'border-rose-300',
            'tag' => '🎧 Electronic'
        ];
    } elseif (strpos($t, 'drama') !== false || strpos($t, 'teater') !== false || strpos($t, 'malin') !== false || strpos($t, 'grubug') !== false) {
        return [
            'genre' => 'Teater & Drama',
            'icon' => 'theater_comedy',
            'badge_bg' => 'bg-purple-100',
            'badge_text' => 'text-purple-900',
            'border_accent' => 'border-purple-300',
            'tag' => '🎭 Panggung'
        ];
    } elseif (strpos($t, 'tari') !== false || strpos($t, 'sendratari') !== false || strpos($t, 'jayaprana') !== false || strpos($t, 'barong') !== false || strpos($t, 'tresna') !== false || strpos($t, 'poleng') !== false) {
        return [
            'genre' => 'Seni Tari',
            'icon' => 'palette',
            'badge_bg' => 'bg-emerald-100',
            'badge_text' => 'text-emerald-900',
            'border_accent' => 'border-emerald-300',
            'tag' => '🪭 Tradisional'
        ];
    } elseif (strpos($t, 'dance') !== false || strpos($t, 'cheer') !== false) {
        return [
            'genre' => 'Modern Dance',
            'icon' => 'bolt',
            'badge_bg' => 'bg-indigo-100',
            'badge_text' => 'text-indigo-900',
            'border_accent' => 'border-indigo-300',
            'tag' => '⚡ Dance'
        ];
    } elseif (strpos($t, 'flashmob') !== false) {
        return [
            'genre' => 'Flashmob Kreasi',
            'icon' => 'celebration',
            'badge_bg' => 'bg-cyan-100',
            'badge_text' => 'text-cyan-900',
            'border_accent' => 'border-cyan-300',
            'tag' => '🎪 Flashmob'
        ];
    } else {
        return [
            'genre' => 'Karya Seni Pertunjukan',
            'icon' => 'stars',
            'badge_bg' => 'bg-blue-100',
            'badge_text' => 'text-blue-900',
            'border_accent' => 'border-blue-300',
            'tag' => '✨ Show'
        ];
    }
}

function getActivePenampilan() {
    $conn = getDbConnection();
    $sql = "SELECT * FROM penampilan WHERE status = 'aktif' ORDER BY angkatan ASC, nama_kelas ASC, id_penampilan ASC";
    $result = mysqli_query($conn, $sql);
    $penampilan = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $penampilan[] = $row;
    }
    return $penampilan;
}

/**
 * Proses pengiriman suara:
 * - Wajib minimal 1 dan maksimal 2 penampilan untuk Kelas X (10)
 * - Wajib minimal 1 dan maksimal 2 penampilan untuk Kelas XI (11)
 * - Total pilihan: minimal 2 dan maksimal 4 penampilan
 */
function submitVote($id_siswa, array $penampilanIds) {
    $conn = getDbConnection();

    // Validasi dasar jumlah pilihan total (antara 2 sampai 4)
    $totalCount = count($penampilanIds);
    if ($totalCount < 2 || $totalCount > 4) {
        return [
            'status' => 'error',
            'message' => 'Total pilihan wajib antara 2 hingga 4 penampilan (minimal 1 dan maksimal 2 penampilan per angkatan).'
        ];
    }

    // Pastikan siswa belum pernah vote
    $stmt = mysqli_prepare($conn, "SELECT has_voted FROM siswa WHERE id_siswa = ? FOR UPDATE");
    mysqli_stmt_bind_param($stmt, "i", $id_siswa);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $siswa = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if (!$siswa) {
        return ['status' => 'error', 'message' => 'Data siswa tidak valid.'];
    }

    if ($siswa['has_voted']) {
        return [
            'status' => 'error',
            'message' => 'Hak suara untuk akun ini sudah digunakan. Voting tidak dapat diulangi.'
        ];
    }

    // Verifikasi angkatan dari setiap penampilan yang dipilih
    $cleanIds = array_map('intval', $penampilanIds);
    $inClause = implode(',', $cleanIds);
    $queryP = "SELECT id_penampilan, nama_kelas, angkatan, nama_penampilan FROM penampilan WHERE id_penampilan IN ($inClause) AND status = 'aktif'";
    $resP = mysqli_query($conn, $queryP);

    $penampilanX = [];
    $penampilanXI = [];
    $chosenList = [];

    while ($p = mysqli_fetch_assoc($resP)) {
        $chosenList[] = "[Kelas {$p['angkatan']}] {$p['nama_kelas']} - {$p['nama_penampilan']}";
        if ($p['angkatan'] == '10') {
            $penampilanX[] = $p;
        } elseif ($p['angkatan'] == '11') {
            $penampilanXI[] = $p;
        }
    }

    // Validasi aturan angkatan
    $countX = count($penampilanX);
    $countXI = count($penampilanXI);

    if ($countX < 1 || $countX > 2) {
        return [
            'status' => 'error',
            'message' => "Pilihan Kelas X tidak valid: Anda memilih $countX penampilan (Wajib minimal 1 dan maksimal 2 penampilan untuk Kelas X)."
        ];
    }

    if ($countXI < 1 || $countXI > 2) {
        return [
            'status' => 'error',
            'message' => "Pilihan Kelas XI tidak valid: Anda memilih $countXI penampilan (Wajib minimal 1 dan maksimal 2 penampilan untuk Kelas XI)."
        ];
    }

    // Transaksi Database
    mysqli_begin_transaction($conn);

    try {
        foreach ($cleanIds as $idP) {
            $stmtVote = mysqli_prepare($conn, "INSERT INTO vote (id_siswa, id_penampilan) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmtVote, "ii", $id_siswa, $idP);
            mysqli_stmt_execute($stmtVote);
            mysqli_stmt_close($stmtVote);

            mysqli_query($conn, "UPDATE penampilan SET total_votes = total_votes + 1 WHERE id_penampilan = $idP");
        }

        // Tandai siswa telah voting
        $stmtSiswa = mysqli_prepare($conn, "UPDATE siswa SET has_voted = 1, voted_at = NOW() WHERE id_siswa = ?");
        mysqli_stmt_bind_param($stmtSiswa, "i", $id_siswa);
        mysqli_stmt_execute($stmtSiswa);
        mysqli_stmt_close($stmtSiswa);

        mysqli_commit($conn);

        // Bersihkan sesi voter agar tidak bisa submit ulang
        unset($_SESSION['voter']);
        $_SESSION['vote_completed'] = [
            'timestamp' => date('d M Y, H:i:s'),
            'chosen' => $chosenList,
            'countX' => $countX,
            'countXI' => $countXI
        ];

        return [
            'status' => 'success',
            'message' => 'Suara Anda berhasil disimpan! Terima kasih telah berpartisipasi.',
            'chosen' => $chosenList
        ];
    } catch (Exception $e) {
        mysqli_rollback($conn);
        return [
            'status' => 'error',
            'message' => 'Terjadi kesalahan sistem saat menyimpan suara: ' . $e->getMessage()
        ];
    }
}

/**
 * Ambil data statistik real-time perolehan voting untuk Live View
 */
function getLiveVotingStats() {
    $conn = getDbConnection();

    // 1. Total siswa & total siswa yang sudah memilih
    $qSiswa = mysqli_query($conn, "SELECT COUNT(*) AS total_siswa, SUM(has_voted) AS total_sudah_vote FROM siswa");
    $dataSiswa = mysqli_fetch_assoc($qSiswa);
    $totalSiswa = intval($dataSiswa['total_siswa']);
    $totalSudahVote = intval($dataSiswa['total_sudah_vote']);
    $persenPartisipasi = $totalSiswa > 0 ? round(($totalSudahVote / $totalSiswa) * 100, 1) : 0;

    // 2. Total suara masuk di tabel vote
    $qVote = mysqli_query($conn, "SELECT COUNT(*) AS total_suara FROM vote");
    $dataVote = mysqli_fetch_assoc($qVote);
    $totalSuara = intval($dataVote['total_suara']);

    // 3. Perolehan per penampilan (urutkan per angkatan dan perolehan suara terbanyak)
    $qPenampilan = mysqli_query($conn, "SELECT id_penampilan, nama_kelas, angkatan, nama_penampilan, total_votes FROM penampilan WHERE status = 'aktif' ORDER BY angkatan ASC, total_votes DESC, nama_kelas ASC");

    $listX = [];
    $listXI = [];
    $maxVotesX = 0;
    $maxVotesXI = 0;

    while ($row = mysqli_fetch_assoc($qPenampilan)) {
        $row['total_votes'] = intval($row['total_votes']);
        if ($row['angkatan'] == '10') {
            $listX[] = $row;
            if ($row['total_votes'] > $maxVotesX) {
                $maxVotesX = $row['total_votes'];
            }
        } else {
            $listXI[] = $row;
            if ($row['total_votes'] > $maxVotesXI) {
                $maxVotesXI = $row['total_votes'];
            }
        }
    }

    return [
        'timestamp' => date('H:i:s'),
        'total_siswa' => $totalSiswa,
        'total_sudah_vote' => $totalSudahVote,
        'persen_partisipasi' => $persenPartisipasi,
        'total_suara' => $totalSuara,
        'max_votes_x' => $maxVotesX,
        'max_votes_xi' => $maxVotesXI,
        'kelas_x' => $listX,
        'kelas_xi' => $listXI
    ];
}
