<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        echo "\nFATAL_SHUTDOWN: " . $err['message'] . " in " . $err['file'] . ":" . $err['line'] . "\n";
    }
});

header('X-Debug-Version: ' . PHP_VERSION);

/*
 * erocms Avtomatik Fon Parseri (CRON)
 * Ishga tushirish: /autocomplete.php?key={CRON_KEY}
 */

require_once __DIR__ . '/core/Functions.php';
require_once __DIR__ . '/core/ParserEngine.php';

// Kalit tekshiruvi
$key = $settings['cron'] ?? '';
if (empty($key) || !isset($_GET['key']) || $key !== $_GET['key']) {
    header('HTTP/1.1 403 Forbidden');
    exit('The key is invalid');
}

// Har bir cron chaqiruvida maksimal qo'shiladigan videolar soni
$max_cron_added = 6;
$added = 0;
$results = [];

// 1. uzbxx.ru dan eng yangi videolar
$uzbxx_links = parser_get_catalog_links_uzbxx(1);
$donor_items = [];
foreach ($uzbxx_links as $l) {
    $donor_items[] = ['donor' => 'uzbxx', 'url' => $l];
}

// 2. uzporno.website dan eng yangi videolar
$uzporno_links = parser_get_catalog_links_uzporno(1);
foreach ($uzporno_links as $l) {
    $donor_items[] = ['donor' => 'uzporno', 'url' => $l];
}

// 3. arhivporno.watch dan eng yangi videolar
$arhiv_items = parser_get_catalog_links_arhivporno(1);
foreach ($arhiv_items as $item) {
    $donor_items[] = [
        'donor' => 'arhivporno',
        'url' => $item['url'],
        'poster' => $item['poster'] ?? '',
        'duration' => $item['duration'] ?? '05:00'
    ];
}

// Har xil donorlardan aralash yuklash uchun
shuffle($donor_items);

foreach ($donor_items as $item) {
    if ($added >= $max_cron_added) {
        break;
    }

    $res = null;
    if ($item['donor'] === 'uzbxx') {
        $res = parse_video_uzbxx($item['url'], 0, 'stream', $mysqli, $settings, $width_S, $height_S);
    } elseif ($item['donor'] === 'uzporno') {
        $res = parse_video_uzporno($item['url'], 0, 'stream', $mysqli, $settings, $width_S, $height_S);
    } elseif ($item['donor'] === 'arhivporno') {
        $res = parse_video_arhivporno($item['url'], 0, 'stream', $mysqli, $settings, $width_S, $height_S, $item);
    }

    if ($res) {
        $results[] = "[{$item['donor']}] {$res['status']}: " . strip_tags($res['message']);
        if ($res['status'] === 'success') {
            $added++;
        }
    }
    usleep(200000); // 0.2s pauza
}

// Keshni tozalash
$doc_root = $_SERVER['DOCUMENT_ROOT'] ?? __DIR__;
@array_map('unlink', glob($doc_root . '/content/cache/*.html'));

header('Content-Type: text/plain; charset=utf-8');
echo "CRON SUCCESS: Added {$added} new videos.\n";
echo "Details:\n" . implode("\n", $results) . "\n";
