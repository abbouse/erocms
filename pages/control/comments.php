<?php
/**
 * EroCMS Izohlar Moderatsiyasi (Comments Moderation)
 */

if (!defined('ADMIN_LOADED') && !isset($user)) {
    header('Location: /control.html');
    exit;
}

// Jadval mavjudligini va to'g'ri strukturasini ta'minlash
$mysqli->query("CREATE TABLE IF NOT EXISTS `ero_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_video` int(11) NOT NULL,
  `author` varchar(128) NOT NULL,
  `text` text NOT NULL,
  `date` int(11) NOT NULL,
  `ip` varchar(45) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_video` (`id_video`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Agar eski id_file yoki comment ustunlari qolgan bo'lsa avtomatik moslash
$chk_col = @$mysqli->query("SHOW COLUMNS FROM `ero_comments` LIKE 'id_file'");
if ($chk_col && $chk_col->num_rows > 0) {
    @$mysqli->query("ALTER TABLE `ero_comments` CHANGE `id_file` `id_video` INT(11) NOT NULL");
}
$chk_txt = @$mysqli->query("SHOW COLUMNS FROM `ero_comments` LIKE 'comment'");
if ($chk_txt && $chk_txt->num_rows > 0) {
    @$mysqli->query("ALTER TABLE `ero_comments` CHANGE `comment` `text` TEXT NOT NULL");
}

$msg = null;

// 1. Yagona izohni o'chirish
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $del_id = (int)$_GET['id'];
    $mysqli->query("DELETE FROM ero_comments WHERE id = '$del_id'");
    $msg = "Izoh (ID: $del_id) muvaffaqiyatli o‘chirildi.";
}

// 2. Ommaviy o'chirish
if (isset($_POST['bulk_delete']) && !empty($_POST['comment_ids'])) {
    $cids = array_map('intval', (array)$_POST['comment_ids']);
    $cids_str = implode(',', $cids);
    if (!empty($cids_str)) {
        $mysqli->query("DELETE FROM ero_comments WHERE id IN ($cids_str)");
        $msg = count($cids) . " ta izoh o‘chirildi.";
    }
}

// 3. Barcha izohlarni tozalash
if (isset($_GET['action']) && $_GET['action'] === 'clear_all') {
    $mysqli->query("TRUNCATE TABLE ero_comments");
    $msg = "Barcha izohlar to‘liq tozalandi.";
}

// 4. Sahifalash
$per_page = 20;
$page = max(1, (int)($_GET['page'] ?? 1));
$start = ($page - 1) * $per_page;

$total_items = (int)($mysqli->query("SELECT count(*) FROM ero_comments")->fetch_row()[0] ?? 0);
$total_pages = ceil($total_items / $per_page);

$comments_res = $mysqli->query("
    SELECT c.*, f.name as video_name, f.translit as video_translit 
    FROM ero_comments c 
    LEFT JOIN ero_files f ON c.id_video = f.id 
    ORDER BY c.id DESC 
    LIMIT $start, $per_page
");
?>

<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title"><i class="fa fa-comments" style="color: #ff9900;"></i> Izohlar Moderatsiyasi</h1>
        <p class="adm-page-subtitle">Saytdagi videolarga qoldirilgan fikr-mulohazalarni nazorat qilish va spamlarni tozalash</p>
    </div>
    <?php if ($total_items > 0): ?>
    <div>
        <a href="/control.html?func=comments&action=clear_all" class="adm-btn adm-btn-danger adm-btn-sm" onclick="return confirm('Diqqat! Saytdagi BARCHA izohlarni o‘chirib tashlashni tasdiqlaysizmi?');">
            <i class="fa fa-trash"></i> Barcha izohlarni tozalash
        </a>
    </div>
    <?php endif; ?>
</div>

<?php if ($msg): ?>
    <div class="adm-alert adm-alert-success"><i class="fa fa-check-circle"></i> <?=$msg?></div>
<?php endif; ?>

<form method="post" onsubmit="return confirm('Tanlangan izohlarni o‘chirishni tasdiqlaysizmi?');">
    <div class="adm-card">
        <div class="adm-card-header">
            <div style="display:flex; align-items:center; gap:12px;">
                <h3 class="adm-card-title"><i class="fa fa-list"></i> Izohlar Ro‘yxati (Jami: <?=number_format($total_items)?> ta)</h3>
                <button type="submit" name="bulk_delete" value="1" class="adm-btn adm-btn-danger adm-btn-sm">
                    <i class="fa fa-trash"></i> Belgilanganlarni o‘chirish
                </button>
            </div>
        </div>

        <div class="adm-table-wrap">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th width="30" style="text-align:center;">
                            <input type="checkbox" onclick="var c=document.querySelectorAll('.cmt-check'); for(var i=0; i<c.length; i++) c[i].checked=this.checked;" />
                        </th>
                        <th width="50">ID</th>
                        <th width="140">Muallif</th>
                        <th>Izoh matni</th>
                        <th>Tegishli Video</th>
                        <th width="120">Sana & IP</th>
                        <th width="60" style="text-align:right;">Amal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($comments_res && $comments_res->num_rows > 0): ?>
                        <?php while ($c = $comments_res->fetch_assoc()): ?>
                        <tr>
                            <td style="text-align:center;">
                                <input type="checkbox" name="comment_ids[]" value="<?=$c['id']?>" class="cmt-check" />
                            </td>
                            <td style="color:#64748b; font-weight:bold;">#<?=$c['id']?></td>
                            <td>
                                <b><?=htmlspecialchars($c['author'])?></b>
                            </td>
                            <td style="color:#e2e8f0; font-size:13px; max-width:300px; word-break:break-word;">
                                <?=htmlspecialchars($c['text'])?>
                            </td>
                            <td>
                                <?php if (!empty($c['video_name'])): ?>
                                    <a href="/watch/<?=$c['video_translit']?>.html" target="_blank" style="color:#ff9900; text-decoration:none; font-size:12px; font-weight:600;">
                                        <?=htmlspecialchars($c['video_name'])?> &rarr;
                                    </a>
                                <?php else: ?>
                                    <span style="color:#64748b; font-size:12px;">O‘chirilgan video (#<?=$c['id_video']?>)</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:11px; color:#94a3b8;">
                                <?=date('d.m.Y H:i', $c['date'])?><br />
                                <code style="color:#64748b;"><?=$c['ip']?></code>
                            </td>
                            <td style="text-align:right;">
                                <a href="/control.html?func=comments&action=delete&id=<?=$c['id']?>" class="adm-btn adm-btn-danger adm-btn-sm" onclick="return confirm('Ushbu izohni o‘chirishni tasdiqlaysizmi?');" title="O‘chirish">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align:center; padding:30px; color:#64748b;">
                                <i class="fa fa-comments" style="font-size:32px; display:block; margin-bottom:10px; color:#383e50;"></i>
                                Hozircha saytda hech qanday izoh qoldirilmagan.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</form>

<?php if ($total_pages > 1): ?>
<div class="adm-pagination">
    <?php
    for ($p = 1; $p <= $total_pages; $p++) {
        if ($p == $page) {
            echo '<span class="active">' . $p . '</span>';
        } else {
            echo '<a href="/control.html?func=comments&page=' . $p . '">' . $p . '</a>';
        }
    }
    ?>
</div>
<?php endif; ?>
