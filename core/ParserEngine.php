<?php

/**
 * erocms Video Parser Engine
 * Qo'llab-quvvatlanuvchi donor saytlar:
 * 1. uzbxx.ru
 * 2. uzporno.website
 * 3. arhivporno.watch (cat-uzbekskii-seks)
 */

if (!defined('PARSER_COOKIE_FILE')) {
    define('PARSER_COOKIE_FILE', sys_get_temp_dir() . '/erocms_parser_cookie.txt');
}

if (!function_exists('transliterate')) {
    function transliterate($string) {
        $replace = [
            'а'  =>  'a','А' => 'a', 'б' => 'b','Б' => 'b', 'в' => 'v','В' => 'v',
            'г' => 'g','Г' => 'g', 'д' => 'd','Д' => 'd', 'е' => 'e','Е' => 'e',
            'ё' => 'e','Ё' => 'e', 'ж' => 'zh','Ж' => 'zh', 'з' => 'z','З' => 'z',
            'и' => 'i','И' => 'i', 'й' => 'y','Й' => 'y', 'к' => 'k','К' => 'k',
            'л' => 'l','Л' => 'l', 'м' => 'm','М' => 'm', 'н' => 'n','Н' => 'n',
            'о' => 'o','О' => 'o', 'п' => 'p','П' => 'p', 'р' => 'r','Р' => 'r',
            'с' => 's','С' => 's', 'т' => 't','Т' => 't', 'у' => 'u','У' => 'u',
            'ф' => 'f','Ф' => 'f', 'х' => 'x','Х' => 'x', 'ц' => 'c','Ц' => 'c',
            'ч' => 'ch','Ч' => 'ch', 'ш' => 'sh','Ш' => 'sh', 'щ' => 'sch','Щ' => 'sch',
            'ъ' => '','Ъ' => '', 'ы' => 'y','Ы' => 'y', 'ь' => '','Ь' => '',
            'э' => 'e','Э' => 'e', 'ю' => 'yu','Ю' => 'yu', 'я' => 'ya','Я' => 'ya',
            ' ' => '_', '—' => '_', '-' => '_'
        ];
        return strtr($string, $replace);
    }
}

function parser_doc_root() {
    return !empty($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/') : dirname(__DIR__);
}

function parser_escape($mysqli, $str) {
    if ($mysqli instanceof mysqli) {
        return mysqli_real_escape_string($mysqli, $str);
    }
    return addslashes((string)$str);
}

/**
 * Xavfsiz va barqaror HTTP GET so'rovi
 */
function parser_fetch($url, $referer = '') {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 6);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
    curl_setopt($ch, CURLOPT_ENCODING, '');
    curl_setopt($ch, CURLOPT_COOKIEJAR, PARSER_COOKIE_FILE);
    curl_setopt($ch, CURLOPT_COOKIEFILE, PARSER_COOKIE_FILE);

    $headers = [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
        'Accept-Language: ru-RU,ru;q=0.9,uz;q=0.8,en;q=0.7',
        'Cache-Control: no-cache',
        'Pragma: no-cache',
    ];
    if (!empty($referer)) {
        curl_setopt($ch, CURLOPT_REFERER, $referer);
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

/**
 * Katta hajmdagi video fayllarni diskka oqimli yuklab olish (Out of Memory xatoligini oldini oladi)
 */
function parser_download_file($src_url, $dest_path, $referer = '') {
    $dir = dirname($dest_path);
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }

    $fp = fopen($dest_path, 'wb');
    if (!$fp) return false;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $src_url);
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 6);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 300); // Katta videolar uchun 5 daqiqa
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
    curl_setopt($ch, CURLOPT_COOKIEJAR, PARSER_COOKIE_FILE);
    curl_setopt($ch, CURLOPT_COOKIEFILE, PARSER_COOKIE_FILE);

    if (!empty($referer)) {
        curl_setopt($ch, CURLOPT_REFERER, $referer);
    }

    $success = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    fclose($fp);

    if (!$success || $http_code >= 400 || (file_exists($dest_path) && filesize($dest_path) < 10000)) {
        @unlink($dest_path);
        return false;
    }
    return true;
}

/**
 * Posterni yuklab olish va o'lchamini sozlash
 */
function parser_download_image($img_url, $save_path, $width_S = 400, $height_S = 225, $water = 0) {
    if (empty($img_url)) return false;

    $dir = dirname($save_path);
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
    @chmod($dir, 0777);

    $img_data = parser_fetch($img_url);
    if (!empty($img_data) && strlen($img_data) > 200) {
        $written = @file_put_contents($save_path, $img_data);
        if ($written !== false && file_exists($save_path) && filesize($save_path) > 200) {
            @chmod($save_path, 0666);
            if (class_exists('SimpleImage') && extension_loaded('gd') && function_exists('imagecreatefromjpeg')) {
                try {
                    $image = new SimpleImage();
                    $image->load($save_path);
                    $image->resize($width_S, $height_S);
                    $image->save($save_path);
                } catch (Throwable $e) {}
            }
            $doc_root = parser_doc_root();
            $water_file = $doc_root . '/designs/water.png';
            if ($water == 1 && file_exists($water_file) && function_exists('water') && extension_loaded('gd')) {
                try {
                    water($save_path, $save_path, $water_file);
                } catch (Throwable $e) {}
            }
            return true;
        }
    }
    return false;
}

/**
 * ISO 8601 yoki turli formatdagi davomiylikni MM:SS ga o'tkazish
 */
