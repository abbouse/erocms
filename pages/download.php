<?php

/**
 * EroCMS Video Yuklab Olish (Download Controller with Remote Fallback)
 */

$translit_raw = filter($_GET['translit'] ?? '');
$translit = preg_replace('/\.mp4$/i', '', $translit_raw);

if (empty($translit)) {
    header('Location: /');
    exit;
}

$stmt = $mysqli->query("SELECT id, address, translit, recoil, embed, remote_url FROM ero_files WHERE translit = '" . mysqli_real_escape_string($mysqli, $translit) . "' LIMIT 1");
$view = ($stmt) ? $stmt->fetch_assoc() : null;

if (!$view) {
    header('Location: /');
    exit;
}

$mysqli->query("UPDATE ero_files SET downloads = downloads + 1, score = score + 15 WHERE id = '{$view['id']}'");
if (function_exists('track_activity')) {
    track_activity('download', $view['id']);
}

$doc_root = !empty($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/') : dirname(__DIR__);
$address = trim($view['address'] ?? '');

// 1. Agar server diskida lokal fayl mavjud bo'lsa
if (!empty($address) && strpos($address, 'http://') !== 0 && strpos($address, 'https://') !== 0 && strpos($address, '<') === false) {
    $local_path = $doc_root . $address;
    if (is_file($local_path) && @filesize($local_path) > 1000) {
        if (!empty($settings['recoil']) && $settings['recoil'] == 1) {
            header('Location: ' . $address);
            exit;
        }

        $filesize = filesize($local_path);
        if (ob_get_level()) ob_end_clean();

        $download_filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $translit) . '_' . preg_replace('/[^a-zA-Z0-9.-]/', '', filter($_SERVER['SERVER_NAME'] ?? 'sekschi')) . '.mp4';

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $download_filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . $filesize);
        readfile($local_path);
        exit;
    }
}

// 2. Lokal fayl diskda topilmadi (o'chirilgan) -> REMOTE FAILOVER (Ota saytdan yuklab olish)
$remote = '';
if (!empty($view['remote_url']) && (strpos($view['remote_url'], 'http://') === 0 || strpos($view['remote_url'], 'https://') === 0)) {
    $remote = $view['remote_url'];
} elseif (!empty($view['recoil']) && (strpos($view['recoil'], 'http://') === 0 || strpos($view['recoil'], 'https://') === 0)) {
    $remote = $view['recoil'];
} elseif (!empty($view['address']) && (strpos($view['address'], 'http://') === 0 || strpos($view['address'], 'https://') === 0)) {
    $remote = $view['address'];
}

if (!empty($remote)) {
    header('Location: ' . $remote, true, 302);
    exit;
}

// 3. Fallback: video sahifasiga qaytarish
header('Location: /watch/' . $translit . '.html');
exit;