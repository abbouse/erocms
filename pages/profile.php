<?php
/**
 * EroCMS - Foydalanuvchi Shaxsiy Profili (Profile)
 */

if (!$member) {
    header('Location: /login.html?need_auth=1');
    exit;
}

$tab = filter($_GET['tab'] ?? 'videos');
$allowed_tabs = ['videos', 'favorites', 'comments', 'replies', 'edit'];
if (!in_array($tab, $allowed_tabs)) {
    $tab = 'videos';
}

$profile_msg = '';
$profile_err = '';

// Profil tahrirlash amali
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['act']) && $_POST['act'] === 'edit_profile') {
    $new_username = trim(filter($_POST['username'] ?? ''));
    $new_email = trim(filter($_POST['email'] ?? ''));
    $new_bio = trim(filter($_POST['bio'] ?? ''));
    $old_pass = trim($_POST['old_password'] ?? '');
    $new_pass = trim($_POST['new_password'] ?? '');
    $new_pass_c = trim($_POST['new_password_confirm'] ?? '');

    if (empty($new_username) || empty($new_email)) {
        $profile_err = 'Foydalanuvchi nomi va email bo‘sh bo‘lishi mumkin emas!';
    } elseif (!preg_match('/^[a-zA-Z0-9_\-\.]{3,30}$/u', $new_username)) {
        $profile_err = 'Foydalanuvchi nomi 3 dan 30 tagacha belgi bo‘lishi kerak!';
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $profile_err = 'Email manzili noto‘g‘ri formatda!';
    } else {
        $safe_u = mysqli_real_escape_string($mysqli, $new_username);
        $safe_e = mysqli_real_escape_string($mysqli, $new_email);
        $m_id = intval($member['id']);

        // Uniqueness check (boshqa user bilan to'qnashmasligi)
        $dup_q = $mysqli->query("SELECT id FROM ero_members WHERE (username = '$safe_u' OR email = '$safe_e') AND id != '$m_id' LIMIT 1");
        if ($dup_q && $dup_q->num_rows > 0) {
            $profile_err = 'Ushbu login yoki email boshqa foydalanuvchi tomonidan band qilingan!';
        } else {
            $avatar_path = $member['avatar'];

            // Avatar yuklash
            if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $file_tmp = $_FILES['avatar']['tmp_name'];
                $file_name = $_FILES['avatar']['name'];
                $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

                if (in_array($ext, $allowed_exts) && getimagesize($file_tmp)) {
                    $avatar_dir = $_SERVER['DOCUMENT_ROOT'] . '/content/avatars';
                    if (!is_dir($avatar_dir)) {
                        @mkdir($avatar_dir, 0777, true);
                    }
                    $avatar_filename = 'av_' . $m_id . '_' . time() . '.' . $ext;
                    $dest = $avatar_dir . '/' . $avatar_filename;

                    if (move_uploaded_file($file_tmp, $dest)) {
                        $avatar_path = '/content/avatars/' . $avatar_filename;
                    }
                } else {
                    $profile_err = 'Avatar formati noto‘g‘ri (faqat JPG, PNG, WebP)!';
                }
            }

            // Parolni yangilash
            $pass_sql = "";
            if (!empty($new_pass)) {
                if (empty($old_pass)) {
                    $profile_err = 'Yangi parol o‘rnatish uchun avval joriy parolingizni kiriting!';
                } elseif (!password_verify($old_pass, $member['password']) && hash('sha256', $old_pass) !== $member['password'] && md5($old_pass) !== $member['password']) {
                    $profile_err = 'Joriy parolingiz noto‘g‘ri!';
                } elseif (mb_strlen($new_pass, 'UTF-8') < 6) {
                    $profile_err = 'Yangi parol kamida 6 ta belgidan iborat bo‘lishi kerak!';
                } elseif ($new_pass !== $new_pass_c) {
                    $profile_err = 'Yangi parollar bir-biriga mos kelmadi!';
                } else {
                    $pass_hash = mysqli_real_escape_string($mysqli, password_hash($new_pass, PASSWORD_DEFAULT));
                    $pass_sql = ", password = '$pass_hash'";
                }
            }

            if (empty($profile_err)) {
                $safe_bio = mysqli_real_escape_string($mysqli, $new_bio);
                $safe_av = mysqli_real_escape_string($mysqli, $avatar_path);

                $upd = $mysqli->query("UPDATE ero_members SET username = '$safe_u', email = '$safe_e', bio = '$safe_bio', avatar = '$safe_av' $pass_sql WHERE id = '$m_id'");
                if ($upd) {
                    $profile_msg = 'Profilingiz muvaffaqiyatli yangilandi!';
                    // Refresh member data
                    $member = $mysqli->query("SELECT * FROM ero_members WHERE id = '$m_id'")->fetch_assoc();
                } else {
                    $profile_err = 'Ma\'lumotlarni saqlashda xatolik!';
                }
            }
        }
    }
}