function parser_iso_duration($dur_raw) {
    if (empty($dur_raw)) return '05:00';
    $dur_raw = trim($dur_raw);

    // Agar allaqachon MM:SS yoki HH:MM:SS formatida bo'lsa
    if (preg_match('/^\d{1,2}:\d{2}(?::\d{2})?$/', $dur_raw)) {
        return $dur_raw;
    }

    // Sof son soniyalar bo'lsa
    if (is_numeric($dur_raw)) {
        $s = intval($dur_raw);
        $h = floor($s / 3600);
        $m = floor(($s % 3600) / 60);
        $sec = $s % 60;
        return $h > 0 ? sprintf('%02d:%02d:%02d', $h, $m, $sec) : sprintf('%02d:%02d', $m, $sec);
    }

    // ISO 8601: PT1H2M3S yoki PT6M11S yoki PT371S
    if (preg_match('/PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?/i', $dur_raw, $m)) {
        $hours = !empty($m[1]) ? intval($m[1]) : 0;
        $mins = !empty($m[2]) ? intval($m[2]) : 0;
        $secs = !empty($m[3]) ? intval($m[3]) : 0;
        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
        } else {
            return sprintf('%02d:%02d', $mins, $secs);
        }
    }

    return '05:00';
}

/**
 * Intellektual kategoriya aniqlash (sekschi.online ning 27 ta toifasi bo'yicha)
 */
function parser_smart_category($title, $tags_str, $donor, $mysqli) {
    static $cats = null;
    if ($cats === null) {
        $cats = [];
        $res = $mysqli->query("SELECT id, name, translit FROM ero_categories ORDER BY id ASC");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $cats[] = $row;
            }
        }
    }

    $text = mb_strtolower($title . ' ' . $tags_str, 'UTF-8');

    $rules = [
        '/(?:раком|догги|doggy|сзади)/iu' => 'rakom',
        '/(?:минет|отсос|сосет|сосёт|в рот|глубокий минет)/iu' => 'minet',
        '/(?:анал|anal|в жопу|в попу|в задниц|анальн)/iu' => 'anal',
        '/(?:пьян|буха|под градусом)/iu' => 'pyanye',
        '/(?:лишение целки|девствен|первый раз)/iu' => 'lishenie_celki',
        '/(?:кунни|лизать пис|лижет|куннилинг)/iu' => 'kunnilingus',
        '/(?:беремен|с пузом)/iu' => 'beremennye',
        '/(?:хентай|аниме|hentai|anime)/iu' => 'anime-hentai',
        '/(?:студент|общаг|сессия)/iu' => 'studenty',
        '/(?:сперм|конча|залп)/iu' => 'sperma',
        '/(?:домашн|частн|любительск|home)/iu' => 'domashnee',
        '/(?:группов|тройничок|втроем|втроём|мжм|жмж|оргия)/iu' => 'gruppovoe',
        '/(?:большие сиськи|сиськ|грудаст|дойки|tits|big tits|огромные сиськи)/iu' => 'siski',
        '/(?:большие члены|большой член|огромный хуй|big cock)/iu' => 'big',
        '/(?:бдсм|bdsm|госпож|рабын|порка|плеть)/iu' => 'bdsm',
        '/(?:жесток|жестк|груб)/iu' => 'zhestkoe',
        '/(?:лесби|девушки целуются|lesbian)/iu' => 'lesbiyanki',
        '/(?:мамк|мамашк|милф|milf|зрел)/iu' => 'mamki',
        '/(?:молод|юная|малолет|teen)/iu' => 'molodye',
        '/(?:волосат|небрит|пушист)/iu' => 'volosatye',
        '/(?:негр|чернокож|bbc)/iu' => 'negry',
        '/(?:блондин|blonde)/iu' => 'blonde',
        '/(?:брюнет|bryunet)/iu' => 'bryunetki',
        '/(?:узбек|uzbek|узбечк|uzbechka|uzbekcha|toshkent|samarqand|andijon|fargona|namangan)/iu' => 'uzbek',
        '/(?:азиат|asian|кореян|японк|китаянк)/iu' => 'asian',
        '/(?:русск|отечествен)/iu' => 'russkoe',
    ];

    foreach ($rules as $pattern => $translit) {
        if (preg_match($pattern, $text)) {
            foreach ($cats as $c) {
                if ($c['translit'] === $translit) {
                    return intval($c['id']);
                }
            }
        }
    }

    // Default uzbek kategoriyasi (donorlar asosan o'zbek pornosi)
    foreach ($cats as $c) {
        if ($c['translit'] === 'uzbek') {
            return intval($c['id']);
        }
    }

    return !empty($cats[0]['id']) ? intval($cats[0]['id']) : 1;
}

/**
 * 1. uzbxx.ru dan videoni parslash
 */
