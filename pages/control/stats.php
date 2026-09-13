<?php
/**
 * EroCMS Statistika & Mehmonlar Harakatlari Monitoringi
 */

if (!defined('ADMIN_LOADED') && !isset($user)) {
    header('Location: /control.html');
    exit;
}

$now = time();
$today_start = strtotime('today midnight');

// 1. Umumiy hisob-kitoblar
$today_views = (int)($mysqli->query("SELECT COUNT(*) FROM ero_activity WHERE action = 'view' AND date >= '$today_start'")->fetch_row()[0] ?? 0);
$today_likes = (int)($mysqli->query("SELECT COUNT(*) FROM ero_activity WHERE action = 'like' AND date >= '$today_start'")->fetch_row()[0] ?? 0);
$today_favs = (int)($mysqli->query("SELECT COUNT(*) FROM ero_activity WHERE action = 'favorite' AND date >= '$today_start'")->fetch_row()[0] ?? 0);
$today_searches = (int)($mysqli->query("SELECT COUNT(*) FROM ero_activity WHERE action = 'search' AND date >= '$today_start'")->fetch_row()[0] ?? 0);
$today_downloads = (int)($mysqli->query("SELECT COUNT(*) FROM ero_activity WHERE action = 'download' AND date >= '$today_start'")->fetch_row()[0] ?? 0);

$total_favs = (int)($mysqli->query("SELECT COUNT(*) FROM ero_favorites")->fetch_row()[0] ?? 0);
$total_likes_all = (int)($mysqli->query("SELECT SUM(likes) FROM ero_files")->fetch_row()[0] ?? 0);

// Onlayn foydalanuvchilar hisobi va sahifalash
$online_now = time();
$total_online_count = (int)($mysqli->query("SELECT COUNT(*) FROM ero_online WHERE date > '$online_now'")->fetch_row()[0] ?? 0);

$on_page = max(1, (int)($_GET['on_page'] ?? 1));
$on_per_page = 15;
$on_start = ($on_page - 1) * $on_per_page;
$total_on_pages = ceil($total_online_count / $on_per_page);

