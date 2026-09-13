<?php

/*
 * DMCA Murojaatlarini Boshqarish Paneli
 * erocms
 */

if ($user['access'] < 1) {
    header('location: /'); 
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

// Amallar: O'chirish, Statusni o'zgartirish, Videoni o'chirish
if (isset($_GET['action'])) {
    $action = filter($_GET['action']);
    $item_id = abs(intval($_GET['id'] ?? 0));

    if ($action === 'delete_notice' && $item_id > 0) {
        $mysqli->query("DELETE FROM ero_dmca WHERE id = '$item_id'");
        $msg = '<div class="functions_data" style="background:#28a745; color:#fff; padding:10px; margin-bottom:15px; border-radius:4px;"><i class="fa fa-check"></i> Murojaat o‘chirildi.</div>';
    } elseif ($action === 'set_status' && $item_id > 0) {
        $new_st = abs(intval($_GET['to'] ?? 1));
        $mysqli->query("UPDATE ero_dmca SET status = '$new_st' WHERE id = '$item_id'");
        $msg = '<div class="functions_data" style="background:#28a745; color:#fff; padding:10px; margin-bottom:15px; border-radius:4px;"><i class="fa fa-check"></i> Murojaat holati yangilandi.</div>';
    } elseif ($action === 'delete_video' && $item_id > 0) {
        // Murojaatdan video havolasini olamiz
        $row_dmca = $mysqli->query("SELECT * FROM ero_dmca WHERE id = '$item_id' LIMIT 1")->fetch_assoc();
        if ($row_dmca && !empty($row_dmca['video_url'])) {
            $raw_url = $row_dmca['video_url'];
            $translit = '';
            if (preg_match('|/watch/([a-zA-Z0-9_-]+)\.html|', $raw_url, $m)) {
                $translit = $m[1];
            }

            if (!empty($translit)) {
                $doc_root = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2);
                $vid_res = $mysqli->query("SELECT id, screenshot, address, recoil FROM ero_files WHERE translit = '".mysqli_real_escape_string($mysqli, $translit)."' LIMIT 1");
                if ($vid_res && $vid_res->num_rows > 0) {
                    $vid = $vid_res->fetch_assoc();
                    if (!empty($vid['screenshot']) && strpos($vid['screenshot'], 'http') !== 0 && file_exists($doc_root . $vid['screenshot'])) {
                        @unlink($doc_root . $vid['screenshot']);
                    }
                    if (!empty($vid['address']) && strpos($vid['address'], '/content/video/') === 0 && file_exists($doc_root . $vid['address'])) {
                        @unlink($doc_root . $vid['address']);
                    }
                    $mysqli->query("DELETE FROM ero_files WHERE id = '{$vid['id']}'");
                    $mysqli->query("DELETE FROM ero_likes WHERE id_video = '{$vid['id']}'");
                    $mysqli->query("DELETE FROM ero_comments WHERE id_video = '{$vid['id']}'");
                    @array_map('unlink', glob($doc_root . '/content/cache/*.html'));
                    $mysqli->query("UPDATE ero_dmca SET status = 1 WHERE id = '$item_id'");
                    $msg = '<div class="functions_data" style="background:#28a745; color:#fff; padding:10px; margin-bottom:15px; border-radius:4px;"><i class="fa fa-check"></i> Shikoyat qilingan video (translit: '.$translit.') saytdan butunlay o‘chirildi va murojaat bajarildi deb belgilandi!</div>';
                } else {
                    $msg = '<div class="functions_data" style="background:#dc3545; color:#fff; padding:10px; margin-bottom:15px; border-radius:4px;"><i class="fa fa-times"></i> Video bazadan topilmadi (ehtimol oldinroq o‘chirilgan).</div>';
                }
            } else {
                $msg = '<div class="functions_data" style="background:#dc3545; color:#fff; padding:10px; margin-bottom:15px; border-radius:4px;"><i class="fa fa-times"></i> Video havolasidan translit topilmadi.</div>';
            }
        }
    }
}

// Murojaatlar statistikasi
$total_dmca = $mysqli->query("SELECT COUNT(*) FROM ero_dmca")->fetch_row()[0];
$new_dmca = $mysqli->query("SELECT COUNT(*) FROM ero_dmca WHERE status = 0")->fetch_row()[0];
$done_dmca = $mysqli->query("SELECT COUNT(*) FROM ero_dmca WHERE status = 1")->fetch_row()[0];

$filter = filter($_GET['filter'] ?? 'all');
$where_sql = "";
if ($filter === 'new') {
    $where_sql = "WHERE status = 0";
} elseif ($filter === 'done') {
    $where_sql = "WHERE status = 1";
}

$query = $mysqli->query("SELECT * FROM ero_dmca $where_sql ORDER BY id DESC LIMIT 50");
?>