// Menga yozilgan javoblarni o'qilgan deb belgilash
if ($tab === 'replies') {
    @$mysqli->query("UPDATE ero_notifications SET is_read = 1 WHERE member_id = '{$member['id']}' AND is_read = 0");
    $unread_notifications = 0;
}

// Hisob statistikasi
$m_id = intval($member['id']);
$total_uploaded = $mysqli->query("SELECT COUNT(*) FROM ero_user_videos WHERE member_id = '$m_id'")->fetch_row()[0] ?? 0;
$approved_count = $mysqli->query("SELECT COUNT(*) FROM ero_user_videos WHERE member_id = '$m_id' AND status = 'approved'")->fetch_row()[0] ?? 0;
$pending_count = $mysqli->query("SELECT COUNT(*) FROM ero_user_videos WHERE member_id = '$m_id' AND status = 'pending'")->fetch_row()[0] ?? 0;
$rejected_count = $mysqli->query("SELECT COUNT(*) FROM ero_user_videos WHERE member_id = '$m_id' AND status = 'rejected'")->fetch_row()[0] ?? 0;

$u_name_safe = mysqli_real_escape_string($mysqli, $member['username']);
$total_comments = $mysqli->query("SELECT COUNT(*) FROM ero_comments WHERE author = '$u_name_safe'")->fetch_row()[0] ?? 0;
$total_replies = $mysqli->query("SELECT COUNT(*) FROM ero_notifications WHERE member_id = '$m_id'")->fetch_row()[0] ?? 0;

$title = 'Mening Profilim - ' . htmlspecialchars($member['username']) . ' | ' . filter($_SERVER['HTTP_HOST'] ?? 'sekschi.online');
head();
?>