$online_users_query = $mysqli->query("
    SELECT ip, date, page_url, user_agent, country_code, last_seen 
    FROM ero_online 
    WHERE date > '$online_now' 
    ORDER BY date DESC 
    LIMIT $on_start, $on_per_page
");

// IP Checker so'rovi
$ip_check_result = null;
$check_ip_query = trim($_GET['check_ip'] ?? '');
if (!empty($check_ip_query)) {
    $ip_info = function_exists('get_ip_country_info') ? get_ip_country_info($check_ip_query) : ['code' => 'UZ', 'name' => 'O‘zbekiston'];
    $extra_info = [];
    try {
        $ctx = stream_context_create(['http' => ['timeout' => 1.5, 'ignore_errors' => true]]);
        $api_json = @file_get_contents("http://ip-api.com/json/{$check_ip_query}?fields=status,message,country,countryCode,regionName,city,zip,lat,lon,timezone,isp,org,as,query", false, $ctx);
        if ($api_json) {
            $extra_info = @json_decode($api_json, true) ?: [];
        }
    } catch (\Throwable $e) {}

    $c_code = strtoupper($extra_info['countryCode'] ?? $ip_info['code']);
    $ip_check_result = [
        'ip' => $check_ip_query,
        'country_code' => $c_code,
        'country_name' => function_exists('get_country_name_uz') ? get_country_name_uz($c_code) : ($extra_info['country'] ?? $ip_info['name']),
        'region' => $extra_info['regionName'] ?? '',
        'city' => $extra_info['city'] ?? '',
        'isp' => $extra_info['isp'] ?? 'Noma’lum ISP',
        'org' => $extra_info['org'] ?? '',
        'timezone' => $extra_info['timezone'] ?? 'Asia/Tashkent'
    ];
}

// Davlatlar bo'yicha umumiy statistika (Geografiya)
$country_stats_q = $mysqli->query("
    SELECT country_code, COUNT(*) as total_visits 
    FROM ero_activity 
    WHERE country_code != '' AND country_code != 'XX' 
    GROUP BY country_code 
    ORDER BY total_visits DESC 
    LIMIT 8
");
$country_total_visits = (int)($mysqli->query("SELECT COUNT(*) FROM ero_activity WHERE country_code != '' AND country_code != 'XX'")->fetch_row()[0] ?? 0);

// 2. Harakatlar oqimi (Activity Stream)
$filter = filter($_GET['filter'] ?? 'all');
$where_act = ["1=1"];

switch ($filter) {
    case 'likes':
        $where_act[] = "a.action IN ('like', 'dislike')";
        break;
    case 'favorites':
        $where_act[] = "a.action IN ('favorite', 'unfavorite')";
        break;
    case 'searches':
        $where_act[] = "a.action = 'search'";
        break;
    case 'downloads':
        $where_act[] = "a.action = 'download'";
        break;
    case 'views':
        $where_act[] = "a.action = 'view'";
        break;
}

$where_sql = implode(' AND ', $where_act);

$per_page = 25;
$page = max(1, (int)($_GET['page'] ?? 1));
$start = ($page - 1) * $per_page;

$total_acts = (int)($mysqli->query("SELECT COUNT(*) FROM ero_activity a WHERE $where_sql")->fetch_row()[0] ?? 0);
$total_pages = ceil($total_acts / $per_page);

$activity_stream = $mysqli->query("
    SELECT a.*, f.name as video_name, f.translit as video_translit, f.screenshot as video_screen 
    FROM ero_activity a 
    LEFT JOIN ero_files f ON a.id_file = f.id 
    WHERE $where_sql 
    ORDER BY a.id DESC 
    LIMIT $start, $per_page
");

// 3. Eng ko'p sevimlilarga qo'shilgan videolar (Top Favorited)
$top_favorited = $mysqli->query("
    SELECT f.id, f.name, f.screenshot, f.translit, f.view, COUNT(fav.id) as fav_count 
    FROM ero_favorites fav 
    JOIN ero_files f ON fav.id_video = f.id 
    GROUP BY fav.id_video 
    ORDER BY fav_count DESC 
    LIMIT 10
");

// 4. Eng ko'p yoqtirilgan videolar (Top Liked)
$top_liked = $mysqli->query("
    SELECT id, name, screenshot, translit, view, likes, dislikes 
    FROM ero_files 
    WHERE likes > 0 
    ORDER BY likes DESC 
    LIMIT 10
");

// 5. Eng ko'p qidirilgan so'zlar (Top Search Keywords)
$top_searches = $mysqli->query("
    SELECT query_text, COUNT(*) as s_count, MAX(date) as last_date 
    FROM ero_activity 
    WHERE action = 'search' AND query_text IS NOT NULL AND query_text != '' 
    GROUP BY query_text 
    ORDER BY s_count DESC 
    LIMIT 15
");
?>

<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title"><i class="fa fa-line-chart" style="color: #ff9900;"></i> Statistika va Mehmonlar Harakatlari</h1>
        <p class="adm-page-subtitle">Mehmonlarning real vaqtdagi faolligi: kim qaysi videoni ko‘rdi, like bosdi, sevimli qildi va nimalarni qidirdi</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="/control.html?func=stats" class="adm-btn adm-btn-secondary adm-btn-sm"><i class="fa fa-refresh"></i> Yangilash</a>
    </div>
</div>

<!-- Statistik Ko'rsatkichlar (Bugun va Umumiy) -->
<div class="adm-stats-grid">
    <div class="adm-stat-card">
        <div class="adm-stat-icon orange"><i class="fa fa-eye"></i></div>
        <div class="adm-stat-info">
            <div class="adm-stat-label">Bugungi Ko‘rishlar</div>
            <div class="adm-stat-value"><?=number_format($today_views)?></div>
            <div style="font-size:11px; color:#10b981; margin-top:2px;">Real tashrifchilar</div>
        </div>
    </div>

    <div class="adm-stat-card">
        <div class="adm-stat-icon red"><i class="fa fa-heart"></i></div>
        <div class="adm-stat-info">
            <div class="adm-stat-label">Bugungi Likelar</div>
            <div class="adm-stat-value"><?=number_format($today_likes)?></div>
            <div style="font-size:11px; color:#94a3b8; margin-top:2px;">Jami like: <?=number_format($total_likes_all)?></div>
        </div>
    </div>

    <div class="adm-stat-card">
        <div class="adm-stat-icon purple"><i class="fa fa-star"></i></div>
        <div class="adm-stat-info">
            <div class="adm-stat-label">Bugungi Sevimlilar</div>
            <div class="adm-stat-value"><?=number_format($today_favs)?></div>
            <div style="font-size:11px; color:#94a3b8; margin-top:2px;">Jami sevimli: <?=number_format($total_favs)?></div>
        </div>
    </div>

    <div class="adm-stat-card">
        <div class="adm-stat-icon blue"><i class="fa fa-search"></i></div>
        <div class="adm-stat-info">
            <div class="adm-stat-label">Bugungi Qidiruvlar</div>
            <div class="adm-stat-value"><?=number_format($today_searches)?></div>
            <div style="font-size:11px; color:#10b981; margin-top:2px;">Foydalanuvchilar qidiruvi</div>
        </div>
    </div>

    <div class="adm-stat-card">
        <div class="adm-stat-icon green"><i class="fa fa-download"></i></div>
        <div class="adm-stat-info">
            <div class="adm-stat-label">Bugungi Yuklashlar</div>
            <div class="adm-stat-value"><?=number_format($today_downloads)?></div>
            <div style="font-size:11px; color:#94a3b8; margin-top:2px;">MP4 yuklab olingan</div>
        </div>
    </div>

    <div class="adm-stat-card">
        <div class="adm-stat-icon" style="background:rgba(34,197,94,0.15); color:#22c55e;"><i class="fa fa-users"></i></div>
        <div class="adm-stat-info">
            <div class="adm-stat-label">Hozirgi Onlayn</div>
            <div class="adm-stat-value" style="color:#22c55e; display:flex; align-items:center; gap:8px;">
                <span class="adm-pulse-dot"></span> <?=number_format($total_online_count)?>
            </div>
            <div style="font-size:11px; color:#94a3b8; margin-top:2px;">Ayni damda saytda</div>
        </div>
    </div>
</div>

<!-- Hozirgi Onlayn Foydalanuvchilar (Monitoring & Pagination) -->
<div class="adm-card" id="online_section" style="margin-bottom:24px;">
    <div class="adm-card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
        <h3 class="adm-card-title">
            <span class="adm-pulse-dot" style="display:inline-block; width:10px; height:10px; border-radius:50%; background:#22c55e; box-shadow:0 0 8px #22c55e; margin-right:8px;"></span>
            Hozirgi Onlayn Tashrif Buyuruvchilar (<?=$total_online_count?> nafar)
        </h3>
        <span class="adm-badge adm-badge-success"><i class="fa fa-clock-o"></i> Oxirgi 5 daqiqadagi real-vaqt faollik</span>
    </div>

    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead>
                <tr>
                    <th width="40">#</th>
                    <th>IP Manzil</th>
                    <th>Hozirgi Sahifasi</th>
                    <th>Qurilma & OS</th>
                    <th>Brauzer</th>
                    <th>Oxirgi faollik</th>
                    <th width="90">Holati</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($online_users_query && $online_users_query->num_rows > 0): ?>
                <?php 
                $on_num = $on_start + 1;
                $my_admin_ip = $_SERVER['REMOTE_ADDR'] ?? '';
                while ($u = $online_users_query->fetch_assoc()): 
                    $u_info = parse_user_agent_details($u['user_agent'] ?? '');
                    $is_admin_user = ($u['ip'] === $my_admin_ip);
                    $page_link = !empty($u['page_url']) ? $u['page_url'] : '/';
                    $sec_ago = max(0, 300 - ($u['date'] - $online_now));
                    $time_text = ($sec_ago < 30) ? 'Hozirgina faol' : floor($sec_ago / 60) . ' daqiqa oldin';
                ?>
                <tr <?=($is_admin_user ? 'style="background:rgba(255,153,0,0.06);"' : '')?>>
                    <td style="color:#64748b; font-weight:700;"><?=$on_num++?></td>
                    <td>
                        <?=render_ip_with_flag($u['ip'], $u['country_code'] ?? null)?>
                        <?php if ($is_admin_user): ?>
                            <span class="adm-badge adm-badge-warning" style="margin-left:6px;"><i class="fa fa-user-secret"></i> Siz (Admin)</span>
                        <?php endif; ?>
                        <a href="/control.html?func=stats&check_ip=<?=$u['ip']?>#ip_checker_box" title="Ushbu IP ni tekshirish" style="color:#60a5fa; font-size:11px; margin-left:5px;"><i class="fa fa-info-circle"></i></a>
                    </td>
                    <td>
                        <a href="<?=$page_link?>" target="_blank" style="color:var(--primary-accent, #ff9900); text-decoration:none; font-weight:600; font-size:12px; display:inline-flex; align-items:center; gap:6px; max-width:320px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                            <i class="fa fa-external-link" style="font-size:10px; opacity:0.7;"></i>
                            <span><?=htmlspecialchars($page_link)?></span>
                        </a>
                    </td>
                    <td>
                        <span style="display:inline-flex; align-items:center; gap:6px; color:<?=$u_info['badge_color']?>; font-weight:600; font-size:12px;">
                            <i class="fa <?=$u_info['device_icon']?>"></i>
                            <?=$u_info['device']?>
                        </span>
                    </td>
                    <td>
                        <span style="display:inline-flex; align-items:center; gap:6px; color:#94a3b8; font-size:12px;" title="<?=htmlspecialchars($u['user_agent'] ?? '')?>">
                            <i class="fa <?=$u_info['browser_icon']?>" style="color:#60a5fa;"></i>
                            <?=$u_info['browser']?>
                        </span>
                    </td>
                    <td style="color:#94a3b8; font-size:12px;">
                        <i class="fa fa-clock-o" style="opacity:0.6;"></i> <?=$time_text?>
                    </td>
                    <td>
                        <span class="adm-badge adm-badge-success" style="font-size:11px;">
                            <span class="adm-pulse-dot"></span> Onlayn
                        </span>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding:30px; color:#64748b;">
                        Hozircha onlayn foydalanuvchilar mavjud emas.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total_on_pages > 1): ?>
    <div class="adm-pagination" style="margin-top:15px; padding-top:10px; border-top:1px solid rgba(255,255,255,0.06);">
        <?php
        $on_base = $_GET;
        unset($on_base['on_page']);
        $on_prefix = '/control.html?' . http_build_query($on_base) . '&on_page=';

        if ($on_page > 1) {
            echo '<a href="' . $on_prefix . ($on_page - 1) . '#online_section">&laquo; Oldingi</a>';
        }

        $r = 2;
        for ($p = max(1, $on_page - $r); $p <= min($total_on_pages, $on_page + $r); $p++) {
            if ($p == $on_page) {
                echo '<span class="active">' . $p . '</span>';
            } else {
                echo '<a href="' . $on_prefix . $p . '#online_section">' . $p . '</a>';
            }
        }

        if ($on_page < $total_on_pages) {
            echo '<a href="' . $on_prefix . ($on_page + 1) . '#online_section">Keyingi &raquo;</a>';
        }
        ?>
    </div>
    <?php endif; ?>
</div>

<!-- Davlatlar Geografiyasi & IP Checker -->
<div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:24px;">
    <!-- 1. Davlatlar Bo'yicha Tashriflar -->
    <div class="adm-card" style="margin-bottom:0;">
        <div class="adm-card-header" style="display:flex; justify-content:space-between; align-items:center;">
            <h3 class="adm-card-title"><i class="fa fa-globe" style="color: #ff9900;"></i> Tashriflar Geografiyasi (Davlatlar)</h3>
            <span class="adm-badge adm-badge-info"><?=number_format($country_total_visits)?> ta faollik</span>
        </div>
        <div style="padding:15px;">
            <?php if ($country_stats_q && $country_stats_q->num_rows > 0): ?>
                <?php while ($c = $country_stats_q->fetch_assoc()): 
                    $c_code = strtoupper($c['country_code']);
                    $c_name = function_exists('get_country_name_uz') ? get_country_name_uz($c_code) : $c_code;
                    $c_count = (int)$c['total_visits'];
                    $c_percent = ($country_total_visits > 0) ? round(($c_count / $country_total_visits) * 100, 1) : 0;
                ?>
                <div style="margin-bottom:14px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; font-size:13px; margin-bottom:4px;">
                        <span style="display:inline-flex; align-items:center; gap:6px; font-weight:600; color:#fff;">
                            <?=function_exists('get_country_flag_badge') ? get_country_flag_badge($c_code, $c_name, true) : ''?>
                            <span><?=$c_name?></span>
                        </span>
                        <span style="color:#94a3b8; font-size:12px;">
                            <b><?=number_format($c_count)?></b> ta (<?=$c_percent?>%)
                        </span>
                    </div>
                    <div style="width:100%; height:6px; background:#262935; border-radius:3px; overflow:hidden;">
                        <div style="width:<?=max(4, $c_percent)?>%; height:100%; background:linear-gradient(90deg, #ff9900, #f59e0b); border-radius:3px;"></div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align:center; padding:30px; color:#64748b;">
                    <i class="fa fa-globe" style="font-size:32px; color:#383e50; display:block; margin-bottom:8px;"></i>
                    Hozircha davlatlar statistikasi yig‘ilmoqda...
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 2. IP Checker Vositalari -->
    <div class="adm-card" id="ip_checker_box" style="margin-bottom:0;">
        <div class="adm-card-header">
            <h3 class="adm-card-title"><i class="fa fa-crosshairs" style="color: #60a5fa;"></i> IP Checker (Mehmon IP Tekshiruvi)</h3>
        </div>
        <div style="padding:15px;">
            <form method="get" action="/control.html" style="margin-bottom:15px;">
                <input type="hidden" name="func" value="stats" />
                <div style="display:flex; gap:8px;">
                    <input type="text" name="check_ip" class="adm-input" value="<?=htmlspecialchars($check_ip_query)?>" placeholder="Tekshirish uchun IP kiriting (masalan: 84.54.120.3)" style="flex:1;" required />
                    <button type="submit" class="adm-btn adm-btn-primary" style="white-space:nowrap; padding:9px 16px;">
                        <i class="fa fa-search"></i> Tekshirish
                    </button>
                </div>
            </form>

            <?php if ($ip_check_result): ?>
                <div style="background:#151821; border:1px solid rgba(96,165,250,0.3); border-radius:6px; padding:15px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; border-bottom:1px solid rgba(255,255,255,0.06); padding-bottom:8px;">
                        <span style="font-size:14px; font-weight:700; color:#fff;">
                            <?=function_exists('get_country_flag_badge') ? get_country_flag_badge($ip_check_result['country_code'], $ip_check_result['country_name']) : ''?>
                            <code><?=$ip_check_result['ip']?></code>
                        </span>
                        <span class="adm-badge adm-badge-info"><?=$ip_check_result['country_name']?> (<?=$ip_check_result['country_code']?>)</span>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; font-size:12px;">
                        <div><span style="color:#64748b;">Shahar:</span> <b style="color:#e2e8f0;"><?=$ip_check_result['city'] ?: 'Noma’lum'?></b></div>
                        <div><span style="color:#64748b;">Viloyat / Region:</span> <b style="color:#e2e8f0;"><?=$ip_check_result['region'] ?: 'Noma’lum'?></b></div>
                        <div><span style="color:#64748b;">Provayder (ISP):</span> <b style="color:#60a5fa;"><?=$ip_check_result['isp']?></b></div>
                        <div><span style="color:#64748b;">Vaqt mintaqasi:</span> <b style="color:#e2e8f0;"><?=$ip_check_result['timezone']?></b></div>
                    </div>
                </div>
            <?php else: ?>
                <div style="background:rgba(255,255,255,0.02); border:1px dashed rgba(255,255,255,0.1); border-radius:6px; padding:20px; text-align:center; color:#64748b; font-size:12px;">
                    <i class="fa fa-info-circle" style="font-size:24px; color:#60a5fa; display:block; margin-bottom:6px;"></i>
                    Jadvallardagi istalgan IP yonidagi <i class="fa fa-info-circle" style="color:#60a5fa;"></i> tugmasini bosing yoki yuqoridagi qidiruvga IP yozing.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:24px;">
    <!-- 1. Eng Ko'p Sevimlilarga Qo'shilgan Videolar -->
    <div class="adm-card" style="margin-bottom:0;">
        <div class="adm-card-header">
            <h3 class="adm-card-title"><i class="fa fa-star" style="color: #ff9900;"></i> Eng Ko‘p Sevimlilarga Qo‘shilganlar (Top 10)</h3>
        </div>
        <div class="adm-table-wrap">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th width="44">Poster</th>
                        <th>Video Nomi</th>
                        <th width="80" style="text-align:center;">Sevimli</th>
                        <th width="70">Ko‘rish</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($top_favorited && $top_favorited->num_rows > 0): ?>
                    <?php while ($fav = $top_favorited->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <img src="<?=$fav['screenshot']?>" style="width:40px; height:28px; object-fit:cover; border-radius:3px;" onerror="this.src='/designs/no_poster.jpg';" />
                        </td>
                        <td>
                            <a href="/watch/<?=$fav['translit']?>.html" target="_blank" style="color:#fff; text-decoration:none; font-weight:600; font-size:12px; display:block; max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                <?=htmlspecialchars($fav['name'])?>
                            </a>
                        </td>
                        <td style="text-align:center;">
                            <span class="adm-badge adm-badge-warning" style="font-weight:700;">
                                <i class="fa fa-star"></i> <?=$fav['fav_count']?> ta
                            </span>
                        </td>
                        <td style="color:#94a3b8; font-size:12px;">
                            <?=number_format($fav['view'])?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align:center; color:#64748b; padding:20px;">Hozircha ma’lumotlar mavjud emas</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 2. Eng Ko'p Yoqtirilgan Videolar (Top Liked) -->
    <div class="adm-card" style="margin-bottom:0;">
        <div class="adm-card-header">
            <h3 class="adm-card-title"><i class="fa fa-thumbs-up" style="color: #10b981;"></i> Eng Ko‘p Yoqtirilgan Videolar (Top 10)</h3>
        </div>
        <div class="adm-table-wrap">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th width="44">Poster</th>
                        <th>Video Nomi</th>
                        <th width="80" style="text-align:center;">Likelar</th>
                        <th width="70">Reyting</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($top_liked && $top_liked->num_rows > 0): ?>
                    <?php while ($tl = $top_liked->fetch_assoc()): ?>
                        <?php
                            $tot = (int)$tl['likes'] + (int)$tl['dislikes'];
                            $percent = $tot > 0 ? round(((int)$tl['likes'] / $tot) * 100) : 100;
                        ?>
                    <tr>
                        <td>
                            <img src="<?=$tl['screenshot']?>" style="width:40px; height:28px; object-fit:cover; border-radius:3px;" onerror="this.src='/designs/no_poster.jpg';" />
                        </td>
                        <td>
                            <a href="/watch/<?=$tl['translit']?>.html" target="_blank" style="color:#fff; text-decoration:none; font-weight:600; font-size:12px; display:block; max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                <?=htmlspecialchars($tl['name'])?>
                            </a>
                        </td>
                        <td style="text-align:center;">
                            <span class="adm-badge adm-badge-success" style="font-weight:700;">
                                <i class="fa fa-thumbs-up"></i> <?=$tl['likes']?>
                            </span>
                        </td>
                        <td style="color:#10b981; font-weight:700; font-size:12px;">
                            <?=$percent?>%
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align:center; color:#64748b; padding:20px;">Hozircha ovoz berilgan videolar yo‘q</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 3. Eng Ko'p Qidirilayotgan So'zlar (Top Searches) -->
<div class="adm-card">
    <div class="adm-card-header">
        <h3 class="adm-card-title"><i class="fa fa-search" style="color: #ff9900;"></i> Mehmonlar Eng Ko‘p Qidirayotgan So‘zlar (Top Qidiruvlar)</h3>
    </div>
    <?php if ($top_searches && $top_searches->num_rows > 0): ?>
    <div style="display:flex; flex-wrap:wrap; gap:10px;">
        <?php while ($ts = $top_searches->fetch_assoc()): ?>
        <a href="/search_?i=<?=urlencode($ts['query_text'])?>" target="_blank" style="display:inline-flex; align-items:center; gap:8px; background:#111318; border:1px solid #282c37; padding:8px 12px; border-radius:6px; text-decoration:none; color:#e2e8f0; font-size:13px; transition:all 0.2s;">
            <i class="fa fa-search" style="color:#ff9900; font-size:11px;"></i>
            <span><?=htmlspecialchars($ts['query_text'])?></span>
            <span class="adm-badge adm-badge-info" style="font-size:11px; padding:1px 6px;"><?=$ts['s_count']?> marta</span>
        </a>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
        <div style="color:#64748b; padding:10px 0;">Qidiruvlar tarixi hali to‘planmadi.</div>
    <?php endif; ?>
</div>

<!-- 4. Mehmonlarning Jonli Harakatlar Lentasi (Live Activity Stream) -->
<div class="adm-card">
    <div class="adm-card-header">
        <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
            <h3 class="adm-card-title"><i class="fa fa-bolt" style="color:#ff9900;"></i> Jonli Harakatlar Lentasi (Jami: <?=number_format($total_acts)?> ta)</h3>
            
            <div style="display:flex; gap:6px; flex-wrap:wrap;">
                <a href="/control.html?func=stats&filter=all" class="adm-btn <?=($filter === 'all' ? 'adm-btn-primary' : 'adm-btn-secondary')?> adm-btn-sm">Barchasi</a>
                <a href="/control.html?func=stats&filter=likes" class="adm-btn <?=($filter === 'likes' ? 'adm-btn-danger' : 'adm-btn-secondary')?> adm-btn-sm"><i class="fa fa-heart"></i> Likelar</a>
                <a href="/control.html?func=stats&filter=favorites" class="adm-btn <?=($filter === 'favorites' ? 'adm-btn-warning' : 'adm-btn-secondary')?> adm-btn-sm"><i class="fa fa-star"></i> Sevimlilar</a>
                <a href="/control.html?func=stats&filter=searches" class="adm-btn <?=($filter === 'searches' ? 'adm-btn-info' : 'adm-btn-secondary')?> adm-btn-sm"><i class="fa fa-search"></i> Qidiruvlar</a>
                <a href="/control.html?func=stats&filter=downloads" class="adm-btn <?=($filter === 'downloads' ? 'adm-btn-success' : 'adm-btn-secondary')?> adm-btn-sm"><i class="fa fa-download"></i> Yuklashlar</a>
                <a href="/control.html?func=stats&filter=views" class="adm-btn <?=($filter === 'views' ? 'adm-btn-primary' : 'adm-btn-secondary')?> adm-btn-sm"><i class="fa fa-eye"></i> Ko‘rishlar</a>
            </div>
        </div>
    </div>

    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead>
                <tr>
                    <th width="140">Vaqt</th>
                    <th width="140">Mehmon IP</th>
                    <th width="130">Harakat turi</th>
                    <th>Tafsilot / Video yoki Qidiruv</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($activity_stream && $activity_stream->num_rows > 0): ?>
                <?php while ($act = $activity_stream->fetch_assoc()): ?>
                    <?php
                        $badge_class = 'adm-badge-info';
                        $badge_icon = 'fa-info-circle';
                        $act_label = $act['action'];

                        switch ($act['action']) {
                            case 'like':
                                $badge_class = 'adm-badge-success';
                                $badge_icon = 'fa-thumbs-up';
                                $act_label = 'Like bosdi';
                                break;
                            case 'dislike':
                                $badge_class = 'adm-badge-danger';
                                $badge_icon = 'fa-thumbs-down';
                                $act_label = 'Dislike bosdi';
                                break;
                            case 'favorite':
                                $badge_class = 'adm-badge-warning';
                                $badge_icon = 'fa-star';
                                $act_label = 'Sevimli qildi';
                                break;
                            case 'unfavorite':
                                $badge_class = 'adm-badge-gray';
                                $badge_icon = 'fa-star-o';
                                $act_label = 'Sevimlidan oldi';
                                break;
                            case 'search':
                                $badge_class = 'adm-badge-info';
                                $badge_icon = 'fa-search';
                                $act_label = 'Qidirdi';
                                break;
                            case 'download':
                                $badge_class = 'adm-badge-success';
                                $badge_icon = 'fa-download';
                                $act_label = 'Yuklab oldi';
                                break;
                            case 'view':
                                $badge_class = 'adm-badge-primary';
                                $badge_icon = 'fa-eye';
                                $act_label = 'Ko‘rdi';
                                break;
                        }
                    ?>
                <tr>
                    <td style="color:#94a3b8; font-size:12px;">
                        <?=date('d.m.Y H:i:s', $act['date'])?><br />
                        <small style="color:#64748b;"><?=time_ago($act['date'])?></small>
                    </td>
                    <td>
                        <?=render_ip_with_flag($act['ip'], $act['country_code'] ?? null)?>
                        <a href="/control.html?func=stats&check_ip=<?=$act['ip']?>#ip_checker_box" title="IP ni tekshirish" style="color:#60a5fa; font-size:10px; margin-left:4px;"><i class="fa fa-info-circle"></i></a>
                    </td>
                    <td>
                        <span class="adm-badge <?=$badge_class?>" style="font-size:12px;">
                            <i class="fa <?=$badge_icon?>"></i> <?=$act_label?>
                        </span>
                    </td>
                    <td>
                        <?php if (!empty($act['query_text'])): ?>
                            <span style="color:#60a5fa; font-weight:600; font-size:13px;">
                                "<?=htmlspecialchars($act['query_text'])?>"
                            </span>
                        <?php elseif (!empty($act['video_name'])): ?>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <?php if (!empty($act['video_screen'])): ?>
                                    <img src="<?=$act['video_screen']?>" style="width:36px; height:24px; object-fit:cover; border-radius:2px;" onerror="this.src='/designs/no_poster.jpg';" />
                                <?php endif; ?>
                                <a href="/watch/<?=$act['video_translit']?>.html" target="_blank" style="color:#fff; text-decoration:none; font-weight:600; font-size:12px;">
                                    <?=htmlspecialchars($act['video_name'])?> &rarr;
                                </a>
                            </div>
                        <?php elseif ($act['id_file'] > 0): ?>
                            <span style="color:#64748b; font-size:12px;">Video #<?=$act['id_file']?></span>
                        <?php else: ?>
                            <span style="color:#64748b;">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align:center; padding:30px; color:#64748b;">
                        Hozircha qayd etilgan harakatlar mavjud emas.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($total_pages > 1): ?>
<div class="adm-pagination">
    <?php
    $base_params = $_GET;
    unset($base_params['page']);
    $url_prefix = '/control.html?' . http_build_query($base_params) . '&page=';

    if ($page > 1) {
        echo '<a href="' . $url_prefix . ($page - 1) . '">&laquo; Oldingi</a>';
    }

    $range = 2;
    for ($p = max(1, $page - $range); $p <= min($total_pages, $page + $range); $p++) {
        if ($p == $page) {
            echo '<span class="active">' . $p . '</span>';
        } else {
            echo '<a href="' . $url_prefix . $p . '">' . $p . '</a>';
        }
    }

    if ($page < $total_pages) {
        echo '<a href="' . $url_prefix . ($page + 1) . '">Keyingi &raquo;</a>';
    }
    ?>
</div>
<?php endif; ?>
