<?php

/**
 * EroCMS Video Streamer & Remote Failover Controller
 *
 * Mantiq:
 * 1. Agar video server diskida mavjud bo'lsa -> serverdagi lokal MP4 o'ynatiladi.
 * 2. Agar foydalanuvchi/admin serverdagi MP4 fayllarni o'chirib yuborgan bo'lsa ->
 *    avtomatik tarzda parserdagi ota saytning remote_url / recoil havolasiga 302 yo'naltiriladi!
 *    Natijada pleyerda hech qachon 404 xatosi chiqmaydi, videolar doimo ochiladi!
 */

$video_raw = filter($_GET['video'] ?? '');
$translit = preg_replace('/\.mp4$/i', '', $video_raw);

if (empty($translit)) {
    header('Location: /');
    exit;
}

$stmt = $mysqli->query("SELECT id, address, translit, recoil, embed, remote_url, yd FROM ero_files WHERE translit = '" . mysqli_real_escape_string($mysqli, $translit) . "' LIMIT 1");
$view = ($stmt) ? $stmt->fetch_assoc() : null;

if (!$view) {
    header('Location: /');
    exit;
}

$doc_root = !empty($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/') : dirname(__DIR__);
$address = trim($view['address'] ?? '');

// 1. Agar lokal fayl diskda haqiqatan mavjud bo'lsa
if (!empty($address) && strpos($address, 'http://') !== 0 && strpos($address, 'https://') !== 0 && strpos($address, '<') === false) {
    $local_path = $doc_root . $address;
    if (is_file($local_path) && @filesize($local_path) > 1000) {
        header('Location: ' . $address);
        exit;
    }
}

// 2. Lokal fayl diskda topilmadi (o'chirilgan) -> REMOTE FAILOVER (Ota saytdan video ko'rsatish)
$remote = '';
if (!empty($view['remote_url']) && (strpos($view['remote_url'], 'http://') === 0 || strpos($view['remote_url'], 'https://') === 0)) {
    $remote = $view['remote_url'];
} elseif (!empty($view['recoil']) && (strpos($view['recoil'], 'http://') === 0 || strpos($view['recoil'], 'https://') === 0)) {
    $remote = $view['recoil'];
} elseif (!empty($view['address']) && (strpos($view['address'], 'http://') === 0 || strpos($view['address'], 'https://') === 0)) {
    $remote = $view['address'];
} elseif (!empty($view['embed']) && (strpos($view['embed'], 'http://') === 0 || strpos($view['embed'], 'https://') === 0)) {
    $remote = $view['embed'];
}

if (!empty($remote)) {
    header('Location: ' . $remote, true, 302);
    exit;
}

// 3. Fallback: agar hech qanday manba bo'lmasa bosh sahifaga
header('Location: /');
exit;