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

<div class="xxxhd-title-top">
    <h1><i class="fa fa-user" style="color:var(--primary-accent, #ff9900);"></i> <?=htmlspecialchars($member['username'])?></h1>
</div>

<?php if (isset($_GET['welcome'])): ?>
    <div style="padding:10px 15px 0;">
        <div class="site-alert success">
            <i class="fa fa-check-circle"></i>
            <span>Xush kelibsiz, <b><?=htmlspecialchars($member['username'])?></b>! Profilingiz faollashtirildi.</span>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($profile_msg)): ?>
    <div style="padding:10px 15px 0;">
        <div class="site-alert success">
            <i class="fa fa-check-circle"></i> <?=$profile_msg?>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($profile_err)): ?>
    <div style="padding:10px 15px 0;">
        <div class="site-alert error">
            <i class="fa fa-exclamation-circle"></i> <?=$profile_err?>
        </div>
    </div>
<?php endif; ?>

<!-- Minimalist Profile Info Strip -->
<div class="profile-bar">
    <div class="profile-bar-left">
        <?php if (!empty($member['avatar']) && file_exists($_SERVER['DOCUMENT_ROOT'] . $member['avatar'])): ?>
            <img src="<?=$member['avatar']?>" class="profile-bar-avatar" alt="<?=htmlspecialchars($member['username'])?>" />
        <?php endif; ?>
        <span class="profile-meta-item"><i class="fa fa-circle" style="color:#28a745; font-size:9px;"></i> Faol a‘zo</span>
        <?php if (!empty($member['email'])): ?>
            <span class="profile-meta-item"><i class="fa fa-envelope-o"></i> <?=htmlspecialchars($member['email'])?></span>
        <?php endif; ?>
        <span class="profile-meta-item"><i class="fa fa-calendar"></i> <?=date('d.m.Y', $member['date'])?></span>
        <?php if (!empty($member['bio'])): ?>
            <span class="profile-meta-item" style="color:#bbb; font-style:italic;">"<?=htmlspecialchars($member['bio'])?>"</span>
        <?php endif; ?>
    </div>
    <div class="profile-bar-right">
        <a href="/upload.html" class="profile-mini-btn accent">
            <i class="fa fa-upload"></i> <?=$lang['upload'] ?? 'Video yuklash'?>
        </a>
        <a href="/logout.html" class="profile-mini-btn" onclick="return confirm('<?=addslashes($lang['exit'] ?? 'Chiqish')?>?');">
            <i class="fa fa-sign-out"></i> <?=$lang['exit'] ?? 'Chiqish'?>
        </a>
    </div>
</div>

<!-- Minimalist Navigation Tabs -->
<div class="profile-tabs-bar">
    <a href="/profile.html?tab=videos" class="profile-tab-item <?=$tab === 'videos' ? 'active' : ''?>">
        <i class="fa fa-film"></i> <?=$lang['my_videos'] ?? 'Videolarim'?> (<?=$total_uploaded?>)
    </a>
    <a href="/profile.html?tab=favorites" class="profile-tab-item <?=$tab === 'favorites' ? 'active' : ''?>">
        <i class="fa fa-star"></i> <?=$lang['my_favorites'] ?? 'Sevimlilar'?>
    </a>
    <a href="/profile.html?tab=comments" class="profile-tab-item <?=$tab === 'comments' ? 'active' : ''?>">
        <i class="fa fa-comments"></i> <?=$lang['my_comments'] ?? 'Izohlarim'?> (<?=$total_comments?>)
    </a>
    <a href="/profile.html?tab=replies" class="profile-tab-item <?=$tab === 'replies' ? 'active' : ''?>">
        <i class="fa fa-reply"></i> <?=$lang['replies_to_me'] ?? 'Menga javoblar'?>
        <?php if (!empty($unread_notifications) && $unread_notifications > 0): ?>
            <span class="tab-badge">+<?=$unread_notifications?></span>
        <?php endif; ?>
    </a>
    <a href="/profile.html?tab=edit" class="profile-tab-item <?=$tab === 'edit' ? 'active' : ''?>">
        <i class="fa fa-cog"></i> <?=$lang['edit_profile'] ?? 'Sozlamalar'?>
    </a>
</div>

