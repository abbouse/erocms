<?php

/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/

if ($user['access'] < 1) {
    header('Location: /'); 
    exit;
}

// cURL orqali kontent olish yordamchi funksiyasi
function parser_get_html($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
        'Accept-Language: ru-RU,ru;q=0.9,uz;q=0.8,en;q=0.7'
    ]);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

// Rasm yuklab olish
function parser_download_image($img_url, $save_path, $width_S = 400, $height_S = 225, $water = 0) {
    $img_data = parser_get_html($img_url);
    if (!empty($img_data) && strlen($img_data) > 500) {
        file_put_contents($save_path, $img_data);
        if (class_exists('SimpleImage') && file_exists($save_path)) {
            try {
                $image = new SimpleImage();
                $image->load($save_path);
                $image->resize($width_S, $height_S);
                $image->save($save_path);
            } catch (Exception $e) {}
        }
        if ($water == 1 && file_exists($_SERVER['DOCUMENT_ROOT'].'/designs/water.png') && function_exists('water')) {
            water($save_path, $save_path, $_SERVER['DOCUMENT_ROOT'].'/designs/water.png');
        }
        return true;
    }
    return false;
}

// uzbxx.ru dan bitta videoni parslash
function parse_video_uzbxx($video_url, $manual_cat, $save_mode, $mysqli, $settings, $width_S, $height_S) {
    $html = parser_get_html($video_url);
    if (empty($html) || strlen($html) < 200) {
        return ['status' => 'error', 'message' => "Sahifani yuklab bo‘lmadi: $video_url"];
    }

    // Title
    $title = '';
    if (preg_match("|<div class=\"xxxhd-title-top\">.*?<h1>(.*?)</h1>|is", $html, $m)) {
        $title = trim(strip_tags($m[1]));
    } elseif (preg_match("|<title>(.*?)</title>|is", $html, $m)) {
        $title = trim(strip_tags($m[1]));
    }
    $title = preg_replace("/('|\"|\r?\n)/", '', $title);
    if (empty($title) || stripos($title, 'can’t be reached') !== false) {
        return ['status' => 'error', 'message' => "Video nomini aniqlab bo‘lmadi ($video_url)"];
    }

    $uniqueness = md5($title);
    $check_exist = $mysqli->query("SELECT id FROM ero_files WHERE uniqueness = '$uniqueness' LIMIT 1");
    if ($check_exist && $check_exist->num_rows > 0) {
        return ['status' => 'skip', 'message' => "Allaqachon mavjud: <b>$title</b>"];
    }

    // Duration
    $duration = '05:00';
    if (preg_match("|<i class=\"fa fa-clock-o\"></i>\s*<b>(.*?)</b>|is", $html, $m)) {
        $duration = trim($m[1]);
    }

    // Poster
    $poster_url = '';
    if (preg_match("|itemprop=\"thumbnailUrl\" href=\"(.*?)\"|is", $html, $m)) {
        $poster_url = trim($m[1]);
    } elseif (preg_match("|poster:\"(.*?)\"|is", $html, $m)) {
        $poster_url = trim($m[1]);
    }
    if (!empty($poster_url) && strpos($poster_url, 'http') !== 0) {
        $poster_url = 'https://uzbxx.ru' . ltrim($poster_url, '/');
    }

    // Stream / File URL
    $video_src = '';
    if (preg_match("|file:\"(.*?)\"|is", $html, $m)) {
        $video_src = trim($m[1]);
    } elseif (preg_match("|https://uzbxx.ru/video_online\?id=[0-9]+|is", $html, $m)) {
        $video_src = $m[0];
    }
    if (empty($video_src)) {
        $video_src = $video_url;
    }

    // Description
    $desc = $title;
    if (preg_match("|itemprop=\"description\" content=\"(.*?)\"|is", $html, $m)) {
        $desc = trim($m[1]);
    }

    // Tags
    $tags_arr = [];
    if (preg_match_all("|<a href=\"https://uzbxx.ru/tags/[^\"]+\"><i class=\"fa fa-tags\"></i>\s*(.*?)</a>|is", $html, $m)) {
        $tags_arr = array_map('trim', $m[1]);
    }
    $tags_str = !empty($tags_arr) ? implode(' ', $tags_arr) : 'узбек секс uzbekcha';

    // Category
    $category_id = $manual_cat;
    if ($category_id == 0) {
        $def_cat = $mysqli->query("SELECT id FROM ero_categories ORDER BY id ASC LIMIT 1")->fetch_assoc();
        $category_id = $def_cat['id'] ?? 1;
    }

    // Fayl nomi va translit
    $rand_id = rand(100, 9999);
    $md5 = md5(microtime(true) . $rand_id);
    $translit = str_replace([' ', '/', '\\', '\''], '_', transliterate($title)) . '_' . $rand_id;
    $translit = preg_replace('/[^a-zA-Z0-9_-]/', '', $translit);

    // Poster saqlash
    $local_screenshot = '/content/screenshots/' . $md5 . '.jpg';
    $save_img_path = $_SERVER['DOCUMENT_ROOT'] . $local_screenshot;
    if (!empty($poster_url)) {
        parser_download_image($poster_url, $save_img_path, $width_S, $height_S, $settings['water'] ?? 0);
    }

    $final_address = $video_src;
    $embed_code = '';

    // Yuklab olish rejimi bo'lsa
    if ($save_mode == 'download') {
        $mp4_data = parser_get_html($video_src);
        if (!empty($mp4_data) && strlen($mp4_data) > 10000) {
            $local_video = '/content/video/' . $md5 . '.mp4';
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . $local_video, $mp4_data);
            $final_address = $local_video;
        }
    }

    $now = time();
    $sql = "INSERT INTO ero_files (
        name, description, screenshot, recoil, tags, translit, duration, downloads, 
        server, address, uniqueness, category, view, date, rewriting, added, yd, embed
    ) VALUES (
        '".mysqli_real_escape_string($mysqli, $title)."',
        '".mysqli_real_escape_string($mysqli, $desc)."',
        '".mysqli_real_escape_string($mysqli, $local_screenshot)."',
        '".mysqli_real_escape_string($mysqli, $final_address)."',
        '".mysqli_real_escape_string($mysqli, $tags_str)."',
        '".mysqli_real_escape_string($mysqli, $translit)."',
        '".mysqli_real_escape_string($mysqli, $duration)."',
        '0',
        'uzbxx.ru',
        '".mysqli_real_escape_string($mysqli, $final_address)."',
        '".mysqli_real_escape_string($mysqli, $uniqueness)."',
        '$category_id',
        '0',
        '$now',
        '0',
        'parser',
        '0',
        '".mysqli_real_escape_string($mysqli, $embed_code)."'
    )";

    if ($mysqli->query($sql)) {
        return ['status' => 'success', 'message' => "Muvaffaqiyatli qo‘shildi: <a href='/watch/{$translit}.html' target='_blank' style='color:#ff9900;'><b>$title</b></a>"];
    } else {
        return ['status' => 'error', 'message' => "Bazaga yozishda xatolik: " . $mysqli->error];
    }
}

