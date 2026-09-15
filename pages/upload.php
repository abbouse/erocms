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

$title = 'Video Yuklash - ' . filter($_SERVER['HTTP_HOST'] ?? 'sekschi.online');
head();
?>

<div class="auth-page-container" style="max-width: 520px;">
    <div class="auth-card auth-minimal">
        <h2 class="auth-minimal-title">Video Yuklash</h2>

        <?php if ($upload_success): ?>
            <div class="auth-alert auth-alert-success" style="display:block; text-align:left; font-size:14px; line-height:1.6;">
                <div style="font-size: 15px; font-weight:bold; margin-bottom: 6px;">
                    <i class="fa fa-check-circle"></i> Videongiz qabul qilindi!
                </div>
                Moderator (admin) tekshirib, tavsif va bo‘limni biriktirgach, saytda e'lon qilinadi.
                <div style="margin-top:14px; display:flex; gap:10px; flex-wrap:wrap;">
                    <a href="/profile.html?tab=videos" class="btn-auth-submit" style="display:inline-block; padding:7px 14px; text-decoration:none; font-size:12px; width:auto;">
                        Yuklagan videolarim
                    </a>
                    <a href="/upload.html" class="auth-register-link" style="padding:7px 14px; text-decoration:none; font-size:12px; border:1px solid #372722; border-radius:4px;">
                        Yana yuklash
                    </a>
                </div>
            </div>
        <?php else: ?>

            <?php if (!empty($upload_err)): ?>
                <div class="auth-alert auth-alert-danger">
                    <?=$upload_err?>
                </div>
            <?php endif; ?>

            <form action="/upload.html" method="post" enctype="multipart/form-data" class="auth-form">
                <input type="hidden" name="act" value="upload_video" />

                <!-- 1. Video Nomi -->
                <div class="form-group-custom">
                    <label for="vid_name">Video nomi:</label>
                    <input type="text" id="vid_name" name="name" class="form-control-auth" placeholder="Masalan: Ajoyib video..." value="<?=htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8')?>" required autofocus />
                </div>

                <!-- 2. Video Fayli -->
                <div class="form-group-custom">
                    <label for="vid_file">Video fayli (.mp4, .webm):</label>
                    <input type="file" id="vid_file" name="video_file" accept="video/mp4,video/webm,video/quicktime" class="form-control-auth" style="padding:7px;" />
                </div>

                <!-- YOKI Video havolasi -->
                <div class="form-group-custom" style="margin-top: -6px;">
                    <div style="text-align:center; color:#666; margin:6px 0; font-size:12px; text-transform:uppercase;">yoki video havolasi (link)</div>
                    <input type="text" id="vid_url" name="video_url" class="form-control-auth" placeholder="https://sayt.com/video.mp4" value="<?=htmlspecialchars($_POST['video_url'] ?? '', ENT_QUOTES, 'UTF-8')?>" />
                </div>

                <div style="font-size:12px; color:#777; margin: 12px 0 16px; line-height:1.4;">
                    <i class="fa fa-info-circle"></i> Tavsif, bo‘lim va SEO ma'lumotlarini moderator (admin) o‘zi to‘ldiradi.
                </div>

                <button type="submit" class="btn-auth-submit">
                    Videoni yuborish
                </button>
            </form>

        <?php endif; ?>

        <div class="auth-card-footer" style="margin-top:16px; padding-top:14px; border-top:1px solid #2a2220; text-align:center; font-size:13px;">
            <a href="/profile.html" class="auth-register-link" style="color:var(--primary-accent, #ff9900); font-weight:600;">Profilga qaytish</a>
        </div>
    </div>
</div>
