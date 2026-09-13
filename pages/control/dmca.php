<?php
/**
 * EroCMS DMCA Murojaatlarini Boshqarish Paneli
 */

if (!defined('ADMIN_LOADED') && !isset($user)) {
    header('Location: /control.html');
    exit;
}

$mysqli->query("CREATE TABLE IF NOT EXISTS `ero_dmca` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `video_url` text NOT NULL,
  `message` text NOT NULL,
  `date` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `ip` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$msg = '';

if (isset($_GET['action'])) {
    $action = filter($_GET['action']);
    $item_id = abs(intval($_GET['id'] ?? 0));

    if ($action === 'delete_notice' && $item_id > 0) {
        $mysqli->query("DELETE FROM ero_dmca WHERE id = '$item_id'");
        $msg = '<div class="adm-alert adm-alert-success"><i class="fa fa-check"></i> Murojaat o‘chirildi.</div>';
    } elseif ($action === 'set_status' && $item_id > 0) {
        $new_st = abs(intval($_GET['to'] ?? 1));
        $mysqli->query("UPDATE ero_dmca SET status = '$new_st' WHERE id = '$item_id'");
        $msg = '<div class="adm-alert adm-alert-success"><i class="fa fa-check"></i> Murojaat holati yangilandi.</div>';
    } elseif ($action === 'delete_video' && $item_id > 0) {
        $row_dmca = $mysqli->query("SELECT * FROM ero_dmca WHERE id = '$item_id' LIMIT 1")->fetch_assoc();
        if ($row_dmca && !empty($row_dmca['video_url'])) {
            $raw_url = $row_dmca['video_url'];
            $translit = '';
            if (preg_match('|/watch/([a-zA-Z0-9_-]+)\.html|', $raw_url, $m)) {
                $translit = $m[1];
            }

            if (!empty($translit)) {
                $doc_root = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2);
                $vid_res = $mysqli->query("SELECT id, screenshot, address FROM ero_files WHERE translit = '".mysqli_real_escape_string($mysqli, $translit)."' LIMIT 1");
                if ($vid_res && $vid_res->num_rows > 0) {
                    $vid = $vid_res->fetch_assoc();
                    if (!empty($vid['screenshot']) && strpos($vid['screenshot'], 'http') !== 0 && file_exists($doc_root . $vid['screenshot'])) {
                        @unlink($doc_root . $vid['screenshot']);
                    }
                    if (!empty($vid['address']) && strpos($vid['address'], '/content/video/') === 0 && file_exists($doc_root . $vid['address'])) {
                        @unlink($doc_root . $vid['address']);
                    }
                    $mysqli->query("DELETE FROM ero_files WHERE id = '{$vid['id']}'");
                    $mysqli->query("DELETE FROM ero_likes WHERE id_file = '{$vid['id']}'");
                    $mysqli->query("DELETE FROM ero_comments WHERE id_file = '{$vid['id']}'");
                    @array_map('unlink', glob($doc_root . '/content/cache/*.html'));
                    $mysqli->query("UPDATE ero_dmca SET status = 1 WHERE id = '$item_id'");
                    $msg = '<div class="adm-alert adm-alert-success"><i class="fa fa-check"></i> Shikoyat qilingan video (translit: '.$translit.') saytdan butunlay o‘chirildi va murojaat bajarildi deb belgilandi!</div>';
                } else {
                    $msg = '<div class="adm-alert adm-alert-danger"><i class="fa fa-times"></i> Video bazadan topilmadi (ehtimol oldinroq o‘chirilgan).</div>';
                }
            } else {
                $msg = '<div class="adm-alert adm-alert-danger"><i class="fa fa-times"></i> Video havolasidan translit topilmadi.</div>';
            }
        }
    }
}

$total_dmca = (int)($mysqli->query("SELECT COUNT(*) FROM ero_dmca")->fetch_row()[0] ?? 0);
$new_dmca = (int)($mysqli->query("SELECT COUNT(*) FROM ero_dmca WHERE status = 0")->fetch_row()[0] ?? 0);
$done_dmca = (int)($mysqli->query("SELECT COUNT(*) FROM ero_dmca WHERE status = 1")->fetch_row()[0] ?? 0);

$filter = filter($_GET['filter'] ?? 'all');
$where_sql = "";
if ($filter === 'new') {
    $where_sql = "WHERE status = 0";
} elseif ($filter === 'done') {
    $where_sql = "WHERE status = 1";
}

$query = $mysqli->query("SELECT * FROM ero_dmca $where_sql ORDER BY id DESC LIMIT 50");
?>

