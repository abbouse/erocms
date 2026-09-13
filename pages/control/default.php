<?php
/**
 * EroCMS Modern Admin Dashboard
 */

if (!defined('ADMIN_LOADED') && !isset($user)) {
    header('Location: /control.html');
    exit;
}

$now = time();
$today_start = strtotime('today midnight');

// 1. Asosiy statistika ko'rsatkichlari
$files_total = $mysqli->query("SELECT count(*), sum(view), sum(downloads) FROM ero_files")->fetch_row();
$total_videos = (int)($files_total[0] ?? 0);
$total_views = (int)($files_total[1] ?? 0);
$total_downloads = (int)($files_total[2] ?? 0);

$today_videos = $mysqli->query("SELECT count(*) FROM ero_files WHERE date >= '$today_start'")->fetch_row()[0] ?? 0;
$categories_count = $mysqli->query("SELECT count(*) FROM ero_categories")->fetch_row()[0] ?? 0;

$online_count = $mysqli->query("SELECT count(*) FROM ero_online WHERE date > '$now'")->fetch_row()[0] ?? 0;
$online_list = $mysqli->query("SELECT ip, date, page_url, user_agent FROM ero_online WHERE date > '$now' ORDER BY date DESC LIMIT 10");

$dmca_unread = 0;
$chk_dmca = $mysqli->query("SHOW TABLES LIKE 'ero_dmca'");
if ($chk_dmca && $chk_dmca->num_rows > 0) {
    $dmca_unread = (int)($mysqli->query("SELECT count(*) FROM ero_dmca WHERE status = 0")->fetch_row()[0] ?? 0);
}

