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
                        <code style="color:#e2e8f0; font-size:12px;"><?=$act['ip']?></code>
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
