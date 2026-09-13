<?php

/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/

session_start();
set_time_limit(0);
ob_start();

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? 'https://' : 'http://';
$version = 16.0;

#Размеры скриншотов
$width_S = 400;
$height_S = 500;

#Настройки доступа к MySQL

$mysqli = new mysqli('localhost', 'sekschi', 'sekschi123', 'sekschi');

if ($mysqli -> connect_error) {
    die('Error : ('. $mysqli -> connect_errno .') '. $mysqli -> connect_error);
}

mysqli_set_charset($mysqli, 'utf8mb4');

# Автоматическая инициализация необходимых таблиц
@$mysqli->query("CREATE TABLE IF NOT EXISTS `ero_likes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_video` INT(11) NOT NULL,
  `ip` VARCHAR(45) NOT NULL,
  `type` ENUM('like','dislike') NOT NULL DEFAULT 'like',
  `date` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_video` (`id_video`),
  KEY `ip_video` (`id_video`, `ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

@$mysqli->query("CREATE TABLE IF NOT EXISTS `ero_comments` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_video` INT(11) NOT NULL,
  `author` VARCHAR(100) NOT NULL,
  `text` TEXT NOT NULL,
  `ip` VARCHAR(45) NOT NULL,
  `date` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_video` (`id_video`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$chk_comm_col = @$mysqli->query("SHOW COLUMNS FROM `ero_comments` LIKE 'id_file'");
if ($chk_comm_col && $chk_comm_col->num_rows > 0) {
    @$mysqli->query("ALTER TABLE `ero_comments` CHANGE `id_file` `id_video` INT(11) NOT NULL");
}
$chk_comm_txt = @$mysqli->query("SHOW COLUMNS FROM `ero_comments` LIKE 'comment'");
if ($chk_comm_txt && $chk_comm_txt->num_rows > 0) {
    @$mysqli->query("ALTER TABLE `ero_comments` CHANGE `comment` `text` TEXT NOT NULL");
}

$check_cols = @$mysqli->query("SHOW COLUMNS FROM `ero_files` LIKE 'likes'");
if ($check_cols && $check_cols->num_rows == 0) {
    @$mysqli->query("ALTER TABLE `ero_files` ADD `likes` INT(11) NOT NULL DEFAULT 0");
}
$check_dis = @$mysqli->query("SHOW COLUMNS FROM `ero_files` LIKE 'dislikes'");
if ($check_dis && $check_dis->num_rows == 0) {
    @$mysqli->query("ALTER TABLE `ero_files` ADD `dislikes` INT(11) NOT NULL DEFAULT 0");
}
$check_embed = @$mysqli->query("SHOW COLUMNS FROM `ero_files` LIKE 'embed'");
if ($check_embed && $check_embed->num_rows == 0) {
    @$mysqli->query("ALTER TABLE `ero_files` ADD `embed` TEXT NULL");
}

$check_on_page = @$mysqli->query("SHOW COLUMNS FROM `ero_online` LIKE 'page_url'");
if ($check_on_page && $check_on_page->num_rows == 0) {
    @$mysqli->query("ALTER TABLE `ero_online` ADD `page_url` VARCHAR(500) NULL DEFAULT '/'");
}
$check_on_ua = @$mysqli->query("SHOW COLUMNS FROM `ero_online` LIKE 'user_agent'");
if ($check_on_ua && $check_on_ua->num_rows == 0) {
    @$mysqli->query("ALTER TABLE `ero_online` ADD `user_agent` VARCHAR(255) NULL DEFAULT ''");
}
$check_on_seen = @$mysqli->query("SHOW COLUMNS FROM `ero_online` LIKE 'last_seen'");
if ($check_on_seen && $check_on_seen->num_rows == 0) {
    @$mysqli->query("ALTER TABLE `ero_online` ADD `last_seen` INT(11) NULL DEFAULT '0'");
}
$check_on_cc = @$mysqli->query("SHOW COLUMNS FROM `ero_online` LIKE 'country_code'");
if ($check_on_cc && $check_on_cc->num_rows == 0) {
    @$mysqli->query("ALTER TABLE `ero_online` ADD `country_code` VARCHAR(4) NULL DEFAULT 'UZ'");
}
$check_on_ref = @$mysqli->query("SHOW COLUMNS FROM `ero_online` LIKE 'referer'");
if ($check_on_ref && $check_on_ref->num_rows == 0) {
    @$mysqli->query("ALTER TABLE `ero_online` ADD `referer` VARCHAR(500) NULL DEFAULT ''");
}
$check_act_cc = @$mysqli->query("SHOW COLUMNS FROM `ero_activity` LIKE 'country_code'");
if ($check_act_cc && $check_act_cc->num_rows == 0) {
    @$mysqli->query("ALTER TABLE `ero_activity` ADD `country_code` VARCHAR(4) NULL DEFAULT 'UZ'");
}
$check_act_ref = @$mysqli->query("SHOW COLUMNS FROM `ero_activity` LIKE 'referer'");
if ($check_act_ref && $check_act_ref->num_rows == 0) {
    @$mysqli->query("ALTER TABLE `ero_activity` ADD `referer` VARCHAR(500) NULL DEFAULT ''");
}

@$mysqli->query("CREATE TABLE IF NOT EXISTS `ero_geoip_cache` (
  `ip` VARCHAR(45) NOT NULL,
  `country_code` VARCHAR(4) NOT NULL DEFAULT 'UZ',
  `country_name` VARCHAR(100) NOT NULL DEFAULT 'O‘zbekiston',
  `date` INT(11) NOT NULL,
  PRIMARY KEY (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

@$mysqli->query("CREATE TABLE IF NOT EXISTS `ero_dmca` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `video_url` TEXT NOT NULL,
  `message` TEXT NOT NULL,
  `date` INT(11) NOT NULL,
  `status` INT(11) NOT NULL DEFAULT '0',
  `ip` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

@$mysqli->query("CREATE TABLE IF NOT EXISTS `ero_activity` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `ip` VARCHAR(45) NOT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `action` ENUM('view', 'like', 'dislike', 'favorite', 'unfavorite', 'download', 'search', 'comment') NOT NULL,
  `id_file` INT(11) DEFAULT '0',
  `query_text` TEXT DEFAULT NULL,
  `date` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_action` (`action`),
  KEY `idx_id_file` (`id_file`),
  KEY `idx_date` (`date`),
  KEY `idx_ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

@$mysqli->query("CREATE TABLE IF NOT EXISTS `ero_advertising` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `site` TEXT NOT NULL,
  `name` TEXT NOT NULL,
  `colour` VARCHAR(32) DEFAULT '#ff9900',
  `term` INT(11) NOT NULL DEFAULT '0',
  `owner` VARCHAR(64) DEFAULT 'admin',
  `position` VARCHAR(32) DEFAULT 'all',
  `clicks` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_term` (`term`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

require_once __DIR__ . '/SeoEngine.php';
require_once __DIR__ . '/ads_helper.php';
require_once __DIR__ . '/GeoIpHelper.php';

function time_ago($time) {
    $diff = time() - $time;
    if ($diff < 60) return 'hozirgina';
    if ($diff < 3600) return floor($diff / 60) . ' daqiqa oldin';
    if ($diff < 86400) return floor($diff / 3600) . ' soat oldin';
    if ($diff < 2592000) return floor($diff / 86400) . ' kun oldin';
    return date('d.m.Y', $time);
}

// Qidiruv robotlari va crawlerlarni aniqlash
function is_crawler_or_bot() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (empty($user_agent)) return false;
    
    $bot_patterns = [
        'googlebot', 'yandex', 'bingbot', 'slurp', 'duckduckbot', 'baiduspider',
        'yeti', 'yodaobot', 'gigabot', 'ia_archiver', 'archive.org_bot',
        'ahrefsbot', 'semrushbot', 'mj12bot', 'dotbot', 'rogue',
        'petalbot', 'bytespider', 'zoominfobot', 'exabot', 'seokicks',
        'curl', 'python', 'urllib', 'wget', 'httpclient', 'postman',
        'telegrambot', 'whatsapp', 'facebookexternalhit', 'vkshare',
        'twitterbot', 'applebot', 'seznambot', 'screaming frog', 'lighthouse',
        'headlesschrome', 'chrome-lighthouse', 'headless', 'inspect'
    ];
    
    $pattern = '/' . implode('|', $bot_patterns) . '/i';
    return (bool)preg_match($pattern, $user_agent);
}

/**
 * Mehmonlar harakatini bazaga yozish (Statistika uchun)
 */
function track_activity($action, $id_file = 0, $query_text = null) {
    global $mysqli;
    if (is_crawler_or_bot()) return;
    $ip = filter($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
    $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 250);
    $action_safe = mysqli_real_escape_string($mysqli, $action);
    $id_safe = (int)$id_file;
    $text_safe = ($query_text !== null && $query_text !== '') ? "'".mysqli_real_escape_string($mysqli, $query_text)."'" : "NULL";
    $raw_ref = substr($_SERVER['HTTP_REFERER'] ?? '', 0, 490);
    $ref_safe = mysqli_real_escape_string($mysqli, filter($raw_ref));
    $time = time();
    $c_info = function_exists('get_ip_country_info') ? get_ip_country_info($ip) : ['code' => 'UZ', 'name' => 'O‘zbekiston'];
    $c_code = mysqli_real_escape_string($mysqli, $c_info['code']);
    @$mysqli->query("INSERT INTO `ero_activity` (`ip`, `user_agent`, `country_code`, `referer`, `action`, `id_file`, `query_text`, `date`) VALUES ('$ip', '".mysqli_real_escape_string($mysqli, $ua)."', '$c_code', '$ref_safe', '$action_safe', $id_safe, $text_safe, '$time')");
}

#Локализация

if (isset($_GET['lang']))   {
    
    $_SESSION['lang'] = filter($_GET['lang']);
    
    header('Refresh: 0; '.$_SERVER['PHP_SELF']);
    exit;
}

$sess_lang = $_SESSION['lang'] ?? 'ru';
if (file_exists(__DIR__ . '/languages/' . $sess_lang . '.php')) {
    include __DIR__ . '/languages/' . $sess_lang . '.php';
} else {
    include __DIR__ . '/languages/ru.php';
}

#Вверхняя часть сайта

function head($var = null, $image = null, $og_type = 'website') {

global $mysqli, $title, $description, $keywords, $protocol, $settings, $user, $lang;

$favorites = $mysqli -> query("select count(*) from ero_favorites where data = '".mysqli_real_escape_string($mysqli, filter($_SERVER['REMOTE_ADDR']))."'") -> fetch_row();
$visitors = $mysqli -> query("select count(*) from ero_online") -> fetch_row();

if ($user)  $view_control = '
<li><a href="/control.php"><i class="fa fa-cog"></i> '.$lang['control_panel'].'</a></li>';
else $view_control = null;

$css_file = $_SERVER['DOCUMENT_ROOT'].'/designs/'.$settings['designs'].'.css';
$css_v = file_exists($css_file) ? filemtime($css_file) : time();

$host = filter($_SERVER['HTTP_HOST'] ?? 'sekschi.online');
$canonical = $protocol . $host . parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

$og_img = (!empty($image) && $image != '/designs/water.png') 
    ? ((strpos($image, 'http') === 0) ? $image : $protocol . $host . $image) 
    : $protocol . $host . '/designs/no_poster.jpg';

$is_home = (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) === '/' || parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) === '/index.php');

echo '<!DOCTYPE html>
<html lang="ru">
  <head>
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="description" content="'.htmlspecialchars($description, ENT_QUOTES, 'UTF-8').'" />
<meta name="keywords" content="'.htmlspecialchars($keywords, ENT_QUOTES, 'UTF-8').'" />
<meta name="robots" content="index, follow, max-image-preview:large" />
<meta name="theme-color" content="#21201f" />
<meta name="rating" content="RTA-5042-1996-1404-4054-RTA" />
<meta name="RATING" content="adult" />

<meta property="og:site_name" content="'.htmlspecialchars($host, ENT_QUOTES, 'UTF-8').'" />
<meta property="og:type" content="'.$og_type.'" />
<meta property="og:title" content="'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'" />
<meta property="og:description" content="'.htmlspecialchars($description, ENT_QUOTES, 'UTF-8').'" />
<meta property="og:url" content="'.$canonical.'" />
<meta property="og:image" content="'.htmlspecialchars($og_img, ENT_QUOTES, 'UTF-8').'" />
<meta property="og:image:width" content="600" />
<meta property="og:image:height" content="338" />';

if ($var != null) {
echo '
<meta property="og:video:duration" content="'.$var.'" />
<meta property="video:duration" content="'.$var.'" />';
}

echo '
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'" />
<meta name="twitter:description" content="'.htmlspecialchars($description, ENT_QUOTES, 'UTF-8').'" />
<meta name="twitter:image" content="'.htmlspecialchars($og_img, ENT_QUOTES, 'UTF-8').'" />

<link rel="canonical" href="'.$canonical.'" />
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png" />
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png" />
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
<link rel="alternate" type="application/rss+xml" title="'.htmlspecialchars($host, ENT_QUOTES, 'UTF-8').' RSS Feed" href="/rss.xml" />
<link rel="sitemap" type="application/xml" title="Sitemap" href="/sitemap.xml" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
<link rel="stylesheet" type="text/css" href="/designs/'.$settings['designs'].'.css?v='.$css_v.'" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<title>'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'</title>';

if ($is_home) {
echo '
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "name": "'.htmlspecialchars($host, ENT_QUOTES, 'UTF-8').'",
  "url": "'.$protocol.$host.'/",
  "potentialAction": {
    "@type": "SearchAction",
    "target": "'.$protocol.$host.'/search_?i={search_term_string}",
    "query-input": "required name=search_term_string"
  }
}
</script>';
}

