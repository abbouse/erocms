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

// Singan yoki 404 bo'lgan skrinshotlarni avtomatik tuzatish
if (function_exists('parser_repair_broken_screenshots')) {
    $repaired = parser_repair_broken_screenshots($mysqli);
    if ($repaired > 0) {
        $results[] = "[Repair] {$repaired} ta video skrinshoti tiklandi.";
    }
}

// 1. sexlar.link dan eng yangi videolar
$sexlar_items = parser_get_catalog_links_sexlar(1);
$donor_items = [];
foreach ($sexlar_items as $item) {
    $url = is_array($item) ? ($item['url'] ?? '') : (string)$item;
    if (!empty($url)) {
        $donor_items[] = [
            'donor'         => 'sexlar',
            'url'           => $url,
            'poster'        => is_array($item) ? ($item['poster'] ?? '') : '',
            'duration'      => is_array($item) ? ($item['duration'] ?? '05:00') : '05:00',
            'title'         => is_array($item) ? ($item['title'] ?? '') : '',
            'category_hint' => is_array($item) ? ($item['category_hint'] ?? '') : ''
        ];
    }
}

// 2. uzbxx.ru dan eng yangi videolar
$uzbxx_items = parser_get_catalog_links_uzbxx(1);
foreach ($uzbxx_items as $item) {
    $url = is_array($item) ? ($item['url'] ?? '') : (string)$item;
    if (!empty($url)) {
        $donor_items[] = [
            'donor'         => 'uzbxx',
            'url'           => $url,
            'poster'        => is_array($item) ? ($item['poster'] ?? '') : '',
            'duration'      => is_array($item) ? ($item['duration'] ?? '05:00') : '05:00',
            'title'         => is_array($item) ? ($item['title'] ?? '') : '',
            'category_hint' => is_array($item) ? ($item['category_hint'] ?? '') : ''
        ];
    }
}

// 3. uzporno.website dan eng yangi videolar
$uzporno_items = parser_get_catalog_links_uzporno(1);
foreach ($uzporno_items as $item) {
    $url = is_array($item) ? ($item['url'] ?? '') : (string)$item;
    if (!empty($url)) {
        $donor_items[] = [
            'donor'         => 'uzporno',
            'url'           => $url,
            'poster'        => is_array($item) ? ($item['poster'] ?? '') : '',
            'duration'      => is_array($item) ? ($item['duration'] ?? '05:00') : '05:00',
            'title'         => is_array($item) ? ($item['title'] ?? '') : '',
            'category_hint' => is_array($item) ? ($item['category_hint'] ?? '') : ''
        ];
    }
}

// 4. arhivporno.watch dan eng yangi videolar
$arhiv_items = parser_get_catalog_links_arhivporno(1);
foreach ($arhiv_items as $item) {
    $url = is_array($item) ? ($item['url'] ?? '') : (string)$item;
    if (!empty($url)) {
        $donor_items[] = [
            'donor'         => 'arhivporno',
            'url'           => $url,
            'poster'        => is_array($item) ? ($item['poster'] ?? '') : '',
            'duration'      => is_array($item) ? ($item['duration'] ?? '05:00') : '05:00',
            'title'         => is_array($item) ? ($item['title'] ?? '') : '',
            'category_hint' => is_array($item) ? ($item['category_hint'] ?? '') : ''
        ];
    }
}

// Har xil donorlardan aralash yuklash uchun
shuffle($donor_items);

foreach ($donor_items as $item) {
    if ($added >= $max_cron_added) {
        break;
    }

    $res = null;
    if ($item['donor'] === 'sexlar') {
        $res = parse_video_sexlar($item['url'], 0, 'stream', $mysqli, $settings, $width_S, $height_S, $item);
    } elseif ($item['donor'] === 'uzbxx') {
        $res = parse_video_uzbxx($item['url'], 0, 'stream', $mysqli, $settings, $width_S, $height_S, $item);
    } elseif ($item['donor'] === 'uzporno') {
        $res = parse_video_uzporno($item['url'], 0, 'stream', $mysqli, $settings, $width_S, $height_S, $item);
    } elseif ($item['donor'] === 'arhivporno') {
        $res = parse_video_arhivporno($item['url'], 0, 'stream', $mysqli, $settings, $width_S, $height_S, $item);
    }

    if ($res) {
        $results[] = "[{$item['donor']}] {$res['status']}: " . strip_tags($res['message'] ?? ($res['title'] ?? ''));
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