function parse_video_uzbxx($video_url, $manual_cat, $save_mode, $mysqli, $settings, $width_S, $height_S) {
    $video_url = trim($video_url);
    $html = parser_fetch($video_url);
    if (empty($html) || strlen($html) < 200) {
        return ['status' => 'error', 'message' => "Sahifani yuklab bo‘lmadi: $video_url"];
    }

    // Title olish (og:title eng toza)
    $title = '';
    if (preg_match('|<meta property="og:title" content="(.*?)"|is', $html, $m)) {
        $title = trim(html_entity_decode($m[1], ENT_QUOTES, 'UTF-8'));
    } elseif (preg_match('|<div class="xxxhd-title-top">.*?<h1>(.*?)</h1>|is', $html, $m)) {
        $title = trim(strip_tags($m[1]));
    } elseif (preg_match('|<title>(.*?)</title>|is', $html, $m)) {
        $title = trim(strip_tags($m[1]));
    }
    $title = preg_replace("/('|\"|\r?\n)/", '', $title);
    if (empty($title) || stripos($title, 'can’t be reached') !== false) {
        return ['status' => 'error', 'message' => "Video nomini aniqlab bo‘lmadi ($video_url)"];
    }

    // Takrorlanmaslikni tekshirish
    $uniqueness = md5($title);
    $check_exist = $mysqli->query("SELECT id FROM ero_files WHERE uniqueness = '$uniqueness' LIMIT 1");
    if ($check_exist && $check_exist->num_rows > 0) {
        return ['status' => 'skip', 'message' => "Allaqachon mavjud: <b>$title</b>"];
    }

    // Davomiylik
    $duration = '05:00';
    if (preg_match('|<meta property="og:duration" content="(\d+)"|is', $html, $m)) {
        $duration = parser_iso_duration($m[1]);
    } elseif (preg_match('|<i class="fa fa-clock-o"></i>\s*<b>(.*?)</b>|is', $html, $m)) {
        $duration = parser_iso_duration($m[1]);
    }

    // Poster
    $poster_url = '';
    if (preg_match('|<meta property="og:image" content="(.*?)"|is', $html, $m)) {
        $poster_url = trim($m[1]);
    } elseif (preg_match('|poster:\s*"([^"]+)"|is', $html, $m)) {
        $poster_url = trim($m[1]);
    }
    if (!empty($poster_url) && strpos($poster_url, 'http') !== 0) {
        $poster_url = 'https://uzbxx.ru/' . ltrim($poster_url, '/');
    }

    // Video stream manzil
    $video_src = '';
    if (preg_match('|file:\s*"([^"]+)"|is', $html, $m)) {
        $video_src = trim($m[1]);
    } elseif (preg_match('|https://uzbxx\.ru/video_online\??[^\"]*|is', $html, $m)) {
        $video_src = $m[0];
    }
    if (empty($video_src)) {
        $video_src = $video_url;
    }
    // uzbxx dagi video_online URLni to'g'rilash (trailing slash)
    if (strpos($video_src, 'video_online?id=') !== false) {
        $video_src = str_replace('video_online?id=', 'video_online/?id=', $video_src);
    }

    // Tavsif
    $desc = $title;
    if (preg_match('|<meta property="og:description" content="(.*?)"|is', $html, $m)) {
        $desc = trim(html_entity_decode($m[1], ENT_QUOTES, 'UTF-8'));
    }

    // Teglar
    $tags_arr = [];
    if (preg_match_all('|<meta property="video:tag" content="(.*?)"|is', $html, $m)) {
        $tags_arr = array_map('trim', $m[1]);
    } elseif (preg_match_all('|<a href="https://uzbxx\.ru/tags/[^"]+"><i class="fa fa-tags"></i>\s*(.*?)</a>|is', $html, $m)) {
        $tags_arr = array_map('trim', $m[1]);
    }
    $tags_str = !empty($tags_arr) ? implode(' ', $tags_arr) : 'узбек секс uzbekcha';

    // Kategoriya
    $category_id = intval($manual_cat);
    if ($category_id === 0) {
        $category_id = parser_smart_category($title, $tags_str, 'uzbxx.ru', $mysqli);
    }

    // Translitsiya va fayl nomlari
    $rand_id = rand(100, 9999);
    $md5 = md5(microtime(true) . $rand_id);
    $doc_root = parser_doc_root();
    $translit = str_replace([' ', '/', '\\', '\''], '_', transliterate($title)) . '_' . $rand_id;
    $translit = preg_replace('/[^a-zA-Z0-9_-]/', '', $translit);

    // Poster saqlash
    $local_screenshot = '/content/screenshots/' . $md5 . '.jpg';
    $save_img_path = $doc_root . $local_screenshot;
    $final_screenshot = '';
    if (!empty($poster_url)) {
        $saved = parser_download_image($poster_url, $save_img_path, $width_S, $height_S, $settings['water'] ?? 0);
        if ($saved && file_exists($save_img_path) && filesize($save_img_path) > 200) {
            $final_screenshot = $local_screenshot;
        } else {
            $final_screenshot = $poster_url;
        }
    } else {
        $final_screenshot = '/designs/no_poster.jpg';
    }

    $final_address = $video_src;
    $embed_code = '';

    // MP4 Yuklab olish rejimi
    if ($save_mode === 'download') {
        $local_video = '/content/video/' . $md5 . '.mp4';
        $save_video_path = $doc_root . $local_video;
        if (parser_download_file($video_src, $save_video_path, $video_url)) {
            $final_address = $local_video;
        }
    }

    $now = time();
    $sql = "INSERT INTO ero_files (
        name, description, screenshot, recoil, tags, translit, duration, downloads, 
        server, address, uniqueness, category, view, date, rewriting, added, yd, embed
    ) VALUES (
        '".parser_escape($mysqli, $title)."',
        '".parser_escape($mysqli, $desc)."',
        '".parser_escape($mysqli, $final_screenshot)."',
        '".parser_escape($mysqli, $final_address)."',
        '".parser_escape($mysqli, $tags_str)."',
        '".parser_escape($mysqli, $translit)."',
        '".parser_escape($mysqli, $duration)."',
        '0',
        'uzbxx.ru',
        '".parser_escape($mysqli, $final_address)."',
        '".parser_escape($mysqli, $uniqueness)."',
        '$category_id',
        '0',
        '$now',
        '0',
        '1',
        '0',
        '".parser_escape($mysqli, $embed_code)."'
    )";

    if ($mysqli->query($sql)) {
        return ['status' => 'success', 'message' => "Muvaffaqiyatli qo‘shildi: <a href='/watch/{$translit}.html' target='_blank' style='color:#ff9900;'><b>$title</b></a>"];
    } else {
        return ['status' => 'error', 'message' => "Bazaga yozishda xatolik: " . $mysqli->error];
    }
}

/**
 * 2. uzporno.website dan videoni parslash
 */