echo '
  </head>
  <body>

<div class="xxxhd-wrapper">
  <div class="xxxhd-head-wrap">
    <div class="xxxhd-head">
      <div class="xxxhd-head-top">
        <div class="xxxhd-logo">
          <a href="/" title="'.filter($_SERVER['HTTP_HOST']).'">
            SEKSCH<span>I</span>.ONLINE
          </a>
        </div>
      </div>
    </div>
    <div class="xxxhd-head-menu">
      <ul class="xxxhd-head-menu-buttons">
        <li><a href="/"><i class="fa fa-home"></i> Bosh sahifa</a></li>
        <li><a href="/new.html"><i class="fa fa-calendar"></i> '.$lang['new'].'</a></li>
        <li><a href="/top.html"><i class="fa fa-fire"></i> '.$lang['popular'].'</a></li>
        <li><a href="/favorites"><i class="fa fa-star"></i> '.$lang['chosen'].' ('.$favorites[0].')</a></li>
        <li><a href="/category.html"><i class="fa fa-th-large"></i> Bo‘limlar</a></li>
        '.$view_control.'
      </ul>
      <div class="xxxhd-search">
        <form method="get" action="/search_">
          <input type="text" name="i" placeholder="'.$lang['search'].'..." />
          <button type="submit"><i class="fa fa-search"></i></button>
        </form>
      </div>
    </div>
  </div>
  <div class="xxxhd-content">
    <h1 class="mark">'.$title.'</h1>';

}

