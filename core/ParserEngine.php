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
 * Xavfsiz, bloklanishga qarshi va barqaror HTTP GET so'rovi (Anti-Block & Retry)
 */
function parser_fetch($url, $referer = '', $max_retries = 2) {
    static $user_agents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:125.0) Gecko/20100101 Firefox/125.0',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_4_1) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4.1 Safari/605.1.15',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36 Edg/124.0.0.0'
    ];
    $ua = $user_agents[array_rand($user_agents)];

    for ($attempt = 1; $attempt <= $max_retries; $attempt++) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 6);
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 25);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_COOKIEJAR, PARSER_COOKIE_FILE);
        curl_setopt($ch, CURLOPT_COOKIEFILE, PARSER_COOKIE_FILE);

        $headers = [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
            'Accept-Language: ru-RU,ru;q=0.9,uz;q=0.8,en-US;q=0.7,en;q=0.6',
            'Sec-Ch-Ua: "Chromium";v="124", "Google Chrome";v="124", "Not-A.Brand";v="99"',
            'Sec-Ch-Ua-Mobile: ?0',
            'Sec-Ch-Ua-Platform: "Windows"',
            'Sec-Fetch-Dest: document',
            'Sec-Fetch-Mode: navigate',
            'Sec-Fetch-Site: none',
            'Sec-Fetch-User: ?1',
            'Upgrade-Insecure-Requests: 1',
            'Cache-Control: no-cache',
            'Pragma: no-cache',
        ];
        if (!empty($referer)) {
            curl_setopt($ch, CURLOPT_REFERER, $referer);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $data = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!empty($data) && strlen($data) > 100 && $http_code < 400) {
            return $data;
        }

        if ($attempt < $max_retries) {
            usleep(300000); // 0.3s kutish va boshqa UA bilan qayta urinish
            $ua = $user_agents[array_rand($user_agents)];
        }
    }

    return $data ?? '';
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
 * Sarlavhani (Title) donor spam so'zlaridan tozalash va toza SEO formatga keltirish
 */