function parse_video_uzporno($video_url, $manual_cat, $save_mode, $mysqli, $settings, $width_S, $height_S) {
    $video_url = trim($video_url);
    $html = parser_fetch($video_url);
    if (empty($html) || strlen($html) < 200) {
        return ['status' => 'error', 'message' => "Sahifani yuklab bo‘lmadi: $video_url"];
    }

    $title = '';
    $desc = '';
    $poster_url = '';
    $embed_url = '';
    $duration = '05:00';
    $tags_str = 'узбек секс uzbekcha uyatli video uzporno';

    // JSON-LD orqali olish
    if (preg_match('|<script type=[\"\\\x27]application/ld\+json[\"\\\x27]>(.*?)</script>|is', $html, $m_json)) {
        $json_data = json_decode($m_json[1], true);
        if ($json_data) {
            $title = $json_data['name'] ?? '';
            $desc = $json_data['description'] ?? '';
            $poster_url = is_array($json_data['thumbnailUrl']) ? end($json_data['thumbnailUrl']) : ($json_data['thumbnailUrl'] ?? '');
            $embed_url = $json_data['embedUrl'] ?? '';
            if (!empty($json_data['duration'])) {
                $duration = parser_iso_duration($json_data['duration']);
            }
            if (!empty($json_data['keywords'])) {
                $tags_str = $json_data['keywords'];
            }
        }
    }

    if (empty($title)) {
        if (preg_match('|<meta property="og:title" content="(.*?)"|is', $html, $m)) {
            $title = trim(html_entity_decode($m[1], ENT_QUOTES, 'UTF-8'));
        } elseif (preg_match('|<div class="xxxhd-title-top">.*?<h1>(.*?)</h1>|is', $html, $m)) {
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
        if (preg_match('|<meta property="og:image" content="(.*?)"|is', $html, $m)) {
            $poster_url = trim($m[1]);
        }
    }

    if (empty($embed_url)) {
        if (preg_match('|<meta property="og:video" content="(https://uzporno\.website/embed/[^"]+)"|is', $html, $m)) {
            $embed_url = trim($m[1]);
        }
    }

    // Slug orqali to'g'ridan-to'g'ri play / download manzili
    $slug = '';
    if (preg_match('|/video/([^/]+)/|', $video_url, $m)) {
        $slug = $m[1];
    }
    if (empty($embed_url) && !empty($slug)) {
        $embed_url = "https://uzporno.website/embed/{$slug}/";
    }
    $play_url = !empty($slug) ? "https://uzporno.website/play/{$slug}/" : $video_url;

    // Kategoriya
    $category_id = intval($manual_cat);
    if ($category_id === 0) {
        $category_id = parser_smart_category($title, $tags_str, 'uzporno.website', $mysqli);
    }

    $rand_id = rand(100, 9999);
    $md5 = md5(microtime(true) . $rand_id);
    $doc_root = parser_doc_root();
    $translit = str_replace([' ', '/', '\\', '\''], '_', transliterate($title)) . '_' . $rand_id;
    $translit = preg_replace('/[^a-zA-Z0-9_-]/', '', $translit);

    // Poster saqlash
    $local_screenshot = '/content/screenshots/' . $md5 . '.jpg';
    $save_img_path = $doc_root . $local_screenshot;
    $final_screenshot = '';
    if (!empty($poster_url)) {
        $saved = parser_download_image($poster_url, $save_img_path, $width_S, $height_S, $settings['water'] ?? 0);
        if ($saved && file_exists($save_img_path) && filesize($save_img_path) > 200) {
            $final_screenshot = $local_screenshot;
        } else {
            $final_screenshot = $poster_url;
        }
    } else {
        $final_screenshot = '/designs/no_poster.jpg';
    }

    $final_address = !empty($embed_url) ? $embed_url : $video_url;

    // Serverga MP4 yuklash rejimi
    if ($save_mode === 'download' && !empty($play_url)) {
        $local_video = '/content/video/' . $md5 . '.mp4';
        $save_video_path = $doc_root . $local_video;
        if (parser_download_file($play_url, $save_video_path, $video_url)) {
            $final_address = $local_video;
            $embed_url = ''; // MP4 yuklanganda to'g'ridan-to'g'ri playerda o'ynaydi
        }
    }

    $now = time();
    $sql = "INSERT INTO ero_files (
        name, description, screenshot, recoil, tags, translit, duration, downloads, 
        server, address, uniqueness, category, view, date, rewriting, added, yd, embed
    ) VALUES (
        '".parser_escape($mysqli, $title)."',
        '".parser_escape($mysqli, $desc)."',
        '".parser_escape($mysqli, $final_screenshot)."',
        '".parser_escape($mysqli, $final_address)."',
        '".parser_escape($mysqli, $tags_str)."',
        '".parser_escape($mysqli, $translit)."',
        '".parser_escape($mysqli, $duration)."',
        '0',
        'uzporno.website',
        '".parser_escape($mysqli, $final_address)."',
        '".parser_escape($mysqli, $uniqueness)."',
        '$category_id',
        '0',
        '$now',
        '0',
        '1',
        '0',
        '".parser_escape($mysqli, $embed_url)."'
    )";

    if ($mysqli->query($sql)) {
        return ['status' => 'success', 'message' => "Muvaffaqiyatli qo‘shildi: <a href='/watch/{$translit}.html' target='_blank' style='color:#ff9900;'><b>$title</b></a>"];
    } else {
        return ['status' => 'error', 'message' => "Bazaga yozishda xatolik: " . $mysqli->error];
    }
}

/**
 * 3. arhivporno.watch dan videoni parslash
 */