#Нижняя часть сайта

function foot() {

global $settings, $lang, $user;

$popunder_html = '';
$is_admin = ($user && isset($user['access']) && $user['access'] == 1);
$is_control = (strpos($_SERVER['REQUEST_URI'] ?? '', 'control') !== false);

// Faqat oddiy foydalanuvchilarga popunder chiqariladi (admin panel va adminga xalaqit bermaslik uchun)
if (!$is_admin && !$is_control) {
    if (file_exists(__DIR__ . '/popunder.php')) {
        ob_start();
        include __DIR__ . '/popunder.php';
        $popunder_html = ob_get_clean();
    }
}

echo '
  </div><!-- xxxhd-content -->

  <div class="xxxhd-footer">
    <div class="xxxhd-foot">
      <p>&copy; '.date('Y').' <b>'.filter($_SERVER['SERVER_NAME']).'</b> '.$lang['rights'].'</p>
      <p style="margin: 8px 0; font-size: 11px; color: #707070;">Saytdagi barcha videolar ochiq manbalardan olingan bo‘lib, 18 yoshga to‘lmagan shaxslarga kirish taqiqlanadi.</p>
      <div style="margin: 10px 0; display: flex; justify-content: center; gap: 15px; align-items: center; flex-wrap: wrap;">
        <a href="/dmca.html" style="color: var(--primary-accent, #ff9900); font-size: 12px; text-decoration: none; font-weight: bold;"><i class="fa fa-shield"></i> DMCA / Mualliflik huquqi</a>
        <a href="/sitemap.html" style="color: #999; font-size: 12px; text-decoration: none;"><i class="fa fa-sitemap"></i> '.$lang['map'].'</a>
      </div>
      <div style="margin-top:8px;">
        <a href="/?lang=ru"><img src="/designs/icons/flags/ru.png" alt="Русский" title="Русский" /></a>
        <a href="/?lang=en"><img src="/designs/icons/flags/en.png" alt="English" title="English" /></a>
        <a href="/?lang=ua"><img src="/designs/icons/flags/ua.png" alt="Українська" title="Українська" /></a>
      </div>
    </div>
  </div>
  <p style="text-align:center;padding:5px">'.$settings['counter'].'</p>
  <h4 style="font-size:10px;text-align:center;color:#595a5c;padding:5px">'.$lang['h4'].'</h4>

</div><!-- xxxhd-wrapper -->
'.$popunder_html.'
  </body>
</html>';

}

