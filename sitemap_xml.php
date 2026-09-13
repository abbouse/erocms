<?php

/*
SEO Video Sitemap Generator for Google and Yandex
*/

require_once 'core/Functions.php';

header('Content-Type: application/xml; charset=utf-8');

$host = filter($_SERVER['HTTP_HOST'] ?? 'sekschi.online');
$base_url = 'https://' . rtrim($host, '/');

$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">' . "\n";

// 1. Asosiy sahifalar
$xml .= "  <url>\n";
$xml .= "    <loc>{$base_url}/</loc>\n";
$xml .= "    <changefreq>hourly</changefreq>\n";
$xml .= "    <priority>1.0</priority>\n";
$xml .= "  </url>\n";

$xml .= "  <url>\n";
$xml .= "    <loc>{$base_url}/new.html</loc>\n";
$xml .= "    <changefreq>daily</changefreq>\n";
$xml .= "    <priority>0.9</priority>\n";
$xml .= "  </url>\n";

$xml .= "  <url>\n";
$xml .= "    <loc>{$base_url}/top.html</loc>\n";
$xml .= "    <changefreq>daily</changefreq>\n";
$xml .= "    <priority>0.9</priority>\n";
$xml .= "  </url>\n";

$xml .= "  <url>\n";
$xml .= "    <loc>{$base_url}/category.html</loc>\n";
$xml .= "    <changefreq>weekly</changefreq>\n";
$xml .= "    <priority>0.8</priority>\n";
$xml .= "  </url>\n";

// 2. Bo'limlar / Kategoriyalar
$cats_q = $mysqli->query("SELECT translit FROM ero_categories ORDER BY id ASC");
if ($cats_q) {
    while ($c = $cats_q->fetch_assoc()) {
        $xml .= "  <url>\n";
        $xml .= "    <loc>{$base_url}/{$c['translit']}/</loc>\n";
        $xml .= "    <changefreq>daily</changefreq>\n";
        $xml .= "    <priority>0.8</priority>\n";
        $xml .= "  </url>\n";
    }
    $cats_q->free();
}

// 3. Videolar (Google Video Sitemap formati)
$videos_q = $mysqli->query("SELECT name, description, screenshot, translit, duration, view, date FROM ero_files WHERE date < '".time()."' ORDER BY id DESC LIMIT 5000");

if ($videos_q) {
    while ($v = $videos_q->fetch_assoc()) {
        $v_url = "{$base_url}/watch/{$v['translit']}.html";
        $thumb_url = (strpos($v['screenshot'], 'http') === 0) ? $v['screenshot'] : $base_url . $v['screenshot'];
        
        $v_title = htmlspecialchars($v['name'], ENT_XML1, 'UTF-8');
        $v_desc = htmlspecialchars(!empty($v['description']) ? $v['description'] : $v['name'], ENT_XML1, 'UTF-8');
        
        // Duration to seconds
        $dur_sec = 300;
        $d_parts = explode(':', $v['duration']);
        if (count($d_parts) == 3) {
            $dur_sec = intval($d_parts[0]) * 3600 + intval($d_parts[1]) * 60 + intval($d_parts[2]);
        } elseif (count($d_parts) == 2) {
            $dur_sec = intval($d_parts[0]) * 60 + intval($d_parts[1]);
        }
        
        $pub_date = date('c', $v['date']);
        $views = intval($v['view']);

        $xml .= "  <url>\n";
        $xml .= "    <loc>{$v_url}</loc>\n";
        $xml .= "    <video:video>\n";
        $xml .= "      <video:thumbnail_loc>".htmlspecialchars($thumb_url, ENT_XML1, 'UTF-8')."</video:thumbnail_loc>\n";
        $xml .= "      <video:title>{$v_title}</video:title>\n";
        $xml .= "      <video:description>{$v_desc}</video:description>\n";
        $xml .= "      <video:player_loc>{$v_url}</video:player_loc>\n";
        $xml .= "      <video:duration>{$dur_sec}</video:duration>\n";
        $xml .= "      <video:view_count>{$views}</video:view_count>\n";
        $xml .= "      <video:publication_date>{$pub_date}</video:publication_date>\n";
        $xml .= "      <video:family_friendly>no</video:family_friendly>\n";
        $xml .= "    </video:video>\n";
        $xml .= "    <changefreq>weekly</changefreq>\n";
        $xml .= "    <priority>0.7</priority>\n";
        $xml .= "  </url>\n";
    }
    $videos_q->free();
}

$xml .= '</urlset>';

// Faylga saqlab qo'yish (statik kirish tezligi uchun)
@file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/sitemap.xml', $xml);

echo $xml;
