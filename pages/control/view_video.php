<?php
/**
 * EroCMS Videolar Boshqaruvi (Mass Video Management & Moderation)
 */

if (!defined('ADMIN_LOADED') && !isset($user)) {
    header('Location: /control.html');
    exit;
}

$msg = null;
$error = null;

// 1. Yagona videoni o'chirish
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $del_id = (int)$_GET['id'];
    $video_info = $mysqli->query("SELECT * FROM ero_files WHERE id = '$del_id'")->fetch_assoc();
    if ($video_info) {
        // Fayllarni o'chirishga urinish
        if (!empty($video_info['screenshot']) && strpos($video_info['screenshot'], '/content/') === 0) {
            @unlink($_SERVER['DOCUMENT_ROOT'] . $video_info['screenshot']);
        }
        if (!empty($video_info['address']) && strpos($video_info['address'], '/content/') === 0) {
            @unlink($_SERVER['DOCUMENT_ROOT'] . $video_info['address']);
        }
        $mysqli->query("DELETE FROM ero_files WHERE id = '$del_id'");
        $mysqli->query("DELETE FROM ero_likes WHERE id_video = '$del_id'");
        $mysqli->query("DELETE FROM ero_comments WHERE id_video = '$del_id'");
        $mysqli->query("DELETE FROM ero_favorites WHERE id_video = '$del_id'");
        @array_map('unlink', glob($_SERVER['DOCUMENT_ROOT'] . '/content/cache/*.html'));
        $msg = "Video (ID: $del_id) muvaffaqiyatli o‘chirildi.";
    }
}

// 2. Ommaviy amallar (Bulk Actions)
if (isset($_POST['bulk_action']) && !empty($_POST['video_ids'])) {
    $action = $_POST['bulk_action'];
    $selected_ids = array_map('intval', (array)$_POST['video_ids']);
    $ids_str = implode(',', $selected_ids);

    if (!empty($ids_str)) {
        if ($action === 'delete') {
            // Ommaviy o'chirish
            $res = $mysqli->query("SELECT screenshot, address FROM ero_files WHERE id IN ($ids_str)");
            while ($row = $res->fetch_assoc()) {
                if (!empty($row['screenshot']) && strpos($row['screenshot'], '/content/') === 0) {
                    @unlink($_SERVER['DOCUMENT_ROOT'] . $row['screenshot']);
                }
                if (!empty($row['address']) && strpos($row['address'], '/content/') === 0) {
                    @unlink($_SERVER['DOCUMENT_ROOT'] . $row['address']);
                }
            }
            $mysqli->query("DELETE FROM ero_files WHERE id IN ($ids_str)");
            $mysqli->query("DELETE FROM ero_likes WHERE id_video IN ($ids_str)");
            $mysqli->query("DELETE FROM ero_comments WHERE id_video IN ($ids_str)");
            $mysqli->query("DELETE FROM ero_favorites WHERE id_video IN ($ids_str)");
            @array_map('unlink', glob($_SERVER['DOCUMENT_ROOT'] . '/content/cache/*.html'));
            $msg = count($selected_ids) . " ta video butunlay o‘chirildi.";
        } elseif ($action === 'move' && isset($_POST['target_category'])) {
            $target_cat = (int)$_POST['target_category'];
            $mysqli->query("UPDATE ero_files SET category = '$target_cat' WHERE id IN ($ids_str)");
            $msg = count($selected_ids) . " ta video tanlangan bo‘limga ko‘chirildi.";
        } elseif ($action === 'reset_views') {
            $mysqli->query("UPDATE ero_files SET view = 0 WHERE id IN ($ids_str)");
            $msg = count($selected_ids) . " ta videoning ko‘rishlar soni nollashtirildi.";
        }
    }
}