#Наложение копирайта

function water($before, $after, $sign)	{
    if (!file_exists($before) || !file_exists($sign) || filesize($before) < 100) {
        return false;
    }
	list($owidth, $oheight) = getimagesize($before);
	
	$width = 600;
	$height = 300; 
	$im = imagecreatetruecolor($width, $height);
	$img_src = imagecreatefromjpeg($before);
	
	imagecopyresampled($im, $img_src, 0, 0, 0, 0, $width, $height, $owidth, $oheight);
	
	$watermark = imagecreatefrompng($sign);
	
	list($w_width, $w_height) = getimagesize($sign);
	
	$pos_x = max(0, $width - $w_width - 8); 
	$pos_y = max(0, $height - $w_height - 8);
	
	imagecopy($im, $watermark, $pos_x, $pos_y, 0, 0, $w_width, $w_height);
	imagejpeg($im, $after, 95);
	imagedestroy($im);
	imagedestroy($watermark);
	
	return true;
	
}

#Узнаем размер файла

function size($variable){
        
$opening = fopen($variable, 'r');
$information = stream_get_meta_data($opening);
    
fclose($opening);
    
foreach($information['wrapper_data'] as $bust)
    
if (stristr($bust, 'content-length')) {
        
$bust = explode(':', $bust);
    
return trim($bust[1]);
    
}
    
}
    
#Узнаем размер Я.диска

function yd_total_space() {
    
global $mysqli, $settings;

$curl = curl_init('https://cloud-api.yandex.net/v1/disk/');
curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: OAuth ' .$settings['OAuth']));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_HEADER, false);
$res = curl_exec($curl);
curl_close($curl);
$res = json_decode($res, true);

return $res['total_space'] - $res['used_space'];

}

#Загрузка файлов на Я.диск

function yd_upload_file($row, $res, $OAuth, $param = false) {

/* проверяем является ли ссылка файлом */

$urlHeaders = @get_headers($row);

if (!strpos($urlHeaders[0], '200')) {
    
    return '/content/not_available.mp4';
    exit;
}

/*** *** *** *** *** ***/

/* загружаем файл на сервер */

$temporary = md5(time()).'.'.$res;  # название файла и разрешение

file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/temporary/'.$temporary, file_get_contents($row)); # загружаем файл

$row = $_SERVER['DOCUMENT_ROOT'].'/content/temporary/'.$temporary;  # присваиваем переменной путь до файла

/*** *** *** *** *** ***/

/* загружаем файл на я.диск */

$curl = curl_init('https://cloud-api.yandex.net/v1/disk/resources/upload?fields=public_url&path=' . urlencode('/uploads/' . basename($row)));
curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: OAuth '.$OAuth));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_HEADER, false);
$res = curl_exec($curl);
curl_close($curl);

$res = json_decode($res, true);

if (empty($res['error'])) {

$fp = fopen($row, 'r');
$curl = curl_init($res['href']);
curl_setopt($curl, CURLOPT_PUT, true);
curl_setopt($curl, CURLOPT_UPLOAD, true);
curl_setopt($curl, CURLOPT_INFILESIZE, filesize($row));
curl_setopt($curl, CURLOPT_INFILE, $fp);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_HEADER, false);
curl_exec($curl);
$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

/*** *** *** *** *** ***/

/* получение публичного ключа */

$curl = curl_init('https://cloud-api.yandex.net/v1/disk/resources/publish?path=' . urlencode('/uploads/' . basename($row)));
curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: OAuth '.$OAuth));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_PUT, true);
curl_setopt($curl, CURLOPT_HEADER, false);
$res = curl_exec($curl);
curl_close($curl);
 
$res = json_decode($res, true);

/*** *** *** *** *** ***/

/* получаем информацию о файле */

$curl = curl_init('https://cloud-api.yandex.net/v1/disk/resources/?path=' . urlencode('/uploads/' . basename($row)));
curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: OAuth '.$OAuth));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_PATCH, true);
curl_setopt($curl, CURLOPT_HEADER, false);
$res = curl_exec($curl);
curl_close($curl);
 
$res = json_decode($res, true);

/*** *** *** *** *** ***/

if ($http_code == 201) {
	    
	unlink($_SERVER['DOCUMENT_ROOT'].'/content/temporary/'.$temporary); # удаляем файл с нашего сервера
	
	/* вывод ссылки на файл */	
	
	if ($param == 'straight')  return  $res['file'];
    else    return 'https://getfile.dokpub.com/yandex/get/'.$res['public_url'];	
	
	/*** *** *** *** *** ***/
	
	}   else return Error_publishing_file;
} 

}

#Перевод текста

