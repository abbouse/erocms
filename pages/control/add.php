<?php
/**
 * EroCMS Video Qo'shish (Upload & Add Video)
 */

if (!defined('ADMIN_LOADED') && !isset($user)) {
    header('Location: /control.html');
    exit;
}

$msg = null;
$error = null;

// Tizimda ffmpeg CLI mavjudligini tekshirish
$ffmpeg_bin = null;
$ffprobe_bin = null;
if (function_exists('exec')) {
    $which_ffmpeg = trim(@shell_exec('which ffmpeg 2>/dev/null') ?? '');
    if (!empty($which_ffmpeg) && is_executable($which_ffmpeg)) {
        $ffmpeg_bin = $which_ffmpeg;
    }
    $which_ffprobe = trim(@shell_exec('which ffprobe 2>/dev/null') ?? '');
    if (!empty($which_ffprobe) && is_executable($which_ffprobe)) {
        $ffprobe_bin = $which_ffprobe;
    }
}

if (isset($_POST['save_video'])) {
    $name = trim(filter($_POST['name'] ?? ''));
    $category = abs(intval($_POST['category'] ?? 0));
    $description = trim(filter($_POST['description'] ?? ''));
    $custom_duration = trim(filter($_POST['duration'] ?? ''));

    if (empty($name)) {
        $error = "Video sarlavhasi (nomi) kiritilishi shart!";
    } elseif (empty($_FILES['video_file']['name'])) {
        $error = "Iltimos, kompyuteringizdan video faylni tanlang!";
    } else {
        $file_name = $_FILES['video_file']['name'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_exts = ['mp4', 'webm', 'mkv', 'avi', 'mov'];

        if (!in_array($ext, $allowed_exts)) {
            $error = "Noto‘g‘ri video formati! Faqat quyidagi formatlar qabul qilinadi: " . implode(', ', $allowed_exts);
        } else {
            $hash = md5(time() . rand(1000, 9999));
            $video_dir = $_SERVER['DOCUMENT_ROOT'] . '/content/video';
            $screen_dir = $_SERVER['DOCUMENT_ROOT'] . '/content/screenshots';

            if (!is_dir($video_dir)) @mkdir($video_dir, 0777, true);
            if (!is_dir($screen_dir)) @mkdir($screen_dir, 0777, true);

            $dest_video = $video_dir . '/' . $hash . '.mp4';
            $web_video = '/content/video/' . $hash . '.mp4';
            $web_screen = '/designs/no_poster.jpg';

            if (move_uploaded_file($_FILES['video_file']['tmp_name'], $dest_video)) {
                
                // 1. Skrinshot / Poster tayyorlash
                if (!empty($_FILES['screenshot_file']['name'])) {
                    $scr_ext = strtolower(pathinfo($_FILES['screenshot_file']['name'], PATHINFO_EXTENSION));
                    if (in_array($scr_ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                        $dest_screen = $screen_dir . '/' . $hash . '.jpg';
                        if (move_uploaded_file($_FILES['screenshot_file']['tmp_name'], $dest_screen)) {
                            $web_screen = '/content/screenshots/' . $hash . '.jpg';
                        }
                    }
                }

                // Agar rasm yuklanmagan bo'lsa va ffmpeg mavjud bo'lsa, videoning 5-soniyasidan kadr olish
                if ($web_screen === '/designs/no_poster.jpg' && $ffmpeg_bin) {
                    $dest_screen = $screen_dir . '/' . $hash . '.jpg';
                    @exec("$ffmpeg_bin -ss 00:00:05 -i " . escapeshellarg($dest_video) . " -vframes 1 -q:v 2 " . escapeshellarg($dest_screen) . " 2>&1");
                    if (file_exists($dest_screen) && filesize($dest_screen) > 100) {
                        $web_screen = '/content/screenshots/' . $hash . '.jpg';
                    }
                }

                // 2. Davomiylikni (duration) aniqlash
                $duration = '05:00';
                if (!empty($custom_duration)) {
                    $duration = $custom_duration;
                } elseif ($ffprobe_bin) {
                    $raw_dur = trim(@shell_exec("$ffprobe_bin -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($dest_video)));
                    $sec = intval($raw_dur);
                    if ($sec > 0) {
                        if ($sec >= 3600) {
                            $duration = sprintf('%02d:%02d:%02d', ($sec/3600), ($sec%3600)/60, $sec%60);
                        } else {
                            $duration = sprintf('%02d:%02d', $sec/60, $sec%60);
                        }
                    }
                }

                // 3. Translit yaratish
                $raw_translit = transliterate($name);
                $clean_translit = preg_replace('/[^a-zA-Z0-9_\-]/', '-', trim($raw_translit));
                $clean_translit = preg_replace('/-+/', '-', $clean_translit);
                $clean_translit = trim($clean_translit, '-');
                if (empty($clean_translit)) {
                    $clean_translit = 'video-' . time();
                }

                $chk_tr = $mysqli->query("SELECT id FROM ero_files WHERE translit = '".mysqli_real_escape_string($mysqli, $clean_translit)."' LIMIT 1");
                if ($chk_tr && $chk_tr->num_rows > 0) {
                    $clean_translit .= '-' . rand(100, 999);
                }

                $safe_name = mysqli_real_escape_string($mysqli, $name);
                $safe_desc = mysqli_real_escape_string($mysqli, $description);
                $safe_tags = mysqli_real_escape_string($mysqli, str_replace(' ', ', ', trim(tags($name . ' ' . $description))));
                $now = time();

                $mysqli->query("
                    INSERT INTO ero_files (
                        name, description, screenshot, recoil, tags, translit, duration, downloads, 
                        server, address, uniqueness, category, view, date, added
                    ) VALUES (
                        '$safe_name',
                        '$safe_desc',
                        '".mysqli_real_escape_string($mysqli, $web_screen)."',
                        '".mysqli_real_escape_string($mysqli, $web_video)."',
                        '$safe_tags',
                        '".mysqli_real_escape_string($mysqli, $clean_translit)."',
                        '$duration',
                        0,
                        '".filter($_SERVER['SERVER_NAME'])."',
                        '".mysqli_real_escape_string($mysqli, $web_video)."',
                        '$hash',
                        '$category',
                        0,
                        '$now',
                        '{$user['id']}'
                    )
                ");

                $new_id = $mysqli->insert_id;
                logs($user['id'], "Yangi video yuklandi: $name", $new_id);

                // Keshni tozalash
                @array_map('unlink', glob($_SERVER['DOCUMENT_ROOT'] . '/content/cache/*.html'));

                $msg = "Video muvaffaqiyatli yuklandi va saytga joylandi! <a href='/watch/{$clean_translit}.html' target='_blank' style='color:#fff; text-decoration:underline; font-weight:bold; margin-left:8px;'><i class='fa fa-external-link'></i> Videoni ko‘rish</a>";
            } else {
                $error = "Faylni serverga yuklashda xatolik yuz berdi (papka huquqlarini tekshiring).";
            }
        }
    }
}

$categories_q = $mysqli->query("SELECT id, name FROM ero_categories ORDER BY name ASC");
?>

<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title"><i class="fa fa-cloud-upload" style="color: #ff9900;"></i> Yangi Video Qo‘shish</h1>
        <p class="adm-page-subtitle">Kompyuterdan to‘g‘ridan-to‘g‘ri video (.mp4) yuklash va saytga joylash</p>
    </div>
    <a href="/control.html?func=view_video" class="adm-btn adm-btn-secondary adm-btn-sm">&larr; Videolar ro‘yxati</a>
</div>

<?php if ($msg): ?>
    <div class="adm-alert adm-alert-success"><i class="fa fa-check-circle"></i> <?=$msg?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="adm-alert adm-alert-danger"><i class="fa fa-exclamation-triangle"></i> <?=$error?></div>
<?php endif; ?>

<div class="adm-card" style="max-width: 800px;">
    <form method="post" enctype="multipart/form-data">
        <div class="adm-form-group">
            <label class="adm-label"><i class="fa fa-file-video-o" style="color:#ff9900;"></i> Video Fayli (.mp4, .webm):</label>
            <input type="file" name="video_file" class="adm-input" accept="video/*" required style="padding: 10px;" />
            <small style="color:#64748b; font-size:11px;">Maksimal hajm: serveringiz sozlamasiga ko‘ra (512MB gacha ruxsat etilgan).</small>
        </div>

        <div class="adm-form-group">
            <label class="adm-label">Video Nomi / Sarlavhasi:</label>
            <input type="text" name="name" class="adm-input" placeholder="Masalan: Qaynoq o‘zbekcha video HD" required />
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
            <div class="adm-form-group">
                <label class="adm-label">Bo‘lim (Kategoriya):</label>
                <select name="category" class="adm-select" required>
                    <?php while ($cat = $categories_q->fetch_assoc()): ?>
                        <option value="<?=$cat['id']?>"><?=$cat['name']?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="adm-form-group">
                <label class="adm-label">Davomiyligi (ixtiyoriy):</label>
                <input type="text" name="duration" class="adm-input" placeholder="Masalan: 08:45" />
                <small style="color:#64748b; font-size:11px;">Bo‘sh qoldirilsa, avtomatik hisoblanadi.</small>
            </div>
        </div>

        <div class="adm-form-group">
            <label class="adm-label"><i class="fa fa-picture-o" style="color:#38bdf8;"></i> Muqova Rasmi / Poster (ixtiyoriy):</label>
            <input type="file" name="screenshot_file" class="adm-input" accept="image/*" style="padding: 10px;" />
            <small style="color:#64748b; font-size:11px;">Agar rasm yuklamasangiz, videoning 5-soniyasidan avtomatik olinadi.</small>
        </div>

        <div class="adm-form-group">
            <label class="adm-label">Video Tavsifi (Description):</label>
            <textarea name="description" class="adm-input" style="height: 90px; resize: vertical;" placeholder="Video haqida qisqacha ma’lumot..."></textarea>
        </div>

        <div style="text-align: right; margin-top: 20px;">
            <button type="submit" name="save_video" value="1" class="adm-btn adm-btn-primary" style="padding: 12px 24px; font-size: 14px;">
                <i class="fa fa-upload"></i> Videoni Saytga Joylash
            </button>
        </div>
    </form>
</div>