<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title"><i class="fa fa-shield" style="color: #ff9900;"></i> DMCA Mualliflik Shikoyatlari</h1>
        <p class="adm-page-subtitle">Mualliflik huquqi egalarining <code>/dmca.html</code> sahifasi orqali yuborgan arizalari</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="?func=dmca&filter=all" class="adm-btn <?=($filter === 'all' ? 'adm-btn-primary' : 'adm-btn-secondary')?> adm-btn-sm">
            Barchasi (<?=$total_dmca?>)
        </a>
        <a href="?func=dmca&filter=new" class="adm-btn <?=($filter === 'new' ? 'adm-btn-danger' : 'adm-btn-secondary')?> adm-btn-sm">
            Yangi (<?=$new_dmca?>)
        </a>
        <a href="?func=dmca&filter=done" class="adm-btn <?=($filter === 'done' ? 'adm-btn-success' : 'adm-btn-secondary')?> adm-btn-sm">
            Bajarilgan (<?=$done_dmca?>)
        </a>
        <a href="/dmca.html" target="_blank" class="adm-btn adm-btn-secondary adm-btn-sm">
            <i class="fa fa-external-link"></i> Ommaviy Forma
        </a>
    </div>
</div>

<?=$msg?>

<?php if ($query && $query->num_rows > 0): ?>
    <div style="display:flex; flex-direction:column; gap:16px;">
    <?php while ($row = $query->fetch_assoc()): ?>
        <?php $is_new = ($row['status'] == 0); ?>
        <div class="adm-card" style="margin-bottom:0; border-left: 4px solid <?=($is_new ? '#ef4444' : '#10b981')?>;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:10px; margin-bottom:12px;">
                <div>
                    <span style="font-size:16px; font-weight:700; color:#fff;"><?=$row['name']?></span> &nbsp;
                    <a href="mailto:<?=htmlspecialchars($row['email'])?>" style="color:#ff9900; text-decoration:none; font-size:13px; font-weight:600;">
                        <i class="fa fa-envelope"></i> <?=htmlspecialchars($row['email'])?>
                    </a>
                </div>
                <div style="display:flex; align-items:center; gap:8px; font-size:12px; color:#94a3b8;">
                    <span><i class="fa fa-clock-o"></i> <?=date('d.m.Y H:i', $row['date'])?></span>
                    <span><i class="fa fa-map-marker"></i> IP: <?=$row['ip']?></span>
                    <?php if ($is_new): ?>
                        <span class="adm-badge adm-badge-danger">YANGI</span>
                    <?php else: ?>
                        <span class="adm-badge adm-badge-success">BAJARILGAN</span>
                    <?php endif; ?>
                </div>
            </div>

            <div style="margin-bottom:12px; background:#111318; padding:10px 14px; border-radius:6px; border:1px solid #282c37; font-size:13px;">
                <b style="color:#ff9900;">Shikoyat qilingan video havolasi:</b><br />
                <a href="<?=htmlspecialchars($row['video_url'])?>" target="_blank" style="color:#60a5fa; word-break:break-all; font-weight:500;">
                    <?=htmlspecialchars($row['video_url'])?> <i class="fa fa-external-link"></i>
                </a>
            </div>

            <div style="margin-bottom:16px; color:#cbd5e1; font-size:13px; line-height:1.6; background:rgba(255,255,255,0.02); padding:10px 14px; border-radius:6px;">
                <b style="color:#ff9900;">Murojaat matni / Asos:</b><br />
                <?=nl2br(htmlspecialchars($row['message']))?>
            </div>

            <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                <a href="?func=dmca&action=delete_video&id=<?=$row['id']?>" onclick="return confirm('Haqiqatan ham ushbu videoni saytdan butunlay o‘chirib tashlamoqchimisiz?');" class="adm-btn adm-btn-danger adm-btn-sm">
                    <i class="fa fa-trash"></i> Videoni Saytdan Butunlay O‘chirish
                </a>

                <?php if ($is_new): ?>
                    <a href="?func=dmca&action=set_status&id=<?=$row['id']?>&to=1" class="adm-btn adm-btn-success adm-btn-sm">
                        <i class="fa fa-check"></i> Bajarildi deb belgilash
                    </a>
                <?php else: ?>
                    <a href="?func=dmca&action=set_status&id=<?=$row['id']?>&to=0" class="adm-btn adm-btn-secondary adm-btn-sm">
                        <i class="fa fa-undo"></i> Qayta yangi qilish
                    </a>
                <?php endif; ?>

                <a href="?func=dmca&action=delete_notice&id=<?=$row['id']?>" onclick="return confirm('Murojaat arizasini o‘chirishni tasdiqlaysizmi?');" class="adm-btn adm-btn-secondary adm-btn-sm" style="color:#ef4444;">
                    <i class="fa fa-times"></i> Arizani o‘chirish
                </a>
            </div>
        </div>
    <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="adm-card" style="text-align:center; padding:40px; color:#64748b;">
        <i class="fa fa-shield" style="font-size:36px; color:#10b981; margin-bottom:12px; display:block;"></i>
        Hozircha hech qanday DMCA murojaatlari mavjud emas.
    </div>
<?php endif; ?>
