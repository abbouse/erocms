<?php
/**
 * EroCMS - Foydalanuvchilar Uchun Oson Video Yuklash Sahifasi (Upload)
 * Foydalanuvchi faqat video va video nomini yozadi, qolganini (kategoriya, tavsif, SEO) admin to'ldiradi.
 */

if (!$member) {
    header('Location: /login.html?redirect=/upload.html&need_auth=1');
    exit;
}

$upload_err = '';
$upload_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['act']) && $_POST['act'] === 'upload_video') {
    $name = trim(filter($_POST['name'] ?? ''));
    $video_url = trim(filter($_POST['video_url'] ?? ''));

    if (empty($name)) {
        $upload_err = 'Iltimos, video nomini kiriting!';
    } else {
        $saved_file_path = '';

        // 1. Video fayli yuklangan bo'lsa
        if (!empty($_FILES['video_file']['name']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
            $tmp = $_FILES['video_file']['tmp_name'];
            $orig_name = $_FILES['video_file']['name'];
            $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
            $allowed_exts = ['mp4', 'webm', 'mov', 'm4v', 'avi', 'mkv'];

            if (!in_array($ext, $allowed_exts)) {
                $upload_err = 'Faqat video formatlari qabul qilinadi (.mp4, .webm, .mov)!';
            } else {
                $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/content/user_uploads';
                if (!is_dir($upload_dir)) {
                    @mkdir($upload_dir, 0777, true);
                }
                $safe_filename = 'uvid_' . $member['id'] . '_' . time() . '_' . rand(100, 999) . '.' . $ext;
                $dest = $upload_dir . '/' . $safe_filename;

                if (move_uploaded_file($tmp, $dest)) {
                    $saved_file_path = '/content/user_uploads/' . $safe_filename;
                } else {
                    $upload_err = 'Video faylini yuklashda xatolik yuz berdi!';
                }
            }
        }

        // 2. Video havola yoki fayl kiritilgan bo'lishi shart
        if (empty($saved_file_path) && empty($video_url) && empty($upload_err)) {
            $upload_err = 'Iltimos, video faylini yuklang yoki video havolasini kiriting!';
        }

        // 3. Bazaga yozish (pending statusda)
        if (empty($upload_err)) {
            $safe_name = mysqli_real_escape_string($mysqli, $name);
            $safe_vurl = mysqli_real_escape_string($mysqli, $video_url);
            $safe_fpath = mysqli_real_escape_string($mysqli, $saved_file_path);
            $ip = mysqli_real_escape_string($mysqli, filter($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'));
            $now = time();
            $m_id = intval($member['id']);

            $ins = $mysqli->query("
                INSERT INTO ero_user_videos 
                (member_id, name, description, category, video_url, file_path, screenshot, status, date, ip) 
                VALUES 
                ('$m_id', '$safe_name', '', 0, '$safe_vurl', '$safe_fpath', '', 'pending', '$now', '$ip')
            ");

            if ($ins) {
                $upload_success = true;
            } else {
                $upload_err = 'Ma\'lumotlar bazasiga saqlashda xatolik yuz berdi: ' . $mysqli->error;
            }
        }
    }
}

$title = ($lang['upload_video_title'] ?? 'Video Yuklash') . ' - ' . filter($_SERVER['HTTP_HOST'] ?? 'sekschi.online');
head();
?>

<div class="xxxhd-title-top">
    <h1><?=$lang['upload_video_title'] ?? 'Video Yuklash'?></h1>
</div>

<div class="site-form-minimal" style="max-width: 500px;">
    <?php if ($upload_success): ?>
        <div class="site-alert success" style="display:block; text-align:left; font-size:14px; line-height:1.6;">
            <div style="font-size: 15px; font-weight:bold; margin-bottom: 6px;">
                <i class="fa fa-check-circle"></i> <?=$lang['video_uploaded_success'] ?? 'Videongiz qabul qilindi!'?>
            </div>
            <?=$lang['video_moderation_note'] ?? 'Moderator (admin) tekshirib, tavsif va bo‘limni biriktirgach, saytda e\'lon qilinadi.'?>
            <div style="margin-top:14px; display:flex; gap:10px; flex-wrap:wrap;">
                <a href="/profile.html?tab=videos" class="btn-min-submit" style="display:inline-block; padding:0 14px; text-decoration:none; font-size:12px; width:auto; height:32px; line-height:32px;">
                    <?=$lang['my_uploaded_videos'] ?? 'Yuklagan videolarim'?>
                </a>
                <a href="/upload.html" class="link-min-accent" style="display:inline-block; padding:0 14px; text-decoration:none; font-size:12px; height:32px; line-height:32px; border:1px solid #372722; border-radius:3px;">
                    <?=$lang['upload_another'] ?? 'Yana yuklash'?>
                </a>
            </div>
        </div>
    <?php else: ?>

        <?php if (!empty($upload_err)): ?>
            <div class="site-alert error">
                <i class="fa fa-exclamation-circle"></i> <?=$upload_err?>
            </div>
        <?php endif; ?>

        <form action="/upload.html" method="post" enctype="multipart/form-data">
            <input type="hidden" name="act" value="upload_video" />

            <!-- 1. Video Nomi -->
            <div class="form-group-min">
                <label for="vid_name"><?=$lang['video_title_field'] ?? 'Video nomi'?>:</label>
                <input type="text" id="vid_name" name="name" class="input-min" placeholder="<?=$lang['video_title_field'] ?? 'Video nomi'?>" value="<?=htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8')?>" required autofocus />
            </div>

            <!-- 2. Video Fayli -->
            <div class="form-group-min">
                <label for="vid_file"><?=$lang['video_file_field'] ?? 'Video fayli (.mp4, .webm)'?>:</label>
                <input type="file" id="vid_file" name="video_file" accept="video/mp4,video/webm,video/quicktime" class="input-min" style="padding:6px 12px; height:auto;" />
            </div>

            <!-- YOKI Video havolasi -->
            <div class="form-group-min" style="margin-top: -4px;">
                <div style="text-align:center; color:#666; margin:6px 0; font-size:12px; text-transform:uppercase;"><?=$lang['or_video_url'] ?? 'yoki video havolasi'?></div>
                <input type="text" id="vid_url" name="video_url" class="input-min" placeholder="https://sayt.com/video.mp4" value="<?=htmlspecialchars($_POST['video_url'] ?? '', ENT_QUOTES, 'UTF-8')?>" />
            </div>

            <div style="font-size:12px; color:#777; margin: 10px 0 16px; line-height:1.4;">
                <i class="fa fa-info-circle"></i> <?=$lang['upload_hint'] ?? 'Tavsif, bo‘lim va SEO ma\'lumotlarini moderator (admin) o‘zi to‘ldiradi.'?>
            </div>

            <button type="submit" class="btn-min-submit">
                <?=$lang['btn_submit_video'] ?? 'Videoni yuborish'?>
            </button>
        </form>

    <?php endif; ?>

    <div class="form-min-footer">
        <a href="/profile.html" class="link-min-accent"><?=$lang['profile'] ?? 'Profil'?> &rarr;</a>
    </div>
</div>