<div class="functions_data" style="border-left: 4px solid #ff9900; background: #1a1815; padding: 16px; margin-bottom: 20px;">
    <h2 style="color:#ff9900; margin: 0 0 6px 0;"><i class="fa fa-shield"></i> DMCA Murojaatlar Boshqaruvi</h2>
    <p style="color:#ccc; font-size:13px; line-height: 1.5; margin: 0;">
        Foydalanuvchilar va mualliflik huquqi egalarining <code>/dmca.html</code> orqali yuborgan murojaatlari.
    </p>
    <div style="margin-top: 12px; display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="?func=dmca&filter=all" class="action-btn <?=($filter === 'all' ? 'active' : '')?>" style="text-decoration:none; padding: 6px 12px; font-size:13px; border-radius:4px; background:<?=($filter === 'all' ? '#ff9900; color:#000; font-weight:bold;' : '#333; color:#fff;')?>">
            Barchasi (<?=$total_dmca?>)
        </a>
        <a href="?func=dmca&filter=new" class="action-btn <?=($filter === 'new' ? 'active' : '')?>" style="text-decoration:none; padding: 6px 12px; font-size:13px; border-radius:4px; background:<?=($filter === 'new' ? '#dc3545; color:#fff; font-weight:bold;' : '#333; color:#fff;')?>">
            Yangi / Ko‘rilmagan (<?=$new_dmca?>)
        </a>
        <a href="?func=dmca&filter=done" class="action-btn <?=($filter === 'done' ? 'active' : '')?>" style="text-decoration:none; padding: 6px 12px; font-size:13px; border-radius:4px; background:<?=($filter === 'done' ? '#28a745; color:#fff; font-weight:bold;' : '#333; color:#fff;')?>">
            Bajarilgan (<?=$done_dmca?>)
        </a>
        <a href="/dmca.html" target="_blank" style="text-decoration:none; padding: 6px 12px; font-size:13px; border-radius:4px; background:#444; color:#ff9900;">
            <i class="fa fa-external-link"></i> DMCA sahifasini ochish
        </a>
    </div>
</div>

<?=$msg?>

<?php if ($query && $query->num_rows > 0): ?>
    <div style="display:flex; flex-direction:column; gap:14px;">
    <?php while ($row = $query->fetch_assoc()): ?>
        <?php
            $is_new = ($row['status'] == 0);
            $card_border = $is_new ? '#dc3545' : '#28a745';
            $bg_card = $is_new ? '#1f1616' : '#141a16';
        ?>
        <div class="functions_data" style="border-left: 4px solid <?=$card_border?>; background: <?=$bg_card?>; padding: 16px; border-radius: 4px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:10px; margin-bottom:10px;">
                <div>
                    <b style="font-size:16px; color:#fff;"><?=$row['name']?></b> &nbsp;
                    <a href="mailto:<?=htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8')?>" style="color:#ff9900; text-decoration:none; font-size:13px;">
                        <i class="fa fa-envelope"></i> <?=htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8')?>
                    </a>
                </div>
                <div style="font-size:12px; color:#888;">
                    <i class="fa fa-clock-o"></i> <?=date('d.m.Y H:i', $row['date'])?> &nbsp;|&nbsp; 
                    <i class="fa fa-map-marker"></i> IP: <?=$row['ip']?> &nbsp;|&nbsp;
                    <?php if ($is_new): ?>
                        <span style="background:#dc3545; color:#fff; padding:2px 6px; border-radius:3px; font-weight:bold;">YANGI</span>
                    <?php else: ?>
                        <span style="background:#28a745; color:#fff; padding:2px 6px; border-radius:3px; font-weight:bold;">BAJARILGAN</span>
                    <?php endif; ?>
                </div>
            </div>

            <div style="margin-bottom:10px; background:#000; padding:10px; border-radius:4px; font-size:13px;">
                <b style="color:#ff9900;">Shikoyat qilingan video URL:</b><br />
                <a href="<?=htmlspecialchars($row['video_url'], ENT_QUOTES, 'UTF-8')?>" target="_blank" style="color:#64b5f6; word-break:break-all;">
                    <?=htmlspecialchars($row['video_url'], ENT_QUOTES, 'UTF-8')?> <i class="fa fa-external-link"></i>
                </a>
            </div>

            <div style="margin-bottom:14px; color:#ddd; font-size:13px; line-height:1.5;">
                <b style="color:#ff9900;">Murojaat matni / Asos:</b><br />
                <?=nl2br(htmlspecialchars($row['message'], ENT_QUOTES, 'UTF-8'))?>
            </div>

            <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                <a href="?func=dmca&action=delete_video&id=<?=$row['id']?>" onclick="return confirm('Rostdan ham ushbu videoni saytdan butunlay o‘chirib tashlamoqchimisiz?');" class="action-btn" style="background:#dc3545; color:#fff; text-decoration:none; padding:6px 12px; border-radius:4px; font-size:12px; font-weight:bold;">
                    <i class="fa fa-trash"></i> Videoni saytdan o‘chirish
                </a>

                <?php if ($is_new): ?>
                    <a href="?func=dmca&action=set_status&id=<?=$row['id']?>&to=1" class="action-btn" style="background:#28a745; color:#fff; text-decoration:none; padding:6px 12px; border-radius:4px; font-size:12px;">
                        <i class="fa fa-check"></i> Bajarildi deb belgilash
                    </a>
                <?php else: ?>
                    <a href="?func=dmca&action=set_status&id=<?=$row['id']?>&to=0" class="action-btn" style="background:#ffc107; color:#000; text-decoration:none; padding:6px 12px; border-radius:4px; font-size:12px;">
                        <i class="fa fa-undo"></i> Qayta yangi qilish
                    </a>
                <?php endif; ?>

                <a href="?func=dmca&action=delete_notice&id=<?=$row['id']?>" onclick="return confirm('Murojaatni o‘chirasizmi?');" class="action-btn" style="background:#555; color:#fff; text-decoration:none; padding:6px 12px; border-radius:4px; font-size:12px;">
                    <i class="fa fa-times"></i> Shikoyatni o‘chirish
                </a>
            </div>
        </div>
    <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="functions_data" style="padding:25px; text-align:center; color:#888;">
        <i class="fa fa-check-circle" style="font-size:32px; color:#28a745; margin-bottom:10px; display:block;"></i>
        Hozircha hech qanday DMCA murojaatlari mavjud emas.
    </div>
<?php endif; ?>