function parse_video_arhivporno($video_url, $manual_cat, $save_mode, $mysqli, $settings, $width_S, $height_S, $cat_context = []) {
    $video_url = trim($video_url);
    $html = parser_fetch($video_url, 'https://arhivporno.watch/cat-uzbekskii-seks/');
    if (empty($html) || strlen($html) < 200) {
        return ['status' => 'error', 'message' => "Sahifani yuklab bo‘lmadi: $video_url"];
    }

    // Title
    $title = '';
    if (preg_match('|<div class="full-column">.*?<h1>(.*?)</h1>|is', $html, $m)) {
        $title = trim(strip_tags($m[1]));
    } elseif (preg_match('|<meta property="og:title" content="(.*?)"|is', $html, $m)) {
        $title = trim(html_entity_decode($m[1], ENT_QUOTES, 'UTF-8'));
    } elseif (preg_match('|<title>(.*?)</title>|is', $html, $m)) {
        $title = trim(strip_tags($m[1]));
    }
    $title = preg_replace('/смотреть онлайн.*$/iu', '', $title);
    $title = trim(preg_replace("/('|\"|\r?\n)/", '', $title));
    if (empty($title)) {
        return ['status' => 'error', 'message' => "Video nomini aniqlab bo‘lmadi ($video_url)"];
    }

    $uniqueness = md5($title);
    $check_exist = $mysqli->query("SELECT id FROM ero_files WHERE uniqueness = '$uniqueness' LIMIT 1");
    if ($check_exist && $check_exist->num_rows > 0) {
        return ['status' => 'skip', 'message' => "Allaqachon mavjud: <b>$title</b>"];
    }

    // Embed URL
    $embed_url = '';
    if (preg_match('|<div class="full-video">.*?<iframe[^>]+src="([^"]+)"|is', $html, $m)) {
        $embed_url = trim($m[1]);
    }

    // Poster
    $poster_url = $cat_context['poster'] ?? '';
    if (empty($poster_url)) {
        // ID ni topish
        $video_id = 0;
        if (preg_match('|data-id="(\d+)"|i', $html, $m_id)) {
            $video_id = intval($m_id[1]);
        } elseif (preg_match('|video_id\s*=\s*(\d+);|i', $html, $m_id)) {
            $video_id = intval($m_id[1]);
        }
        if ($video_id > 0) {
            $dir_block = floor($video_id / 1000) * 1000;
            $poster_url = "https://arhivporno.watch/contents/videos_screenshots/{$dir_block}/{$video_id}/320x180/1.jpg";
        }
    }
    if (empty($poster_url) && !empty($embed_url) && strpos($embed_url, 'pornosektor.com') !== false) {
        if (preg_match('|embed/(\d+)|', $embed_url, $m_emb)) {
            $p_id = intval($m_emb[1]);
            $p_dir = floor($p_id / 1000) * 1000;
            $poster_url = "https://pornosektor.com/contents/videos_screenshots/{$p_dir}/{$p_id}/preview.jpg";
        }
    }

    // Davomiylik
    $duration = $cat_context['duration'] ?? '05:00';

    // Tavsif
    $desc = $title;
    if (preg_match('|<p class="video-text">(.*?)</p>|is', $html, $m)) {
        $desc = trim(strip_tags($m[1]));
    }

    // Teglar
    $tags_arr = [];
    if (preg_match_all('|<div class="full-meta cats-links">.*?<a[^>]+title="([^"]+)"|is', $html, $m)) {
        $tags_arr = array_map('trim', $m[1]);
    }
    $tags_str = !empty($tags_arr) ? implode(' ', $tags_arr) : 'узбек секс узбечка arhivporno';

    // Kategoriya
    $category_id = intval($manual_cat);
    if ($category_id === 0) {
        $category_id = parser_smart_category($title, $tags_str, 'arhivporno.watch', $mysqli);
    }

    $rand_id = rand(100, 9999);
    $md5 = md5(microtime(true) . $rand_id);
    $doc_root = parser_doc_root();
    $translit = str_replace([' ', '/', '\\', '\''], '_', transliterate($title)) . '_' . $rand_id;
    $translit = preg_replace('/[^a-zA-Z0-9_-]/', '', $translit);

    // Poster saqlash
    $local_screenshot = '/content/screenshots/' . $md5 . '.jpg';
    $save_img_path = $doc_root . $local_screenshot;
    $final_screenshot = '';
    if (!empty($poster_url)) {
        $saved = parser_download_image($poster_url, $save_img_path, $width_S, $height_S, $settings['water'] ?? 0);
        if ($saved && file_exists($save_img_path) && filesize($save_img_path) > 200) {
            $final_screenshot = $local_screenshot;
        } else {
            $final_screenshot = $poster_url;
        }
    } else {
        $final_screenshot = '/designs/no_poster.jpg';
    }

    $final_address = !empty($embed_url) ? $embed_url : $video_url;

    $now = time();
    $sql = "INSERT INTO ero_files (
        name, description, screenshot, recoil, tags, translit, duration, downloads, 
        server, address, uniqueness, category, view, date, rewriting, added, yd, embed
    ) VALUES (
        '".parser_escape($mysqli, $title)."',
        '".parser_escape($mysqli, $desc)."',
        '".parser_escape($mysqli, $final_screenshot)."',
        '".parser_escape($mysqli, $final_address)."',
        '".parser_escape($mysqli, $tags_str)."',
        '".parser_escape($mysqli, $translit)."',
        '".parser_escape($mysqli, $duration)."',
        '0',
        'arhivporno.watch',
        '".parser_escape($mysqli, $final_address)."',
        '".parser_escape($mysqli, $uniqueness)."',
        '$category_id',
        '0',
        '$now',
        '0',
        '1',
        '0',
        '".parser_escape($mysqli, $embed_url)."'
    )";

    if ($mysqli->query($sql)) {
        return ['status' => 'success', 'message' => "Muvaffaqiyatli qo‘shildi: <a href='/watch/{$translit}.html' target='_blank' style='color:#ff9900;'><b>$title</b></a>"];
    } else {
        return ['status' => 'error', 'message' => "Bazaga yozishda xatolik: " . $mysqli->error];
    }
}