// 3. Filtrlash va Qidiruv parametrlari
$where_clauses = ["1=1"];
$search_query = trim($_GET['q'] ?? '');
if (!empty($search_query)) {
    $safe_q = mysqli_real_escape_string($mysqli, $search_query);
    if (is_numeric($search_query)) {
        $where_clauses[] = "(f.id = '$safe_q' OR f.name LIKE '%$safe_q%')";
    } else {
        $where_clauses[] = "f.name LIKE '%$safe_q%'";
    }
}

$filter_cat = isset($_GET['category']) ? (int)$_GET['category'] : 0;
if ($filter_cat > 0) {
    $where_clauses[] = "f.category = '$filter_cat'";
}

$sort = $_GET['sort'] ?? 'new';
switch ($sort) {
    case 'view_desc':
        $order_by = "f.view DESC";
        break;
    case 'view_asc':
        $order_by = "f.view ASC";
        break;
    case 'downloads_desc':
        $order_by = "f.downloads DESC";
        break;
    default:
        $order_by = "f.id DESC";
        break;
}

$where_sql = implode(' AND ', $where_clauses);

// 4. Sahifalash (Pagination)
$per_page = 20;
$page = max(1, (int)($_GET['page'] ?? 1));
$start = ($page - 1) * $per_page;

$total_res = $mysqli->query("SELECT count(*) FROM ero_files f WHERE $where_sql")->fetch_row();
$total_items = (int)($total_res[0] ?? 0);
$total_pages = ceil($total_items / $per_page);

