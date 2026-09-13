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

function time_ago($time) {
    $diff = time() - $time;
    if ($diff < 60) return 'hozirgina';
    if ($diff < 3600) return floor($diff / 60) . ' daqiqa oldin';
    if ($diff < 86400) return floor($diff / 3600) . ' soat oldin';
    if ($diff < 2592000) return floor($diff / 86400) . ' kun oldin';
    return date('d.m.Y', $time);
}

#Локализация

if (isset($_GET['lang']))   {
    
    $_SESSION['lang'] = filter($_GET['lang']);
    
    header('Refresh: 0; '.$_SERVER['PHP_SELF']);
    exit;
}

if (file_exists($_SERVER['DOCUMENT_ROOT'].'/core/languages/'.$_SESSION['lang'].'.php'))
    include $_SERVER['DOCUMENT_ROOT'].'/core/languages/'.$_SESSION['lang'].'.php';
else
    include $_SERVER['DOCUMENT_ROOT'].'/core/languages/ru.php';

#Вверхняя часть сайта

function head($var = null) {

global $mysqli, $title, $description, $keywords, $protocol, $settings, $user, $lang;

$favorites = $mysqli -> query("select count(*) from ero_favorites where data = '".mysqli_real_escape_string($mysqli, filter($_SERVER['REMOTE_ADDR']))."'") -> fetch_row();
$visitors = $mysqli -> query("select count(*) from ero_online") -> fetch_row();

if ($user)  $view_control = '
<li><a href="/control.php"><i class="fa fa-cog"></i> '.$lang['control_panel'].'</a></li>';
else $view_control = null;

echo '
<html lang="ru">
  <head>
<meta charset="utf-8" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="description" content="'.$description.'" />
<meta name="keywords" content="'.$keywords.'" />
<meta name="robots" content="INDEX,ALL" />
<meta name="theme-color" content="#21201f" />
<meta property="og:type" content="article" />
<meta property="og:title" content="'.$title.'" />';
if ($var != null)
echo '
<meta property="video:duration" content="'.$var.'"/>';
echo '
<meta property="og:url" content="'.$protocol.filter($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']).'" />
<meta property="og:description" content="'.$description.'" />
<link rel="canonical" href="'.$protocol.filter($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']).'" />
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
<link rel="stylesheet" type="text/css" href="/designs/'.$settings['designs'].'.css" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<title>'.$title.'</title>
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

global $settings, $lang;

echo '
  </div><!-- xxxhd-content -->

  <div class="xxxhd-footer">
    <div class="xxxhd-foot">
      <p>&copy; '.date('Y').' <b>'.filter($_SERVER['SERVER_NAME']).'</b> '.$lang['rights'].'</p>
      <p style="margin: 8px 0; font-size: 11px; color: #707070;">Saytdagi barcha videolar ochiq manbalardan olingan bo‘lib, 18 yoshga to‘lmagan shaxslarga kirish taqiqlanadi.</p>
      <div style="margin-top:8px;">
        <a href="/?lang=ru"><img src="/designs/icons/flags/ru.png" alt="Русский" title="Русский" /></a>
        <a href="/?lang=en"><img src="/designs/icons/flags/en.png" alt="English" title="English" /></a>
        <a href="/?lang=ua"><img src="/designs/icons/flags/ua.png" alt="Українська" title="Українська" /></a>
      </div>
    </div>
  </div>
  <a href="/advertising.html"><p style="text-align:right;color:#ff9900;padding:5px 10px;font-size:12px">'.$lang['pay'].'</p></a>
  <p style="text-align:center;padding:5px">'.$settings['counter'].'</p>
  <h4 style="font-size:10px;text-align:center;color:#595a5c;padding:5px">'.$lang['h4'].'</h4>
  <a href="/sitemap.html"><p style="text-align:center;color:#ff9900;padding:5px;font-size:12px">'.$lang['map'].'</p></a>

</div><!-- xxxhd-wrapper -->
  </body>
</html>';

}

#Наложение копирайта

function water($before, $after, $sign)	{
    
	list($owidth, $oheight) = getimagesize($before);
	
	$width = 600;
	$height = 300; 
	$im = imagecreatetruecolor($width, $height);
	$img_src = imagecreatefromjpeg($before);
	
	imagecopyresampled($im, $img_src, 0, 0, 0, 0, $width, $height, $owidth, $oheight);
	
	$watermark = imagecreatefrompng($sign);
	
	list($w_width, $w_height) = getimagesize($sign);
	
	$pos_x = $width - $w_width; 
	$pos_y = $height - $w_height;
	
	imagecopy($im, $watermark, $pos_x, $pos_y, 0, 0, $w_width, $w_height);
	imagejpeg($im, $after, 100);
	imagedestroy($im);
	
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
    
    echo '<p align="left">';
    
    $query = $mysqli -> query("select id, site, name, colour from ero_advertising where term > '".time()."' order by rand()");

    while($row = $query -> fetch_assoc())
    
    echo '<a target="_blank" href="'.$row['site'].'" class="tach"><img src="/designs/icons/view/site.png" width="16" height="16" /> <font color="'.$row['colour'].'">'.$row['name'].'</font></a>';

    echo '</p>';

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

# Онлайн

$visitor = $mysqli -> query("select count(*) from ero_online where ip = '".mysqli_real_escape_string($mysqli, filter($_SERVER['REMOTE_ADDR']))."'") -> fetch_row();

if ($visitor[0] == 0) $mysqli -> query("INSERT INTO ero_online SET ip = '".mysqli_real_escape_string($mysqli, filter($_SERVER['REMOTE_ADDR']))."', date = '".(time() + 300)."'");

$mysqli -> query("delete from ero_online where date < '".time()."'");