/**
 * Katalog havolalarini olish: uzbxx.ru
 */
function parser_get_catalog_links_uzbxx($page = 1) {
    $url = ($page == 1) ? 'https://uzbxx.ru/' : "https://uzbxx.ru/{$page}";
    $html = parser_fetch($url);
    if (empty($html)) return [];
    preg_match_all('|<a href="(https://uzbxx\.ru/video/[^"]+)"|i', $html, $m);
    return !empty($m[1]) ? array_values(array_unique($m[1])) : [];
}

/**
 * Katalog havolalarini olish: uzporno.website
 */
function parser_get_catalog_links_uzporno($page = 1) {
    $url = ($page == 1) ? 'https://uzporno.website/' : "https://uzporno.website/{$page}";
    $html = parser_fetch($url);
    if (empty($html)) return [];
    preg_match_all('|<a href="(https://uzporno\.website/video/[^"]+)"|i', $html, $m);
    return !empty($m[1]) ? array_values(array_unique($m[1])) : [];
}

/**
 * Katalog havolalarini olish: arhivporno.watch
 * Qabul qiladi: sahifa raqami (1, 2...) yoki to'liq bo'lim URLi
 */
function parser_get_catalog_links_arhivporno($page_or_url = 1) {
    if (is_numeric($page_or_url)) {
        $url = ($page_or_url == 1) ? 'https://arhivporno.watch/cat-uzbekskii-seks/' : "https://arhivporno.watch/cat-uzbekskii-seks/{$page_or_url}/";
    } else {
        $url = trim($page_or_url);
    }

    $html = parser_fetch($url);
    if (empty($html)) return [];

    // Video kartochkalaridan havolalar va qo'shimcha ma'lumotlarni yig'ish
    $items = [];
    preg_match_all('|<a href="(https://arhivporno\.watch/[^"/]+/)"[^>]*class="traff".*?<img[^>]+data-original="([^"]+)".*?<span class="thumb-time">([0-9:]+)</span>|is', $html, $m);

    if (!empty($m[1])) {
        foreach ($m[1] as $idx => $link) {
            $items[$link] = [
                'url' => $link,
                'poster' => $m[2][$idx] ?? '',
                'duration' => $m[3][$idx] ?? '05:00'
            ];
        }
    } else {
        // Oddiy href qidiruv
        preg_match_all('|<a href="(https://arhivporno\.watch/[^"/]+/)"[^>]*class="traff"|i', $html, $m2);
        if (!empty($m2[1])) {
            foreach (array_unique($m2[1]) as $link) {
                $items[$link] = [
                    'url' => $link,
                    'poster' => '',
                    'duration' => '05:00'
                ];
            }
        }
    }

    return array_values($items);
}

/**
 * 4. sexlar.link dan videoni parslash
 */
