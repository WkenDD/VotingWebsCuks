<?php
require_once __DIR__ . '/../controller/functions.php';

// Cek apakah klien meminta SSE Stream atau JSON biasa
$isStream = isset($_GET['stream']) && $_GET['stream'] == '1';

if ($isStream) {
    // Mode Server-Sent Events (SSE) - streaming data langsung
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');
    header('X-Accel-Buffering: no');

    // Hindari timeout eksekusi PHP pada streaming
    set_time_limit(60);

    // Kirim data berkala selama koneksi aktif (hingga 50 iterasi / ~50 detik sebelum reconnect otomatis)
    for ($i = 0; $i < 25; $i++) {
        if (connection_aborted()) {
            break;
        }

        $stats = getLiveVotingStats();
        echo "event: voteUpdate\n";
        echo "data: " . json_encode($stats) . "\n\n";

        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();

        sleep(2);
    }
    exit;
} else {
    // Mode JSON response standar
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo json_encode(getLiveVotingStats());
    exit;
}
