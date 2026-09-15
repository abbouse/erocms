<?php
/**
 * EroCMS - Foydalanuvchilar Uchun Oson Video Yuklash Sahifasi (Upload)
 */

if (!$member) {
    header('Location: /login.html?redirect=/upload.html&need_auth=1');
    exit;
}

$upload_err = '';
$upload_success = false;

// Kategoriyalar ro'yxatini olish
$categories_q = $mysqli->query("SELECT id, name FROM ero_categories ORDER BY name ASC");
$categories = [];
while ($cat = $categories_q->fetch_assoc()) {
    $categories[] = $cat;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['act']) && $_POST['act'] === 'upload_video') {
    $name = trim(filter($_POST['name'] ?? ''));
    $desc = trim(filter($_POST['description'] ?? ''));
    $category_id = intval($_POST['category'] ?? 0);
    $video_url = trim(filter($_POST['video_url'] ?? ''));

    if (empty($name)) {
        $upload_err = 'Iltimos, video nomini kiriting!';
    } elseif ($category_id <= 0) {
        $upload_err = 'Iltimos, videoga mos bo‘limni (kategoriyani) tanlang!';
    } else {
        $saved_file_path = '';
        $saved_screenshot_path = '';

        // 1. Video fayli yuklanganmi?
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

        // 2. Video havola yoki fayl bo'lishi shart
        if (empty($saved_file_path) && empty($video_url) && empty($upload_err)) {
            $upload_err = 'Iltimos, video faylini yuklang yoki video havolasini kiriting!';
        }

        // 3. Muqova / Skrinshot yuklangan bo'lsa (ixtiyoriy)
        if (empty($upload_err) && !empty($_FILES['screenshot']['name']) && $_FILES['screenshot']['error'] === UPLOAD_ERR_OK) {
            $img_tmp = $_FILES['screenshot']['tmp_name'];
            $img_ext = strtolower(pathinfo($_FILES['screenshot']['name'], PATHINFO_EXTENSION));
            $img_allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($img_ext, $img_allowed) && getimagesize($img_tmp)) {
                $scr_dir = $_SERVER['DOCUMENT_ROOT'] . '/content/screenshots';
                if (!is_dir($scr_dir)) {
                    @mkdir($scr_dir, 0777, true);
                }
                $scr_filename = 'uscr_' . $member['id'] . '_' . time() . '.' . $img_ext;
                if (move_uploaded_file($img_tmp, $scr_dir . '/' . $scr_filename)) {
                    $saved_screenshot_path = '/content/screenshots/' . $scr_filename;
                }
            }
        }

        // 4. Bazaga yozish (pending statusda)
        if (empty($upload_err)) {
            $safe_name = mysqli_real_escape_string($mysqli, $name);
            $safe_desc = mysqli_real_escape_string($mysqli, $desc);
            $safe_vurl = mysqli_real_escape_string($mysqli, $video_url);
            $safe_fpath = mysqli_real_escape_string($mysqli, $saved_file_path);
            $safe_scr = mysqli_real_escape_string($mysqli, $saved_screenshot_path);
            $ip = mysqli_real_escape_string($mysqli, filter($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'));
            $now = time();
            $m_id = intval($member['id']);

            $ins = $mysqli->query("
                INSERT INTO ero_user_videos 
                (member_id, name, description, category, video_url, file_path, screenshot, status, date, ip) 
                VALUES 
                ('$m_id', '$safe_name', '$safe_desc', '$category_id', '$safe_vurl', '$safe_fpath', '$safe_scr', 'pending', '$now', '$ip')
            ");

            if ($ins) {
                $upload_success = true;
            } else {
                $upload_err = 'Ma\'lumotlar bazasiga saqlashda xatolik yuz berdi!';
            }
        }
    }
}

$title = 'Video Yuklash - ' . filter($_SERVER['HTTP_HOST'] ?? 'sekschi.online');
head();
?>

<div class="auth-page-container" style="max-width: 680px;">
    <div class="auth-card" style="max-width: 100%;">
        <div class="auth-card-header">
            <div class="auth-icon-circle" style="color: #ff9900; background: rgba(255, 153, 0, 0.1);">
                <i class="fa fa-cloud-upload"></i>
            </div>
            <h2>Video Yuklash</h2>
            <p>O‘z videongizni saytga joylang. Admin ko‘rib chiqqach, u darhol saytda e'lon qilinadi!</p>
        </div>

        <?php if ($upload_success): ?>
            <div class="auth-alert auth-alert-success" style="text-align:left; font-size:14px; line-height:1.6;">
                <div style="font-size: 16px; font-weight:bold; margin-bottom: 5px;">
                    <i class="fa fa-check-circle" style="font-size:22px;"></i> Videongiz muvaffaqiyatli qabul qilindi!
                </div>
                Videongiz moderator (admin) tekshiruviga yuborildi. Tekshiruvdan o‘tgach, u tanlagan bo‘limingizda e'lon qilinadi va sayt bosh sahifasida chiqadi.
                <div style="margin-top:15px; display:flex; gap:10px;">
                    <a href="/profile.html?tab=videos" class="btn-auth-submit" style="display:inline-block; padding:8px 16px; text-decoration:none; font-size:13px;">
                        <i class="fa fa-film"></i> Yuklagan videolarimni ko‘rish
                    </a>
                    <a href="/upload.html" class="btn-profile-upload-sm" style="display:inline-block; padding:8px 16px; text-decoration:none; font-size:13px; background:#372722;">
                        <i class="fa fa-plus"></i> Yana video yuklash
                    </a>
                </div>
            </div>
        <?php else: ?>

            <?php if (!empty($upload_err)): ?>
                <div class="auth-alert auth-alert-danger">
                    <i class="fa fa-exclamation-circle"></i> <?=$upload_err?>
                </div>
            <?php endif; ?>

            <form action="/upload.html" method="post" enctype="multipart/form-data" class="auth-form">
                <input type="hidden" name="act" value="upload_video" />

                <!-- 1. Video Nomi -->
                <div class="form-group-custom">
                    <label for="vid_name"><i class="fa fa-tag"></i> Video nomi: <span style="color:#ff4444;">*</span></label>
                    <input type="text" id="vid_name" name="name" class="form-control-auth" placeholder="Masalan: Go‘zal o‘zbek qizi bilan ajoyib video..." value="<?=htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8')?>" required />
                    <small style="color:#777; font-size:12px;">Qisqa va qiziqarli nom bering</small>
                </div>

                <!-- 2. Bo'lim tanlash -->
                <div class="form-group-custom">
                    <label for="vid_cat"><i class="fa fa-folder-open"></i> Bo‘lim (Kategoriya): <span style="color:#ff4444;">*</span></label>
                    <select id="vid_cat" name="category" class="form-control-auth" style="background:#190c08; color:#ebdbd6;" required>
                        <option value="">-- Bo‘limni tanlang --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?=$cat['id']?>" <?=(isset($_POST['category']) && intval($_POST['category']) === intval($cat['id'])) ? 'selected' : ''?>>
                                <?=htmlspecialchars($cat['name'])?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- 3. Video Fayli -->
                <div class="form-group-custom">
                    <label for="vid_file"><i class="fa fa-file-video-o"></i> Video fayli (MP4, WebM):</label>
                    <input type="file" id="vid_file" name="video_file" accept="video/mp4,video/webm,video/quicktime" class="form-control-auth" />
                    <small style="color:#777; font-size:12px;">Telefoningiz yoki kompyuteringizdan video faylni tanlang</small>
                </div>

                <!-- YOKI Video havolasi -->
                <div class="form-group-custom" style="margin-top: -5px;">
                    <div style="text-align:center; color:#555; margin:5px 0; font-weight:bold; font-size:12px;">&mdash; YOKI &mdash;</div>
                    <label for="vid_url"><i class="fa fa-link"></i> Video havolasi / Manzili (URL):</label>
                    <input type="text" id="vid_url" name="video_url" class="form-control-auth" placeholder="https://sayt.com/video.mp4 yoki embed link" value="<?=htmlspecialchars($_POST['video_url'] ?? '', ENT_QUOTES, 'UTF-8')?>" />
                    <small style="color:#777; font-size:12px;">Agar fayl katta bo‘lsa, to‘g‘ridan-to‘g‘ri havolasini yozishingiz mumkin</small>
                </div>

                <!-- 4. Video Haqida (Tavsif) -->
                <div class="form-group-custom">
                    <label for="vid_desc"><i class="fa fa-align-left"></i> Video haqida (Tavsifi):</label>
                    <textarea id="vid_desc" name="description" class="form-control-auth" rows="3" placeholder="Video haqida qisqacha ma'lumot yozing..."><?=htmlspecialchars($_POST['description'] ?? '', ENT_QUOTES, 'UTF-8')?></textarea>
                </div>

                <!-- 5. Muqova / Skrinshot (ixtiyoriy) -->
                <div class="form-group-custom">
                    <label for="vid_screen"><i class="fa fa-picture-o"></i> Muqova rasmi (Skrinshot - ixtiyoriy):</label>
                    <input type="file" id="vid_screen" name="screenshot" accept="image/*" class="form-control-auth" />
                    <small style="color:#777; font-size:12px;">Agar yuklamasangiz, standart muqova qo‘yiladi</small>
                </div>

                <div style="background:#23130e; border:1px solid #372722; padding:12px 16px; border-radius:6px; margin: 15px 0; font-size:13px; color:#aaa;">
                    <i class="fa fa-info-circle" style="color:#ff9900;"></i> <b>Eslatma:</b> Yuklangan barcha videolar admin tomonidan ko‘rib chiqiladi va qoidalarga mos kelsa darhol saytda e'lon qilinadi.
                </div>

                <button type="submit" class="btn-auth-submit" style="font-size:16px; padding:12px;">
                    <i class="fa fa-cloud-upload"></i> Videoni yuborish (Ko‘rib chiqishga)
                </button>
            </form>

        <?php endif; ?>

        <div class="auth-card-footer">
            <a href="/profile.html" class="auth-register-link"><i class="fa fa-user"></i> Mening profilim</a>
        </div>
    </div>
</div>

<?php
foot();