function parse_video_sexlar($video_url, $manual_cat, $save_mode, $mysqli, $settings, $width_S, $height_S, $cat_context = []) {
    $video_url = trim($video_url);
    if (strpos($video_url, 'http') !== 0) {
        $video_url = 'https://sexlar.link' . (strpos($video_url, '/') === 0 ? '' : '/') . $video_url;
    }

    $html = parser_fetch($video_url, 'https://sexlar.link/');
    if (empty($html) || strlen($html) < 200) {
        return ['status' => 'error', 'message' => "Sahifani yuklab bo‘lmadi: $video_url"];
    }

    // 1. Sarlavha (Title)
    $title = '';
    if (preg_match('|<div class="headline">.*?<h1>(.*?)</h1>|is', $html, $m)) {
        $title = trim(strip_tags($m[1]));
    } elseif (preg_match('|<meta property="og:title" content="(.*?)"|is', $html, $m)) {
        $title = trim(html_entity_decode($m[1], ENT_QUOTES, 'UTF-8'));
    } elseif (preg_match('|<title>(.*?)</title>|is', $html, $m)) {
        $title = trim(strip_tags($m[1]));
    }
    $title = preg_replace('/смотреть онлайн.*$/iu', '', $title);
    $title = trim(preg_replace("/('|\"|\r?\n)/", '', $title));
    if (empty($title)) {
        return ['status' => 'error', 'message' => "Video nomini aniqlab bo‘lmadi ($video_url)"];
    }

    // Takroriylikni tekshirish
    $uniqueness = md5($title);
    $check_exist = $mysqli->query("SELECT id FROM ero_files WHERE uniqueness = '$uniqueness' LIMIT 1");
    if ($check_exist && $check_exist->num_rows > 0) {
        return ['status' => 'skip', 'message' => "Allaqachon mavjud: <b>$title</b>"];
    }

    // 2. Video fayli va Embed URL
    $video_file_url = '';
    $embed_url = '';
    $video_id = 0;

    if (preg_match('|file:\s*[\'"](https?://[^\'"]+\.mp4[^\'"]*)[\'"]|i', $html, $m_file)) {
        $video_file_url = trim($m_file[1]);
        if (preg_match('|/storage/\d+/(\d+)/\1\.mp4|i', $video_file_url, $m_id)) {
            $video_id = intval($m_id[1]);
        } elseif (preg_match('|storage/\d+/(\d+)|i', $video_file_url, $m_id)) {
            $video_id = intval($m_id[1]);
        }
    }

    if ($video_id > 0) {
        $embed_url = "https://666.watch/embed/{$video_id}";
    } elseif (preg_match('|/embed/(\d+)|i', $html, $m_emb)) {
        $embed_url = "https://666.watch/embed/" . intval($m_emb[1]);
    }

    if (empty($video_file_url) && empty($embed_url)) {
        return ['status' => 'error', 'message' => "Video manbasi (MP4 yoki embed) topilmadi: $video_url"];
    }

    // 3. Poster (Skrinshot)
    $poster_url = $cat_context['poster'] ?? '';
    if (empty($poster_url) && preg_match('|image:\s*[\'"]([^\'"]+)[\'"]|i', $html, $m_img)) {
        $poster_url = trim($m_img[1]);
        if (strpos($poster_url, 'http') !== 0) {
            $poster_url = 'https://sexlar.link' . (strpos($poster_url, '/') === 0 ? '' : '/') . $poster_url;
        }
    }
    if (empty($poster_url) && preg_match('|<meta property="og:image" content="(.*?)"|is', $html, $m_og)) {
        $poster_url = trim($m_og[1]);
    }

    // 4. Davomiylik (Duration)
    $duration = $cat_context['duration'] ?? '';
    if (empty($duration) && preg_match('|<span>Длительность:\s*<em>(.*?)</em></span>|is', $html, $m_dur)) {
        $duration = trim($m_dur[1]);
    }
    if (empty($duration) && preg_match('|<div class="duration">(.*?)</div>|is', $html, $m_dur2)) {
        $duration = trim($m_dur2[1]);
    }
    $duration = parser_iso_duration($duration);

    // 5. Tavsif va teglar
    $desc = $title;
    if (preg_match('|<div class="item">([^<]+(?:<a[^>]*>[^<]+</a>[^<]*)*)</div>|is', $html, $m_desc)) {
        $desc = trim(strip_tags($m_desc[1]));
        $desc = preg_replace('/https?:\/\/[^\s]+/i', '', $desc);
    }
    if (strlen($desc) < 10) {
        $desc = $title;
    }

    $tags_str = 'узбек секс узбечка sexlar uzb seks';

    // 6. Kategoriya aniqlash: Agar foydalanuvchi tanlagan bo'lsa o'shani oladi, bo'lmasa mavzuga qarab o'rnatadi
    $category_id = intval($manual_cat);
    if ($category_id === 0) {
        $category_id = parser_smart_category($title, $tags_str . ' ' . $desc, 'sexlar.link', $mysqli);
    }

    // 7. Unikal identifikatorlar
    $rand_id = rand(100, 9999);
    $md5 = md5(microtime(true) . $rand_id);
    $doc_root = parser_doc_root();
    $translit = str_replace([' ', '/', '\\', '\''], '_', transliterate($title)) . '_' . $rand_id;
    $translit = preg_replace('/[^a-zA-Z0-9_-]/', '', $translit);

    // 8. Skrinshotni yuklash
    $local_screenshot = '/content/screenshots/' . $md5 . '.jpg';
    $save_img_path = $doc_root . $local_screenshot;
    $final_screenshot = '';
    if (!empty($poster_url)) {
        $saved = parser_download_image($poster_url, $save_img_path, $width_S, $height_S, $settings['water'] ?? 0);
        if ($saved && file_exists($save_img_path) && filesize($save_img_path) > 200) {
            $final_screenshot = $local_screenshot;
        } else {
            $final_screenshot = $poster_url;
        }
    } else {
        $final_screenshot = '/designs/no_poster.jpg';
    }

    // 9. Saqlash rejimi: server (MP4) yoki stream (embed/direct)
    $final_address = '';
    $final_embed = $embed_url;

    if ($save_mode === 'server' && !empty($video_file_url)) {
        $save_vid_path = $doc_root . '/content/video/' . $md5 . '.mp4';
        $downloaded = parser_download_file($video_file_url, $save_vid_path, 'https://sexlar.link/');
        if ($downloaded && file_exists($save_vid_path) && filesize($save_vid_path) > 100000) {
            $final_address = '/content/video/' . $md5 . '.mp4';
            $final_embed = '';
        } else {
            $final_address = !empty($embed_url) ? $embed_url : $video_file_url;
        }
    } else {
        $final_address = !empty($embed_url) ? $embed_url : $video_file_url;
    }

    $now = time();
    $sql = "INSERT INTO ero_files (
        name, description, screenshot, recoil, tags, translit, duration, downloads, 
        server, address, uniqueness, category, view, date, rewriting, added, yd, embed
    ) VALUES (
        '".parser_escape($mysqli, $title)."',
        '".parser_escape($mysqli, $desc)."',
        '".parser_escape($mysqli, $final_screenshot)."',
        '".parser_escape($mysqli, $final_address)."',
        '".parser_escape($mysqli, $tags_str)."',
        '".parser_escape($mysqli, $translit)."',
        '".parser_escape($mysqli, $duration)."',
        '0',
        'sexlar.link',
        '".parser_escape($mysqli, $final_address)."',
        '".parser_escape($mysqli, $uniqueness)."',
        '$category_id',
        '0',
        '$now',
        '0',
        '1',
        '0',
        '".parser_escape($mysqli, $final_embed)."'
    )";

    if ($mysqli->query($sql)) {
        return ['status' => 'success', 'message' => "Muvaffaqiyatli qo‘shildi: <a href='/watch/{$translit}.html' target='_blank' style='color:#ff9900;'><b>$title</b></a>"];
    } else {
        return ['status' => 'error', 'message' => "Bazaga yozishda xatolik: " . $mysqli->error];
    }
}

/**
 * Katalog havolalarini olish: sexlar.link
 * Qabul qiladi: sahifa raqami (1, 2, 3...) yoki URL
 */
