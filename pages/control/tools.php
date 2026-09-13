<?php
/**
 * EroCMS Tizim Vositalari & Texnik Xizmat Ko'rsatish (System Tools)
 */

if (!defined('ADMIN_LOADED') && !isset($user)) {
    header('Location: /control.html');
    exit;
}

$action_msg = null;
$action_type = 'success';

// 1. Keshni Tozalash
if (isset($_GET['action']) && $_GET['action'] === 'clear_cache') {
    $cache_dir = $_SERVER['DOCUMENT_ROOT'] . '/content/cache/';
    $files = glob($cache_dir . '*.html');
    $del_count = 0;
    $bytes_freed = 0;
    if ($files) {
        foreach ($files as $file) {
            $bytes_freed += @filesize($file);
            if (@unlink($file)) {
                $del_count++;
            }
        }
    }
    $mb_freed = round($bytes_freed / 1048576, 2);
    $action_msg = "HTML kesh to‘liq tozalandi! Jami <b>{$del_count} ta</b> fayl o‘chirildi va <b>{$mb_freed} MB</b> joy bo‘shatildi.";
    logs($user['id'], 'Kesh tozalandi', 0);
}

// 2. Ma'lumotlar Bazasini Optimizatsiya Qilish
if (isset($_GET['action']) && $_GET['action'] === 'optimize_db') {
    $tables = ['ero_files', 'ero_categories', 'ero_logs', 'ero_online', 'ero_dmca', 'ero_likes', 'ero_comments', 'ero_users', 'ero_settings'];
    $optimized = [];
    foreach ($tables as $tbl) {
        $chk = $mysqli->query("SHOW TABLES LIKE '$tbl'");
        if ($chk && $chk->num_rows > 0) {
            $mysqli->query("OPTIMIZE TABLE `$tbl`");
            $optimized[] = $tbl;
        }
    }
    $action_msg = "Ma’lumotlar bazasi optimizatsiya qilindi! Qayta indekslangan jadvallar: <b>" . implode(', ', $optimized) . "</b>.";
    logs($user['id'], 'Baza optimizatsiya qilindi', 0);
}

// 3. Sitemap & Robots.txt Generatsiya
if (isset($_GET['action']) && $_GET['action'] === 'gen_sitemap') {
    $host = filter($_SERVER['HTTP_HOST'] ?? 'sekschi.online');
    $base_url = 'https://' . rtrim($host, '/');
    
    // Sitemap.xml yaratish
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    $xml .= '  <url><loc>' . $base_url . '/</loc><changefreq>always</changefreq><priority>1.0</priority></url>' . "\n";
    $xml .= '  <url><loc>' . $base_url . '/video_new.html</loc><changefreq>hourly</changefreq><priority>0.9</priority></url>' . "\n";
    $xml .= '  <url><loc>' . $base_url . '/video_top.html</loc><changefreq>daily</changefreq><priority>0.9</priority></url>' . "\n";
    $xml .= '  <url><loc>' . $base_url . '/dmca.html</loc><changefreq>monthly</changefreq><priority>0.5</priority></url>' . "\n";
    
    // Toifalar
    $cat_q = $mysqli->query("SELECT translit FROM ero_categories");
    while ($c = $cat_q->fetch_assoc()) {
        $xml .= '  <url><loc>' . $base_url . '/' . $c['translit'] . '/</loc><changefreq>daily</changefreq><priority>0.8</priority></url>' . "\n";
    }
    
    // Oxirgi 1000 ta video
    $vid_q = $mysqli->query("SELECT translit, date FROM ero_files ORDER BY id DESC LIMIT 1000");
    while ($v = $vid_q->fetch_assoc()) {
        $xml .= '  <url><loc>' . $base_url . '/watch/' . $v['translit'] . '.html</loc><lastmod>' . date('Y-m-d', $v['date']) . '</lastmod><priority>0.7</priority></url>' . "\n";
    }
    $xml .= '</urlset>';
    
    @file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/sitemap.xml', $xml);
    
    // Robots.txt
    $robots = "User-agent: *\nAllow: /\nDisallow: /control.html\nDisallow: /control.php\nDisallow: /search_\nSitemap: " . $base_url . "/sitemap.xml\n";
    @file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/robots.txt', $robots);
    
    $action_msg = "Sitemap.xml va robots.txt muvaffaqiyatli qayta yaratildi!";
    logs($user['id'], 'Sitemap yangilandi', 0);
}

// 4. Eski Loglarni Tozalash (30 kundan oldingi)
if (isset($_GET['action']) && $_GET['action'] === 'clean_logs') {
    $month_ago = time() - (86400 * 30);
    $mysqli->query("DELETE FROM ero_logs WHERE date < '$month_ago'");
    $action_msg = "30 kundan eski bo‘lgan tizim loglari muvaffaqiyatli tozalandi.";
}