// uzporno.website dan bitta videoni parslash
function parse_video_uzporno($video_url, $manual_cat, $save_mode, $mysqli, $settings, $width_S, $height_S) {
    $html = parser_get_html($video_url);
    if (empty($html) || strlen($html) < 200) {
        return ['status' => 'error', 'message' => "Sahifani yuklab bo‘lmadi: $video_url"];
    }

    // JSON-LD VideoObject
    $title = '';
    $desc = '';
    $poster_url = '';
    $embed_url = '';
    $duration = '05:00';

    if (preg_match("|<script type=[\"\\\x27]application/ld\+json[\"\\\x27]>(.*?)</script>|is", $html, $m_json)) {
        $json_data = json_decode($m_json[1], true);
        if ($json_data) {
            $title = $json_data['name'] ?? '';
            $desc = $json_data['description'] ?? '';
            $poster_url = $json_data['thumbnailUrl'][0] ?? '';
            $embed_url = $json_data['embedUrl'] ?? '';
            $dur_raw = $json_data['duration'] ?? '';
            if (preg_match("|PT([0-9]+)S|i", $dur_raw, $m_sec)) {
                $s = intval($m_sec[1]);
                $duration = sprintf("%02d:%02d", floor($s / 60), $s % 60);
            }
        }
    }

    if (empty($title)) {
        if (preg_match("|<div class=\"xxxhd-title-top\">.*?<h1>(.*?)</h1>|is", $html, $m)) {
            $title = trim(strip_tags($m[1]));
        } elseif (preg_match("|<title>(.*?)</title>|is", $html, $m)) {
            $title = trim(strip_tags($m[1]));
        }
    }

    $title = preg_replace("/('|\"|\r?\n)/", '', $title);
    if (empty($title)) {
        return ['status' => 'error', 'message' => "Video nomini aniqlab bo‘lmadi ($video_url)"];
    }

    $uniqueness = md5($title);
    $check_exist = $mysqli->query("SELECT id FROM ero_files WHERE uniqueness = '$uniqueness' LIMIT 1");
    if ($check_exist && $check_exist->num_rows > 0) {
        return ['status' => 'skip', 'message' => "Allaqachon mavjud: <b>$title</b>"];
    }

    if (empty($poster_url)) {
        if (preg_match("|data-original=\"(https://uzporno.website/files/screens/[^\"]+)\"|i", $html, $m)) {
            $poster_url = $m[1];
        }
    }

    if (empty($embed_url)) {
        if (preg_match("|src=\"(https://uzporno.website/embed/[^\"]+)\"|i", $html, $m)) {
            $embed_url = $m[1];
        }
    }

    $tags_str = 'узбек секс uzbekcha uyatli video uzporno';

    // Category
    $category_id = $manual_cat;
    if ($category_id == 0) {
        $def_cat = $mysqli->query("SELECT id FROM ero_categories ORDER BY id ASC LIMIT 1")->fetch_assoc();
        $category_id = $def_cat['id'] ?? 1;
    }

    $rand_id = rand(100, 9999);
    $md5 = md5(microtime(true) . $rand_id);
    $translit = str_replace([' ', '/', '\\', '\''], '_', transliterate($title)) . '_' . $rand_id;
    $translit = preg_replace('/[^a-zA-Z0-9_-]/', '', $translit);

    // Poster saqlash
    $local_screenshot = '/content/screenshots/' . $md5 . '.jpg';
    $save_img_path = $_SERVER['DOCUMENT_ROOT'] . $local_screenshot;
    if (!empty($poster_url)) {
        parser_download_image($poster_url, $save_img_path, $width_S, $height_S, $settings['water'] ?? 0);
    }

    $final_address = !empty($embed_url) ? $embed_url : $video_url;
    $now = time();

    $sql = "INSERT INTO ero_files (
        name, description, screenshot, recoil, tags, translit, duration, downloads, 
        server, address, uniqueness, category, view, date, rewriting, added, yd, embed
    ) VALUES (
        '".mysqli_real_escape_string($mysqli, $title)."',
        '".mysqli_real_escape_string($mysqli, $desc)."',
        '".mysqli_real_escape_string($mysqli, $local_screenshot)."',
        '".mysqli_real_escape_string($mysqli, $final_address)."',
        '".mysqli_real_escape_string($mysqli, $tags_str)."',
        '".mysqli_real_escape_string($mysqli, $translit)."',
        '".mysqli_real_escape_string($mysqli, $duration)."',
        '0',
        'uzporno.website',
        '".mysqli_real_escape_string($mysqli, $final_address)."',
        '".mysqli_real_escape_string($mysqli, $uniqueness)."',
        '$category_id',
        '0',
        '$now',
        '0',
        'parser',
        '0',
        '".mysqli_real_escape_string($mysqli, $embed_url)."'
    )";

    if ($mysqli->query($sql)) {
        return ['status' => 'success', 'message' => "Muvaffaqiyatli qo‘shildi: <a href='/watch/{$translit}.html' target='_blank' style='color:#ff9900;'><b>$title</b></a>"];
    } else {
        return ['status' => 'error', 'message' => "Bazaga yozishda xatolik: " . $mysqli->error];
    }
}