<!-- Tab 1: Men yuklagan videolar -->
<?php if ($tab === 'videos'): ?>
    <?php
    $v_q = $mysqli->query("SELECT uv.*, c.name as cat_name FROM ero_user_videos uv LEFT JOIN ero_categories c ON uv.category = c.id WHERE uv.member_id = '$m_id' ORDER BY uv.id DESC");
    if ($v_q && $v_q->num_rows > 0):
    ?>
        <div class="xxxhd-thumbs-content">
            <?php while ($uv = $v_q->fetch_assoc()): 
                $status = $uv['status'];
                $poster = !empty($uv['screenshot']) ? $uv['screenshot'] : '/designs/no_poster.jpg';
                // Saytdagi translitini aniqlash
                $live_t = '';
                if ($status === 'approved') {
                    $trans_q = $mysqli->query("SELECT translit FROM ero_files WHERE name = '".mysqli_real_escape_string($mysqli, $uv['name'])."' LIMIT 1");
                    $live_t = $trans_q ? ($trans_q->fetch_assoc()['translit'] ?? '') : '';
                }
                $link = !empty($live_t) ? '/watch/' . $live_t . '.html' : '#';
            ?>
                <div class="xxxhd-thumb-wr">
                    <div class="xxxhd-thumb">
                        <a href="<?=$link?>" title="<?=htmlspecialchars($uv['name'], ENT_QUOTES, 'UTF-8')?>">
                            <div class="thumb-image-wrap">
                                <img src="<?=$poster?>" alt="<?=htmlspecialchars($uv['name'], ENT_QUOTES, 'UTF-8')?>" loading="lazy" onerror="this.src='/designs/no_poster.jpg';" />
                            </div>
                            <div class="xxxhd-thumb-name" title="<?=htmlspecialchars($uv['name'], ENT_QUOTES, 'UTF-8')?>"><?=htmlspecialchars($uv['name'])?></div>
                        </a>
                        <span class="xxxhd-thumb-top top-left"><?=htmlspecialchars($uv['cat_name'] ?? 'Video')?></span>
                        <?php if ($status === 'approved'): ?>
                            <span class="xxxhd-thumb-top top-right" style="background:#28a745; color:#fff;"><i class="fa fa-check"></i> Tasdiqlangan</span>
                        <?php elseif ($status === 'rejected'): ?>
                            <span class="xxxhd-thumb-top top-right" style="background:#dc3545; color:#fff;"><i class="fa fa-times"></i> Rad etildi</span>
                        <?php else: ?>
                            <span class="xxxhd-thumb-top top-right" style="background:#ff9900; color:#000;"><i class="fa fa-clock-o"></i> Kutilmoqda</span>
                        <?php endif; ?>
                        <span class="xxxhd-thumb-bottom bottom-left"><i class="fa fa-calendar"></i> <?=date('d.m.Y', $uv['date'])?></span>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="profile-empty-notice">
            <i class="fa fa-film"></i>
            <div>Siz hali birorta ham video yuklamagansiz.</div>
            <div style="margin-top:10px;">
                <a href="/upload.html" class="profile-mini-btn accent"><i class="fa fa-upload"></i> Video yuklash</a>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- Tab 2: Sevimlilar -->
<?php if ($tab === 'favorites'): ?>
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
        <div class="xxxhd-thumbs-content">
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
        <div class="profile-empty-notice">
            <i class="fa fa-star-o"></i>
            <div>Sevimlilar ro‘yxatingiz hozircha bo‘sh.</div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- Tab 3: Mening izohlarim -->
<?php if ($tab === 'comments'): ?>
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
        <div>
            <?php while ($cm = $comm_q->fetch_assoc()): ?>
                <div class="profile-row-item">
                    <div class="profile-row-header">
                        <span>
                            <i class="fa fa-film"></i> 
                            <?php if (!empty($cm['video_translit'])): ?>
                                <a href="/watch/<?=$cm['video_translit']?>.html"><?=htmlspecialchars($cm['video_name'] ?? 'Video')?></a>
                            <?php else: ?>
                                <span><?=htmlspecialchars($cm['video_name'] ?? 'Video')?></span>
                            <?php endif; ?>
                        </span>
                        <span><i class="fa fa-clock-o"></i> <?=time_ago($cm['date'])?></span>
                    </div>
                    <div class="profile-row-body">
                        <?=nl2br(htmlspecialchars($cm['text'], ENT_QUOTES, 'UTF-8'))?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="profile-empty-notice">
            <i class="fa fa-comment-o"></i>
            <div>Siz hali birorta ham izoh qoldirmagansiz.</div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- Tab 4: Menga yozilgan javoblar -->