// 2. Oxirgi yuklangan videolar
$recent_videos = $mysqli->query("
    SELECT f.id, f.name, f.screenshot, f.view, f.duration, f.date, f.translit, c.name as cat_name 
    FROM ero_files f 
    LEFT JOIN ero_categories c ON f.category = c.id 
    ORDER BY f.id DESC LIMIT 6
");

// 3. Eng ko'p ko'rilgan videolar (Top)
$top_videos = $mysqli->query("
    SELECT f.id, f.name, f.screenshot, f.view, f.duration, f.translit, c.name as cat_name 
    FROM ero_files f 
    LEFT JOIN ero_categories c ON f.category = c.id 
    ORDER BY f.view DESC LIMIT 5
");
?>

<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title"><i class="fa fa-tachometer" style="color: #ff9900;"></i> Boshqaruv Paneli</h1>
        <p class="adm-page-subtitle">Sayt holati, asosiy ko‘rsatkichlar va tezkor boshqaruv amallari</p>
    </div>
    <div style="display:flex; gap:10px; align-items:center;">
        <span class="adm-badge adm-badge-info"><i class="fa fa-calendar"></i> <?=date('d.m.Y H:i')?></span>
        <a href="/control.html" class="adm-btn adm-btn-secondary adm-btn-sm"><i class="fa fa-refresh"></i> Yangilash</a>
    </div>
</div>

<?php if ($dmca_unread > 0): ?>
<div class="adm-alert adm-alert-danger" style="display:flex; justify-content:space-between; align-items:center;">
    <div>
        <i class="fa fa-shield" style="font-size:18px;"></i>
        <b>Diqqat:</b> Sizda <b><?=$dmca_unread?> ta</b> ko‘rib chiqilmagan yangi DMCA mualliflik huquqi shikoyati bor!
    </div>
    <a href="/control.html?func=dmca" class="adm-btn adm-btn-danger adm-btn-sm">Shikoyatlarni ko‘rish</a>
</div>
<?php endif; ?>

<!-- Metrika Statistikasi Kartochkalari -->
<div class="adm-stats-grid">
    <div class="adm-stat-card">
        <div class="adm-stat-icon orange"><i class="fa fa-film"></i></div>
        <div class="adm-stat-info">
            <div class="adm-stat-label">Jami Videolar</div>
            <div class="adm-stat-value"><?=number_format($total_videos)?></div>
            <div style="font-size:11px; color:#10b981; margin-top:2px;">
                <i class="fa fa-arrow-up"></i> Bugun: +<?=$today_videos?> ta
            </div>
        </div>
    </div>

    <div class="adm-stat-card">
        <div class="adm-stat-icon blue"><i class="fa fa-eye"></i></div>
        <div class="adm-stat-info">
            <div class="adm-stat-label">Jami Ko‘rishlar</div>
            <div class="adm-stat-value"><?=number_format($total_views)?></div>
            <div style="font-size:11px; color:#94a3b8; margin-top:2px;">
                Sayt bo‘yicha umumiy
            </div>
        </div>
    </div>

    <div class="adm-stat-card">
        <div class="adm-stat-icon green"><i class="fa fa-users"></i></div>
        <div class="adm-stat-info">
            <div class="adm-stat-label">Hozir Onlayn</div>
            <div class="adm-stat-value" style="color: #10b981; display:flex; align-items:center; gap:8px;">
                <span class="adm-pulse-dot"></span> <?=$online_count?>
            </div>
            <div style="font-size:11px; color:#94a3b8; margin-top:2px;">
                Faol tashrifchilar
            </div>
        </div>
    </div>

    <div class="adm-stat-card">
        <div class="adm-stat-icon purple"><i class="fa fa-folder-open"></i></div>
        <div class="adm-stat-info">
            <div class="adm-stat-label">Toifalar & Bo‘limlar</div>
            <div class="adm-stat-value"><?=$categories_count?></div>
            <div style="font-size:11px; color:#94a3b8; margin-top:2px;">
                SEO bilan ta’minlangan
            </div>
        </div>
    </div>

    <div class="adm-stat-card">
        <div class="adm-stat-icon orange"><i class="fa fa-download"></i></div>
        <div class="adm-stat-info">
            <div class="adm-stat-label">Yuklab Olishlar</div>
            <div class="adm-stat-value"><?=number_format($total_downloads)?></div>
            <div style="font-size:11px; color:#94a3b8; margin-top:2px;">
                Foydalanuvchilar tomonidan
            </div>
        </div>
    </div>

    <div class="adm-stat-card">
        <div class="adm-stat-icon red"><i class="fa fa-shield"></i></div>
        <div class="adm-stat-info">
            <div class="adm-stat-label">DMCA Shikoyatlar</div>
            <div class="adm-stat-value" style="<?=($dmca_unread > 0 ? 'color:#ef4444;' : '')?>"><?=$dmca_unread?></div>
            <div style="font-size:11px; color:#94a3b8; margin-top:2px;">
                <?=($dmca_unread > 0 ? 'Kutilayotgan arizalar' : 'Barcha arizalar hal qilingan')?>
            </div>
        </div>
    </div>
</div>

<!-- Tezkor Harakatlar (Quick Actions) -->
<div class="adm-card">
    <div class="adm-card-header">
        <h3 class="adm-card-title"><i class="fa fa-bolt" style="color: #ff9900;"></i> Tezkor Amallar va Vositalar</h3>
    </div>
    <div class="adm-actions-grid" style="margin-bottom:0;">
        <a href="/control.html?func=parsing" class="adm-action-btn">
            <i class="fa fa-bolt"></i>
            <div>
                <div>Universal Parser</div>
                <small style="color:#94a3b8; font-size:11px;">1 bosishda video tortish</small>
            </div>
        </a>

        <a href="/control.html?func=view_video" class="adm-action-btn">
            <i class="fa fa-film"></i>
            <div>
                <div>Videolar Boshqaruvi</div>
                <small style="color:#94a3b8; font-size:11px;">Qidiruv, filtr, ommaviy o‘chirish</small>
            </div>
        </a>

        <a href="/control.html?func=parsing&repair=1" class="adm-action-btn">
            <i class="fa fa-wrench"></i>
            <div>
                <div>Rasmlarni Davolash</div>
                <small style="color:#94a3b8; font-size:11px;">Singan 404 posterlarni tuzatish</small>
            </div>
        </a>

        <a href="/control.html?func=tools&action=clear_cache" class="adm-action-btn">
            <i class="fa fa-trash"></i>
            <div>
                <div>Keshni Tozalash</div>
                <small style="color:#94a3b8; font-size:11px;">HTML keshni to‘liq bo‘shatish</small>
            </div>
        </a>

        <a href="/control.html?func=tools&action=optimize_db" class="adm-action-btn">
            <i class="fa fa-database"></i>
            <div>
                <div>Bazani Optimizatsiya</div>
                <small style="color:#94a3b8; font-size:11px;">Jadvallarni tozalash va tezlashtirish</small>
            </div>
        </a>

        <a href="/control.html?func=view_categories" class="adm-action-btn">
            <i class="fa fa-folder-open"></i>
            <div>
                <div>Kategoriyalar & SEO</div>
                <small style="color:#94a3b8; font-size:11px;">Kalit so‘zlarni yangilash</small>
            </div>
        </a>
    </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:20px;">
    <!-- Oxirgi Qo'shilgan Videolar -->
    <div class="adm-card" style="margin-bottom:0;">
        <div class="adm-card-header">
            <h3 class="adm-card-title"><i class="fa fa-clock-o" style="color: #ff9900;"></i> So‘nggi Qo‘shilgan Videolar</h3>
            <a href="/control.html?func=view_video" class="adm-btn adm-btn-secondary adm-btn-sm">Barchasi &rarr;</a>
        </div>
        <div class="adm-table-wrap">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th width="48">Poster</th>
                        <th>Nomi / Bo‘lim</th>
                        <th>Ko‘rishlar</th>
                        <th width="70">Amal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recent_videos && $recent_videos->num_rows > 0): ?>
                        <?php while ($v = $recent_videos->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <img src="<?=$v['screenshot']?>" style="width:44px; height:30px; object-fit:cover; border-radius:4px; border:1px solid #333;" onerror="this.src='/designs/no_poster.jpg';" />
                            </td>
                            <td>
                                <a href="/watch/<?=$v['translit']?>.html" target="_blank" style="color:#fff; text-decoration:none; font-weight:600; font-size:12px; display:block; max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                    <?=htmlspecialchars($v['name'])?>
                                </a>
                                <span class="adm-badge adm-badge-warning" style="font-size:10px; padding:1px 5px; margin-top:2px;">
                                    <?=$v['cat_name'] ?? 'Bo‘limsiz'?>
                                </span>
                            </td>
                            <td style="color:#94a3b8; font-size:12px;">
                                <i class="fa fa-eye"></i> <?=number_format($v['view'])?>
                            </td>
                            <td>
                                <a href="/control.html?func=rewriting&id=<?=$v['id']?>" class="adm-btn adm-btn-secondary adm-btn-sm" style="padding:2px 6px;" title="Tahrirlash">
                                    <i class="fa fa-pencil"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align:center; color:#777;">Hozircha videolar mavjud emas</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Eng Ommabop Videolar (Top) -->
    <div class="adm-card" style="margin-bottom:0;">
        <div class="adm-card-header">
            <h3 class="adm-card-title"><i class="fa fa-star" style="color: #ff9900;"></i> Eng Ko‘p Ko‘rilgan Videolar (Top)</h3>
            <a href="/control.html?func=view_video&sort=view_desc" class="adm-btn adm-btn-secondary adm-btn-sm">Reyting &rarr;</a>
        </div>
        <div class="adm-table-wrap">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th width="48">Poster</th>
                        <th>Nomi / Bo‘lim</th>
                        <th>Ko‘rishlar</th>
                        <th width="70">Amal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($top_videos && $top_videos->num_rows > 0): ?>
                        <?php while ($tv = $top_videos->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <img src="<?=$tv['screenshot']?>" style="width:44px; height:30px; object-fit:cover; border-radius:4px; border:1px solid #333;" onerror="this.src='/designs/no_poster.jpg';" />
                            </td>
                            <td>
                                <a href="/watch/<?=$tv['translit']?>.html" target="_blank" style="color:#fff; text-decoration:none; font-weight:600; font-size:12px; display:block; max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                    <?=htmlspecialchars($tv['name'])?>
                                </a>
                                <span class="adm-badge adm-badge-info" style="font-size:10px; padding:1px 5px; margin-top:2px;">
                                    <?=$tv['cat_name'] ?? 'Bo‘limsiz'?>
                                </span>
                            </td>
                            <td style="color:#10b981; font-weight:700; font-size:12px;">
                                <i class="fa fa-fire"></i> <?=number_format($tv['view'])?>
                            </td>
                            <td>
                                <a href="/control.html?func=rewriting&id=<?=$tv['id']?>" class="adm-btn adm-btn-secondary adm-btn-sm" style="padding:2px 6px;" title="Tahrirlash">
                                    <i class="fa fa-pencil"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align:center; color:#777;">Hozircha videolar mavjud emas</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Real-time Onlayn Foydalanuvchilar -->
<div class="adm-card">
    <div class="adm-card-header">
        <h3 class="adm-card-title">
            <span class="adm-pulse-dot"></span>
            Hozir Saytda Onlayn Foydalanuvchilar (<b style="color:#10b981;"><?=$online_count?> ta</b>)
        </h3>
        <a href="/control.html" class="adm-btn adm-btn-secondary adm-btn-sm"><i class="fa fa-refresh"></i> Yangilash</a>
    </div>
    
    <?php if ($online_list && $online_list->num_rows > 0): ?>
    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead>
                <tr>
                    <th width="40">#</th>
                    <th>IP Manzil</th>
                    <th>Hozirgi Sahifa</th>
                    <th>Qurilma / Brauzer</th>
                    <th>Oxirgi faollik</th>
                    <th width="80">Holati</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $num = 1;
                $my_ip = $_SERVER['REMOTE_ADDR'] ?? '';
                while ($on = $online_list->fetch_assoc()): 
                    $u_info = parse_user_agent_details($on['user_agent'] ?? '');
                    $seconds_ago = max(0, 300 - ($on['date'] - $now));
                    $act_text = ($seconds_ago < 30) ? 'Hozirgina faol' : floor($seconds_ago / 60) . ' daqiqa oldin';
                    $is_me = ($on['ip'] === $my_ip);
                    $page_link = !empty($on['page_url']) ? $on['page_url'] : '/';
                ?>
                <tr <?=($is_me ? 'style="background:rgba(255,153,0,0.06);"' : '')?>>
                    <td style="color:#64748b;"><?=$num++?></td>
                    <td>
                        <code style="color:#e2e8f0; font-size:13px;"><?=$on['ip']?></code>
                        <?php if ($is_me): ?>
                            <span class="adm-badge adm-badge-warning" style="margin-left:6px;"><i class="fa fa-user-secret"></i> Siz</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?=$page_link?>" target="_blank" style="color:var(--primary-accent, #ff9900); text-decoration:none; font-size:12px; font-weight:600; max-width:200px; display:inline-block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                            <i class="fa fa-external-link" style="font-size:10px; opacity:0.6;"></i> <?=htmlspecialchars($page_link)?>
                        </a>
                    </td>
                    <td>
                        <span style="display:inline-flex; align-items:center; gap:5px; color:<?=$u_info['badge_color']?>; font-size:12px; font-weight:600;">
                            <i class="fa <?=$u_info['device_icon']?>"></i> <?=$u_info['device']?>
                        </span>
                        <span style="color:#94a3b8; font-size:11px; margin-left:4px;">(<?=$u_info['browser']?>)</span>
                    </td>
                    <td style="color:#94a3b8; font-size:12px;"><?=$act_text?></td>
                    <td>
                        <span class="adm-badge adm-badge-success" style="font-size:11px;">
                            <span class="adm-pulse-dot"></span> Onlayn
                        </span>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <div style="padding:12px; text-align:right; background:rgba(255,255,255,0.02); border-top:1px solid rgba(255,255,255,0.06);">
        <a href="/control.html?func=stats#online_section" class="adm-btn adm-btn-secondary adm-btn-sm">
            <i class="fa fa-users"></i> Barcha Onlayn Foydalanuvchilarni Ko‘rish (Statistikada) &rarr;
        </a>
    </div>
    <?php else: ?>
    <div style="text-align:center; padding:20px; color:#64748b;">
        Hozircha onlayn tashrifchilar qayd etilmagan.
    </div>
    <?php endif; ?>
</div>