// Videolar ro'yxati
$videos_res = $mysqli->query("
    SELECT f.*, c.name as cat_name 
    FROM ero_files f 
    LEFT JOIN ero_categories c ON f.category = c.id 
    WHERE $where_sql 
    ORDER BY $order_by 
    LIMIT $start, $per_page
");

// Toifalar ro'yxati
$categories_res = $mysqli->query("SELECT id, name FROM ero_categories ORDER BY id ASC");
$all_categories = [];
while ($c = $categories_res->fetch_assoc()) {
    $all_categories[$c['id']] = $c['name'];
}
?>

<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title"><i class="fa fa-film" style="color: #ff9900;"></i> Videolar Boshqaruvi</h1>
        <p class="adm-page-subtitle">Jami <b><?=number_format($total_items)?> ta</b> video topildi. Qidiruv, saralash va ommaviy amallar.</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="/control.html?func=parsing" class="adm-btn adm-btn-primary adm-btn-sm"><i class="fa fa-bolt"></i> Parserdan Yuklash</a>
        <a href="/control.html?func=import" class="adm-btn adm-btn-secondary adm-btn-sm"><i class="fa fa-plus"></i> Yangi Qo‘shish</a>
    </div>
</div>

<?php if ($msg): ?>
    <div class="adm-alert adm-alert-success"><i class="fa fa-check-circle"></i> <?=$msg?></div>
<?php endif; ?>

<!-- Qidiruv va Filtrlash Paneli -->
<div class="adm-card" style="padding:14px;">
    <form method="get" action="/control.html" style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
        <input type="hidden" name="func" value="view_video" />
        
        <div style="flex:1; min-width:200px;">
            <input type="text" name="q" class="adm-input" placeholder="Video nomi yoki ID si bo‘yicha qidiring..." value="<?=htmlspecialchars($search_query)?>" />
        </div>

        <div style="min-width:160px;">
            <select name="category" class="adm-select">
                <option value="0">Barcha toifalar</option>
                <?php foreach ($all_categories as $cid => $cname): ?>
                    <option value="<?=$cid?>" <?=($filter_cat == $cid ? 'selected' : '')?>><?=htmlspecialchars($cname)?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="min-width:160px;">
            <select name="sort" class="adm-select">
                <option value="new" <?=($sort === 'new' ? 'selected' : '')?>>Eng yangilar (ID ↓)</option>
                <option value="view_desc" <?=($sort === 'view_desc' ? 'selected' : '')?>>Eng ko‘p ko‘rilgan (Ko‘rishlar ↓)</option>
                <option value="downloads_desc" <?=($sort === 'downloads_desc' ? 'selected' : '')?>>Eng ko‘p yuklangan (Yuklash ↓)</option>
                <option value="view_asc" <?=($sort === 'view_asc' ? 'selected' : '')?>>Kam ko‘rilganlar (Ko‘rishlar ↑)</option>
            </select>
        </div>

        <button type="submit" class="adm-btn adm-btn-primary">
            <i class="fa fa-search"></i> Qidirish
        </button>

        <?php if (!empty($search_query) || $filter_cat > 0 || $sort !== 'new'): ?>
            <a href="/control.html?func=view_video" class="adm-btn adm-btn-secondary" title="Filtrni tozalash">
                <i class="fa fa-times"></i>
            </a>
        <?php endif; ?>
    </form>
</div>

<!-- Videolar Jadvali va Ommaviy Boshqaruv -->
<form method="post" id="bulkForm" onsubmit="return confirmBulkAction();">
    <div class="adm-card" style="margin-bottom:0;">
        <div class="adm-card-header">
            <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                <span style="font-weight:700; color:#fff; font-size:14px;">
                    <i class="fa fa-list"></i> Videolar Ro‘yxati
                </span>
                
                <!-- Ommaviy amallar qatori -->
                <div style="display:flex; align-items:center; gap:8px; background:#12141a; padding:4px 8px; border-radius:6px; border:1px solid #282c37;">
                    <select name="bulk_action" id="bulkActionSelect" class="adm-select" style="padding:4px 8px; font-size:12px; width:auto;" onchange="toggleCategorySelect();">
                        <option value="">-- Tanlanganlarga amal --</option>
                        <option value="delete">🗑 Ommaviy o‘chirish</option>
                        <option value="move">📁 Bo‘limga ko‘chirish</option>
                        <option value="reset_views">🔄 Ko‘rishlarni nollash</option>
                    </select>

                    <select name="target_category" id="targetCatSelect" class="adm-select" style="padding:4px 8px; font-size:12px; width:auto; display:none;">
                        <?php foreach ($all_categories as $cid => $cname): ?>
                            <option value="<?=$cid?>"><?=htmlspecialchars($cname)?></option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit" class="adm-btn adm-btn-secondary adm-btn-sm">Bajarish</button>
                </div>
            </div>

            <div style="font-size:12px; color:#94a3b8;">
                Ko‘rsatilmoqda: <b><?=min($total_items, $start + 1)?>-<?=min($total_items, $start + $per_page)?></b> / Jami: <?=number_format($total_items)?>
            </div>
        </div>

        <div class="adm-table-wrap">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th width="30" style="text-align:center;">
                            <input type="checkbox" id="selectAll" onclick="toggleSelectAll(this);" />
                        </th>
                        <th width="50">ID</th>
                        <th width="70">Poster</th>
                        <th>Video Nomi & Tafsilotlari</th>
                        <th>Bo‘lim</th>
                        <th width="100">Statistika</th>
                        <th width="110">Sana</th>
                        <th width="100" style="text-align:right;">Amallar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($videos_res && $videos_res->num_rows > 0): ?>
                        <?php while ($row = $videos_res->fetch_assoc()): ?>
                        <tr>
                            <td style="text-align:center;">
                                <input type="checkbox" name="video_ids[]" value="<?=$row['id']?>" class="video-checkbox" />
                            </td>
                            <td style="color:#64748b; font-weight:700;">
                                #<?=$row['id']?>
                            </td>
                            <td>
                                <div style="position:relative; width:64px; height:42px;">
                                    <img src="<?=$row['screenshot']?>" style="width:100%; height:100%; object-fit:cover; border-radius:4px; border:1px solid #333;" onerror="this.src='/designs/no_poster.jpg';" />
                                    <?php if (!empty($row['duration'])): ?>
                                        <span style="position:absolute; bottom:2px; right:2px; background:rgba(0,0,0,0.8); color:#fff; font-size:9px; padding:1px 3px; border-radius:2px;">
                                            <?=$row['duration']?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <a href="/watch/<?=$row['translit']?>.html" target="_blank" style="color:#fff; text-decoration:none; font-weight:600; font-size:13px; line-height:1.3; display:block; margin-bottom:4px;">
                                    <?=htmlspecialchars($row['name'])?>
                                </a>
                                <div style="display:flex; gap:8px; font-size:11px; color:#64748b;">
                                    <?php if (!empty($row['server'])): ?>
                                        <span><i class="fa fa-server"></i> <?=htmlspecialchars($row['server'])?></span>
                                    <?php endif; ?>
                                    <?php if ($row['embed']): ?>
                                        <span class="adm-badge adm-badge-info" style="font-size:10px; padding:0 4px;">Embed Player</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="adm-badge adm-badge-warning">
                                    <?=$row['cat_name'] ?? 'Bo‘limsiz'?>
                                </span>
                            </td>
                            <td>
                                <div style="font-size:12px; color:#e2e8f0;">
                                    <i class="fa fa-eye" style="color:#ff9900;"></i> <?=number_format($row['view'])?>
                                </div>
                                <div style="font-size:11px; color:#64748b;">
                                    <i class="fa fa-download"></i> <?=number_format($row['downloads'])?> yuklash
                                </div>
                            </td>
                            <td style="color:#94a3b8; font-size:12px;">
                                <?=date('d.m.Y', $row['date'])?><br />
                                <small style="color:#64748b;"><?=date('H:i', $row['date'])?></small>
                            </td>
                            <td style="text-align:right; white-space:nowrap;">
                                <a href="/watch/<?=$row['translit']?>.html" target="_blank" class="adm-btn adm-btn-secondary adm-btn-sm" title="Saytda ko‘rish">
                                    <i class="fa fa-external-link"></i>
                                </a>
                                <a href="/control.html?func=rewriting&id=<?=$row['id']?>" class="adm-btn adm-btn-secondary adm-btn-sm" title="Tahrirlash">
                                    <i class="fa fa-pencil"></i>
                                </a>
                                <a href="/control.html?func=view_video&action=delete&id=<?=$row['id']?>" class="adm-btn adm-btn-danger adm-btn-sm" onclick="return confirm('Haqiqatan ham ushbu videoni o‘chirmoqchimisiz?');" title="O‘chirish">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align:center; padding:30px; color:#64748b;">
                                <i class="fa fa-film" style="font-size:32px; display:block; margin-bottom:10px; color:#383e50;"></i>
                                Berilgan parametrlar bo‘yicha hech qanday video topilmadi.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</form>

<!-- Sahifalash (Pagination) -->
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

<script>
function toggleSelectAll(master) {
    var checkboxes = document.querySelectorAll('.video-checkbox');
    for (var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = master.checked;
    }
}

function toggleCategorySelect() {
    var action = document.getElementById('bulkActionSelect').value;
    var catSelect = document.getElementById('targetCatSelect');
    if (action === 'move') {
        catSelect.style.display = 'inline-block';
    } else {
        catSelect.style.display = 'none';
    }
}

function confirmBulkAction() {
    var action = document.getElementById('bulkActionSelect').value;
    if (!action) {
        alert('Iltimos, bajariladigan amalni tanlang!');
        return false;
    }
    var checked = document.querySelectorAll('.video-checkbox:checked');
    if (checked.length === 0) {
        alert('Iltimos, kamida bitta videoni belgilang!');
        return false;
    }
    if (action === 'delete') {
        return confirm('Diqqat! Belgilangan ' + checked.length + ' ta videoni haqiqatan ham butunlay o‘chirmoqchimisiz?');
    }
    return true;
}
</script>