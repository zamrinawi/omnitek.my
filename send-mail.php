<?php
/**
 * send-mail.php
 * Backend handler ringkas untuk borang hubungi di index.html.
 * Guna fungsi mail() bawaan PHP (cPanel/sendmail) — tiada SMTP setup diperlukan.
 */

header('Content-Type: application/json; charset=utf-8');

// Hanya benarkan permintaan POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Kaedah tidak dibenarkan.']);
    exit;
}

// Honeypot anti-spam — jika medan tersembunyi ini diisi, ia kemungkinan bot
if (!empty($_POST['website'])) {
    echo json_encode(['success' => true]);
    exit;
}

// Ambil & bersihkan input
$nama     = trim($_POST['Nama'] ?? '');
$syarikat = trim($_POST['Syarikat'] ?? '');
$emel     = trim($_POST['Emel'] ?? '');
$jenis    = trim($_POST['Jenis_Sistem'] ?? '');
$mesej    = trim($_POST['Mesej'] ?? '');

// Validasi medan wajib
if ($nama === '' || $emel === '' || $mesej === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Sila lengkapkan semua medan wajib.']);
    exit;
}

// Validasi format emel
if (!filter_var($emel, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Alamat emel tidak sah.']);
    exit;
}

// Elak header injection pada nama/subjek
$nama = str_replace(["\r", "\n"], '', $nama);

$to      = 'hello@omnitek.my';
$subject = 'Pertanyaan Baru dari Laman Web - ' . $nama;

$body  = "Pertanyaan baru diterima daripada laman web OmniTek:\n\n";
$body .= "Nama: $nama\n";
$body .= "Syarikat/Organisasi: " . ($syarikat !== '' ? $syarikat : '-') . "\n";
$body .= "Emel: $emel\n";
$body .= "Jenis Sistem Diperlukan: " . ($jenis !== '' ? $jenis : '-') . "\n\n";
$body .= "Mesej:\n$mesej\n";

$headers   = [];
$headers[] = 'From: OmniTek Website <no-reply@omnitek.my>';
$headers[] = 'Reply-To: ' . $emel;
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-Type: text/plain; charset=UTF-8';

$sent = @mail($to, $subject, $body, implode("\r\n", $headers));

if ($sent) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal menghantar. Sila cuba lagi atau emel terus ke hello@omnitek.my.']);
}