function translate($row, $with, $on) {

    $data = array('client' => 'x', 'q' => $row, 'sl' => $with, 'tl' => $on);

    $array = array(
        'http' => array(
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 YaBrowser/18.6.1.770 Yowser/2.5 Safari/537.36',
            'method' => 'POST',
            'header' => 'Content-type: application/x-www-form-urlencoded',
            'content' => http_build_query($data))
    );

    return str_replace(array('"','\n'), '', file_get_contents('https://translate.google.ru/translate_a/t', false, stream_context_create($array)));

}

#Вывод ошибок

function error($row) {

echo '<p class="err">'.$row.'</p>
<a onclick="javascript:history.back(); return false;" class="byecos"><font color="black">Вернуться назад</font></a>';

foot();
exit;
    
}

#Определение категории по описанию

function categories($row) {
    
    if (strripos($row, 'азиатк') or strripos($row, 'asian') or strripos($row, 'вьетна')) $category = 1;
    else if (strripos($row, 'лесби') or strripos($row, 'lesbi')) $category = 12;
    else if (strripos($row, 'негр') or strripos($row, 'черн') or strripos($row, 'чёрн') or strripos($row, 'negr')) $category = 16;
    else if (strripos($row, 'мам') or strripos($row, 'мать') or strripos($row, 'mama')) $category = 13;
    else if (strripos($row, 'русс') or strripos($row, 'rus')) $category = 18;
    else if (strripos($row, 'дом') or strripos($row, 'home')) $category = 10;    
    else if (strripos($row, 'групп') or strripos($row, 'два') or strripos($row, 'lot') or strripos($row, 'guys')) $category = 9;    
    else if (strripos($row, 'анал') or strripos($row, 'жоп') or strripos($row, 'очк') or strripos($row, 'anal') or strripos($row, 'ass')) $category = 2;
    else if (strripos($row, 'страп') or strripos($row, 'slave') or strripos($row, 'раб') or strripos($row, 'mistre') or strripos($row, 'госпож') or strripos($row, 'bdsm') or strripos($row, 'шарик') or strripos($row, 'бдсм') or strripos($row, 'порево') or strripos($row, 'плет') or strripos($row, 'прищ') or strripos($row, 'извр')) $category = 3;
    else if (strripos($row, 'блондинк') or strripos($row, 'бело') or strripos($row, 'blond')) $category = 4;
    else if (strripos($row, 'большие') or strripos($row, 'большая') or strripos($row, 'awesome')) $category = 5;
    else if (strripos($row, 'члены') or strripos($row, 'огром') or strripos($row, 'big')) $category = 6;
    else if (strripos($row, 'брюнетк') or strripos($row, 'темно') or strripos($row, 'brunett')) $category = 7;
    else if (strripos($row, 'волосат') or strripos($row, 'зарос') or strripos($row, 'hair') or strripos($row, 'shav')) $category = 8;
    else if (strripos($row, 'жест') or strripos($row, 'груб') or strripos($row, 'hard')) $category = 11;
    else if (strripos($row, 'минет') or strripos($row, 'орал') or strripos($row, 'рот') or strripos($row, 'сос') or strripos($row, 'blow')) $category = 14;
    else if (strripos($row, 'молод') or strripos($row, '18') or strripos($row, 'young') or strripos($row, 'finish')) $category = 15;
    else if (strripos($row, 'рак') or strripos($row, 'нагн') or strripos($row, 'cancer')) $category = 17;
    else if (strripos($row, 'сперм') or strripos($row, 'конч') or strripos($row, 'sperm')) $category = 19;
    else if (strripos($row, 'студ') or strripos($row, 'учит') or strripos($row, 'учен') or strripos($row, 'teach')) $category = 20;
    else $category = rand(5, 6);
    
    return $category;
}

#Фильтрация

function filter($row) {
    return strip_tags(htmlspecialchars(stripslashes($row), ENT_QUOTES, 'UTF-8'));
}

#Пагинация

function page($k_page = 1) {
    
    $page = 1;
    
    if (isset($_GET['page']) and $_GET['page'] == 'end') $page = intval($k_page); else if (is_numeric($_GET['page'])) $page = intval($_GET['page']);
    
    if ($page < 1) $page = 1;
    
    if ($page > $k_page) $page = $k_page;
    
    return $page;
}

#Пагинация

function k_page($k_post = 0, $k_p_str = 10) {
    
    if ($k_post != 0) {
        
        $v_pages = ceil($k_post/$k_p_str);
        
        return $v_pages;
    
    } else return 1;                        
}

#Пагинация

function str($link = '?', $k_page = 1, $page = 1) {
    
    global $version;
    
    if ($page < 1) $page = 1;
    
    echo '<div class="pages">';
    
    if ($page > 1) echo ' <a href="'. $link .'page='. ($page - 1) .'" title="Вернуться на предыдущую страницу">&laquo;</a> ';
    
    if ($page < $k_page) echo ' <a href="'. $link .'page='. ($page + 1) .'" title="Перейти на следующую страницу">&raquo;</a> ';
    
    if ($page != 1) echo '<a href="'. $link .'page=1" title="Вернуться на первую страницу"> <b> 1</b></a>'; 
    
    else echo ' <a href="#">1</a>';
    
    for ($i = -3; $i <= 3; $i++) {
        
        if ($page + $i > 1 && $page + $i < $k_page) {

            $conclusion = $page + $i;
            
            if ($i != 0) echo ' <a href="'. $link .'page='. $conclusion .'" title="Перейти на '. $conclusion .' страницу">'. $conclusion .'</a>'; else echo ' <a href="#" title="Страница '. $conclusion .'">'. $conclusion .'</a>';
            
        }
    }
    
    if ($page != $k_page) echo ' <a href="'. $link .'page='. $k_page .'" title="Перейти на '. $k_page .' страницу">'. $k_page .'</a>'; else if ($k_page > 1) echo ' <a href="#" title="Страница '. $k_page .'">'. $k_page .'</a>';
    
    echo '</div> <!-- EroCMS '.$version.' -->';
}