// POST amallarini bajarish
$logs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action_type = filter($_POST['action_type'] ?? '');
    $donor = filter($_POST['donor'] ?? 'uzbxx');
    $category_choice = abs(intval($_POST['category'] ?? 0));
    $save_mode = filter($_POST['save_mode'] ?? 'stream');

    if ($action_type === 'single_url') {
        $single_url = trim($_POST['single_url'] ?? '');
        if (empty($single_url)) {
            $logs[] = ['status' => 'error', 'message' => 'Video havolasi kiritilmadi!'];
        } else {
            if (stripos($single_url, 'uzbxx.ru') !== false) {
                $logs[] = parse_video_uzbxx($single_url, $category_choice, $save_mode, $mysqli, $settings, $width_S, $height_S);
            } elseif (stripos($single_url, 'uzporno.website') !== false) {
                $logs[] = parse_video_uzporno($single_url, $category_choice, $save_mode, $mysqli, $settings, $width_S, $height_S);
            } else {
                $logs[] = ['status' => 'error', 'message' => 'Noma‘lum havola! Faqat uzbxx.ru yoki uzporno.website havolalari qo‘llab-quvvatlanadi.'];
            }
        }
    } elseif ($action_type === 'mass_parse') {
        $page_num = max(1, abs(intval($_POST['page_num'] ?? 1)));
        $limit_count = min(30, max(1, abs(intval($_POST['count'] ?? 10))));

        if ($donor === 'uzbxx') {
            $catalog_url = ($page_num == 1) ? 'https://uzbxx.ru/' : "https://uzbxx.ru/{$page_num}";
            $cat_html = parser_get_html($catalog_url);
            
            preg_match_all("|<a href=\"(https://uzbxx.ru/video/[^\"]+)\"|i", $cat_html, $matches);
            $video_links = !empty($matches[1]) ? array_unique($matches[1]) : [];

            if (empty($video_links)) {
                $logs[] = ['status' => 'error', 'message' => "uzbxx.ru katalogidan video havolalar topilmadi ($catalog_url)."];
            } else {
                $collected = 0;
                foreach ($video_links as $v_url) {
                    if ($collected >= $limit_count) break;
                    $res = parse_video_uzbxx($v_url, $category_choice, $save_mode, $mysqli, $settings, $width_S, $height_S);
                    $logs[] = $res;
                    if ($res['status'] === 'success') {
                        $collected++;
                    }
                    usleep(300000); // 0.3s pauza
                }
            }
        } elseif ($donor === 'uzporno') {
            $catalog_url = ($page_num == 1) ? 'https://uzporno.website/' : "https://uzporno.website/{$page_num}";
            $cat_html = parser_get_html($catalog_url);

            preg_match_all("|<a href=\"(https://uzporno.website/video/[^\"]+)\"|i", $cat_html, $matches);
            $video_links = !empty($matches[1]) ? array_unique($matches[1]) : [];

            if (empty($video_links)) {
                $logs[] = ['status' => 'error', 'message' => "uzporno.website katalogidan video havolalar topilmadi ($catalog_url)."];
            } else {
                $collected = 0;
                foreach ($video_links as $v_url) {
                    if ($collected >= $limit_count) break;
                    $res = parse_video_uzporno($v_url, $category_choice, $save_mode, $mysqli, $settings, $width_S, $height_S);
                    $logs[] = $res;
                    if ($res['status'] === 'success') {
                        $collected++;
                    }
                    usleep(300000); // 0.3s pauza
                }
            }
        }
    }
}
?>