// Papkalar hajmi va huquqlarini tekshirish
function get_dir_size_info($path) {
    $full_path = $_SERVER['DOCUMENT_ROOT'] . $path;
    $is_writable = is_writable($full_path);
    $files = @glob($full_path . '*');
    $count = $files ? count($files) : 0;
    $bytes = 0;
    if ($files) {
        foreach ($files as $f) {
            $bytes += @filesize($f);
        }
    }
    return [
        'writable' => $is_writable,
        'count'    => $count,
        'size_mb'  => round($bytes / 1048576, 2)
    ];
}

$info_screenshots = get_dir_size_info('/content/screenshots/');
$info_video = get_dir_size_info('/content/video/');
$info_cache = get_dir_size_info('/content/cache/');

// Server disk holati
$disk_free = @disk_free_space($_SERVER['DOCUMENT_ROOT']);
$disk_total = @disk_total_space($_SERVER['DOCUMENT_ROOT']);
$disk_free_gb = $disk_free ? round($disk_free / 1073741824, 2) : 0;
$disk_total_gb = $disk_total ? round($disk_total / 1073741824, 2) : 0;
$disk_used_percent = ($disk_total > 0) ? round((($disk_total - $disk_free) / $disk_total) * 100, 1) : 0;
?>

<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title"><i class="fa fa-wrench" style="color: #ff9900;"></i> Tizim Vositalari & Optimizatsiya</h1>
        <p class="adm-page-subtitle">Keshni tozalash, ma’lumotlar bazasini tezlashtirish, SEO fayllari va server diagnostikasi</p>
    </div>
</div>

<?php if ($action_msg): ?>
    <div class="adm-alert adm-alert-<?=$action_type?>">
        <i class="fa fa-check-circle"></i> <?=$action_msg?>
    </div>
<?php endif; ?>