function parser_clean_seo_title($title) {
    if (empty($title)) return '';
    $title = trim(html_entity_decode((string)$title, ENT_QUOTES, 'UTF-8'));
    
    // Donor saytlarining keraksiz reklama qo'shimchalarini tozalash
    $spam_patterns = [
        '/смотреть онлайн(?: бесплатно)?/iu',
        '/в (?:отличном|хорошем|hd|full hd|высоком) качестве/iu',
        '/порно видео(?: онлайн)?/iu',
        '/скачать (?:бесплатно|на телефон)?/iu',
        '/\b(?:sexlar\.link|uzbxx\.ru|uzporno\.website|arhivporno\.watch|uzporno\.ru|vaginke\.me)\b/iu',
        '/\[.*?\]/u', // Qavs ichidagi [1080p] kabilarni olib tashlash
        '/\(.*?(?:1080|720|4k|hd|онлайн).*?\)/iu'
    ];
    $title = preg_replace($spam_patterns, '', $title);
    $title = preg_replace('/[\r\n\t]+/', ' ', $title);
    $title = preg_replace('/\s{2,}/', ' ', $title);
    $title = trim($title, " \t\n\r\0\x0B-_–—:;|");
    
    if (empty($title)) {
        return 'O‘zbekcha yangi seks video';
    }
    
    // Birinchi harfni katta qilish
    return mb_strtoupper(mb_substr($title, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($title, 1, null, 'UTF-8');
}

/**
 * Toza va samarali SEO tavsif (Meta Description) tayyorlash
 */
function parser_generate_seo_description($title, $raw_desc, $category_name = '') {
    $desc = trim(strip_tags((string)$raw_desc));
    $desc = preg_replace('/https?:\/\/[^\s]+/i', '', $desc);
    $desc = preg_replace('/\b(?:sexlar\.link|uzbxx\.ru|uzporno\.website|arhivporno\.watch|uzporno\.ru|vaginke\.me|t\.me\/[^\s]+)\b/iu', '', $desc);
    $desc = preg_replace('/\s{2,}/', ' ', $desc);
    $desc = trim($desc);
    
    // Agar tavsif juda qisqa yoki bo'sh bo'lsa, avtomatik boyitilgan SEO tavsif yaratamiz
    if (mb_strlen($desc, 'UTF-8') < 30) {
        $cat_part = !empty($category_name) ? " Bo‘lim: {$category_name}." : "";
        $desc = "Смотрите «{$title}» онлайн в хорошем качестве на sekschi.online. Eng sara o‘zbekcha seks va erotik videolar bepul hamda ro‘yxatdan o‘tmasdan tomosha qiling.{$cat_part}";
    }
    
    return mb_substr($desc, 0, 350, 'UTF-8');
}

/**
 * Yuqori qidiruv trafigi uchun boyitilgan SEO teglar (Keywords) tayyorlash
 */
function parser_generate_seo_tags($title, $donor_tags, $category_id, $desc, $mysqli) {
    $all_tags = [];
    
    // 1. Donordan kelgan teglarni qo'shish
    if (is_array($donor_tags)) {
        foreach ($donor_tags as $t) {
            $t = trim(strip_tags($t));
            if (!empty($t) && mb_strlen($t, 'UTF-8') >= 2) $all_tags[] = mb_strtolower($t, 'UTF-8');
        }
    } elseif (is_string($donor_tags) && !empty($donor_tags)) {
        $parts = preg_split('/[,;\s]+/u', $donor_tags);
        foreach ($parts as $t) {
            $t = trim($t);
            if (!empty($t) && mb_strlen($t, 'UTF-8') >= 2) $all_tags[] = mb_strtolower($t, 'UTF-8');
        }
    }
    
    // 2. Kategoriya kalit so'zlarini qo'shish
    if ($category_id > 0 && $mysqli instanceof mysqli) {
        $c_res = $mysqli->query("SELECT name, keywords, translit FROM ero_categories WHERE id = '$category_id' LIMIT 1");
        if ($c_res && $c_row = $c_res->fetch_assoc()) {
            $c_tags = explode(',', $c_row['keywords'] ?? '');
            foreach ($c_tags as $ct) {
                $ct = trim($ct);
                if (!empty($ct)) $all_tags[] = mb_strtolower($ct, 'UTF-8');
            }
            $all_tags[] = mb_strtolower($c_row['translit'], 'UTF-8');
        }
    }
    
    // 3. Sarlavhadan muhim so'zlarni ajratib olish
    $clean_title = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $title);
    $words = explode(' ', $clean_title);
    $stop_words = ['для', 'под', 'над', 'это', 'как', 'все', 'он', 'она', 'они', 'его', 'ее', 'их', 'при', 'после', 'или', 'что', 'года', 'video', 'watch', 'online'];
    foreach ($words as $w) {
        $w = mb_strtolower(trim($w), 'UTF-8');
        if (mb_strlen($w, 'UTF-8') >= 3 && !in_array($w, $stop_words)) {
            $all_tags[] = $w;
        }
    }
    
    // 4. Doimiy eng yuqori qidiruvdagi o'zbek adult teglari
    $core_uzbek_tags = ['узбек секс', 'uzbekcha seks', 'uzbek sex', 'uzb porno', 'узбечка', 'секс видео', 'порно онлайн', 'hd porno'];
    foreach ($core_uzbek_tags as $cut) {
        $all_tags[] = $cut;
    }
    
    // Takroriylarni tozalash va tartiblash
    $unique_tags = [];
    foreach ($all_tags as $t) {
        $t = trim($t);
        if (!empty($t) && !in_array($t, $unique_tags) && strlen($t) < 50) {
            $unique_tags[] = $t;
        }
        if (count($unique_tags) >= 25) break;
    }
    
    return implode(', ', $unique_tags);
}

/**
 * Intellektual kategoriya aniqlash (sekschi.online ning 27 ta toifasi bo'yicha)
 * Donor toifasi, URL, sarlavha va teglarni chuqur tahlil qiladi
 */
function parser_smart_category($title, $tags_str, $donor, $mysqli, $context = []) {
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

    $find_id = function($translit) use ($cats) {
        foreach ($cats as $c) {
            if ($c['translit'] === $translit) {
                return intval($c['id']);
            }
        }
        return null;
    };

    // 1. Agar contextda donor kategoriyasi yoki maxsus ko'rsatma bo'lsa
    $hint = mb_strtolower(trim(($context['category_hint'] ?? '') . ' ' . ($context['donor_category'] ?? '') . ' ' . ($context['catalog_url'] ?? '')), 'UTF-8');
    if (!empty($hint)) {
        $hint_map = [
            'minet'           => ['minet', 'cat-minet', 'cat-glubokii-minet', 'cat-konchaut-v-rot', 'oral', 'otsos', 'в рот', 'минет'],
            'anal'            => ['anal', 'cat-anal', 'cat-anal-porno', 'cat-russkii-anal', 'cat-zhestkii-anal', 'analnyy-seks', 'cat-v-popu', 'cat-v-zhopu', 'анал', 'анальный'],
            'rakom'           => ['rakom', 'cat-rakom', 'cat-porno-rakom', 'cat-szadi', 'раком', 'doggy'],
            'domashnee'       => ['domashnee', 'cat-domashnee', 'cat-domashnee-porno', 'cat-domashnii-incest', 'cat-porno-s-zhenoi', 'cat-lyubitelskoe', 'proverennoe-lyubitelskoe', 'lyubitelskoe', 'домашнее', 'любительское'],
            'studenty'        => ['studenty', 'cat-studenty', 'cat-porno-studentov', 'cat-studentki', 'студенты', 'студентки'],
            'siski'           => ['siski', 'cat-bolshie-siski', 'cat-uprugie-siski', 'bolshaya-grud', 'cat-grudastye', 'большие сиськи', 'сиськи'],
            'big'             => ['big', 'cat-bolshie-chleny', 'cat-bolshoi-chlen', 'большие члены', 'большой член'],
            'mamki'           => ['mamki', 'cat-mamki', 'cat-porno-zrelih', 'cat-porno-milf', 'cat-porno-s-mamoi', 'mamochki', 'cat-milfy', 'мамки', 'милфы', 'мамочки'],
            'molodye'         => ['molodye', 'cat-molodye', 'cat-porno-molodih', 'cat-anal-s-molodimi', 'cat-starie-s-molodimi', '18-letnie', 'podrostki', 'cat-yunye', 'молодые', 'юные'],
            'sperma'          => ['sperma', 'cat-sperma', 'cat-konchaut-na-lico', 'сперма', 'залпы'],
            'gruppovoe'       => ['gruppovoe', 'cat-gruppovoe', 'cat-gruppovoe-porno', 'cat-seks-vtroem', 'cat-troinichok', 'групповое', 'тройничок'],
            'kunnilingus'     => ['kunnilingus', 'cat-kunnilingus', 'cat-lizhet-pizdu', 'куннилингус', 'кунни'],
            'lishenie_celki'  => ['lishenie_celki', 'cat-lishenie-celki', 'лишение целки', 'целка'],
            'pyanye'          => ['pyanye', 'cat-pyanye', 'cat-seks-so-spyaschimi', 'пьяные'],
            'beremennye'      => ['beremennye', 'cat-beremennye', 'беременные'],
            'volosatye'       => ['volosatye', 'cat-volosatye', 'cat-volosataya-pizda', 'волосатые'],
            'bdsm'            => ['bdsm', 'cat-bdsm', 'бдсм'],
            'zhestkoe'        => ['zhestkoe', 'cat-zhestkoe', 'cat-zhestkoe-porno', 'grubyy-seks', 'cat-iznasilovaniya', 'cat-jestokoe', 'жесткое', 'жестокое'],
            'negry'           => ['negry', 'cat-negry', 'cat-zhena-s-negrom', 'cat-bolshoi-chernii-chlen', 'негры'],
            'blonde'          => ['blonde', 'cat-blondinki', 'cat-porno-s-blondinkami', 'блондинки'],
            'bryunetki'       => ['bryunetki', 'cat-bryunetki', 'cat-porno-s-brunetkami', 'брюнетки'],
            'lesbiyanki'      => ['lesbiyanki', 'cat-lesbiyanki', 'лесбиянки'],
            'asian'           => ['asian', 'cat-aziatki', 'cat-kitaiskoe-porno', 'азиатки', 'азия'],
            'russkoe'         => ['russkoe', 'cat-russkoe', 'cat-russkoe-porno', 'cat-russkii-incest', 'русское'],
            'anime-hentai'    => ['anime-hentai', 'cat-anime', 'cat-ai-porno', 'аниме', 'хентай'],
            'uzbek'           => ['cat-uzbekskii-seks', 'uzbekskoe', 'uzbek', 'узбекский', 'узбекское']
        ];
        foreach ($hint_map as $translit => $tokens) {
            foreach ($tokens as $tk) {
                if (strpos($hint, $tk) !== false) {
                    $cid = $find_id($translit);
                    if ($cid) return $cid;
                }
            }
        }
    }

    // 2. Sarlavha, teglar va tavsif bo'yicha semantik tahlil (O'ziga xos harakatlar ustuvor)
    $text = mb_strtolower($title . ' ' . $tags_str . ' ' . ($context['description'] ?? ''), 'UTF-8');

    $priority_rules = [
        '/(?:минет|отсос|сосет|сосёт|в рот|глубокий минет|членосос|oral|rotga|ogizga|og\'ziga|blowjob|suck|amur)/iu' => 'minet',
        '/(?:анал|anal|в жопу|в попу|в задниц|в очко|анальн|tor amga|ketiga|anali|orqasiga|orqaga|ass|analnoe)/iu' => 'anal',
        '/(?:раком|догги|doggy|doggystyle|сзади|рачком|поза раком|rakom|orqasidan|orqadan|tizzalab)/iu' => 'rakom',
        '/(?:кунни|лизать пис|лижет|куннилинг|am yalash|cunnilingus|klitor|til bilan)/iu' => 'kunnilingus',
        '/(?:лишение целки|девствен|первый раз|целк|qizlik|bokiralik|birinchi marta)/iu' => 'lishenie_celki',
        '/(?:пьян|буха|под градусом|набухал|mast holda|alkogol|mast)/iu' => 'pyanye',
        '/(?:беремен|с пузом|с животом|pregnant|homilador)/iu' => 'beremennye',
        '/(?:хентай|аниме|hentai|anime|3d hentai)/iu' => 'anime-hentai',
        '/(?:студент|студентк|общаг|сесси|вписк|talaba|talabalar|yotoqxona)/iu' => 'studenty',
        '/(?:сперм|конча|залп|кончил|cumshot|yuziga sperma|oqizish|bukkake)/iu' => 'sperma',
        '/(?:домашн|частн|любительск|home|скрытая камера|слив|samopal|uyda|er-xotin|kelin|kelinchak|xotin)/iu' => 'domashnee',
        '/(?:группов|тройничок|втроем|втроём|мжм|жмж|оргия|threesome|guruhli)/iu' => 'gruppovoe',
        '/(?:большие сиськи|сиськ|грудаст|дойки|tits|big tits|огромные сиськи|katta emchak|emish|emchaklar)/iu' => 'siski',
        '/(?:большие члены|большой член|огромный хуй|big cock|толстый член|katta olat|ulkan asbob|katta quroq)/iu' => 'big',
        '/(?:бдсм|bdsm|госпож|рабын|порка|плеть|подчинение)/iu' => 'bdsm',
        '/(?:жесток|жестк|груб|hardcore|разрыв дырки|qopol|shafqatsiz)/iu' => 'zhestkoe',
        '/(?:лесби|девушки целуются|lesbian|qizlar qizlar|ikki qiz)/iu' => 'lesbiyanki',
        '/(?:мамк|мамашк|милф|milf|зрел|мачех|xola|katta xotin)/iu' => 'mamki',
        '/(?:молод|юная|малолет|teen|18 лет|yosh qiz|yoshlik|maktab)/iu' => 'molodye',
        '/(?:волосат|небрит|пушист|мохнат|hairy|tukli)/iu' => 'volosatye',
        '/(?:негр|чернокож|bbc|qoratanli)/iu' => 'negry',
        '/(?:блондин|blonde|светловолосая|sariq sochli)/iu' => 'blonde',
        '/(?:брюнет|bryunet|темноволосая|qora sochli)/iu' => 'bryunetki',
        '/(?:азиат|asian|кореян|японк|китаянк|osiyo)/iu' => 'asian',
        '/(?:русск|отечествен|ruscha|rossiya)/iu' => 'russkoe',
        '/(?:узбек|uzbek|узбечк|uzbechka|uzbekcha|toshkent|samarqand|andijon|fargona|namangan|buxoro|xorazm|vodiy|uzb)/iu' => 'uzbek',
    ];

    foreach ($priority_rules as $pattern => $translit) {
        if (preg_match($pattern, $text)) {
            $cid = $find_id($translit);
            if ($cid) return $cid;
        }
    }

    // 3. Default: donorlar asosan o'zbek pornosi bo'lgani sababli 'uzbek' toifasiga yo'naltirish
    $default_uzbek = $find_id('uzbek');
    if ($default_uzbek) {
        return $default_uzbek;
    }

    // Agar uzbek toifasi bo'lmasa, eng mos keladigan umumiy toifa - 'domashnee'
    $default_domashnee = $find_id('domashnee');
    if ($default_domashnee) {
        return $default_domashnee;
    }

    return !empty($cats[0]['id']) ? intval($cats[0]['id']) : 1;
}

/**
 * 1. uzbxx.ru dan videoni parslash
 */
function parse_video_uzbxx($video_url, $manual_cat, $save_mode, $mysqli, $settings, $width_S, $height_S, $cat_context = []) {
    $video_url = trim($video_url);
    $html = parser_fetch($video_url, 'https://uzbxx.ru/');
    if (empty($html) || strlen($html) < 200) {
        return ['status' => 'error', 'message' => "Sahifani yuklab bo‘lmadi: $video_url"];
    }

    // Title olish va SEO tozalash
    $raw_title = '';
    if (preg_match('|<meta property="og:title" content="(.*?)"|is', $html, $m)) {
        $raw_title = trim($m[1]);
    } elseif (preg_match('|<div class="xxxhd-title-top">.*?<h1>(.*?)</h1>|is', $html, $m)) {
        $raw_title = trim(strip_tags($m[1]));
    } elseif (preg_match('|<title>(.*?)</title>|is', $html, $m)) {
        $raw_title = trim(strip_tags($m[1]));
    }
    $title = parser_clean_seo_title($raw_title);
    if (empty($title) || stripos($title, 'can’t be reached') !== false) {
        return ['status' => 'error', 'message' => "Video nomini aniqlab bo‘lmadi ($video_url)"];
    }

    // Takrorlanmaslikni tekshirish
    $uniqueness = md5($title);
    $check_exist = $mysqli->query("SELECT id FROM ero_files WHERE uniqueness = '$uniqueness' LIMIT 1");
    if ($check_exist && $check_exist->num_rows > 0) {
        return ['status' => 'skip', 'message' => "Allaqachon mavjud: <b>$title</b>", 'title' => $title];
    }

    // Davomiylik
    $duration = '05:00';
    if (preg_match('|<meta property="og:duration" content="(\d+)"|is', $html, $m)) {
        $duration = parser_iso_duration($m[1]);
    } elseif (preg_match('|<i class="fa fa-clock-o"></i>\s*<b>(.*?)</b>|is', $html, $m)) {
        $duration = parser_iso_duration($m[1]);
    }

    // Poster
    $poster_url = $cat_context['poster'] ?? '';
    if (empty($poster_url)) {
        if (preg_match('|<meta property="og:image" content="(.*?)"|is', $html, $m)) {
            $poster_url = trim($m[1]);
        } elseif (preg_match('|poster:\s*"([^"]+)"|is', $html, $m)) {
            $poster_url = trim($m[1]);
        }
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
    $raw_desc = '';
    if (preg_match('|<meta property="og:description" content="(.*?)"|is', $html, $m)) {
        $raw_desc = trim(html_entity_decode($m[1], ENT_QUOTES, 'UTF-8'));
    }

    // Teglar
    $tags_arr = [];
    if (preg_match_all('|<meta property="video:tag" content="(.*?)"|is', $html, $m)) {
        $tags_arr = array_map('trim', $m[1]);
    } elseif (preg_match_all('|<a href="https://uzbxx\.ru/tags/[^"]+"><i class="fa fa-tags"></i>\s*(.*?)</a>|is', $html, $m)) {
        $tags_arr = array_map('trim', $m[1]);
    }
    if (preg_match('|<meta\s+name=["\']keywords["\']\s+content=["\']([^"\']+)["\']|is', $html, $m_kw)) {
        $kw_items = explode(',', $m_kw[1]);
        foreach ($kw_items as $kwi) {
            $kwi = trim($kwi);
            if (!empty($kwi)) $tags_arr[] = $kwi;
        }
    }
    // Kategoriyalardan ham teglarni olamiz
    if (preg_match_all('|<a href="https://uzbxx\.ru/category/[^"]+">([^<]+)</a>|is', $html, $m_c)) {
        foreach ($m_c[1] as $cname) {
            $tags_arr[] = trim($cname);
        }
    }

    // Kategoriya aniqlash
    $category_id = intval($manual_cat);
    if ($category_id === 0) {
        $context = array_merge($cat_context, [
            'donor_category' => implode(' ', $tags_arr),
            'description'    => $raw_desc
        ]);
        $category_id = parser_smart_category($title, implode(' ', $tags_arr), 'uzbxx.ru', $mysqli, $context);
    }

    // Kategoriya nomini olish
    $cat_name = 'Umumiy';
    $cat_q = $mysqli->query("SELECT name FROM ero_categories WHERE id = '$category_id' LIMIT 1");
    if ($cat_q && $crow = $cat_q->fetch_assoc()) {
        $cat_name = $crow['name'];
    }

    // Yakuniy SEO tavsif va teglar
    $desc = parser_generate_seo_description($title, $raw_desc, $cat_name);
    $tags_str = parser_generate_seo_tags($title, $tags_arr, $category_id, $desc, $mysqli);

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

    // MP4 Yuklab olish rejimi (server yoki download)
    if (($save_mode === 'server' || $save_mode === 'download') && !empty($video_src)) {
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
        return [
            'status'        => 'success',
            'message'       => "Muvaffaqiyatli qo‘shildi: <a href='/watch/{$translit}.html' target='_blank' style='color:#ff9900;'><b>$title</b></a> <span style='color:#17a2b8; font-size:12px;'>[{$cat_name}]</span>",
            'title'         => $title,
            'translit'      => $translit,
            'duration'      => $duration,
            'category_id'   => $category_id,
            'category_name' => $cat_name,
            'screenshot'    => $final_screenshot
        ];
    } else {
        return ['status' => 'error', 'message' => "Bazaga yozishda xatolik: " . $mysqli->error];
    }
}

/**
 * 2. uzporno.website dan videoni parslash
 */
function parse_video_uzporno($video_url, $manual_cat, $save_mode, $mysqli, $settings, $width_S, $height_S, $cat_context = []) {
    $video_url = trim($video_url);
    $html = parser_fetch($video_url, 'https://uzporno.website/');
    if (empty($html) || strlen($html) < 200) {
        return ['status' => 'error', 'message' => "Sahifani yuklab bo‘lmadi: $video_url"];
    }

    $raw_title = '';
    $raw_desc = '';
    $poster_url = $cat_context['poster'] ?? '';
    $embed_url = '';
    $duration = $cat_context['duration'] ?? '05:00';
    $tags_arr = [];

    // JSON-LD orqali olish
    if (preg_match('|<script type=[\"\\\x27]application/ld\+json[\"\\\x27]>(.*?)</script>|is', $html, $m_json)) {
        $json_data = json_decode($m_json[1], true);
        if ($json_data) {
            $raw_title = $json_data['name'] ?? '';
            $raw_desc = $json_data['description'] ?? '';
            if (empty($poster_url)) {
                $poster_url = is_array($json_data['thumbnailUrl'] ?? '') ? end($json_data['thumbnailUrl']) : ($json_data['thumbnailUrl'] ?? '');
            }
            $embed_url = $json_data['embedUrl'] ?? '';
            if (!empty($json_data['duration'])) {
                $duration = parser_iso_duration($json_data['duration']);
            }
            if (!empty($json_data['keywords'])) {
                $tags_arr = array_merge($tags_arr, explode(',', $json_data['keywords']));
            }
        }
    }

    if (empty($raw_title)) {
        if (preg_match('|<meta property="og:title" content="(.*?)"|is', $html, $m)) {
            $raw_title = trim($m[1]);
        } elseif (preg_match('|<div class="xxxhd-title-top">.*?<h1>(.*?)</h1>|is', $html, $m)) {
            $raw_title = trim(strip_tags($m[1]));
        }
    }

    $title = parser_clean_seo_title($raw_title);
    if (empty($title)) {
        return ['status' => 'error', 'message' => "Video nomini aniqlab bo‘lmadi ($video_url)"];
    }

    $uniqueness = md5($title);
    $check_exist = $mysqli->query("SELECT id FROM ero_files WHERE uniqueness = '$uniqueness' LIMIT 1");
    if ($check_exist && $check_exist->num_rows > 0) {
        return ['status' => 'skip', 'message' => "Allaqachon mavjud: <b>$title</b>", 'title' => $title];
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

    // Meta teglar
    if (preg_match('|<meta\s+name=["\']keywords["\']\s+content=["\']([^"\']+)["\']|is', $html, $m_kw)) {
        $tags_arr = array_merge($tags_arr, explode(',', $m_kw[1]));
    }
    if (empty($raw_desc) && preg_match('|<meta\s+name=["\']description["\']\s+content=["\']([^"\']+)["\']|is', $html, $m_d)) {
        $raw_desc = $m_d[1];
    }

    // Kategoriya aniqlash
    $category_id = intval($manual_cat);
    if ($category_id === 0) {
        $context = array_merge($cat_context, [
            'donor_category' => implode(' ', $tags_arr),
            'description'    => $raw_desc
        ]);
        $category_id = parser_smart_category($title, implode(' ', $tags_arr), 'uzporno.website', $mysqli, $context);
    }

    $cat_name = 'Umumiy';
    $cat_q = $mysqli->query("SELECT name FROM ero_categories WHERE id = '$category_id' LIMIT 1");
    if ($cat_q && $crow = $cat_q->fetch_assoc()) {
        $cat_name = $crow['name'];
    }

    $desc = parser_generate_seo_description($title, $raw_desc, $cat_name);
    $tags_str = parser_generate_seo_tags($title, $tags_arr, $category_id, $desc, $mysqli);

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

    // Serverga MP4 yuklash rejimi (server yoki download)
    if (($save_mode === 'server' || $save_mode === 'download') && !empty($play_url)) {
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
        return [
            'status'        => 'success',
            'message'       => "Muvaffaqiyatli qo‘shildi: <a href='/watch/{$translit}.html' target='_blank' style='color:#ff9900;'><b>$title</b></a> <span style='color:#17a2b8; font-size:12px;'>[{$cat_name}]</span>",
            'title'         => $title,
            'translit'      => $translit,
            'duration'      => $duration,
            'category_id'   => $category_id,
            'category_name' => $cat_name,
            'screenshot'    => $final_screenshot
        ];
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
    $raw_title = '';
    if (preg_match('|<div class="full-column">.*?<h1>(.*?)</h1>|is', $html, $m)) {
        $raw_title = trim(strip_tags($m[1]));
    } elseif (preg_match('|<meta property="og:title" content="(.*?)"|is', $html, $m)) {
        $raw_title = trim(html_entity_decode($m[1], ENT_QUOTES, 'UTF-8'));
    } elseif (preg_match('|<title>(.*?)</title>|is', $html, $m)) {
        $raw_title = trim(strip_tags($m[1]));
    }
    $title = parser_clean_seo_title($raw_title);
    if (empty($title)) {
        return ['status' => 'error', 'message' => "Video nomini aniqlab bo‘lmadi ($video_url)"];
    }

    $uniqueness = md5($title);
    $check_exist = $mysqli->query("SELECT id FROM ero_files WHERE uniqueness = '$uniqueness' LIMIT 1");
    if ($check_exist && $check_exist->num_rows > 0) {
        return ['status' => 'skip', 'message' => "Allaqachon mavjud: <b>$title</b>", 'title' => $title];
    }

    // Embed URL
    $embed_url = '';
    if (preg_match('|<div class="full-video">.*?<iframe[^>]+src="([^"]+)"|is', $html, $m)) {
        $embed_url = trim($m[1]);
    }

    // Poster
    $poster_url = $cat_context['poster'] ?? '';
    if (empty($poster_url)) {
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
    $raw_desc = '';
    if (preg_match('|<p class="video-text">(.*?)</p>|is', $html, $m)) {
        $raw_desc = trim(strip_tags($m[1]));
    }

    // Teglar va toifalar
    $tags_arr = [];
    if (preg_match_all('#<a[^>]+href="https://arhivporno\.watch/cat-[^"]+"[^>]*>(.*?)</a>#is', $html, $m_cat)) {
        foreach ($m_cat[1] as $ct) {
            $clean_ct = trim(strip_tags($ct));
            if (!empty($clean_ct) && mb_strlen($clean_ct, 'UTF-8') < 35 && stripos($clean_ct, 'видео') === false) {
                $tags_arr[] = $clean_ct;
            }
        }
    }
    if (preg_match_all('|<div class="full-meta tags-links">.*?<a[^>]+title="([^"]+)"|is', $html, $m_t)) {
        foreach ($m_t[1] as $t_tag) {
            $t_clean = trim(preg_replace('/-.*$/', '', $t_tag));
            if (!empty($t_clean)) $tags_arr[] = $t_clean;
        }
    }

    // Kategoriya
    $category_id = intval($manual_cat);
    if ($category_id === 0) {
        $context = array_merge($cat_context, [
            'donor_category' => implode(' ', $tags_arr),
            'description'    => $raw_desc
        ]);
        $category_id = parser_smart_category($title, implode(' ', $tags_arr), 'arhivporno.watch', $mysqli, $context);
    }

    $cat_name = 'Umumiy';
    $cat_q = $mysqli->query("SELECT name FROM ero_categories WHERE id = '$category_id' LIMIT 1");
    if ($cat_q && $crow = $cat_q->fetch_assoc()) {
        $cat_name = $crow['name'];
    }

    $desc = parser_generate_seo_description($title, $raw_desc, $cat_name);
    $tags_str = parser_generate_seo_tags($title, $tags_arr, $category_id, $desc, $mysqli);

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
        return [
            'status'        => 'success',
            'message'       => "Muvaffaqiyatli qo‘shildi: <a href='/watch/{$translit}.html' target='_blank' style='color:#ff9900;'><b>$title</b></a> <span style='color:#17a2b8; font-size:12px;'>[{$cat_name}]</span>",
            'title'         => $title,
            'translit'      => $translit,
            'duration'      => $duration,
            'category_id'   => $category_id,
            'category_name' => $cat_name,
            'screenshot'    => $final_screenshot
        ];
    } else {
        return ['status' => 'error', 'message' => "Bazaga yozishda xatolik: " . $mysqli->error];
    }
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
    $raw_title = '';
    if (preg_match('|<div class="headline">.*?<h1>(.*?)</h1>|is', $html, $m)) {
        $raw_title = trim(strip_tags($m[1]));
    } elseif (preg_match('|<meta property="og:title" content="(.*?)"|is', $html, $m)) {
        $raw_title = trim(html_entity_decode($m[1], ENT_QUOTES, 'UTF-8'));
    } elseif (preg_match('|<title>(.*?)</title>|is', $html, $m)) {
        $raw_title = trim(strip_tags($m[1]));
    }
    $title = parser_clean_seo_title($raw_title);
    if (empty($title)) {
        return ['status' => 'error', 'message' => "Video nomini aniqlab bo‘lmadi ($video_url)"];
    }

    // Takroriylikni tekshirish
    $uniqueness = md5($title);
    $check_exist = $mysqli->query("SELECT id FROM ero_files WHERE uniqueness = '$uniqueness' LIMIT 1");
    if ($check_exist && $check_exist->num_rows > 0) {
        return ['status' => 'skip', 'message' => "Allaqachon mavjud: <b>$title</b>", 'title' => $title];
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
    $raw_desc = '';
    if (preg_match('|<meta\s+name=["\']description["\']\s+content=["\']([^"\']+)["\']|is', $html, $m_meta_d)) {
        $raw_desc = trim($m_meta_d[1]);
    } elseif (preg_match('|<div class="item">([^<]+(?:<a[^>]*>[^<]+</a>[^<]*)*)</div>|is', $html, $m_desc)) {
        $raw_desc = trim(strip_tags($m_desc[1]));
    }

    // Teglarni yig'ish
    $tags_arr = ['узбек секс', 'uzbekcha seks', 'узбечка'];
    if (preg_match_all('#<a[^>]+href="(/tags/[^"]+|/categories/[^"]+)"[^>]*>(.*?)</a>#is', $html, $m_tg)) {
        foreach ($m_tg[2] as $tname) $tags_arr[] = trim(strip_tags($tname));
    }

    // 6. Kategoriya aniqlash
    $category_id = intval($manual_cat);
    if ($category_id === 0) {
        $context = array_merge($cat_context, [
            'donor_category' => implode(' ', $tags_arr),
            'description'    => $raw_desc
        ]);
        $category_id = parser_smart_category($title, implode(' ', $tags_arr), 'sexlar.link', $mysqli, $context);
    }

    $cat_name = 'Umumiy';
    $cat_q = $mysqli->query("SELECT name FROM ero_categories WHERE id = '$category_id' LIMIT 1");
    if ($cat_q && $crow = $cat_q->fetch_assoc()) {
        $cat_name = $crow['name'];
    }

    $desc = parser_generate_seo_description($title, $raw_desc, $cat_name);
    $tags_str = parser_generate_seo_tags($title, $tags_arr, $category_id, $desc, $mysqli);

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

    if (($save_mode === 'server' || $save_mode === 'download') && !empty($video_file_url)) {
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
        return [
            'status'        => 'success',
            'message'       => "Muvaffaqiyatli qo‘shildi: <a href='/watch/{$translit}.html' target='_blank' style='color:#ff9900;'><b>$title</b></a> <span style='color:#17a2b8; font-size:12px;'>[{$cat_name}]</span>",
            'title'         => $title,
            'translit'      => $translit,
            'duration'      => $duration,
            'category_id'   => $category_id,
            'category_name' => $cat_name,
            'screenshot'    => $final_screenshot
        ];
    } else {
        return ['status' => 'error', 'message' => "Bazaga yozishda xatolik: " . $mysqli->error];
    }
}

/**
 * Katalog havolalarini olish: uzbxx.ru
 */
function parser_get_catalog_links_uzbxx($page = 1, $custom_url = '') {
    $page = max(1, abs(intval($page)));
    if (!empty($custom_url)) {
        if (strpos($custom_url, '{PAGE}') !== false || strpos($custom_url, '{page}') !== false) {
            $url = str_ireplace('{page}', $page, $custom_url);
        } else {
            $base = rtrim($custom_url, '/');
            $url = ($page == 1) ? $base . '/' : $base . '/page=' . $page;
        }
    } else {
        $url = ($page == 1) ? 'https://uzbxx.ru/' : "https://uzbxx.ru/page={$page}";
    }

    $html = parser_fetch($url);
    if (empty($html)) return [];

    $items = [];
    preg_match_all('#<a[^>]+href="(https://uzbxx\.ru/video/[^"]+)"[^>]*>.*?<img[^>]+(?:data-src|src)="([^"]+)"[^>]*alt="([^"]*)"#is', $html, $m);
    if (!empty($m[1])) {
        foreach ($m[1] as $idx => $link) {
            $items[$link] = [
                'url'           => $link,
                'title'         => trim(html_entity_decode($m[3][$idx] ?? '', ENT_QUOTES, 'UTF-8')),
                'poster'        => $m[2][$idx] ?? '',
                'duration'      => '05:00',
                'category_hint' => $url
            ];
        }
    } else {
        preg_match_all('|<a href="(https://uzbxx\.ru/video/[^"]+)"|i', $html, $m2);
        if (!empty($m2[1])) {
            foreach (array_unique($m2[1]) as $link) {
                $items[$link] = [
                    'url'           => $link,
                    'title'         => '',
                    'poster'        => '',
                    'duration'      => '05:00',
                    'category_hint' => $url
                ];
            }
        }
    }
    return array_values($items);
}

/**
 * Katalog havolalarini olish: uzporno.website
 */
function parser_get_catalog_links_uzporno($page = 1, $custom_url = '') {
    $page = max(1, abs(intval($page)));
    if (!empty($custom_url)) {
        if (strpos($custom_url, '{PAGE}') !== false || strpos($custom_url, '{page}') !== false) {
            $url = str_ireplace('{page}', $page, $custom_url);
        } else {
            $base = rtrim($custom_url, '/');
            $url = ($page == 1) ? $base . '/' : $base . '/' . $page;
        }
    } else {
        $url = ($page == 1) ? 'https://uzporno.website/' : "https://uzporno.website/{$page}";
    }

    $html = parser_fetch($url);
    if (empty($html)) return [];

    $items = [];
    preg_match_all('#<a[^>]+href="(https://uzporno\.website/video/[^"]+)"[^>]*>.*?<img[^>]+(?:data-original|src)="([^"]+)"[^>]*alt="([^"]*)"#is', $html, $m);
    if (!empty($m[1])) {
        foreach ($m[1] as $idx => $link) {
            $items[$link] = [
                'url'           => $link,
                'title'         => trim(html_entity_decode($m[3][$idx] ?? '', ENT_QUOTES, 'UTF-8')),
                'poster'        => $m[2][$idx] ?? '',
                'duration'      => '05:00',
                'category_hint' => $url
            ];
        }
    } else {
        preg_match_all('|<a href="(https://uzporno\.website/video/[^"]+)"|i', $html, $m2);
        if (!empty($m2[1])) {
            foreach (array_unique($m2[1]) as $link) {
                $items[$link] = [
                    'url'           => $link,
                    'title'         => '',
                    'poster'        => '',
                    'duration'      => '05:00',
                    'category_hint' => $url
                ];
            }
        }
    }
    return array_values($items);
}

/**
 * Katalog havolalarini olish: arhivporno.watch
 */
function parser_get_catalog_links_arhivporno($page_or_url = 1, $custom_url = '') {
    $page = 1;
    $url = '';

    if (!empty($custom_url)) {
        $page = max(1, abs(intval($page_or_url)));
        if (strpos($custom_url, '{PAGE}') !== false || strpos($custom_url, '{page}') !== false) {
            $url = str_ireplace('{page}', $page, $custom_url);
        } else {
            $base = rtrim($custom_url, '/');
            $url = ($page == 1) ? $base . '/' : $base . '/' . $page . '/';
        }
    } elseif (is_numeric($page_or_url)) {
        $page = max(1, abs(intval($page_or_url)));
        $url = ($page == 1) ? 'https://arhivporno.watch/cat-uzbekskii-seks/' : "https://arhivporno.watch/cat-uzbekskii-seks/{$page}/";
    } else {
        $url = trim($page_or_url);
    }

    $html = parser_fetch($url, 'https://arhivporno.watch/');
    if (empty($html) && empty($custom_url) && $page > 2) {
        // Agar o'zbek bo'limi tugagan bo'lsa (2 sahifadan so'ng), asosiy yangi videolar oqimidan davom ettiramiz
        $url = "https://arhivporno.watch/{$page}/";
        $html = parser_fetch($url, 'https://arhivporno.watch/');
    }
    if (empty($html)) return [];

    $items = [];
    preg_match_all('#<a href="(https://arhivporno\.watch/[^"/]+/)"[^>]*class="traff".*?<img[^>]+(?:data-original|src)="([^"]+)".*?<span class="thumb-time">([0-9:]+)</span>(?:.*?<p>(.*?)</p>)?#is', $html, $m);

    if (!empty($m[1])) {
        foreach ($m[1] as $idx => $link) {
            $title_raw = trim(strip_tags($m[4][$idx] ?? ''));
            $items[$link] = [
                'url'           => $link,
                'title'         => $title_raw,
                'poster'        => $m[2][$idx] ?? '',
                'duration'      => $m[3][$idx] ?? '05:00',
                'category_hint' => $url
            ];
        }
    } else {
        preg_match_all('|<a href="(https://arhivporno\.watch/[^"/]+/)"[^>]*class="traff"|i', $html, $m2);
        if (!empty($m2[1])) {
            foreach (array_unique($m2[1]) as $link) {
                $items[$link] = [
                    'url'           => $link,
                    'title'         => '',
                    'poster'        => '',
                    'duration'      => '05:00',
                    'category_hint' => $url
                ];
            }
        }
    }

    return array_values($items);
}

/**
 * Katalog havolalarini olish: sexlar.link
 */
function parser_get_catalog_links_sexlar($page_or_url = 1, $custom_url = '') {
    $page = 1;
    $url = '';

    if (!empty($custom_url)) {
        $page = max(1, abs(intval($page_or_url)));
        if (strpos($custom_url, '{PAGE}') !== false || strpos($custom_url, '{page}') !== false) {
            $url = str_ireplace('{page}', $page, $custom_url);
        } else {
            $base = rtrim($custom_url, '/');
            $url = ($page == 1) ? $base . '/' : $base . '/' . $page . '/';
        }
    } elseif (is_numeric($page_or_url)) {
        $page = max(1, abs(intval($page_or_url)));
        $url = ($page == 1) ? 'https://sexlar.link/' : "https://sexlar.link/{$page}/";
    } else {
        $url = trim($page_or_url);
    }

    $html = parser_fetch($url, 'https://sexlar.link/');
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
                'url'           => $link,
                'title'         => trim(html_entity_decode($m[2][$idx] ?? '', ENT_QUOTES, 'UTF-8')),
                'poster'        => $img,
                'duration'      => trim($m[4][$idx] ?? '05:00'),
                'category_hint' => $url
            ];
        }
    } else {
        preg_match_all('|<a href="(/sekis/[^"]+/)"|i', $html, $m2);
        if (!empty($m2[1])) {
            foreach (array_unique($m2[1]) as $rel_link) {
                $link = 'https://sexlar.link' . $rel_link;
                $items[$link] = [
                    'url'           => $link,
                    'title'         => '',
                    'poster'        => '',
                    'duration'      => '05:00',
                    'category_hint' => $url
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