<div class="functions_data">
    <h2><i class="fa fa-cloud-download" style="color:#ff9900;"></i> Zamonaviy Video Parser (uzbxx.ru & uzporno.website)</h2>
    <p style="color:#888; margin-top:4px;">Ushbu modul orqali uzbxx.ru va uzporno.website saytlaridan videolarni bir zumda saytingizga import qilishingiz mumkin.</p>
</div>

<?php if (!empty($logs)): ?>
<div class="functions_data" style="background:#111; border:1px solid #ff9900; margin-bottom:15px;">
    <h3 style="color:#ff9900; margin-bottom:10px;"><i class="fa fa-list-alt"></i> Natijalar jurnali:</h3>
    <div style="max-height: 250px; overflow-y: auto; font-family: monospace; font-size: 13px;">
    <?php foreach ($logs as $log): ?>
        <?php if ($log['status'] === 'success'): ?>
            <div style="color: #28a745; margin-bottom: 4px;"><i class="fa fa-check-circle"></i> [Qo‘shildi] <?=$log['message']?></div>
        <?php elseif ($log['status'] === 'skip'): ?>
            <div style="color: #ffc107; margin-bottom: 4px;"><i class="fa fa-info-circle"></i> [O‘tkazildi] <?=$log['message']?></div>
        <?php else: ?>
            <div style="color: #dc3545; margin-bottom: 4px;"><i class="fa fa-times-circle"></i> [Xatolik] <?=$log['message']?></div>
        <?php endif; ?>
    <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- 1. Yagona havola orqali import qilish -->