#Транслит

function transliterate($string) {
    
	$replace = array(
		'а'  =>  'a','А' => 'a',
		'б' => 'b','Б' => 'b',
		'в' => 'v','В' => 'v',
		'г' => 'g','Г' => 'g',
		'д' => 'd','Д' => 'd',
		'е' => 'e','Е' => 'e',
		'ё' => 'e','Ё' => 'e',
		'ж' => 'zh','Ж' => 'zh',
		'з' => 'z','З' => 'z',
		'и' => 'i','И' => 'i',
		'й' => 'y','Й' => 'y',
		'к' => 'k','К' => 'k',
		'л' => 'l','Л' => 'l',
		'м' => 'm','М' => 'm',
		'н' => 'n','Н' => 'n',
		'о' => 'o','О' => 'o',
		'п' => 'p','П' => 'p',
		'р' => 'r','Р' => 'r',
		'с' => 's','С' => 's',
		'т' => 't','Т' => 't',
		'у' => 'u','У' => 'u',
		'ф' => 'f','Ф' => 'f',
		'х' => 'x','Х' => 'x',
		'ц' => 'c','Ц' => 'c',
		'ч' => 'ch','Ч' => 'ch',
		'ш' => 'sh','Ш' => 'sh',
		'щ' => 'sch','Щ' => 'sch',
		'ъ' => '','Ъ' => '',
		'ы' => 'y','Ы' => 'y',
		'ь' => '','Ь' => '',
		'э' => 'e','Э' => 'e',
		'ю' => 'yu','Ю' => 'yu',
		'я' => 'ya','Я' => 'ya',
		'і' => 'i','І' => 'i',
		'ї' => 'yi','Ї' => 'yi',
		'є' => 'e','Є' => 'e'
	);
	
	$str = iconv('UTF-8', 'UTF-8//IGNORE', strtr($string, $replace));
	
	return preg_replace('/[^\p{L}0-9 ]/iu', '', mb_strtolower($str));
	
}

#Генерация тегов

function tags($contents, $symbol = 5, $words = 5){
    
	$contents = @preg_replace(array("'<[\/\!]*?[^<>]*?>'si","'([\r\n])[\s]+'si","'&[a-z0-9]{1,6};'si","'( +)'si"),
	
	array('', '\\1 ', ' ', ' '), strip_tags($contents));
	
	$rearray = array('~','!','@','#','$','%','^','&','*','(',')','_','+',
		                 "`","'",'№',';',':','?','-','=','|','\'','\\','/',
		                 '[',']','{','}',"'",',','.','<','>','\r\n','\n','\t','«','»');

	$adjectivearray = array('ые','ое','ие','ий','ая','ый','ой','ми','ых','ее','ую','их','ым',
		                        'как','для','что','или','это','этих',
		                        'всех','вас','они','оно','еще','когда',
		                        'где','эта','лишь','уже','вам','нет',
		                        'если','надо','все','так','его','чем',
		                        'при','даже','мне','есть','только','очень',
		                        'сейчас','точно','обычно'
	                        );

	$contents = @str_replace($rearray,' ',$contents);
	
	$keywordcache = @explode(' ',$contents);
	
	$rearray = array();

	foreach($keywordcache as $word){
	    
		if(strlen($word)>=$symbol && !is_numeric($word)){
		    
			$adjective = substr($word,-2);
			
			if(!in_array($adjective,$adjectivearray) && !in_array($word,$adjectivearray)){
			    
				$rearray[$word] = (array_key_exists($word,$rearray)) ? ($rearray[$word] + 1) : 1;
				
			}
		}
	}

	@arsort($rearray);
	
	$keywordcache = @array_slice($rearray, 0, $words);
	
	$keywords = '';

	foreach($keywordcache as $word => $count){
	    
		$keywords.= ' '.$word;
		
	}

	return substr($keywords, 1);
}

#Вычисление размера папки

function getFilesSize($path){
    
    $Size = 0;
    
    $dir = scandir($path);

    foreach($dir as $file)
    {
        if (($file != '.') && ($file != '..'))
        
            if (is_dir($path . '/' . $file))
                $Size += getFilesSize($path.'/'.$file);
            else
                $Size += filesize($path . '/' . $file);
    }

    return round($Size / 1024 / 1000, 2).' мб.';
}

#Вывод рекламы

function advertising() {
    global $mysqli;
    if (function_exists('ads_get_config')) {
        $cfg = ads_get_config();
        if (empty($cfg['ads_enabled']) || empty($cfg['text_ads_enabled'])) {
            return;
        }
    }
    $time = time();
    $query = $mysqli->query("SELECT id, site, name, colour FROM ero_advertising WHERE (term = 0 OR term > '$time') ORDER BY id DESC LIMIT 6");
    if ($query && $query->num_rows > 0) {
        echo '<div class="xxxhd-ad-container" style="display:flex; flex-wrap:wrap; gap:8px; padding:10px 12px; margin:8px 0; background:rgba(255,153,0,0.04); border-radius:6px; border:1px solid rgba(255,153,0,0.18); align-items:center;">';
        while ($row = $query->fetch_assoc()) {
            $col = !empty($row['colour']) ? $row['colour'] : '#ff9900';
            echo '<a target="_blank" rel="noopener nofollow" href="'.$row['site'].'" style="display:inline-flex; align-items:center; gap:6px; text-decoration:none; padding:6px 12px; border-radius:4px; font-size:12px; font-weight:700; color:'.$col.'; background:#181a20; border:1px solid rgba(255,255,255,0.08); transition:all 0.2s;">
                <i class="fa fa-bullhorn" style="color:'.$col.'; font-size:12px;"></i>
                <span>'.htmlspecialchars($row['name']).'</span>
                <i class="fa fa-external-link" style="font-size:10px; opacity:0.5;"></i>
            </a>';
        }
        echo '</div>';
    }
}