function parser_get_catalog_links_sexlar($page = 1) {
    if (is_numeric($page)) {
        $url = ($page == 1) ? 'https://sexlar.link/' : "https://sexlar.link/{$page}/";
    } else {
        $url = trim($page);
    }

    $html = parser_fetch($url);
    if (empty($html)) return [];

    $items = [];
    preg_match_all('#<div class="item">\s*<a href="(/sekis/[^"]+/)"\s*title="([^"]+)".*?data-src="([^"]+)".*?<div class="duration">([^<]+)</div>#is', $html, $m);

    if (!empty($m[1])) {
        foreach ($m[1] as $idx => $rel_link) {
            $link = 'https://sexlar.link' . $rel_link;
            $img = $m[3][$idx] ?? '';
            if (!empty($img) && strpos($img, 'http') !== 0) {
                $img = 'https://sexlar.link' . (strpos($img, '/') === 0 ? '' : '/') . $img;
            }
            $items[$link] = [
                'url' => $link,
                'title' => trim(html_entity_decode($m[2][$idx] ?? '', ENT_QUOTES, 'UTF-8')),
                'poster' => $img,
                'duration' => trim($m[4][$idx] ?? '05:00')
            ];
        }
    } else {
        preg_match_all('|<a href="(/sekis/[^"]+/)"|i', $html, $m2);
        if (!empty($m2[1])) {
            foreach (array_unique($m2[1]) as $rel_link) {
                $link = 'https://sexlar.link' . $rel_link;
                $items[$link] = [
                    'url' => $link,
                    'title' => '',
                    'poster' => '',
                    'duration' => '05:00'
                ];
            }
        }
    }

    return array_values($items);
}

/**
 * Singan yoki 404 bo'lgan skrinshotlarni avtomatik aniqlab tuzatish
 */
function parser_repair_broken_screenshots($mysqli) {
    $doc_root = parser_doc_root();
    @chmod($doc_root . '/content/screenshots', 0777);

    // /content/screenshots/ bo'lgan yoki bo'sh bo'lgan videolarni olamiz
    $res = $mysqli->query("SELECT id, name, screenshot, recoil, address, server, translit, embed FROM ero_files WHERE screenshot LIKE '/content/screenshots/%' OR screenshot = '' OR screenshot IS NULL ORDER BY id DESC LIMIT 500");
    if (!$res || $res->num_rows === 0) {
        return 0;
    }

    $repaired_count = 0;
    while ($row = $res->fetch_assoc()) {
        $full_local = $doc_root . ($row['screenshot'] ?? '');
        // Agar lokal fayl haqiqatan mavjud bo'lsa va o'lchami 200 baytdan katta bo'lsa, tegmaymiz
        if (!empty($row['screenshot']) && file_exists($full_local) && filesize($full_local) > 200) {
            continue;
        }

        $new_screenshot = '';
        $addr = ($row['address'] ?? '') . ' ' . ($row['recoil'] ?? '') . ' ' . ($row['embed'] ?? '');

        // 1. sexlar.link / 666.watch
        if (($row['server'] ?? '') === 'sexlar.link' || strpos($addr, '666.watch') !== false || strpos($addr, 'sexlar.link') !== false) {
            $vid_id = 0;
            if (preg_match('|/storage/\d+/(\d+)/|', $addr, $m)) {
                $vid_id = intval($m[1]);
            } elseif (preg_match('|embed/(\d+)|', $addr, $m)) {
                $vid_id = intval($m[1]);
            } elseif (preg_match('|/(\d+)\.mp4|', $addr, $m)) {
                $vid_id = intval($m[1]);
            } elseif (preg_match('|_(\d+)$|', $row['translit'] ?? '', $m)) {
                $cand = intval($m[1]);
                if ($cand >= 100 && $cand <= 100000) {
                    $vid_id = $cand;
                }
            }

            if ($vid_id > 0) {
                $dir_block = floor($vid_id / 1000) * 1000;
                $new_screenshot = "https://666.watch/contents/videos_screenshots/{$dir_block}/{$vid_id}/preview.jpg";
            }
        }

        // 2. arhivporno.watch / pornosektor.com
        if (empty($new_screenshot) && (($row['server'] ?? '') === 'arhivporno.watch' || strpos($addr, 'arhivporno') !== false || strpos($addr, 'pornosektor') !== false)) {
            $vid_id = 0;
            if (preg_match('|/storage/\d+/(\d+)/|', $addr, $m)) {
                $vid_id = intval($m[1]);
            } elseif (preg_match('|embed/(\d+)|', $addr, $m)) {
                $vid_id = intval($m[1]);
            } elseif (preg_match('|/(\d+)/|', $addr, $m)) {
                $vid_id = intval($m[1]);
            }
            if ($vid_id > 0) {
                $dir_block = floor($vid_id / 1000) * 1000;
                $new_screenshot = "https://arhivporno.watch/contents/videos_screenshots/{$dir_block}/{$vid_id}/320x180/1.jpg";
            }
        }

        // 3. uzporno.website
        if (empty($new_screenshot) && (($row['server'] ?? '') === 'uzporno.website' || strpos($addr, 'uzporno') !== false)) {
            if (preg_match('|/video/([^/]+)/|', $addr, $m) || preg_match('|/embed/([^/]+)/|', $addr, $m)) {
                $slug = $m[1];
                $new_screenshot = "https://uzporno.website/embed/{$slug}/";
            }
        }

        // 4. Default poster
        if (empty($new_screenshot)) {
            $new_screenshot = '/designs/no_poster.jpg';
        }

        if (!empty($new_screenshot)) {
            $safe_s = parser_escape($mysqli, $new_screenshot);
            $mysqli->query("UPDATE ero_files SET screenshot = '{$safe_s}' WHERE id = '{$row['id']}'");
            $repaired_count++;
        }
    }

    if ($repaired_count > 0) {
        // Keshni tozalash
        @array_map('unlink', glob($doc_root . '/content/cache/*.html'));
    }

    return $repaired_count;
}