<div class="profile-page-wrapper">

    <?php if (isset($_GET['welcome'])): ?>
        <div class="profile-alert profile-alert-success" style="margin-bottom:15px;">
            <i class="fa fa-smile-o" style="font-size:20px;"></i>
            <span>Xush kelibsiz, <b><?=htmlspecialchars($member['username'])?></b>! Profilingiz faollashtirildi. Endi istalgan bo‘limga video yuklashingiz yoki sevimli videolaringizni saqlashingiz mumkin.</span>
        </div>
    <?php endif; ?>

    <?php if (!empty($profile_msg)): ?>
        <div class="profile-alert profile-alert-success" style="margin-bottom:15px;">
            <i class="fa fa-check-circle"></i> <?=$profile_msg?>
        </div>
    <?php endif; ?>

    <?php if (!empty($profile_err)): ?>
        <div class="profile-alert profile-alert-danger" style="margin-bottom:15px;">
            <i class="fa fa-exclamation-circle"></i> <?=$profile_err?>
        </div>
    <?php endif; ?>

    <!-- Foydalanuvchi Asosiy Kartasi (Hero Header) -->
    <div class="profile-hero-card">
        <div class="profile-hero-avatar">
            <?php if (!empty($member['avatar']) && file_exists($_SERVER['DOCUMENT_ROOT'] . $member['avatar'])): ?>
                <img src="<?=$member['avatar']?>" alt="<?=htmlspecialchars($member['username'])?>" />
            <?php else: ?>
                <div class="avatar-placeholder">
                    <i class="fa fa-user"></i>
                </div>
            <?php endif; ?>
        </div>

        <div class="profile-hero-info">
            <div class="profile-name-row">
                <h2 class="profile-username"><?=htmlspecialchars($member['username'])?></h2>
                <span class="profile-badge-active"><i class="fa fa-circle"></i> Faol a‘zo</span>
            </div>
            <div class="profile-meta-list">
                <span><i class="fa fa-envelope-o"></i> <?=htmlspecialchars($member['email'])?></span>
                <span><i class="fa fa-calendar"></i> A‘zo bo‘lgan: <b><?=date('d.m.Y', $member['date'])?></b></span>
                <span><i class="fa fa-clock-o"></i> Oxirgi faollik: <b><?=time_ago($member['last_seen'])?></b></span>
            </div>
            <?php if (!empty($member['bio'])): ?>
                <div class="profile-bio-text">
                    "<?=nl2br(htmlspecialchars($member['bio'], ENT_QUOTES, 'UTF-8'))?>"
                </div>
            <?php endif; ?>
        </div>

        <div class="profile-hero-actions">
            <a href="/upload.html" class="btn-profile-upload">
                <i class="fa fa-upload"></i> <?=$lang['upload'] ?? 'Video yuklash'?>
            </a>
            <a href="/logout.html" class="btn-profile-logout" onclick="return confirm('<?=addslashes($lang['exit'] ?? 'Chiqish')?>?');">
                <i class="fa fa-sign-out"></i> <?=$lang['exit'] ?? 'Chiqish'?>
            </a>
        </div>
    </div>

    <!-- Tezkor Statistika Qatori -->
    <div class="profile-stat-strip">
        <div class="profile-stat-item">
            <span class="stat-number"><?=$total_uploaded?></span>
            <span class="stat-label"><i class="fa fa-film"></i> <?=$lang['my_uploaded_videos'] ?? 'Yuklangan videolar'?></span>
        </div>
        <div class="profile-stat-item">
            <span class="stat-number" style="color:#28a745;"><?=$approved_count?></span>
            <span class="stat-label"><i class="fa fa-check-circle"></i> <?=$lang['approved'] ?? 'Tasdiqlangan'?></span>
        </div>
        <div class="profile-stat-item">
            <span class="stat-number" style="color:#ff9900;"><?=$pending_count?></span>
            <span class="stat-label"><i class="fa fa-clock-o"></i> <?=$lang['pending'] ?? 'Kutilmoqda'?></span>
        </div>
        <div class="profile-stat-item">
            <span class="stat-number"><?=$total_comments?></span>
            <span class="stat-label"><i class="fa fa-comment-o"></i> <?=$lang['my_comments'] ?? 'Izohlarim'?></span>
        </div>
        <div class="profile-stat-item">
            <span class="stat-number"><?=$total_replies?></span>
            <span class="stat-label"><i class="fa fa-bell-o"></i> <?=$lang['replies_to_me'] ?? 'Menga javoblar'?></span>
        </div>
    </div>

    <!-- Profil Bo'limlari Tablari -->
    <div class="profile-tabs-nav">
        <a href="/profile.html?tab=videos" class="profile-tab-btn <?=$tab === 'videos' ? 'active' : ''?>">
            <i class="fa fa-video-camera"></i> <?=$lang['my_videos'] ?? 'Men yuklagan videolar'?> (<?=$total_uploaded?>)
        </a>
        <a href="/profile.html?tab=favorites" class="profile-tab-btn <?=$tab === 'favorites' ? 'active' : ''?>">
            <i class="fa fa-star"></i> <?=$lang['my_favorites'] ?? 'Sevimlilarim'?>
        </a>
        <a href="/profile.html?tab=comments" class="profile-tab-btn <?=$tab === 'comments' ? 'active' : ''?>">
            <i class="fa fa-comments"></i> <?=$lang['my_comments'] ?? 'Mening izohlarim'?> (<?=$total_comments?>)
        </a>
        <a href="/profile.html?tab=replies" class="profile-tab-btn <?=$tab === 'replies' ? 'active' : ''?>">
            <i class="fa fa-reply"></i> <?=$lang['replies_to_me'] ?? 'Menga yozilgan javoblar'?> 
            <?php if (!empty($unread_notifications) && $unread_notifications > 0): ?>
                <span class="tab-badge">+<?=$unread_notifications?></span>
            <?php endif; ?>
        </a>
        <a href="/profile.html?tab=edit" class="profile-tab-btn <?=$tab === 'edit' ? 'active' : ''?>">
            <i class="fa fa-cog"></i> <?=$lang['edit_profile'] ?? 'Profilni tahrirlash'?>
        </a>
    </div>

    <!-- Tab 1: Men yuklagan videolar -->
    <?php if ($tab === 'videos'): ?>
        <div class="profile-tab-content">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; flex-wrap:wrap; gap:10px;">
                <h3 style="color:#ebdbd6; font-size:18px; margin:0;"><i class="fa fa-film" style="color:#ff9900;"></i> Men yuklagan videolar</h3>
                <a href="/upload.html" class="btn-profile-upload-sm"><i class="fa fa-plus-circle"></i> Yangi video yuklash</a>
            </div>

            <?php
            $v_q = $mysqli->query("SELECT uv.*, c.name as cat_name FROM ero_user_videos uv LEFT JOIN ero_categories c ON uv.category = c.id WHERE uv.member_id = '$m_id' ORDER BY uv.id DESC");
            if ($v_q && $v_q->num_rows > 0):
            ?>
                <div class="user-videos-grid">
                    <?php while ($uv = $v_q->fetch_assoc()): 
                        $status = $uv['status'];
                        $poster = !empty($uv['screenshot']) ? $uv['screenshot'] : '/designs/no_poster.jpg';
                    ?>
                        <div class="user-video-card">
                            <div class="user-video-poster">
                                <img src="<?=$poster?>" alt="<?=htmlspecialchars($uv['name'])?>" onerror="this.src='/designs/no_poster.jpg';" />
                                <?php if ($status === 'pending'): ?>
                                    <span class="uv-badge badge-pending"><i class="fa fa-clock-o"></i> Kutilmoqda</span>
                                <?php elseif ($status === 'approved'): ?>
                                    <span class="uv-badge badge-approved"><i class="fa fa-check"></i> Tasdiqlangan</span>
                                <?php else: ?>
                                    <span class="uv-badge badge-rejected"><i class="fa fa-times"></i> Rad etilgan</span>
                                <?php endif; ?>
                            </div>

                            <div class="user-video-body">
                                <h4 class="uv-title" title="<?=htmlspecialchars($uv['name'])?>">
                                    <?=htmlspecialchars($uv['name'])?>
                                </h4>
                                <div class="uv-meta">
                                    <span><i class="fa fa-folder-open"></i> <?=htmlspecialchars($uv['cat_name'] ?? 'Boshqa')?></span>
                                    <span><i class="fa fa-calendar"></i> <?=date('d.m.Y H:i', $uv['date'])?></span>
                                </div>
                                <?php if (!empty($uv['description'])): ?>
                                    <p class="uv-desc"><?=htmlspecialchars(mb_substr($uv['description'], 0, 90, 'UTF-8'))?>...</p>
                                <?php endif; ?>

                                <?php if ($status === 'approved'): 
                                    // Saytdagi translitini aniqlash
                                    $trans_q = $mysqli->query("SELECT translit FROM ero_files WHERE name = '".mysqli_real_escape_string($mysqli, $uv['name'])."' LIMIT 1");
                                    $live_t = $trans_q ? ($trans_q->fetch_assoc()['translit'] ?? '') : '';
                                ?>
                                    <div style="margin-top:10px;">
                                        <?php if (!empty($live_t)): ?>
                                            <a href="/watch/<?=$live_t?>.html" target="_blank" class="btn-watch-live">
                                                <i class="fa fa-play-circle"></i> Videoni tomosha qilish &rarr;
                                            </a>
                                        <?php else: ?>
                                            <span style="color:#28a745; font-size:12px;"><i class="fa fa-check"></i> Saytda faol</span>
                                        <?php endif; ?>
                                    </div>
                                <?php elseif ($status === 'rejected'): ?>
                                    <div class="uv-reject-box">
                                        <b>Rad etilish sababi:</b> <?=htmlspecialchars($uv['reject_reason'] ?: 'Admin tomonidan ma\'qullanmadi')?>
                                    </div>
                                <?php else: ?>
                                    <div class="uv-pending-info">
                                        <i class="fa fa-info-circle"></i> Video admin moderator tekshiruvida. Tez orada saytda e'lon qilinadi.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-tab-box">
                    <i class="fa fa-film" style="font-size:48px; color:#444;"></i>
                    <p>Siz hali birorta ham video yuklamagansiz.</p>
                    <a href="/upload.html" class="btn-profile-upload-sm" style="display:inline-block; margin-top:10px;">
                        <i class="fa fa-upload"></i> Birinchi videoni yuklash
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Tab 2: Sevimlilarim -->
    <?php if ($tab === 'favorites'): ?>
        <div class="profile-tab-content">
            <h3 style="color:#ebdbd6; font-size:18px; margin-bottom:15px;"><i class="fa fa-star" style="color:#ff9900;"></i> Saqlangan Sevimli Videolarim</h3>
            <?php
            $client_ip = mysqli_real_escape_string($mysqli, filter($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'));
            $fav_q = $mysqli->query("
                SELECT f.id, f.name, f.screenshot, f.translit, f.duration, f.view, f.likes, f.dislikes 
                FROM ero_favorites fav 
                INNER JOIN ero_files f ON fav.id_video = f.id 
                WHERE fav.data = '$client_ip' 
                ORDER BY fav.id DESC LIMIT 50
            ");

            if ($fav_q && $fav_q->num_rows > 0):
            ?>
                <div class="xxxhd-thumbs-content" style="padding:0;">
                    <?php while ($fv = $fav_q->fetch_assoc()): 
                        $fav_img = (!empty($fv['screenshot']) && $fv['screenshot'] != '/designs/water.png') ? $fv['screenshot'] : '/designs/no_poster.jpg';
                    ?>
                        <div class="xxxhd-thumb-wr">
                            <div class="xxxhd-thumb">
                                <a href="/watch/<?=$fv['translit']?>.html" title="<?=htmlspecialchars($fv['name'], ENT_QUOTES, 'UTF-8')?>">
                                    <div class="thumb-image-wrap">
                                        <img src="<?=$fav_img?>" alt="<?=htmlspecialchars($fv['name'], ENT_QUOTES, 'UTF-8')?>" loading="lazy" onerror="this.src='/designs/no_poster.jpg';" />
                                    </div>
                                    <div class="xxxhd-thumb-name"><?=htmlspecialchars($fv['name'])?></div>
                                </a>
                                <span class="xxxhd-thumb-top top-left">HD</span>
                                <span class="xxxhd-thumb-bottom bottom-left"><i class="fa fa-eye"></i> <?=$fv['view']?></span>
                                <span class="xxxhd-thumb-bottom bottom-right"><i class="fa fa-clock-o"></i> <?=$fv['duration']?></span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-tab-box">
                    <i class="fa fa-star-o" style="font-size:48px; color:#444;"></i>
                    <p>Sevimlilar ro‘yxatingiz hozircha bo‘sh. Yoqtirgan videolaringiz ostidagi "Sevimlilarga qo‘shish" tugmasini bosing!</p>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Tab 3: Mening izohlarim -->
    <?php if ($tab === 'comments'): ?>
        <div class="profile-tab-content">
            <h3 style="color:#ebdbd6; font-size:18px; margin-bottom:15px;"><i class="fa fa-comments" style="color:#ff9900;"></i> Mening izohlarim</h3>
            <?php
            $comm_q = $mysqli->query("
                SELECT c.*, f.name as video_name, f.translit as video_translit 
                FROM ero_comments c 
                LEFT JOIN ero_files f ON c.id_video = f.id 
                WHERE c.author = '$u_name_safe' 
                ORDER BY c.id DESC LIMIT 50
            ");

            if ($comm_q && $comm_q->num_rows > 0):
            ?>
                <div class="profile-comments-list">
                    <?php while ($cm = $comm_q->fetch_assoc()): ?>
                        <div class="profile-comment-item">
                            <div class="p-comm-header">
                                <span class="p-comm-video">
                                    <i class="fa fa-film"></i> 
                                    <?php if (!empty($cm['video_translit'])): ?>
                                        <a href="/watch/<?=$cm['video_translit']?>.html"><?=htmlspecialchars($cm['video_name'] ?? 'Video')?></a>
                                    <?php else: ?>
                                        <span><?=htmlspecialchars($cm['video_name'] ?? 'Video')?></span>
                                    <?php endif; ?>
                                </span>
                                <span class="p-comm-date"><i class="fa fa-clock-o"></i> <?=time_ago($cm['date'])?></span>
                            </div>
                            <div class="p-comm-body">
                                <?=nl2br(htmlspecialchars($cm['text'], ENT_QUOTES, 'UTF-8'))?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-tab-box">
                    <i class="fa fa-comment-o" style="font-size:48px; color:#444;"></i>
                    <p>Siz hali birorta ham izoh qoldirmagansiz.</p>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Tab 4: Menga yozilgan javoblar -->
    <?php if ($tab === 'replies'): ?>
        <div class="profile-tab-content">
            <h3 style="color:#ebdbd6; font-size:18px; margin-bottom:15px;"><i class="fa fa-reply" style="color:#ff9900;"></i> Izohlarda Menga Yozilgan Javoblar</h3>
            <?php
            $rep_q = $mysqli->query("
                SELECT n.*, f.name as video_name, f.translit as video_translit 
                FROM ero_notifications n 
                LEFT JOIN ero_files f ON n.id_video = f.id 
                WHERE n.member_id = '$m_id' 
                ORDER BY n.id DESC LIMIT 50
            ");

            if ($rep_q && $rep_q->num_rows > 0):
            ?>
                <div class="profile-replies-list">
                    <?php while ($rp = $rep_q->fetch_assoc()): ?>
                        <div class="profile-reply-item">
                            <div class="p-reply-meta">
                                <span class="p-reply-author"><i class="fa fa-user-circle"></i> <b><?=htmlspecialchars($rp['from_author'])?></b> sizga javob yozdi:</span>
                                <span class="p-reply-time"><i class="fa fa-clock-o"></i> <?=time_ago($rp['date'])?></span>
                            </div>
                            <div class="p-reply-text">
                                <?=nl2br(htmlspecialchars($rp['text'], ENT_QUOTES, 'UTF-8'))?>
                            </div>
                            <div class="p-reply-footer">
                                <?php if (!empty($rp['video_translit'])): ?>
                                    <a href="/watch/<?=$rp['video_translit']?>.html" class="p-reply-link">
                                        <i class="fa fa-play-circle"></i> Videoga o‘tish: <b><?=htmlspecialchars($rp['video_name'] ?? 'Video')?></b> &rarr;
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-tab-box">
                    <i class="fa fa-bell-slash-o" style="font-size:48px; color:#444;"></i>
                    <p>Hozircha sizga yozilgan javoblar yoki yangiliklar yo‘q.</p>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Tab 5: Profilni tahrirlash -->
    <?php if ($tab === 'edit'): ?>
        <div class="profile-tab-content">
            <h3 style="color:#ebdbd6; font-size:18px; margin-bottom:15px;"><i class="fa fa-cog" style="color:#ff9900;"></i> Profil Ma'lumotlarini Tahrirlash</h3>

            <form action="/profile.html?tab=edit" method="post" enctype="multipart/form-data" class="profile-edit-form">
                <input type="hidden" name="act" value="edit_profile" />

                <div class="form-row-custom">
                    <div class="form-group-custom">
                        <label for="edit_u"><i class="fa fa-user"></i> Foydalanuvchi nomi:</label>
                        <input type="text" id="edit_u" name="username" class="form-control-auth" value="<?=htmlspecialchars($member['username'])?>" required />
                    </div>

                    <div class="form-group-custom">
                        <label for="edit_e"><i class="fa fa-envelope"></i> Email manzili:</label>
                        <input type="email" id="edit_e" name="email" class="form-control-auth" value="<?=htmlspecialchars($member['email'])?>" required />
                    </div>
                </div>

                <div class="form-group-custom">
                    <label for="edit_bio"><i class="fa fa-pencil"></i> Bio (O‘zingiz haqingizda qisqacha):</label>
                    <textarea id="edit_bio" name="bio" class="form-control-auth" rows="3" placeholder="O‘zingiz haqingizda bir-ikki og‘iz so‘z..."><?=htmlspecialchars($member['bio'] ?? '')?></textarea>
                </div>

                <div class="form-group-custom">
                    <label for="edit_av"><i class="fa fa-camera"></i> Avatar yuklash (JPG, PNG, WebP):</label>
                    <input type="file" id="edit_av" name="avatar" accept="image/*" class="form-control-auth" />
                    <?php if (!empty($member['avatar'])): ?>
                        <small style="color:#aaa; display:block; margin-top:4px;">Hozirgi avatar: <a href="<?=$member['avatar']?>" target="_blank" style="color:#ff9900;">Ko‘rish</a></small>
                    <?php endif; ?>
                </div>

                <hr style="border:none; border-top:1px solid #372722; margin:20px 0;" />
                <h4 style="color:#ebdbd6; font-size:15px; margin-bottom:10px;"><i class="fa fa-lock" style="color:#ff9900;"></i> Parolni o‘zgartirish (agar kerak bo‘lsa)</h4>

                <div class="form-row-custom">
                    <div class="form-group-custom">
                        <label for="old_p">Joriy parolingiz:</label>
                        <input type="password" id="old_p" name="old_password" class="form-control-auth" placeholder="Eski parol" />
                    </div>

                    <div class="form-group-custom">
                        <label for="new_p">Yangi parol:</label>
                        <input type="password" id="new_p" name="new_password" class="form-control-auth" placeholder="Kamida 6 ta belgi" />
                    </div>

                    <div class="form-group-custom">
                        <label for="new_pc">Yangi parolni takrorlang:</label>
                        <input type="password" id="new_pc" name="new_password_confirm" class="form-control-auth" placeholder="Yangi parolni takrorlang" />
                    </div>
                </div>

                <button type="submit" class="btn-auth-submit" style="max-width:260px; margin-top:15px;">
                    <i class="fa fa-save"></i> O‘zgarishlarni saqlash
                </button>
            </form>
        </div>
    <?php endif; ?>

</div>
