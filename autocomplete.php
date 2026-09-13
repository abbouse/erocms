<?php

/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/

require 'core/Functions.php';

$key = $settings['cron'];
if (empty($key) || !isset($_GET['key']) || $key !== $_GET['key']) {
    exit('The key is invalid');
}

// Avtomatik parser funksiyalari
function cron_get_html($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

$all_links = [];

// uzbxx.ru dan eng yangilar
$h1 = cron_get_html('https://uzbxx.ru/');
preg_match_all("|<a href=\"(https://uzbxx.ru/video/[^\"]+)\"|i", $h1, $m1);
if (!empty($m1[1])) {
    foreach (array_unique($m1[1]) as $link) {
        $all_links[] = ['donor' => 'uzbxx', 'url' => $link];
    }
}

// uzporno.website dan eng yangilar
$h2 = cron_get_html('https://uzporno.website/');
preg_match_all("|<a href=\"(https://uzporno.website/video/[^\"]+)\"|i", $h2, $m2);
if (!empty($m2[1])) {
    foreach (array_unique($m2[1]) as $link) {
        $all_links[] = ['donor' => 'uzporno', 'url' => $link];
    }
}

$added = 0;
$max = 5; // Har bir cron chaqiruvida 5 tagacha yangi video

foreach ($all_links as $item) {
    if ($added >= $max) break;

    $html = cron_get_html($item['url']);
    if (empty($html) || strlen($html) < 200) continue;

    $title = '';
    if (preg_match("|<div class=\"xxxhd-title-top\">.*?<h1>(.*?)</h1>|is", $html, $m)) {
        $title = trim(strip_tags($m[1]));
    } elseif (preg_match("|<title>(.*?)</title>|is", $html, $m)) {
        $title = trim(strip_tags($m[1]));
    }
    $title = preg_replace("/('|\"|\r?\n)/", '', $title);
    if (empty($title) || stripos($title, 'can’t be reached') !== false) continue;

    $uniqueness = md5($title);
    $check = $mysqli->query("SELECT id FROM ero_files WHERE uniqueness = '$uniqueness' LIMIT 1");
    if ($check && $check->num_rows > 0) continue;

    $duration = '05:00';
    if (preg_match("|<i class=\"fa fa-clock-o\"></i>\s*<b>(.*?)</b>|is", $html, $m)) {
        $duration = trim($m[1]);
    }

    $poster = '';
    if (preg_match("|itemprop=\"thumbnailUrl\" href=\"(.*?)\"|is", $html, $m)) {
        $poster = trim($m[1]);
    } elseif (preg_match("|data-original=\"(https://uzporno.website/files/screens/[^\"]+)\"|i", $html, $m)) {
        $poster = $m[1];
    }

    $video_src = $item['url'];
    if (preg_match("|file:\"(.*?)\"|is", $html, $m)) {
        $video_src = trim($m[1]);
    }

    $rand_id = rand(100, 9999);
    $md5 = md5(microtime(true) . $rand_id);
    $translit = str_replace([' ', '/', '\\', '\''], '_', transliterate($title)) . '_' . $rand_id;
    $translit = preg_replace('/[^a-zA-Z0-9_-]/', '', $translit);

    $local_screenshot = '/content/screenshots/' . $md5 . '.jpg';
    if (!empty($poster)) {
        $img_data = cron_get_html($poster);
        if (!empty($img_data) && strlen($img_data) > 500) {
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . $local_screenshot, $img_data);
        }
    }

    $now = time();
    $mysqli->query("INSERT INTO ero_files (
        name, description, screenshot, recoil, tags, translit, duration, downloads, 
        server, address, uniqueness, category, view, date, rewriting, added, yd, embed
    ) VALUES (
        '".mysqli_real_escape_string($mysqli, $title)."',
        '".mysqli_real_escape_string($mysqli, $title)."',
        '".mysqli_real_escape_string($mysqli, $local_screenshot)."',
        '".mysqli_real_escape_string($mysqli, $video_src)."',
        'узбек секс uzbekcha',
        '".mysqli_real_escape_string($mysqli, $translit)."',
        '".mysqli_real_escape_string($mysqli, $duration)."',
        '0',
        '{$item['donor']}',
        '".mysqli_real_escape_string($mysqli, $video_src)."',
        '".mysqli_real_escape_string($mysqli, $uniqueness)."',
        '1',
        '0',
        '$now',
        '0',
        'cron',
        '0',
        '".mysqli_real_escape_string($mysqli, $video_src)."'
    )");

    $added++;
    echo "[+] Qo‘shildi: $title\n";
}

@array_map('unlink', glob($_SERVER['DOCUMENT_ROOT'].'/content/cache/*.html'));
echo "Tugadi! Qo‘shilgan videolar: $added\n";