#Логирование

function logs($id_user, $act, $id_file) {
    
    global $mysqli;
    
    $mysqli -> query("INSERT INTO ero_logs SET id_user = '$id_user', act = '$act', id_file = '$id_file', date = '".time()."'");
    
    array_map('unlink' , glob($_SERVER['DOCUMENT_ROOT']."/content/cache/*.html"));

}

# Настройки

$settings = $mysqli -> query("select * from ero_settings WHERE id = 1 limit 1") -> fetch_assoc();

# Иницилизация пользователя

$auth_pass = filter($_SESSION['password'] ?? $_COOKIE['password'] ?? '');
$user = !empty($auth_pass) ? ($mysqli -> query("select * from ero_users where password = '".mysqli_real_escape_string($mysqli, $auth_pass)."'") -> fetch_assoc()) : null;

# Foydalanuvchi qurilmasi va brauzerini aniqlash
function parse_user_agent_details($ua) {
    $res = [
        'device' => 'Kompyuter',
        'device_icon' => 'fa-desktop',
        'os' => 'Windows',
        'os_icon' => 'fa-windows',
        'browser' => 'Brauzer',
        'browser_icon' => 'fa-globe',
        'badge_color' => '#64748b'
    ];

    if (empty($ua)) return $res;

    // Robot / Botlar
    if (preg_match('/(googlebot|bingbot|yandexbot|ahrefs|semrush|baiduspider|curl|python|wget|facebookexternalhit|whatsapp|telegrambot)/i', $ua)) {
        return [
            'device' => 'Robot / Bot',
            'device_icon' => 'fa-cogs',
            'os' => 'Qidiruv boti',
            'os_icon' => 'fa-bug',
            'browser' => 'Crawler / Spider',
            'browser_icon' => 'fa-globe',
            'badge_color' => '#f59e0b'
        ];
    }

    // Qurilma & OS
    if (stripos($ua, 'Android') !== false) {
        $res['device'] = 'Mobil (Android)';
        $res['device_icon'] = 'fa-mobile';
        $res['os'] = 'Android';
        $res['os_icon'] = 'fa-android';
        $res['badge_color'] = '#22c55e';
    } elseif (stripos($ua, 'iPhone') !== false) {
        $res['device'] = 'iPhone';
        $res['device_icon'] = 'fa-mobile';
        $res['os'] = 'iOS';
        $res['os_icon'] = 'fa-apple';
        $res['badge_color'] = '#38bdf8';
    } elseif (stripos($ua, 'iPad') !== false) {
        $res['device'] = 'iPad';
        $res['device_icon'] = 'fa-tablet';
        $res['os'] = 'iPadOS';
        $res['os_icon'] = 'fa-apple';
        $res['badge_color'] = '#38bdf8';
    } elseif (stripos($ua, 'Macintosh') !== false || stripos($ua, 'Mac OS') !== false) {
        $res['device'] = 'Mac (Apple)';
        $res['device_icon'] = 'fa-desktop';
        $res['os'] = 'macOS';
        $res['os_icon'] = 'fa-apple';
        $res['badge_color'] = '#e2e8f0';
    } elseif (stripos($ua, 'Windows') !== false) {
        $res['device'] = 'Kompyuter (PC)';
        $res['device_icon'] = 'fa-desktop';
        $res['os'] = 'Windows';
        $res['os_icon'] = 'fa-windows';
        $res['badge_color'] = '#60a5fa';
    } elseif (stripos($ua, 'Linux') !== false) {
        $res['device'] = 'Linux';
        $res['device_icon'] = 'fa-desktop';
        $res['os'] = 'Linux';
        $res['os_icon'] = 'fa-linux';
        $res['badge_color'] = '#fbbf24';
    }

    // Brauzer
    if (stripos($ua, 'Telegram') !== false) {
        $res['browser'] = 'Telegram Web';
        $res['browser_icon'] = 'fa-paper-plane';
    } elseif (stripos($ua, 'Edg') !== false) {
        $res['browser'] = 'MS Edge';
        $res['browser_icon'] = 'fa-edge';
    } elseif (stripos($ua, 'OPR') !== false || stripos($ua, 'Opera') !== false) {
        $res['browser'] = 'Opera';
        $res['browser_icon'] = 'fa-globe';
    } elseif (stripos($ua, 'Chrome') !== false) {
        $res['browser'] = 'Chrome';
        $res['browser_icon'] = 'fa-chrome';
    } elseif (stripos($ua, 'Safari') !== false) {
        $res['browser'] = 'Safari';
        $res['browser_icon'] = 'fa-safari';
    } elseif (stripos($ua, 'Firefox') !== false) {
        $res['browser'] = 'Firefox';
        $res['browser_icon'] = 'fa-firefox';
    }

    return $res;
}