<?php if ($tab === 'replies'): ?>
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
        <div>
            <?php while ($rp = $rep_q->fetch_assoc()): ?>
                <div class="profile-row-item">
                    <div class="profile-row-header">
                        <span>
                            <i class="fa fa-user-circle"></i> <b><?=htmlspecialchars($rp['from_author'])?></b> sizga javob yozdi:
                        </span>
                        <span><i class="fa fa-clock-o"></i> <?=time_ago($rp['date'])?></span>
                    </div>
                    <div class="profile-row-body" style="margin-bottom:6px;">
                        <?=nl2br(htmlspecialchars($rp['text'], ENT_QUOTES, 'UTF-8'))?>
                    </div>
                    <?php if (!empty($rp['video_translit'])): ?>
                        <div style="font-size:12px;">
                            <a href="/watch/<?=$rp['video_translit']?>.html" style="color:var(--primary-accent, #ff9900);">
                                <i class="fa fa-play-circle"></i> Videoga o‘tish: <b><?=htmlspecialchars($rp['video_name'] ?? 'Video')?></b> &rarr;
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="profile-empty-notice">
            <i class="fa fa-bell-slash-o"></i>
            <div>Hozircha sizga yozilgan javoblar yo‘q.</div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- Tab 5: Profilni tahrirlash -->
<?php if ($tab === 'edit'): ?>
    <div class="site-form-minimal" style="max-width: 480px;">
        <form action="/profile.html?tab=edit" method="post" enctype="multipart/form-data">
            <input type="hidden" name="act" value="edit_profile" />

            <div class="form-group-min">
                <label for="edit_u"><i class="fa fa-user"></i> Foydalanuvchi nomi:</label>
                <input type="text" id="edit_u" name="username" class="input-min" value="<?=htmlspecialchars($member['username'])?>" required />
            </div>

            <div class="form-group-min">
                <label for="edit_e"><i class="fa fa-envelope"></i> Email manzili:</label>
                <input type="email" id="edit_e" name="email" class="input-min" value="<?=htmlspecialchars($member['email'])?>" placeholder="Email manzilingiz" />
            </div>

            <div class="form-group-min">
                <label for="edit_bio"><i class="fa fa-pencil"></i> Bio (O‘zingiz haqingizda):</label>
                <textarea id="edit_bio" name="bio" class="input-min" rows="3" placeholder="O‘zingiz haqingizda qisqacha..."><?=htmlspecialchars($member['bio'] ?? '')?></textarea>
            </div>

            <div class="form-group-min">
                <label for="edit_av"><i class="fa fa-camera"></i> Avatar (JPG, PNG, WebP):</label>
                <input type="file" id="edit_av" name="avatar" accept="image/*" class="input-min" style="padding:6px 12px; height:auto;" />
                <?php if (!empty($member['avatar'])): ?>
                    <small style="color:#aaa; display:block; margin-top:4px;">Hozirgi avatar: <a href="<?=$member['avatar']?>" target="_blank" style="color:var(--primary-accent, #ff9900);">Ko‘rish</a></small>
                <?php endif; ?>
            </div>

            <div style="border-top:1px solid #323130; margin:16px 0 14px; padding-top:10px;">
                <span style="font-size:13px; font-weight:600; color:#ddd;"><i class="fa fa-lock"></i> Parolni yangilash (ixtiyoriy)</span>
            </div>

            <div class="form-group-min">
                <label for="old_p">Joriy parol:</label>
                <input type="password" id="old_p" name="old_password" class="input-min" placeholder="Eski parol" />
            </div>

            <div class="form-group-min">
                <label for="new_p">Yangi parol:</label>
                <input type="password" id="new_p" name="new_password" class="input-min" placeholder="Kamida 6 ta belgi" />
            </div>

            <div class="form-group-min">
                <label for="new_pc">Yangi parolni takrorlang:</label>
                <input type="password" id="new_pc" name="new_password_confirm" class="input-min" placeholder="Yangi parolni takrorlang" />
            </div>

            <button type="submit" class="btn-min-submit" style="margin-top:14px;">
                <i class="fa fa-save"></i> O‘zgarishlarni saqlash
            </button>
        </form>
    </div>
<?php endif; ?>

