<?php
/**
 * EroCMS Modern Admin Layout Engine
 * Provides standard, premium dark shell for all admin subpages
 */

function admin_head($title = 'Boshqaruv Paneli', $active_func = 'default') {
    global $mysqli, $settings, $user, $lang, $version;
    
    $now = time();
    $online_count = $mysqli->query("SELECT count(*) FROM ero_online WHERE date > '$now'")->fetch_row()[0] ?? 0;
    $dmca_unread = 0;
    $chk_dmca = $mysqli->query("SHOW TABLES LIKE 'ero_dmca'");
    if ($chk_dmca && $chk_dmca->num_rows > 0) {
        $dmca_unread = $mysqli->query("SELECT count(*) FROM ero_dmca WHERE status = 0")->fetch_row()[0] ?? 0;
    }
    
    $pending_uv = 0;
    $chk_uv = $mysqli->query("SHOW TABLES LIKE 'ero_user_videos'");
    if ($chk_uv && $chk_uv->num_rows > 0) {
        $pending_uv = (int)($mysqli->query("SELECT count(*) FROM ero_user_videos WHERE status = 'pending'")->fetch_row()[0] ?? 0);
    }
    
    $host = filter($_SERVER['HTTP_HOST'] ?? 'sekschi.online');
    $css_file = $_SERVER['DOCUMENT_ROOT'].'/designs/admin.css';
    $css_v = file_exists($css_file) ? filemtime($css_file) : time();
    
    $nav_items = [
        'default'         => ['icon' => 'fa-dashboard',   'label' => 'Boshqaruv',        'href' => '/control.html'],
        'parsing'         => ['icon' => 'fa-bolt',        'label' => 'Universal Parser', 'href' => '/control.html?func=parsing'],
        'view_video'      => ['icon' => 'fa-film',        'label' => 'Videolar',         'href' => '/control.html?func=view_video'],
        'user_videos'     => ['icon' => 'fa-cloud-upload', 'label' => 'User Videolari',  'href' => '/control.html?func=user_videos', 'badge' => $pending_uv],
        'stats'           => ['icon' => 'fa-line-chart',  'label' => 'Statistika',       'href' => '/control.html?func=stats'],
        'advertising'     => ['icon' => 'fa-bullhorn',    'label' => 'Reklama',          'href' => '/control.html?func=advertising'],
        'view_categories' => ['icon' => 'fa-folder-open', 'label' => 'Toifalar & SEO',  'href' => '/control.html?func=view_categories'],
        'dmca'            => ['icon' => 'fa-shield',      'label' => 'DMCA',             'href' => '/control.html?func=dmca', 'badge' => $dmca_unread],
        'comments'        => ['icon' => 'fa-comments',    'label' => 'Izohlar',          'href' => '/control.html?func=comments'],
        'tools'           => ['icon' => 'fa-wrench',      'label' => 'Tizim Vositalari', 'href' => '/control.html?func=tools'],
        'settings'        => ['icon' => 'fa-cogs',        'label' => 'Sozlamalar',       'href' => '/control.html?func=settings'],
        'users'           => ['icon' => 'fa-users',       'label' => 'Adminlar',         'href' => '/control.html?func=users'],
        'logs'            => ['icon' => 'fa-history',     'label' => 'Jurnal',           'href' => '/control.html?func=logs'],
    ];

    echo '<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').' | EroCMS Admin</title>
    <meta name="robots" content="noindex, nofollow" />
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="/designs/admin.css?v='.$css_v.'" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
</head>
<body class="admin-body">

<header class="adm-header">
    <div class="adm-header-inner">
        <a href="/control.html" class="adm-brand">
            <i class="fa fa-play-circle" style="color: #ff9900; font-size: 24px;"></i>
            <span>'.htmlspecialchars($host, ENT_QUOTES, 'UTF-8').'</span>
            <span class="adm-brand-badge">ADMIN</span>
        </a>
        <div class="adm-header-actions">
            <a href="/control.html?func=stats#online_section" class="adm-live-badge" title="Onlayn foydalanuvchilar monitoringi" style="text-decoration:none;">
                <span class="adm-pulse-dot"></span>
                <span>Onlayn: <b>'.$online_count.'</b></span>
            </a>
            <a href="/" target="_blank" class="adm-btn-site">
                <i class="fa fa-external-link"></i> Saytni ko‘rish
            </a>
            <a href="/control.html?func=out" class="adm-btn-logout" title="Chiqish">
                <i class="fa fa-sign-out"></i> Chiqish
            </a>
        </div>
    </div>
</header>

<nav class="adm-nav">
    <div class="adm-nav-inner">';
    
    foreach ($nav_items as $k => $item) {
        $is_active = ($active_func === $k) ? 'active' : '';
        echo '<a href="'.$item['href'].'" class="adm-nav-item '.$is_active.'">
            <i class="fa '.$item['icon'].'"></i>
            <span>'.$item['label'].'</span>';
        if (!empty($item['badge']) && $item['badge'] > 0) {
            echo '<span class="adm-nav-badge">'.$item['badge'].'</span>';
        }
        echo '</a>';
    }
    
    echo '</div>
</nav>

<main class="adm-main">';
}

function admin_foot() {
    global $mysqli, $version;
    $exec_time = round(microtime(true) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true)), 3);
    $db_info = '';
    try {
        if ($mysqli instanceof mysqli) {
            $db_info = @$mysqli->server_info ?: '';
        }
    } catch (\Throwable $e) {
        $db_info = '';
    }
    echo '</main>

<footer class="adm-footer">
    <div style="max-width: 1400px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div>&copy; '.date('Y').' <b>EroCMS Pro</b> &mdash; Barcha huquqlar himoyalangan.</div>
        <div style="color: #64748b;">PHP '.phpversion().(!empty($db_info) ? ' | MySQL '.$db_info : '').' | Bajarilish: '.$exec_time.'s</div>
    </div>
</footer>

</body>
</html>';
}