# Manba (Referer - qayerdan kelganini) aniqlash
function parse_referer_source($referer) {
    if (empty($referer)) {
        return [
            'type' => 'direct',
            'title' => 'To‘g‘ridan-to‘g‘ri (Direct)',
            'icon' => 'fa-globe',
            'color' => '#94a3b8',
            'url' => '',
            'host' => 'Direct'
        ];
    }

    $host = parse_url($referer, PHP_URL_HOST) ?? '';
    $host_clean = preg_replace('/^www\./i', '', strtolower($host));

    // O'z saytimiz ichidagi o'tishlar
    $my_host = preg_replace('/^www\./i', '', strtolower($_SERVER['HTTP_HOST'] ?? 'sekschi.online'));
    if ($host_clean === $my_host || empty($host_clean)) {
        return [
            'type' => 'internal',
            'title' => 'Sayt ichidan (Ichki o‘tish)',
            'icon' => 'fa-refresh',
            'color' => '#64748b',
            'url' => $referer,
            'host' => 'Ichki'
        ];
    }

    // Google
    if (stripos($host_clean, 'google.') !== false) {
        return [
            'type' => 'search',
            'title' => 'Google Qidiruv',
            'icon' => 'fa-google',
            'color' => '#3b82f6',
            'url' => $referer,
            'host' => $host_clean
        ];
    }

    // Yandex
    if (stripos($host_clean, 'yandex.') !== false || stripos($host_clean, 'ya.ru') !== false) {
        return [
            'type' => 'search',
            'title' => 'Yandex Qidiruv',
            'icon' => 'fa-search',
            'color' => '#ef4444',
            'url' => $referer,
            'host' => $host_clean
        ];
    }

    // Telegram
    if (stripos($host_clean, 't.me') !== false || stripos($host_clean, 'telegram') !== false) {
        return [
            'type' => 'social',
            'title' => 'Telegram',
            'icon' => 'fa-paper-plane',
            'color' => '#0ea5e9',
            'url' => $referer,
            'host' => $host_clean
        ];
    }

    // Bing
    if (stripos($host_clean, 'bing.com') !== false) {
        return [
            'type' => 'search',
            'title' => 'Bing Qidiruv',
            'icon' => 'fa-search',
            'color' => '#0284c7',
            'url' => $referer,
            'host' => $host_clean
        ];
    }

    // DuckDuckGo
    if (stripos($host_clean, 'duckduckgo.com') !== false) {
        return [
            'type' => 'search',
            'title' => 'DuckDuckGo',
            'icon' => 'fa-search',
            'color' => '#f97316',
            'url' => $referer,
            'host' => $host_clean
        ];
    }

    // Instagram
    if (stripos($host_clean, 'instagram.com') !== false) {
        return [
            'type' => 'social',
            'title' => 'Instagram',
            'icon' => 'fa-instagram',
            'color' => '#ec4899',
            'url' => $referer,
            'host' => $host_clean
        ];
    }

    // TikTok
    if (stripos($host_clean, 'tiktok.com') !== false) {
        return [
            'type' => 'social',
            'title' => 'TikTok',
            'icon' => 'fa-video-camera',
            'color' => '#f43f5e',
            'url' => $referer,
            'host' => $host_clean
        ];
    }

    // YouTube
    if (stripos($host_clean, 'youtube.com') !== false || stripos($host_clean, 'youtu.be') !== false) {
        return [
            'type' => 'social',
            'title' => 'YouTube',
            'icon' => 'fa-youtube-play',
            'color' => '#ef4444',
            'url' => $referer,
            'host' => $host_clean
        ];
    }

    // Boshqa tashqi sayt
    return [
        'type' => 'external',
        'title' => $host_clean,
        'icon' => 'fa-external-link',
        'color' => '#10b981',
        'url' => $referer,
        'host' => $host_clean
    ];
}

# Онлайн (Real-time faollik hisoblagichi)
$client_ip = mysqli_real_escape_string($mysqli, filter($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'));
$current_page = mysqli_real_escape_string($mysqli, filter($_SERVER['REQUEST_URI'] ?? '/'));
$raw_ua = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 250);
$current_ua = mysqli_real_escape_string($mysqli, filter($raw_ua));
$raw_ref = substr($_SERVER['HTTP_REFERER'] ?? '', 0, 490);
$current_ref = mysqli_real_escape_string($mysqli, filter($raw_ref));
$now = time();
$expire = $now + 300;
$c_info = function_exists('get_ip_country_info') ? get_ip_country_info($client_ip) : ['code' => 'UZ', 'name' => 'O‘zbekiston'];
$c_code = mysqli_real_escape_string($mysqli, $c_info['code']);

$check_vis = $mysqli->query("SELECT id, referer FROM ero_online WHERE ip = '$client_ip' LIMIT 1");
if ($check_vis && $check_vis->num_rows > 0) {
    $row_vis = $check_vis->fetch_assoc();
    // Agar oldingi referer bo'lsa va yangisi bo'sh bo'lsa (ichki sahifalarda yurganda), oldingi manbani saqlab qolamiz
    $save_ref = (!empty($current_ref) && stripos($current_ref, $_SERVER['HTTP_HOST'] ?? '') === false) 
        ? $current_ref 
        : (!empty($row_vis['referer']) ? mysqli_real_escape_string($mysqli, $row_vis['referer']) : $current_ref);
    $mysqli->query("UPDATE ero_online SET date = '$expire', page_url = '$current_page', user_agent = '$current_ua', country_code = '$c_code', referer = '$save_ref', last_seen = '$now' WHERE ip = '$client_ip'");
} else {
    $mysqli->query("INSERT INTO ero_online (ip, date, page_url, user_agent, country_code, referer, last_seen) VALUES ('$client_ip', '$expire', '$current_page', '$current_ua', '$c_code', '$current_ref', '$now')");
}
$mysqli->query("DELETE FROM ero_online WHERE date < '$now'");