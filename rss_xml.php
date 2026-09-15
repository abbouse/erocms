<?php

/*
SEO RSS 2.0 & MRSS Feed Generator
*/

require_once 'core/Functions.php';

header('Content-Type: application/rss+xml; charset=utf-8');

$host = filter($_SERVER['HTTP_HOST'] ?? 'sekschi.online');
$base_url = 'https://' . rtrim($host, '/');

$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/">' . "\n";
$xml .= "  <channel>\n";
$xml .= "    <title>".htmlspecialchars($settings['title'], ENT_XML1, 'UTF-8')."</title>\n";
$xml .= "    <link>{$base_url}/</link>\n";
$xml .= "    <description>".htmlspecialchars($settings['description'], ENT_XML1, 'UTF-8')."</description>\n";
$xml .= "    <language>uz</language>\n";
$xml .= "    <atom:link href=\"{$base_url}/rss.xml\" rel=\"self\" type=\"application/rss+xml\" />\n";

$videos_q = $mysqli->query("SELECT name, description, screenshot, translit, date, duration FROM ero_files WHERE date < '".time()."' ORDER BY id DESC LIMIT 50");

if ($videos_q) {
    while ($v = $videos_q->fetch_assoc()) {
        $v_url = "{$base_url}/watch/{$v['translit']}.html";
        $thumb_url = (strpos($v['screenshot'], 'http') === 0) ? $v['screenshot'] : $base_url . $v['screenshot'];
        $v_title = htmlspecialchars($v['name'], ENT_XML1, 'UTF-8');
        $v_desc = htmlspecialchars(!empty($v['description']) ? $v['description'] : $v['name'], ENT_XML1, 'UTF-8');
        $pub_date = date('r', $v['date']);

        $xml .= "    <item>\n";
        $xml .= "      <title>{$v_title}</title>\n";
        $xml .= "      <link>{$v_url}</link>\n";
        $xml .= "      <guid isPermaLink=\"true\">{$v_url}</guid>\n";
        $xml .= "      <pubDate>{$pub_date}</pubDate>\n";
        $xml .= "      <description>{$v_desc}</description>\n";
        $xml .= "      <media:thumbnail url=\"".htmlspecialchars($thumb_url, ENT_XML1, 'UTF-8')."\" />\n";
        $xml .= "      <media:content url=\"".htmlspecialchars($thumb_url, ENT_XML1, 'UTF-8')."\" medium=\"image\">\n";
        $xml .= "        <media:title>{$v_title}</media:title>\n";
        $xml .= "        <media:description>{$v_desc}</media:description>\n";
        $xml .= "        <media:adult>true</media:adult>\n";
        $xml .= "      </media:content>\n";
        $xml .= "    </item>\n";

    }
    $videos_q->free();
}

$xml .= "  </channel>\n";
$xml .= '</rss>';

@file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/rss.xml', $xml);

echo $xml;