<!-- Tezkor Ta'mirlash va Optimizatsiya Bloki -->
<div class="adm-card">
    <div class="adm-card-header">
        <h3 class="adm-card-title"><i class="fa fa-bolt" style="color: #ff9900;"></i> 1 Bosishda Bajariladigan Ta’mirlash Amallari</h3>
    </div>
    
    <div class="adm-actions-grid" style="margin-bottom:0;">
        <a href="/control.html?func=tools&action=clear_cache" class="adm-action-btn" onclick="return confirm('HTML keshni to‘liq tozalashni tasdiqlaysizmi?');">
            <i class="fa fa-trash"></i>
            <div>
                <div>HTML Keshni Bo‘shatish</div>
                <small style="color:#94a3b8; font-size:11px;">Mavjud: <?=$info_cache['count']?> ta fayl (<?=$info_cache['size_mb']?> MB)</small>
            </div>
        </a>

        <a href="/control.html?func=tools&action=optimize_db" class="adm-action-btn" onclick="return confirm('Barcha MySQL jadvallarini defragmentatsiya qilib optimallashtirishni tasdiqlaysizmi?');">
            <i class="fa fa-database"></i>
            <div>
                <div>Bazani Optimizatsiya Qilish</div>
                <small style="color:#94a3b8; font-size:11px;">Indexlarni tiklash va tezlashtirish</small>
            </div>
        </a>

        <a href="/control.html?func=parsing&repair=1" class="adm-action-btn">
            <i class="fa fa-wrench"></i>
            <div>
                <div>404 Rasmlarni Tiklash</div>
                <small style="color:#94a3b8; font-size:11px;">Singan rasmlarni CDN ga ulash</small>
            </div>
        </a>

        <a href="/control.html?func=tools&action=gen_sitemap" class="adm-action-btn">
            <i class="fa fa-sitemap"></i>
            <div>
                <div>Sitemap.xml Qayta Yaratish</div>
                <small style="color:#94a3b8; font-size:11px;">Google & Yandex qidiruv indekslari</small>
            </div>
        </a>

        <a href="/control.html?func=tools&action=clean_logs" class="adm-action-btn" onclick="return confirm('30 kundan eski bo‘lgan tizim loglarini o‘chirishni tasdiqlaysizmi?');">
            <i class="fa fa-history"></i>
            <div>
                <div>Eski Loglarni Tozalash</div>
                <small style="color:#94a3b8; font-size:11px;">30 kundan oldingi yozuvlar</small>
            </div>
        </a>

        <a href="/sitemap.xml" target="_blank" class="adm-action-btn">
            <i class="fa fa-external-link"></i>
            <div>
                <div>Sitemap.xml Ni Ko‘rish</div>
                <small style="color:#94a3b8; font-size:11px;">Brauzerda tekshirish</small>
            </div>
        </a>
    </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
    <!-- Papkalar va Xotira Holati -->
    <div class="adm-card">
        <div class="adm-card-header">
            <h3 class="adm-card-title"><i class="fa fa-folder-open" style="color: #ff9900;"></i> Server Papkalari Ruxsatlari (Permissions)</h3>
        </div>
        <div class="adm-table-wrap">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th>Papka</th>
                        <th>Yozish huquqi</th>
                        <th>Fayllar soni</th>
                        <th>Hajmi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>/content/screenshots/</code></td>
                        <td>
                            <?php if ($info_screenshots['writable']): ?>
                                <span class="adm-badge adm-badge-success"><i class="fa fa-check"></i> Ruxsat bor (OK)</span>
                            <?php else: ?>
                                <span class="adm-badge adm-badge-danger"><i class="fa fa-times"></i> Ruxsat yo‘q (777 bering)</span>
                            <?php endif; ?>
                        </td>
                        <td><?=$info_screenshots['count']?> ta</td>
                        <td><?=$info_screenshots['size_mb']?> MB</td>
                    </tr>
                    <tr>
                        <td><code>/content/video/</code></td>
                        <td>
                            <?php if ($info_video['writable']): ?>
                                <span class="adm-badge adm-badge-success"><i class="fa fa-check"></i> Ruxsat bor (OK)</span>
                            <?php else: ?>
                                <span class="adm-badge adm-badge-danger"><i class="fa fa-times"></i> Ruxsat yo‘q</span>
                            <?php endif; ?>
                        </td>
                        <td><?=$info_video['count']?> ta</td>
                        <td><?=$info_video['size_mb']?> MB</td>
                    </tr>
                    <tr>
                        <td><code>/content/cache/</code></td>
                        <td>
                            <?php if ($info_cache['writable']): ?>
                                <span class="adm-badge adm-badge-success"><i class="fa fa-check"></i> Ruxsat bor (OK)</span>
                            <?php else: ?>
                                <span class="adm-badge adm-badge-danger"><i class="fa fa-times"></i> Ruxsat yo‘q</span>
                            <?php endif; ?>
                        </td>
                        <td><?=$info_cache['count']?> ta</td>
                        <td><?=$info_cache['size_mb']?> MB</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <?php if ($disk_total_gb > 0): ?>
        <div style="margin-top:20px; padding:12px; background:#12141a; border-radius:6px; border:1px solid #282c37;">
            <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:6px;">
                <span>Server Disk Bandligi: <b><?=$disk_used_percent?>%</b></span>
                <span>Bo‘sh joy: <b><?=$disk_free_gb?> GB</b> / <?=$disk_total_gb?> GB</span>
            </div>
            <div style="width:100%; height:8px; background:#222; border-radius:4px; overflow:hidden;">
                <div style="width:<?=$disk_used_percent?>%; height:100%; background:<?=($disk_used_percent > 85 ? '#ef4444' : '#ff9900')?>;"></div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Server & Dastur Diagnostikasi -->
    <div class="adm-card">
        <div class="adm-card-header">
            <h3 class="adm-card-title"><i class="fa fa-server" style="color: #ff9900;"></i> Server va Muhit Diagnostikasi</h3>
        </div>
        <div class="adm-table-wrap">
            <table class="adm-table">
                <tbody>
                    <tr>
                        <td style="color:#94a3b8; width:160px;">PHP Versiyasi</td>
                        <td><b><?=phpversion()?></b></td>
                    </tr>
                    <tr>
                        <td style="color:#94a3b8;">MySQL Versiyasi</td>
                        <td><b><?=$mysqli->server_info?></b></td>
                    </tr>
                    <tr>
                        <td style="color:#94a3b8;">Web-server</td>
                        <td><?=htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'Nginx / Apache')?></td>
                    </tr>
                    <tr>
                        <td style="color:#94a3b8;">cURL Kengaytmasi</td>
                        <td>
                            <?php if (extension_loaded('curl')): ?>
                                <span class="adm-badge adm-badge-success">Yoqilgan (cURL OK)</span>
                            <?php else: ?>
                                <span class="adm-badge adm-badge-danger">O‘chirilgan</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="color:#94a3b8;">GD Rasmlar Moduli</td>
                        <td>
                            <?php if (extension_loaded('gd')): ?>
                                <span class="adm-badge adm-badge-success">Yoqilgan (GD OK)</span>
                            <?php else: ?>
                                <span class="adm-badge adm-badge-danger">O‘chirilgan</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="color:#94a3b8;">FFmpeg Moduli</td>
                        <td>
                            <?php if (class_exists('ffmpeg_movie')): ?>
                                <span class="adm-badge adm-badge-success">Mavjud (PHP FFmpeg)</span>
                            <?php else: ?>
                                <span class="adm-badge adm-badge-gray">O‘rnatilmagan (CDN oqimi ishlatilmoqda)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="color:#94a3b8;">Xotira Limiti (Memory)</td>
                        <td><b><?=ini_get('memory_limit')?></b></td>
                    </tr>
                    <tr>
                        <td style="color:#94a3b8;">Maksimal Yuklash (Upload)</td>
                        <td><b><?=ini_get('upload_max_filesize')?></b> (post: <?=ini_get('post_max_size')?>)</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