<div class="functions_data" style="margin-bottom:15px;">
    <h3><i class="fa fa-link" style="color:#ff9900;"></i> 1. Yagona havola (URL) orqali video qo‘shish</h3>
    <p style="color:#888; font-size:12px; margin-bottom:8px;">uzbxx.ru yoki uzporno.website dagi istalgan video sahifasi havolasini kiriting:</p>
    <form method="post">
        <input type="hidden" name="action_type" value="single_url" />
        <p>
            <input type="url" name="single_url" class="injected" placeholder="Masalan: https://uzbxx.ru/video/nomi/ yoki https://uzporno.website/video/nomi/" required style="width:100%; font-size:14px;" />
        </p>
        <p style="margin-top:8px;">
            <b>Bo‘lim (Kategoriya):</b>
            <select name="category" class="injected" style="width:250px; display:inline-block; margin-left:10px;">
                <option value="0">-- Avtomatik --</option>
                <?php
                $cats_q = $mysqli->query("SELECT id, name FROM ero_categories ORDER BY id ASC");
                while ($c = $cats_q->fetch_assoc()) {
                    echo '<option value="'.$c['id'].'">'.$c['name'].'</option>';
                }
                ?>
            </select>
            &nbsp;
            <b>Rejim:</b>
            <select name="save_mode" class="injected" style="width:200px; display:inline-block; margin-left:10px;">
                <option value="stream" selected>Oqim (Stream / Embed)</option>
                <option value="download">Serverga yuklash (MP4)</option>
            </select>
        </p>
        <p style="margin-top:12px;">
            <button type="submit" class="byecos"><i class="fa fa-download"></i> Videoni import qilish</button>
        </p>
    </form>
</div>

<!-- 2. Katalogdan ommaviy yuklash (Avto-grabber) -->
<div class="functions_data">
    <h3><i class="fa fa-tasks" style="color:#ff9900;"></i> 2. Katalogdan ommaviy parslash (Avto-grabber)</h3>
    <p style="color:#888; font-size:12px; margin-bottom:8px;">Donor sayt katalogidan yangi videolarni avtomatik yig‘ib olish:</p>
    <form method="post">
        <input type="hidden" name="action_type" value="mass_parse" />
        <p>
            <b>Donor sayt:</b><br />
            <label style="margin-right:20px; cursor:pointer;">
                <input type="radio" name="donor" value="uzbxx" checked /> <b>uzbxx.ru</b> (UzbXX)
            </label>
            <label style="cursor:pointer;">
                <input type="radio" name="donor" value="uzporno" /> <b>uzporno.website</b> (UzPorno)
            </label>
        </p>
        <br />
        <p>
            <b>Katalog sahifasi raqami:</b>
            <input type="number" name="page_num" value="1" min="1" max="100" class="injected" style="width:80px; display:inline-block; margin-left:10px;" />
            <small style="color:#888;">(1 - eng yangilar, 2, 3... - oldingi sahifalar)</small>
        </p>
        <br />
        <p>
            <b>Yuklanadigan videolar soni:</b>
            <select name="count" class="injected" style="width:120px; display:inline-block; margin-left:10px;">
                <option value="5">5 ta video</option>
                <option value="10" selected>10 ta video</option>
                <option value="15">15 ta video</option>
                <option value="20">20 ta video</option>
            </select>
        </p>
        <br />
        <p>
            <b>Bo‘lim (Kategoriya):</b>
            <select name="category" class="injected" style="width:250px; display:inline-block; margin-left:10px;">
                <option value="0">-- Avtomatik --</option>
                <?php
                $cats_q2 = $mysqli->query("SELECT id, name FROM ero_categories ORDER BY id ASC");
                while ($c = $cats_q2->fetch_assoc()) {
                    echo '<option value="'.$c['id'].'">'.$c['name'].'</option>';
                }
                ?>
            </select>
            &nbsp;
            <b>Rejim:</b>
            <select name="save_mode" class="injected" style="width:200px; display:inline-block; margin-left:10px;">
                <option value="stream" selected>Oqim (Stream / Embed)</option>
                <option value="download">Serverga yuklash (MP4)</option>
            </select>
        </p>
        <br />
        <p>
            <button type="submit" class="byecos" onclick="return confirm('Parslash boshlansinmi? Bu bir necha soniya vaqt olishi mumkin.');">
                <i class="fa fa-play"></i> Parslashni boshlash
            </button>
        </p>
    </form>
</div>
