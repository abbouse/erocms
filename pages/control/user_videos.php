<?php
/**
 * EroCMS Admin - Foydalanuvchilar Yuklagan Videolarini Moderatsiya Qilish
 */

if (!defined('ADMIN_LOADED') && !isset($user)) {
    header('Location: /control.html');
    exit;
}

if ($user['access'] < 1) {
    header('Location: /');
    exit;
}

$notice = '';
$error = '';

// Amallar (Action)
$action = filter($_GET['action'] ?? '');
$id = intval($_GET['id'] ?? 0);

// 1. Videoni tasdiqlash (Approve)
if ($action === 'approve' && $id > 0) {
    $uv_q = $mysqli->query("SELECT * FROM ero_user_videos WHERE id = '$id' LIMIT 1");
    $uv = $uv_q ? $uv_q->fetch_assoc() : null;

    if ($uv) {
        $raw_translit = transliterate($uv['name']);
        $clean_translit = preg_replace('/[^a-zA-Z0-9_\-]/', '-', trim($raw_translit));
        $clean_translit = preg_replace('/-+/', '-', $clean_translit);
        $clean_translit = trim($clean_translit, '-');
        if (empty($clean_translit)) {
            $clean_translit = 'video-' . time();
        }

        // Translit takrorlanmasligini tekshirish
        $chk_t = $mysqli->query("SELECT id FROM ero_files WHERE translit = '$clean_translit' LIMIT 1");
        if ($chk_t && $chk_t->num_rows > 0) {
            $clean_translit .= '-' . rand(100, 999);
        }

        $safe_translit = mysqli_real_escape_string($mysqli, $clean_translit);
        $safe_name = mysqli_real_escape_string($mysqli, $uv['name']);
        $safe_desc = mysqli_real_escape_string($mysqli, $uv['description']);
        $cat_id = intval($uv['category']);
        $now = time();
        $member_id = intval($uv['member_id']);

        $final_address = !empty($uv['file_path']) ? $uv['file_path'] : $uv['video_url'];
        $final_recoil = $final_address;
        $final_screen = !empty($uv['screenshot']) ? $uv['screenshot'] : '/designs/no_poster.jpg';
        $server_host = filter($_SERVER['HTTP_HOST'] ?? 'sekschi.online');

        $tags_raw = tags($uv['name'] . ' ' . $uv['description']);
        $tags_str = !empty($tags_raw) ? str_replace(' ', ', ', trim($tags_raw)) : $uv['name'];
        $safe_tags = mysqli_real_escape_string($mysqli, $tags_str);

        $embed_code = '';
        if (strpos($final_address, '<iframe') !== false || (strpos($final_address, 'http') === 0 && (strpos($final_address, 'embed') !== false || strpos($final_address, 'player') !== false))) {
            $embed_code = $final_address;
        }

        $ins = $mysqli->query("
            INSERT INTO ero_files (
                name, description, screenshot, recoil, tags, translit, duration, downloads, 
                server, address, uniqueness, category, view, date, added, member_id, embed
            ) VALUES (
                '$safe_name',
                '$safe_desc',
                '".mysqli_real_escape_string($mysqli, $final_screen)."',
                '".mysqli_real_escape_string($mysqli, $final_recoil)."',
                '$safe_tags',
                '$safe_translit',
                '07:30',
                0,
                '$server_host',
                '".mysqli_real_escape_string($mysqli, $final_address)."',
                '".md5($safe_name . $now)."',
                '$cat_id',
                0,
                '$now',
                '{$user['id']}',
                '$member_id',
                '".mysqli_real_escape_string($mysqli, $embed_code)."'
            )
        ");

        if ($ins) {
            $new_file_id = $mysqli->insert_id;
            $mysqli->query("UPDATE ero_user_videos SET status = 'approved', reject_reason = '' WHERE id = '$id'");
            logs($user['id'], "Foydalanuvchi videosi tasdiqlandi: " . $uv['name'], $new_file_id);
            $notice = "Video muvaffaqiyatli tasdiqlandi va saytda e'lon qilindi! <a href='/watch/{$safe_translit}.html' target='_blank' style='color:#ff9900; font-weight:bold;'>Videoni ko‘rish &rarr;</a>";
        } else {
            $error = "Bazaga qo‘shishda xatolik yuz berdi: " . $mysqli->error;
        }
    } else {
        $error = "Video topilmadi!";
    }
}

// 2. Videoni rad etish (Reject)
if ($action === 'reject' && $id > 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = trim(filter($_POST['reject_reason'] ?? ''));
    if (empty($reason)) {
        $reason = 'Sayt qoidalariga yoki talablariga mos kelmadi.';
    }
    $safe_reason = mysqli_real_escape_string($mysqli, $reason);

    $upd = $mysqli->query("UPDATE ero_user_videos SET status = 'rejected', reject_reason = '$safe_reason' WHERE id = '$id'");
    if ($upd) {
        $notice = "Video muvaffaqiyatli rad etildi.";
        logs($user['id'], "Foydalanuvchi videosi rad etildi (ID: $id, Sabab: $reason)", 0);
    } else {
        $error = "Rad etishda xatolik!";
    }
}

// 3. Videoni o'chirish (Delete)
if ($action === 'delete' && $id > 0) {
    $uv = $mysqli->query("SELECT * FROM ero_user_videos WHERE id = '$id' LIMIT 1")->fetch_assoc();
    if ($uv) {
        if (!empty($uv['file_path']) && file_exists($_SERVER['DOCUMENT_ROOT'] . $uv['file_path'])) {
            @unlink($_SERVER['DOCUMENT_ROOT'] . $uv['file_path']);
        }
        $mysqli->query("DELETE FROM ero_user_videos WHERE id = '$id'");
        logs($user['id'], "Foydalanuvchi videosi butunlay o‘chirildi (ID: $id)", 0);
        $notice = "Video muvaffaqiyatli o‘chirildi.";
    }
}

// Filtr tablari
$filter_status = filter($_GET['status'] ?? 'pending');
$where = "1=1";
if ($filter_status === 'pending') {
    $where = "uv.status = 'pending'";
} elseif ($filter_status === 'approved') {
    $where = "uv.status = 'approved'";
} elseif ($filter_status === 'rejected') {
    $where = "uv.status = 'rejected'";
}

$pending_total = $mysqli->query("SELECT COUNT(*) FROM ero_user_videos WHERE status = 'pending'")->fetch_row()[0] ?? 0;
$approved_total = $mysqli->query("SELECT COUNT(*) FROM ero_user_videos WHERE status = 'approved'")->fetch_row()[0] ?? 0;
$rejected_total = $mysqli->query("SELECT COUNT(*) FROM ero_user_videos WHERE status = 'rejected'")->fetch_row()[0] ?? 0;

$videos_q = $mysqli->query("
    SELECT uv.*, m.username, m.email, c.name as cat_name 
    FROM ero_user_videos uv 
    LEFT JOIN ero_members m ON uv.member_id = m.id 
    LEFT JOIN ero_categories c ON uv.category = c.id 
    WHERE $where 
    ORDER BY uv.id DESC LIMIT 100
");

admin_head('Foydalanuvchi Videolari Moderatsiyasi', 'user_videos');
?>

<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title"><i class="fa fa-cloud-upload" style="color: #ff9900;"></i> Foydalanuvchi Videolari Moderatsiyasi</h1>
        <p class="adm-page-subtitle">Sayt a'zolari tomonidan yuklangan videolarni tekshirish, tasdiqlash yoki rad etish</p>
    </div>
    <div style="display:flex; gap:10px;">
        <a href="/control.html?func=user_videos" class="adm-btn adm-btn-secondary adm-btn-sm"><i class="fa fa-refresh"></i> Yangilash</a>
    </div>
</div>

<?php if (!empty($notice)): ?>
    <div class="adm-alert adm-alert-success" style="margin-bottom:20px;">
        <i class="fa fa-check-circle"></i> <?=$notice?>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="adm-alert adm-alert-danger" style="margin-bottom:20px;">
        <i class="fa fa-exclamation-circle"></i> <?=$error?>
    </div>
<?php endif; ?>

<!-- Holat tablari -->
<div style="display:flex; gap:8px; margin-bottom:20px; flex-wrap:wrap;">
    <a href="/control.html?func=user_videos&status=pending" class="adm-btn <?=$filter_status === 'pending' ? 'adm-btn-primary' : 'adm-btn-secondary'?> adm-btn-sm">
        <i class="fa fa-clock-o"></i> Kutilayotganlar <b>(<?=$pending_total?>)</b>
    </a>
    <a href="/control.html?func=user_videos&status=approved" class="adm-btn <?=$filter_status === 'approved' ? 'adm-btn-primary' : 'adm-btn-secondary'?> adm-btn-sm">
        <i class="fa fa-check"></i> Tasdiqlanganlar <b>(<?=$approved_total?>)</b>
    </a>
    <a href="/control.html?func=user_videos&status=rejected" class="adm-btn <?=$filter_status === 'rejected' ? 'adm-btn-primary' : 'adm-btn-secondary'?> adm-btn-sm">
        <i class="fa fa-times"></i> Rad etilganlar <b>(<?=$rejected_total?>)</b>
    </a>
    <a href="/control.html?func=user_videos&status=all" class="adm-btn <?=$filter_status === 'all' ? 'adm-btn-primary' : 'adm-btn-secondary'?> adm-btn-sm">
        Barchasi
    </a>
</div>

<div class="adm-card">
    <div class="adm-card-header">
        <h3 class="adm-card-title"><i class="fa fa-list"></i> Yuklangan Videolar Ro‘yxati</h3>
    </div>

    <?php if ($videos_q && $videos_q->num_rows > 0): ?>
        <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 20px; padding: 20px;">
            <?php while ($v = $videos_q->fetch_assoc()): 
                $poster = !empty($v['screenshot']) ? $v['screenshot'] : '/designs/no_poster.jpg';
                $video_src = !empty($v['file_path']) ? $v['file_path'] : $v['video_url'];
                $is_file = !empty($v['file_path']);
            ?>
                <div style="background:#1a1e29; border:1px solid #282c37; border-radius:10px; overflow:hidden; display:flex; flex-direction:column;">
                    
                    <!-- Player / Preview -->
                    <div style="background:#000; height:200px; position:relative; display:flex; align-items:center; justify-content:center;">
                        <?php if ($is_file): ?>
                            <video src="<?=$v['file_path']?>" controls poster="<?=$poster?>" style="width:100%; height:100%; object-fit:contain;"></video>
                        <?php elseif (strpos($video_src, '<iframe') !== false): ?>
                            <div style="width:100%; height:100%;"><?=$video_src?></div>
                        <?php else: ?>
                            <video src="<?=$video_src?>" controls poster="<?=$poster?>" style="width:100%; height:100%; object-fit:contain;"></video>
                        <?php endif; ?>
                    </div>

                    <!-- Video Details -->
                    <div style="padding:16px; flex:1; display:flex; flex-direction:column; justify-content:space-between;">
                        <div>
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
                                <h4 style="color:#f8fafc; font-size:15px; margin:0; line-height:1.4;" title="<?=htmlspecialchars($v['name'])?>">
                                    <?=htmlspecialchars($v['name'])?>
                                </h4>
                                <?php if ($v['status'] === 'pending'): ?>
                                    <span class="adm-badge adm-badge-warning" style="margin-left:8px; white-space:nowrap;">Kutilmoqda</span>
                                <?php elseif ($v['status'] === 'approved'): ?>
                                    <span class="adm-badge adm-badge-success" style="margin-left:8px; white-space:nowrap;">Tasdiqlangan</span>
                                <?php else: ?>
                                    <span class="adm-badge adm-badge-danger" style="margin-left:8px; white-space:nowrap;">Rad etilgan</span>
                                <?php endif; ?>
                            </div>

                            <div style="font-size:12px; color:#94a3b8; margin-bottom:10px; line-height:1.8;">
                                <div><i class="fa fa-user" style="color:#ff9900;"></i> Muallif: <b><?=htmlspecialchars($v['username'] ?: 'Noma\'lum')?></b> (<?=htmlspecialchars($v['email'] ?: '-')?>)</div>
                                <div><i class="fa fa-folder-open"></i> Bo‘lim: <b style="color:#f8fafc;"><?=htmlspecialchars($v['cat_name'] ?: 'Tanlanmagan')?></b></div>
                                <div><i class="fa fa-calendar"></i> Sana: <?=date('d.m.Y H:i', $v['date'])?> &bull; IP: <?=htmlspecialchars($v['ip'])?></div>
                            </div>

                            <?php if (!empty($v['description'])): ?>
                                <div style="background:#12141a; padding:8px 12px; border-radius:6px; font-size:12px; color:#cbd5e1; margin-bottom:12px; max-height:80px; overflow-y:auto;">
                                    <?=nl2br(htmlspecialchars($v['description']))?>
                                </div>
                            <?php endif; ?>

                            <?php if ($v['status'] === 'rejected' && !empty($v['reject_reason'])): ?>
                                <div style="background:#3b1111; color:#fca5a5; padding:8px 12px; border-radius:6px; font-size:12px; margin-bottom:12px;">
                                    <b>Rad sababi:</b> <?=htmlspecialchars($v['reject_reason'])?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Action Buttons -->
                        <div style="border-top:1px solid #282c37; padding-top:12px; display:flex; gap:8px; flex-wrap:wrap; justify-content:space-between; align-items:center;">
                            <?php if ($v['status'] !== 'approved'): ?>
                                <a href="/control.html?func=user_videos&action=approve&id=<?=$v['id']?>" class="adm-btn adm-btn-success adm-btn-sm" onclick="return confirm('Ushbu videoni tasdiqlab, asosiy saytga chiqarmoqchimisiz?');">
                                    <i class="fa fa-check"></i> Tasdiqlash
                                </a>
                            <?php endif; ?>

                            <?php if ($v['status'] === 'pending'): ?>
                                <button type="button" class="adm-btn adm-btn-danger adm-btn-sm" onclick="showRejectModal(<?=$v['id']?>)">
                                    <i class="fa fa-ban"></i> Rad etish
                                </button>
                            <?php endif; ?>

                            <a href="/control.html?func=user_videos&action=delete&id=<?=$v['id']?>" class="adm-btn adm-btn-secondary adm-btn-sm" onclick="return confirm('Haqiqatdan ham butunlay o‘chirmoqchimisiz?');" style="color:#ef4444;" title="Butunlay o‘chirish">
                                <i class="fa fa-trash"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div style="padding:40px; text-align:center; color:#64748b;">
            <i class="fa fa-folder-open-o" style="font-size:48px; margin-bottom:12px; display:block;"></i>
            Hozircha ushbu bo‘limda hech qanday video mavjud emas.
        </div>
    <?php endif; ?>
</div>

<!-- Rad etish modali -->
<div id="reject-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.8); z-index:99999; align-items:center; justify-content:center;">
    <div style="background:#1a1e29; border:1px solid #282c37; border-radius:12px; padding:24px; max-width:450px; width:90%;">
        <h3 style="color:#f8fafc; margin-top:0; font-size:18px;"><i class="fa fa-ban" style="color:#ef4444;"></i> Videoni Rad Etish</h3>
        <p style="color:#94a3b8; font-size:13px;">Foydalanuvchi profilida rad etilish sababi ko‘rinadi. Iltimos, sababini yozing:</p>
        <form id="reject-form" method="post" action="">
            <textarea name="reject_reason" id="reject_reason" class="adm-form-control" rows="3" placeholder="Masalan: Video sifati juda past, yoki qoidalarga zid..." required style="width:100%; box-sizing:border-box; background:#12141a; color:#f8fafc; border:1px solid #282c37; border-radius:6px; padding:10px; margin-bottom:15px;"></textarea>
            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="adm-btn adm-btn-secondary" onclick="hideRejectModal()">Bekor qilish</button>
                <button type="submit" class="adm-btn adm-btn-danger">Rad etish</button>
            </div>
        </form>
    </div>
</div>

<script>
function showRejectModal(id) {
    var modal = document.getElementById('reject-modal');
    var form = document.getElementById('reject-form');
    form.action = '/control.html?func=user_videos&action=reject&id=' + id;
    modal.style.display = 'flex';
}
function hideRejectModal() {
    document.getElementById('reject-modal').style.display = 'none';
}
</script>

<?php
admin_foot();
