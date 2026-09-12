<?php

/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/

    if ($user['access'] < 1) {
        header('location: /'); 
        exit;
    }
    
    
    if (isset($_POST['mode']))  {
    
    if ($_SESSION['protective'] != $_POST['protective'])    {           
        
        header('location: /control.html?func=parsing'); 
        exit;
    }
    
    $publish = strtotime($_POST['publish']) + rand(300, 21600);
    
    if ($_POST['server'] == 0) {
    
    $start = rand(1, 4960);
    $collected = 0;
    
    for($go = $start; $go < ($start + $_POST['results']); $go++){

    $_carry = file_get_contents('http://mobolto.com/porno/video-'.$go.'/');

    if (strripos($_carry, 'file:')){

    preg_match_all('|<h1>(.*?)</h1>|is', $_carry, $name);
    preg_match_all('|<div class="iblock4">(.*?)</div>|is', $_carry, $description);
    preg_match('|poster:"(.*?)"|is', $_carry, $poster);
    preg_match('|file:"(.*?)"|is', $_carry, $file);
    preg_match('|Длительность: <b>(.*?)</b>|is', $_carry, $duration);

    $name[1][0] = preg_replace("/('|\"|\r?\n)/", '', $name[1][0]);
    $description[1][1] = preg_replace("/('|\"|\r?\n)/", '', $description[1][1]);
    $md5 = md5(rand(1, 9999).$go);
    $translit = str_replace(' ', '_', transliterate($name[1][0])).'_'.rand(1, 9999);
    $uniqueness = md5(str_replace(' ', '_', transliterate($name[1][0])));
    
    $quantity = $mysqli -> query("select count(*) from ero_files where uniqueness = '$uniqueness' and server = 'mobolto.com'") -> fetch_row();
    
    if ($quantity[0] == 0) {
        
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', file_get_contents($poster[1]));

    $image = new SimpleImage();
    $image->load($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');
    $image->resize($width_S, $height_S);
    $image->save($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');

    if ($settings['water'] == 1)
	water($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/designs/water.png');
	
	$yd_sql = 0;
	
    if ($_POST['mode'] == '2') {

    $yd = yd_upload_file($file[1], 'mp4', $settings['OAuth']);
    $recoil = $yd;
    $yd_sql = 1;
	
    } else if ($_POST['mode'] == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents($file[1]));

    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }
    
    $sample = $description[1][1];
    
    if ($_POST['selection'] == 0) $category = categories($sample); else $category = abs(intval($_POST['category']));
    
    if ($_POST['translate'] == 2) {
        
        $description[1][1] = translate($description[1][1], 'ru', 'en');
        $name[1][0] = translate($name[1][0], 'ru', 'en');
    }
    
    $mysqli -> query("INSERT INTO ero_files SET yd = '$yd_sql', category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = '".$file[1]."', server = 'mobolto.com', tags = '".tags($description[1][1])."', name = '".$name[1][0]."', description = '".$description[1][1]."', translit = '$translit', duration = '".$duration[1]."', date = '$publish'");
    
    $collected++;
    
    }
    
    sleep($_POST['delay']);
    
    }
    
    }
    
    } else if ($_POST['server'] == 1) {
    
    $collected = 0;
    
    for($go = 0; $go < $_POST['results']; $go++){
        
    $categories = array('damy', 
    'konchil-v-pizdu', 
    '18-let', 
    'macheha', 
    'zhestkoe', 
    'domashnee', 
    'starushki', 
    'telki', 
    'sborniki', 
    'gruppovoe', 
    'kiska', 
    'vanal', 
    's-chernymi',
    'krupnym-planom', 
    'na-ulice', 
    'izmena-zheny', 
    'blondinki', 
    'ogromnye-siski', 
    'negrityanki', 
    'Cheshskoe-porno', 
    'na-grud', 
    'hd-video', 
    'rvotnye-dvizheniya', 
    'dildo', 
    'negry', 
    'Erotika', 
    'verhovaja-ezda', 
    'kasting'
    );

    $array = array_rand($categories, 2);

    $_carry = file_get_contents('https://pornomir.tv/'.$categories[$array[0]].'/');

    preg_match('|<div class="previews-block">(.*?)<div class="text-desc-block">|is', $_carry, $previews);
    preg_match_all('|<div class="preview-block">(.*?)<div class="preview-block">|is', $previews[1], $video);

    $array = rand(1, 40);

    preg_match('|<div class="preview-name"><span>(.*?)</span></div>|is', $video[1][$array], $name);
    preg_match('|data-original="(.*?)"|is', $video[1][$array], $poster);
    preg_match('|<a href="(.*?)">|is', $video[1][$array], $link);
    preg_match('|<div class="preview-dur-value">(.*?)</div>|is', $video[1][$array], $duration);
    
    $duration[1] = str_replace('<i class="fa fa-clock-o"></i>', '', $duration[1]);
    $duration[1] = str_replace('<span>', '', $duration[1]);
    $duration[1] = str_replace('</span>', '', $duration[1]);
    $duration[1] = str_replace(' ', '', $duration[1]);
    
    $_carry = file_get_contents('https://pornomir.tv'.$link[1]);

    preg_match('|<source src="(.*?)"|is', $_carry, $file);
        
    if (strripos($_carry, 'video/mp4') and !strripos($file[1], 'видео')){
        
    $md5 = md5(rand(1, 9999).$go);
    $translit = str_replace(' ', '_', transliterate($name[1])).'_'.rand(1, 9999);
    $uniqueness = md5(str_replace(' ', '_', transliterate($name[1])));
    
    $quantity = $mysqli -> query("select count(*) from ero_files where uniqueness = '$uniqueness' and server = 'pornomir.tv'") -> fetch_row();
    
    if ($quantity[0] == 0) {
        
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', file_get_contents('https://pornomir.tv'.$poster[1]));

    $image = new SimpleImage();
    $image->load($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');
    $image->resize($width_S, $height_S);
    $image->save($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');

    if ($settings['water'] == 1)
	water($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/designs/water.png');
	
	$yd_sql = 0;
	
    if ($_POST['mode'] == '2') {

    $yd = yd_upload_file($file[1], 'mp4', $settings['OAuth']);
    $recoil = $yd;
    $yd_sql = 1;
	
    } else if ($_POST['mode'] == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents($file[1]));
    
    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }
        
    $sample = $name[1];
    
    if ($_POST['selection'] == 0) $category = categories($sample); else $category = abs(intval($_POST['category']));

    if ($_POST['translate'] == 2) {
        
        $name[1] = translate($name[1], 'ru', 'en');
        
    }
    
    $mysqli -> query("INSERT INTO ero_files SET yd = '$yd_sql', category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = '".$file[1]."', server = 'pornomir.tv', tags = '".tags($name[1])."', name = '".$name[1]."', description = '".$name[1]."', translit = '$translit', duration = '".$duration[1]."', date = '$publish'");
        
    $collected++;
    
    }
    
    sleep($_POST['delay']);
    
    }
    
    }
    
    } else if ($_POST['server'] == 2) {
    
    $collected = 0;
    
    for($go = 0; $go < $_POST['results']; $go++){
      
    $categories = array('Analqnyj_seks', 
    'Blondinki', 
    'Bolqshie_sisqki', 
    'Bolqshim_chlenom', 
    'Bryunetki', 
    'Gruppovoe', 
    'Minet_i_otsos', 
    'Molodyee_devki', 
    'Russkoe_porno');

    $array = array_rand($categories, 2);

    $_carry = file_get_contents('http://pornoraketa.tv/category/'.$categories[$array[0]].'/page'.rand(1, 10));

    preg_match("|<div class='sizepole'>(.*?)<div class='nextblock'>|is", $_carry, $category);
    preg_match_all("|<a href='(.*?)'>|is", $category[1], $page);
    preg_match_all("|<img src='(.*?)'|is", $category[1], $poster);

    $array = rand(0, 19);

    $_carry = file_get_contents('http://pornoraketa.tv'.$page[1][$array]);

    if (strripos($_carry, 'likpole')){

    preg_match("|<h1 class='toptx'>(.*?)</h1>|is", $_carry, $name);
    preg_match("|a class='skachka' href='(.*?)'><img src='css/img/dwlv.png'> Скачать HD <span class='skcount'>|is", $_carry, $file);
    preg_match("|<img src='css/img/durdl.png'>(.*?)</span>|is", $_carry, $duration);
    
    $md5 = md5(rand(1, 9999).$go);
    $translit = str_replace(' ', '_', transliterate($name[1])).'_'.rand(1, 9999);
    $uniqueness = md5(str_replace(' ', '_', transliterate($name[1])));
    
    $quantity = $mysqli -> query("select count(*) from ero_files where uniqueness = '$uniqueness' and server = 'pornoraketa.tv'") -> fetch_row();
    
    if ($quantity[0] == 0) {
        
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', file_get_contents('http://pornoraketa.tv'.$poster[1][$array]));

    $image = new SimpleImage();
    $image->load($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');
    $image->resize($width_S, $height_S);
    $image->save($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');

    if ($settings['water'] == 1)
	water($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/designs/water.png');
	
	$yd_sql = 0;
	
    if ($_POST['mode'] == '2') {

    $yd = yd_upload_file('http://pornoraketa.tv/'.$file[1], 'mp4', $settings['OAuth']);
    $recoil = $yd;
    $yd_sql = 1;
	
    } else if ($_POST['mode'] == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents('http://pornoraketa.tv/'.$file[1]));
    
    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }

    $sample = $name[1];
    
    if ($_POST['selection'] == 0) $category = categories($sample); else $category = abs(intval($_POST['category']));

    if ($_POST['translate'] == 2) {
        
        $name[1] = translate($name[1], 'ru', 'en');
        
    }
    
    $mysqli -> query("INSERT INTO ero_files SET yd = '$yd_sql', category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = 'http://pornoraketa.tv/".$file[1]."', server = 'pornoraketa.tv', tags = '".tags($name[1])."', name = '".$name[1]."', description = '".$name[1]."', translit = '$translit', duration = '".$duration[1]."', date = '$publish'");
        
    $collected++;
    
    }
    
    sleep($_POST['delay']);

    }
        
    }
    
    } else if ($_POST['server'] == 4) {
    
    $start = rand(1, 900);
    $collected = 0;
    
    for($go = $start; $go < ($start + $_POST['results']); $go++){

    $_carry = file_get_contents('http://airporno.ru/views/'.$go.'/');
    
    preg_match('|<h1 class="htit">(.*?)</h1>|is', $_carry, $name);
    
    if (strripos($_carry, 'source') or !strripos($name[1], 'href')){
    
    preg_match('|<source src="(.*?)"|is', $_carry, $file);
    preg_match('|poster="(.*?)"|is', $_carry, $poster);
    preg_match('|Время:(.*?)<br />|is', $_carry, $duration);
    
    $md5 = md5(rand(1, 9999).$go);
    $translit = str_replace(' ', '_', transliterate($name[1])).'_'.rand(1, 9999);
    $uniqueness = md5(str_replace(' ', '_', transliterate($name[1])));
    
    $quantity = $mysqli -> query("select count(*) from ero_files where uniqueness = '$uniqueness' and server = 'airporno.ru'") -> fetch_row();
    
    if ($quantity[0] == 0) {
        
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', file_get_contents($poster[1]));

    $image = new SimpleImage();
    $image->load($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');
    $image->resize($width_S, $height_S);
    $image->save($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');

    if ($settings['water'] == 1)
	water($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/designs/water.png');
	
	$yd_sql = 0;
	
    if ($_POST['mode'] == '2') {

    $yd = yd_upload_file($file[1], 'mp4', $settings['OAuth']);
    $recoil = $yd;
    $yd_sql = 1;
	
    } else if ($_POST['mode'] == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents($file[1]));
    
    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }
    
    $duration = trim($duration[1]);
    
    if (strlen($duration) == 4) $duration = '0'.$duration;
    
    $sample = $name[1];
    
    if ($_POST['selection'] == 0) $category = categories($sample); else $category = abs(intval($_POST['category']));

    if ($_POST['translate'] == 2) {
        
        $name[1] = translate($name[1], 'ru', 'en');
        
    }
    
    $mysqli -> query("INSERT INTO ero_files SET yd = '$yd_sql', category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = '".$file[1]."', server = 'airporno.ru', tags = '".tags($name[1])."', name = '".$name[1]."', description = '".$name[1]."', translit = '$translit', duration = '$duration', date = '$publish'");
    
    $collected++;
    
    }
    
    sleep($_POST['delay']);    
    
    }
    
    }
    
    } else if ($_POST['server'] == 5) {
    
    $collected = 0;
    
    for($go = 0; $go < $_POST['results']; $go++){

    $categories = array(
    'azerbaydjanskoe_porno', 
    'arabskoe_porno', 
    'kavkazskoe_porno', 
    'kazahskoe_porno', 
    'kirgizskoe_porno', 
    'russkoe_porno', 
    'tadjikskoe_porno', 
    'turetskoe_porno', 
    'uzbekskiy_seks', 
    'chechenskoe_porno', 
    'yakutskoe_porno'
    );

    $array = array_rand($categories, 2);

    $_carry = file_get_contents('https://pizdauz.ru/'.$categories[$array[0]].'/?page='.rand(1, 20));

    preg_match('|<div class="etoall">(.*?)<div class="cleaning"></div>|is', $_carry, $_all);
    preg_match_all('|<a(.*?)</a>|is', $_all[1], $_url);
    
    $_arr = $_url[0][rand(0, 19)];
    
    preg_match('|<a href="(.*?)"|is', $_arr, $_link);
    preg_match('|title="(.*?)"|is', $_arr, $name);
    preg_match('|<img src="(.*?)"|is', $_arr, $poster);
    preg_match('|<div class="etodur">(.*?)</div>|is', $_arr, $duration);
    
    $_carry = file_get_contents($_link[1]);
    
    preg_match('|<div class="etoinfodate">Описание:(.*?)</div>|is', $_carry, $description);
    preg_match('|<source src="(.*?)"|is', $_carry, $file);

    if (strripos($_carry, 'source')){

    preg_match('|<meta property="og:image" content="(.*?)"|is', $_carry, $poster);
    preg_match('|<meta property="og:title" content="(.*?)"|is', $_carry, $name);
    preg_match('|<b>Длительность</b>:(.*?)<br />|is', $_carry, $duration);
    preg_match('|<meta name="description" content="(.*?)"|is', $_carry, $description);
    preg_match('|<source src="(.*?)"|is', $_carry, $file);
    
    $md5 = md5(rand(1, 9999).$go);
    $translit = str_replace(' ', '_', transliterate($name[1])).'_'.rand(1, 9999);
    $uniqueness = md5(str_replace(' ', '_', transliterate($name[1])));
    
    $quantity = $mysqli -> query("select count(*) from ero_files where uniqueness = '$uniqueness' and server = 'pizdauz.ru'") -> fetch_row();
    
    if ($quantity[0] == 0) {
        
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', file_get_contents($poster[1]));

    $image = new SimpleImage();
    $image->load($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');
    $image->resize($width_S, $height_S);
    $image->save($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');

    if ($settings['water'] == 1)
	water($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/designs/water.png');
	
	$yd_sql = 0;
	
    if ($_POST['mode'] == '2') {

    $yd = yd_upload_file($file[1], 'mp4', $settings['OAuth']);
    $recoil = $yd;
    $yd_sql = 1;
	
    } else if ($_POST['mode'] == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents($file[1]));
    
    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }
    
    $sample = trim($description[1]);
    
    if ($_POST['selection'] == 0) $category = categories($sample); else $category = abs(intval($_POST['category']));

    if ($_POST['translate'] == 2) {
        
        $name[1] = translate($name[1], 'ru', 'en');
        $description[1] = translate(trim($description[1]), 'ru', 'en');
        
    }
    
    $mysqli -> query("INSERT INTO ero_files SET yd = '$yd_sql', category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = '".$file[1]."', server = 'pizdauz.ru', tags = '".tags(trim($description[1]))."', name = '".$name[1]."', description = '".trim($description[1])."', translit = '$translit', duration = '".trim($duration[1])."', date = '$publish'");
    
    $collected++;

    }
    
    }
    
    sleep($_POST['delay']);    
    
    }
    
    } else if ($_POST['server'] == 6) {
    
    $collected = 0;
    
    for($go = 0; $go < $_POST['results']; $go++){

    $categories = array('1080p', 
    '18yearsold', 
    '3way', 
    'absolutely', 
    'american', 
    'amateur-xxx', 
    'anal-fuck', 
    'asian', 
    'babes', 
    'bath', 
    'best-blowjob-video', 
    'bed', 
    'big-black-dick', 
    'boy', 
    'brasil', 
    'check', 
    'club', 
    'dad', 
    'dando', 
    'freckles', 
    'girl-fuck', 
    'hair', 
    'hard-porn', 
    'hidden', 
    'lesbian', 
    'mallu', 
    '18-porn', 
    'amatuer-videos', 
    'anal-licking', 
    'argentina', 
    'all', 
    'big',
    'arab',
    'bikini',
    'russian',
    'france',
    'office',
    'morrita',
    'solo',
    'woman-fucking', 
    'vip',
    'xxx',
    'webcamchat',
    'top',
    'throat',
    'student',
    'real-sex',
    'realsex',
    'rabo'
    );

    $array = array_rand($categories, 2);
    
    $_carry = file_get_contents('https://www.xnxx.com/tags/'.$categories[$array[0]].'/'.rand(1, 30).'/');
    
    preg_match('|<div class="mozaique">(.*?)<div class="pagination ">|is', $_carry, $url);
    preg_match_all('|<a href="(.*?)"|is', $url[1], $out);
    
    $_carry = file_get_contents('https://www.xnxx.com'.$out[1][rand(0, 70)]);

    preg_match('|<title>(.*?)</title>|is', $_carry, $name);
    preg_match('|setVideoUrlLow(.*?)html5player|is', $_carry, $file);
    preg_match('|<span class="metadata">(.*?)</span>|is', $_carry, $duration);
    preg_match('|<meta property="og:image" content="(.*?)"|is', $_carry, $poster);
        
    $file[0] = str_replace("setVideoUrlLow('", '', $file[0]);
    $file[0] = str_replace("');", '', $file[0]);
    $file[0] = str_replace('html5player', '', $file[0]);
    $name[1] = str_replace(' - XNXX.COM', '', $name[1]);
    $duration[1] = preg_replace('|min(.*?)hits|is', '', $duration[1]);
    $duration[1] = str_replace(' ', ':'.rand(10,20), $duration[1]);
    $duration[1] = preg_replace('|-(.*?)p|is', '', $duration[1]);
    
    if (strripos($_carry, '<span class="metadata">')){
    
    $md5 = md5(rand(1, 9999).$go);
    $translit = str_replace(' ', '_', transliterate($name[1])).'_'.rand(1, 9999);
    $uniqueness = md5(str_replace(' ', '_', transliterate($name[1])));
    
    $quantity = $mysqli -> query("select count(*) from ero_files where uniqueness = '$uniqueness' and server = 'xnxx.com'") -> fetch_row();
    
    if ($quantity[0] == 0) {

	$yd_sql = 0;
	
    if ($_POST['mode'] == '2') {

    $yd = yd_upload_file(trim($file[0]), 'mp4', $settings['OAuth']);
    $recoil = $yd;
    $yd_sql = 1;
	
    } else if ($_POST['mode'] == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents(trim($file[0])));
    
    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }
    
    $sample = trim($name[1]);
    
    if (mb_strlen(trim($duration[1])) == 5) {
    
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', file_get_contents($poster[1]));

    $image = new SimpleImage();
    $image->load($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');
    $image->resize($width_S, $height_S);
    $image->save($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');

    if ($settings['water'] == 1)
	water($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/designs/water.png');
	
    if ($_POST['selection'] == 0) $category = categories($sample); else $category = abs(intval($_POST['category']));

    if ($_POST['translate'] == 2) {
        
        $name[1] = translate($name[1], 'ru', 'en');
        
    }
    
    $mysqli -> query("INSERT INTO ero_files SET yd = '$yd_sql', category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = '".trim($file[0])."', server = 'xnxx.com', tags = '".tags(trim($name[1]))."', name = '".$name[1]."', description = '".$name[1]."', translit = '$translit', duration = '".trim($duration[1])."', date = '$publish'");
    
    $collected++;
        
    }

    }
    
    }
    
    sleep($_POST['delay']);    
    
    }
    
    } else if ($_POST['server'] == 7) {
    
    $collected = 0;
    
    for($go = 0; $go < $_POST['results']; $go++){
	
	$categories = array('porno-v-hd', 
    'aziatki', 
    'bolshaya-grud', 
    'bolshie-chleny', 
    'gey-porno', 
    'gruppovoe-porno', 
    'domashnee-porno', 
    'zadnicy', 
    'zrelye', 
    'iznasilovanie', 
    'lesbiyanki', 
    'mamashi', 
    'masturbaciya',
    'mezhrasovoe-porno', 
    'minet', 
    'russkoe-porno', 
    'skvirt', 
    'fisting', 
    'chernye'
    );

    $array = array_rand($categories, 2);
    
    $_carry = file_get_contents('http://pornolomka.mobi/'.$categories[$array[0]].'/page/'.rand(1, 30).'/');
    
    $_carry = iconv('windows-1251', 'UTF-8', $_carry);
    
    preg_match_all('|<article class="shortstory cf">(.*?)</article>|is', $_carry, $previews);
    
    $_article = $previews[0][rand(0, 18)];
    
    preg_match('|title="(.*?)"|is', $_article, $name);
    preg_match('|<div class="video_time">(.*?)</div>|is', $_article, $duration);
    preg_match('|<img src="(.*?)"|is', $_article, $poster);
    preg_match('|<a href="(.*?)"|is', $_article, $link);
    
    $_carry = file_get_contents($link[1]);
    
    $_carry = iconv('windows-1251', 'UTF-8', $_carry);
    
    preg_match('|itemprop="description">(.*?)<br>|is', $_carry, $description);
    preg_match('|url="(.*?)"|is', $_carry, $file);
	
	if (strripos($_carry, 'url=')){
	
	$md5 = md5(rand(1, 9999).$go);
    $translit = str_replace(' ', '_', transliterate($name[1])).'_'.rand(1, 9999);
    $uniqueness = md5(str_replace(' ', '_', transliterate($name[1])));
    
    $quantity = $mysqli -> query("select count(*) from ero_files where uniqueness = '$uniqueness' and server = 'pornolomka.mobi'") -> fetch_row();
    
    if ($quantity[0] == 0) {
	
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', file_get_contents('http://pornolomka.mobi'.$poster[1]));

    $image = new SimpleImage();
    $image->load($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');
    $image->resize($width_S, $height_S);
    $image->save($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');

    if ($settings['water'] == 1)
	water($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/designs/water.png');
	
	$yd_sql = 0;
	
    if ($_POST['mode'] == '2') {

    $yd = yd_upload_file(trim($file[1]), 'mp4', $settings['OAuth']);
    $recoil = $yd;
    $yd_sql = 1;
	
    } else if ($_POST['mode'] == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents($file[1]));
    
    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }
    
    if (strripos($description[1], 'dle')) $description[1] = $name[1];
        
    $sample = $description[1];
    
    if ($_POST['selection'] == 0) $category = categories($sample); else $category = abs(intval($_POST['category']));

    if ($_POST['translate'] == 2) {
        
        $name[1] = translate($name[1], 'ru', 'en');
        $description[1] = translate($description[1], 'ru', 'en');
        
    }
    
    $mysqli -> query("INSERT INTO ero_files SET yd = '$yd_sql', category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = '".$file[1]."', server = 'pornolomka.mobi', tags = '".tags(trim($description[1]))."', name = '".$name[1]."', description = '".trim($description[1])."', translit = '$translit', duration = '".trim($duration[1])."', date = '$publish'");
    
    $collected++;	
	
	}
	
	}
	
	sleep($_POST['delay']);
	
	}
	
	} else if ($_POST['server'] == 8) {
    
    $collected = 0;
    
    for($go = 0; $go < $_POST['results']; $go++){
	
    $categories = array(
    'https://pornosto.com/yaponskoe/', 
    'https://pornosto.com/v_chulkah/', 
    'https://pornosto.com/hudye/', 
    'https://pornosto.com/studencheskoe/', 
    'https://pornosto.com/sekretarshi/', 
    'https://pornosto.com/bryzgi_spermy/', 
    'https://pornosto.com/spyaschie/', 
    'https://pornosto.com/strapon/', 
    'https://pornosto.com/tolstye/', 
    'https://pornosto.com/pyanye/',
    'https://pornosto.com/premium/',
    'https://pornosto.com/pikap/',
    'https://pornosto.com/orgii/',
    'https://pornosto.com/orgazmy/',
    'https://pornosto.com/chernokojie/',
    'https://pornosto.com/publichnoe/',
    'https://pornosto.com/na_prirode/',
    'https://pornosto.com/masturbaciya/',
    'https://pornosto.com/oralnoe/',
    'https://pornosto.com/molodenkie/',
    'https://pornosto.com/mulatki/',
    'https://pornosto.com/aziatki/',
    'https://pornosto.com/analnoe/',
    'https://pornosto.com/bdsm/',
    'https://pornosto.com/blondinki/',
    'https://pornosto.com/bolshie_siski/',
    'https://pornosto.com/bolshie_chleny/',
    'https://pornosto.com/bryunetki/',
    'https://pornosto.com/v_vannoi/',
    'https://pornosto.com/gruppovoe/',
    'https://pornosto.com/v_dva_stvola/',
    'https://pornosto.com/domashnee/',
    'https://pornosto.com/dominirovanie/',
    'https://pornosto.com/drochat/'
    );
    
    $array = array_rand($categories, 2);

    $_carry = file_get_contents($categories[$array[0]].rand(1, 5));
    
    preg_match_all('|<div class="thumb">(.*?)</div>|is', $_carry, $_uri);
    
    $_go = $_uri[0][rand(0, 11)];
    
    preg_match('|alt="(.*?)"|is', $_go, $name);
    preg_match('|href="(.*?)"|is', $_go, $_href); 
    preg_match('|src="(.*?)"|is', $_go, $poster);
    
    $_carry = file_get_contents(trim($_href[1]));
    
    preg_match('|<div class="f-desc full-text clearfix" style="padding-top: 0px;">(.*?)</div>|is', $_carry, $description);
    preg_match('|file:"(.*?)"|is', $_carry, $file);
    preg_match('|Длительность:(.*?)Просмотров|is', $_carry, $duration);
    
    $duration[1] = str_replace('|', '', $duration[1]);
    $duration[1] = str_replace(' ', '', $duration[1]);
	
	if (strripos($_carry, 'file:"')){
	
	$md5 = md5(rand(1, 9999).$go);
    $translit = str_replace(' ', '_', transliterate($name[1])).'_'.rand(1, 9999);
    $uniqueness = md5(str_replace(' ', '_', transliterate($name[1])));
    
    $quantity = $mysqli -> query("select count(*) from ero_files where uniqueness = '$uniqueness' and server = 'pornosto.com'") -> fetch_row();
    
    if ($quantity[0] == 0) {
	
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', file_get_contents($poster[1]));

    $image = new SimpleImage();
    $image->load($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');
    $image->resize($width_S, $height_S);
    $image->save($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');

    if ($settings['water'] == 1)
	water($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/designs/water.png');
	
	$yd_sql = 0;
	
    if ($_POST['mode'] == '2') {

    $yd = yd_upload_file(trim($file[1]), 'mp4', $settings['OAuth']);
    $recoil = $yd;
    $yd_sql = 1;
	
    } else if ($_POST['mode'] == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents($file[1]));
    
    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }
        
    $sample = $description[1];
    
    if ($_POST['selection'] == 0) $category = categories($sample); else $category = abs(intval($_POST['category']));

    if ($_POST['translate'] == 2) {
        
        $name[1] = translate($name[1], 'ru', 'en');
        $description[1] = translate($description[1], 'ru', 'en');
        
    }
    
    $mysqli -> query("INSERT INTO ero_files SET yd = '$yd_sql', category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = '".$file[1]."', server = 'pornosto.com', tags = '".tags(trim($description[1]))."', name = '".$name[1]."', description = '".trim($description[1])."', translit = '$translit', duration = '".trim($duration[1])."', date = '$publish'");
    
    $collected++;	
	
	}
	
	}
	
	sleep($_POST['delay']);
	
	}
	
	} else if ($_POST['server'] == 9) {
    
    $collected = 0;
    
    for($go = 0; $go < $_POST['results']; $go++){
	
    $_carry = file_get_contents('http://www.russpornotube.com/'.rand(1, 1525).'/');

    preg_match('|<div class="th-videos" id="list_videos_newest_videos_list">(.*?)<div id="list_videos_newest_videos_list_pagination" class="pager">|is', $_carry, $_uri);
    preg_match_all('|<a href="(.*?)">|is', $_uri[0], $_get);

    $_go = rand(0, 19);
    $_carry = file_get_contents($_get[1][$_go]);

    preg_match('|<title>(.*?)</title>|is', $_carry, $name);
    preg_match('|<meta name="description" content="(.*?)"/>|is', $_carry, $description);
    preg_match('|http://www.russpornotube.com/get_file/(.*?)480.mp4|is', $_carry, $file);
    preg_match('|preview_url: \'(.*?).jpg|is', $_carry, $poster);
    preg_match('|<span class="dur">(.*?)</span>|is', $_carry, $duration);

    $duration[1] = str_replace(' м. ', ':', $duration[1]);
    $duration[1] = str_replace('с', '', $duration[1]);
	$duration[1] = trim($duration[1]);
	
	if (strripos($_carry, 'preview_url:')){
	
	$md5 = md5(rand(1, 9999).$go);
    $translit = str_replace(' ', '_', transliterate($name[1])).'_'.rand(1, 9999);
    $uniqueness = md5(str_replace(' ', '_', transliterate($name[1])));
    
    $quantity = $mysqli -> query("select count(*) from ero_files where uniqueness = '$uniqueness' and server = 'russpornotube.com'") -> fetch_row();
    
    if ($quantity[0] == 0) {
	
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', file_get_contents($poster[1].'.jpg'));

    $image = new SimpleImage();
    $image->load($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');
    $image->resize($width_S, $height_S);
    $image->save($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');

    if ($settings['water'] == 1)
	water($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/designs/water.png');
	
	$yd_sql = 0;
	
    if ($_POST['mode'] == '2') {

    $yd = yd_upload_file(trim($file[0]), 'mp4', $settings['OAuth']);
    $recoil = $yd;
    $yd_sql = 1;
	
    } else if ($_POST['mode'] == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents($file[0]));
    
    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }
        
    $sample = $description[1];
    
    if ($_POST['selection'] == 0) $category = categories($sample); else $category = abs(intval($_POST['category']));

    if ($_POST['translate'] == 2) {
        
        $name[1] = translate($name[1], 'ru', 'en');
        $description[1] = translate($description[1], 'ru', 'en');
        
    }
    
    $mysqli -> query("INSERT INTO ero_files SET yd = '$yd_sql', category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = '".$file[0]."', server = 'russpornotube.com', tags = '".tags(trim($description[1]))."', name = '".$name[1]."', description = '".trim($description[1])."', translit = '$translit', duration = '".trim($duration[1])."', date = '$publish'");
    
    $collected++;	
	
	}
	
	}
	
	sleep($_POST['delay']);
	
	}
	
	} else if ($_POST['server'] == 10) {
    
    $collected = 0;
    
    for($go = 0; $go < $_POST['results']; $go++){
	
    $categories = array('porno-hd-720p', 
    'amerikan_porn', 
    'gang-bang', 
    'incest-amerikanskiy', 
    'bolshie-siski', 
    'gruppovuha', 
    'porno_2018', 
    'porno-4k-ultra-full-hd', 
    'anal', 
    'domashnee', 
    'porno_2019', 
    'chernye'
    );
    
    $array = array_rand($categories, 2);
    
    $_carry = file_get_contents('http://rsuka.tv/'.$categories[$array[0]].'/page/'.rand(1, 50).'/');
    
    preg_match_all('|<div class="item">(.*?)<div class="item">|is', $_carry, $_uri);

    $_go = $_uri[0][rand(0, 11)];

    preg_match('|<div class="item-title">(.*?)</div>|is', $_go, $name);
    preg_match('|href="(.*?)"|is', $_go, $_href); 
    preg_match('|data-original="(.*?)"|is', $_go, $poster);
    preg_match('|<div class="item-meta meta-time">(.*?)</div>|is', $_go, $duration);
    
    $_carry = file_get_contents(trim($_href[1]));
    
    preg_match('|<source src="(.*?)"|is', $_carry, $file);
    
	if (strripos($_carry, '<source src="') and !strripos($file[1], 'newstream') and $duration[1]){
	
	$md5 = md5(rand(1, 9999).$go);
    $translit = str_replace(' ', '_', transliterate($name[1])).'_'.rand(1, 9999);
    $uniqueness = md5(str_replace(' ', '_', transliterate($name[1])));
    
    $quantity = $mysqli -> query("select count(*) from ero_files where uniqueness = '$uniqueness' and server = 'rsuka.tv'") -> fetch_row();
    
    if ($quantity[0] == 0) {
	
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', file_get_contents($poster[1]));

    $image = new SimpleImage();
    $image->load($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');
    $image->resize($width_S, $height_S);
    $image->save($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');

    if ($settings['water'] == 1)
	water($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/designs/water.png');
	
	$yd_sql = 0;
	
    if ($_POST['mode'] == '2') {

    $yd = yd_upload_file(trim($file[1]), 'mp4', $settings['OAuth']);
    $recoil = $yd;
    $yd_sql = 1;
	
    } else if ($_POST['mode'] == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents($file[1]));
    
    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }
        
    $sample = $name[1];
    
    if ($_POST['selection'] == 0) $category = categories($sample); else $category = abs(intval($_POST['category']));

    if ($_POST['translate'] == 2) {
        
        $name[1] = translate($name[1], 'ru', 'en');
        
    }
    
    $mysqli -> query("INSERT INTO ero_files SET yd = '$yd_sql', category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = '".$file[1]."', server = 'rsuka.tv', tags = '".tags(trim($name[1]))."', name = '".$name[1]."', description = '".trim($name[1])."', translit = '$translit', duration = '".trim($duration[1])."', date = '$publish'");
    
    $collected++;	
	
	}
	
	}
	
	sleep($_POST['delay']);
	
	}
	
	} else if ($_POST['server'] == 11) {
    
    $collected = 0;
    
    for($go = 0; $go < $_POST['results']; $go++){
	
    $categories = array('Domashnee-porno', 
    'Analynyy-seks', 
    'Arabskoe', 
    'Aziatki', 
    'Bolyshie-popy', 
    'CHernokoghie', 
    'Mulatki', 
    'Blondinki', 
    'Minet-i-sperma', 
    'Bryunetki', 
    'Bolyshie-sisyki', 
    'V-mashine', 
    'V-kolledghe',
    'V-bolynice', 
    'Tolstushki', 
    '2-devki-i-pareny', 
    'Trahayutsya-tolpoy', 
    'Gruppovuha', 
    'Volosatye', 
    'Hardkor-seks', 
    'Indiyskoe', 
    'Latinskoe', 
    'Lesbiyanki', 
    'Belye-i-chulki', 
    'Zrelye-ghenschiny', 
    'Mamochki-GHeny', 
    'V-ofise', 
    'Starye-i-molodye',
    'Vecherinki',
    'Ryghie',
    'Ot-pervogo-lica',
    'Devushki-solo',
    'Molodye-devushki',
    'V-uniforme',
    'Podglyadyvaniya',
    'Vebkamery'
    );

    $array = array_rand($categories, 2);
    
    $_carry = file_get_contents('http://oxtube.tv/porno/'.$categories[$array[0]].'/'.rand(1, 13).'.html');
    
    preg_match_all('|<div class="video"><div>(.*?)<div class="video"><div>|is', $_carry, $_url);
    
    $array = rand(0, 23);

    preg_match('|<span class="duration">(.*?)</span>|is', $_url[0][$array], $duration);
    preg_match('|<a href="(.*?)"|is', $_url[1][rand(0, 9)], $_link);
    
    $_carry = file_get_contents(trim($_link[1].'?online=1'));
    
    preg_match('|<h1>(.*?)</h1>|is', $_carry, $name);
    preg_match('|file:"(.*?)"|is', $_carry, $file);
    preg_match('|poster:"(.*?)"|is', $_carry, $poster);
    
	if (strripos($_carry, 'poster:"') and $duration[1]){
	
	$md5 = md5(rand(1, 9999).$go);
    $translit = str_replace(' ', '_', transliterate($name[1])).'_'.rand(1, 9999);
    $uniqueness = md5(str_replace(' ', '_', transliterate($name[1])));
    
    $quantity = $mysqli -> query("select count(*) from ero_files where uniqueness = '$uniqueness' and server = 'oxtube.tv'") -> fetch_row();
    
    if ($quantity[0] == 0) {
	
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', file_get_contents($poster[1]));

    $image = new SimpleImage();
    $image->load($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');
    $image->resize($width_S, $height_S);
    $image->save($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');

    if ($settings['water'] == 1)
	water($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/designs/water.png');
	
	$yd_sql = 0;
	
    if ($_POST['mode'] == '2') {

    $yd = yd_upload_file(trim($file[1]), 'mp4', $settings['OAuth']);
    $recoil = $yd;
    $yd_sql = 1;
	
    } else if ($_POST['mode'] == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents($file[1]));
    
    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }
        
    $sample = $name[1];
    
    if ($_POST['selection'] == 0) $category = categories($sample); else $category = abs(intval($_POST['category']));

    if ($_POST['translate'] == 2) {
        
        $name[1] = translate($name[1], 'ru', 'en');
        
    }
    
    $mysqli -> query("INSERT INTO ero_files SET yd = '$yd_sql', category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = '".$file[1]."', server = 'oxtube.tv', tags = '".tags(trim($name[1]))."', name = '".$name[1]."', description = '".trim($name[1])."', translit = '$translit', duration = '".trim($duration[1])."', date = '$publish'");
    
    $collected++;	
	
	}
	
	}
	
	sleep($_POST['delay']);
	
	}
	
	} else if ($_POST['server'] == 12) {
    
    $collected = 0;
    
    for($go = 0; $go < $_POST['results']; $go++){
	
    $_carry = file_get_contents('http://hentai-x.ru/page-uncenz.php?id='.rand(1, 20));
        
    preg_match_all('|<table>(.*?)</table>|is', $_carry, $match);
    
    $i = $match[0][rand(1, 9)];
        
    preg_match("|src='(.*?)'|is", $i, $src);    #http://hentai-x.ru/
    preg_match('|Описание хентая:</span></p>(.*?)</p>|is', $i, $description);
    preg_match("|href = '(.*?)'|is", $i, $href);
       
    $description[1] = str_replace('<p>', '', $description[1]);
        
    $_carry = file_get_contents('http://hentai-x.ru/'.$href[1]);
        
    preg_match('|<title>(.*?)</title>|is', $_carry, $name);
    preg_match('|"video": "(.*?)"|is', $_carry, $file);
    preg_match('|Продолжительность:</span>(.*?)</p>|is', $_carry, $duration);
    
	if ($file[1] and $duration[1] and $name[1]){
	
	$md5 = md5(rand(1, 9999).$go);
    $translit = str_replace(' ', '_', transliterate($name[1])).'_'.rand(1, 9999);
    $uniqueness = md5(str_replace(' ', '_', transliterate($name[1])));
    
    $quantity = $mysqli -> query("select count(*) from ero_files where uniqueness = '$uniqueness' and server = 'hentai-x.ru'") -> fetch_row();
    
    if ($quantity[0] == 0) {
	
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', file_get_contents('http://hentai-x.ru/'.$src[1]));

    $image = new SimpleImage();
    $image->load($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');
    $image->resize($width_S, $height_S);
    $image->save($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');

    if ($settings['water'] == 1)
	water($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/designs/water.png');
	
	$yd_sql = 0;
	
    if ($_POST['mode'] == '2') {

    $yd = yd_upload_file(trim($file[1]), 'mp4', $settings['OAuth']);
    $recoil = $yd;
    $yd_sql = 1;
	
    } else if ($_POST['mode'] == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents($file[1]));
    
    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }
        
    $sample = $description[1];
    
    if ($_POST['selection'] == 0) $category = categories($sample); else $category = abs(intval($_POST['category']));

    if ($_POST['translate'] == 2) {
        
        $name[1] = translate($name[1], 'ru', 'en');
        $description[1] = translate($description[1], 'ru', 'en');
        
    }
    
    $mysqli -> query("INSERT INTO ero_files SET yd = '$yd_sql', category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = '".$file[1]."', server = 'hentai-x.ru', tags = '".tags(trim($description[1]))."', name = '".$name[1]."', description = '".trim($description[1])."', translit = '$translit', duration = '".trim($duration[1])."', date = '$publish'");
    
    $collected++;	
	
	}
	
	}
	
	sleep($_POST['delay']);
	
	}
	
	} else if ($_POST['server'] == 13) {
    
    $collected = 0;
    
    for($go = 0; $go < $_POST['results']; $go++){
        
    $categories = array('hd', 
    'aziatki', 
    'analnyi_seks', 
    'bolshie-siski/', 
    'bolshie-chleny', 
    'bukkake', 
    'grybij_seks', 
    'gruppovoj_seks', 
    'domashnee_porno', 
    'zrelye', 
    'masturbaciya', 
    'mezhrassovoe', 
    'russkoe',
    'sperma', 
    'seks-igrushki', 
    'tolstuhi'
    );
    
    $array = array_rand($categories, 2);

    $_carry = file_get_contents('https://ebun.me/'.$categories[$array[0]].'/page/'.rand(1, 5).'/');
    
    preg_match_all('|<article class="shortstory cf">(.*?)</article>|is', $_carry, $article);
    
    $array = rand(1, 23);
    
    preg_match('|<div class="video_time">(.*?)</div>|is', $article[1][$array], $duration);
    preg_match('|<img src="(.*?)"|is', $article[1][$array], $poster);
    preg_match('|alt="(.*?)"|is', $article[1][$array], $name);
    preg_match('|<a href="(.*?)"|is', $article[1][$array], $href);
    
    $_carry = file_get_contents($href[1]);
    
    preg_match('|file:"(.*?)"|is', $_carry, $file);
    preg_match('|itemprop="description">(.*?)<script|is', $_carry, $description);
    
    if (strripos($_carry, 'file:')){
        
    $md5 = md5(rand(1, 9999).$go);
    $translit = str_replace(' ', '_', transliterate($name[1])).'_'.rand(1, 9999);
    $uniqueness = md5(str_replace(' ', '_', transliterate($name[1])));
    
    $quantity = $mysqli -> query("select count(*) from ero_files where uniqueness = '$uniqueness' and server = 'ebun.me'") -> fetch_row();
    
    if ($quantity[0] == 0) {
        
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', file_get_contents('https://ebun.me'.$poster[1]));

    $image = new SimpleImage();
    $image->load($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');
    $image->resize($width_S, $height_S);
    $image->save($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');

    if ($settings['water'] == 1)
	water($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/designs/water.png');
	
	$yd_sql = 0;
	
    if ($_POST['mode'] == '2') {

    $yd = yd_upload_file('https://ebun.me'.trim($file[1]), 'mp4', $settings['OAuth']);
    $recoil = $yd;
    $yd_sql = 1;
	
    } else if ($_POST['mode'] == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents('https://ebun.me'.$file[1]));
    
    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }
        
    $sample = $name[1];     
        
    if ($_POST['selection'] == 0) $category = categories($sample); else $category = abs(intval($_POST['category']));

    if ($_POST['translate'] == 2) {
        
        $name[1] = translate($name[1], 'ru', 'en');
        $description[1] = translate(trim($description[1]), 'ru', 'en');
    }
    
    $mysqli -> query("INSERT INTO ero_files SET yd = '$yd_sql', category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = 'https://ebun.me".$file[1]."', server = 'ebun.me', tags = '".tags($description[1])."', name = '".$name[1]."', description = '".trim($description[1])."', translit = '$translit', duration = '".$duration[1]."', date = '$publish'");
        
    $collected++;
    
    }
    
    sleep($_POST['delay']);
    
    }
    
    }
    
    } else if ($_POST['server'] == 14) {
    
    $collected = 0;
    
    for($go = 0; $go < $_POST['results']; $go++){
        
    $categories = array('pov', 
    'anal', 
    'blonde', 
    'bigass', 
    'bigtits', 
    'big_cock', 
    'brunette', 
    'deep_throat', 
    'long_hair', 
    'zhmzh', 
    'beauty', 
    'cunnuslingo', 
    'lesbians',
    'masturbation', 
    'interracial', 
    'blowjob', 
    'teen', 
    'riding', 
    'shavedpussy', 
    'doggystyle', 
    'cumshot', 
    'creampie', 
    'cumshots_face', 
    'fingering', 
    'hardkor', 
    'skinnygirls', 
    'kiss'
    );
    
    $array = array_rand($categories, 2);

    $_carry = file_get_contents('http://vaginke.com/'.$categories[$array[0]].'/page'.rand(1, 20).'/');
    
    preg_match_all('|<div class="video"><div>(.*?)<div class="view">|is', $_carry, $_url);
    
    $_article = $_url[1][rand(1, 14)];
    
    preg_match('|alt="(.*?)"|is', $_article, $name);
    preg_match('|<span class="duration">(.*?)</span>|is', $_article, $duration);
    preg_match('|href="(.*?)"|is', $_article, $href);
    
    $_carry = file_get_contents('http://vaginke.com'.$href[1]);
    
    preg_match('|file":"(.*?)"|is', $_carry, $file);
    preg_match('|poster":"(.*?)"|is', $_carry, $poster);
    preg_match('|<div class="center"><div class="opisanie">(.*?)</div></div>|is', $_carry, $description);
    
    if (!$description[1]) $description[1] = $name[1];
    
    if (strripos($_carry, 'file":"')){
        
    $md5 = md5(rand(1, 9999).$go);
    $translit = str_replace(' ', '_', transliterate($name[1])).'_'.rand(1, 9999);
    $uniqueness = md5(str_replace(' ', '_', transliterate($name[1])));
    
    $quantity = $mysqli -> query("select count(*) from ero_files where uniqueness = '$uniqueness' and server = 'vaginke.com'") -> fetch_row();
    
    if ($quantity[0] == 0) {
        
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', file_get_contents($poster[1]));

    $image = new SimpleImage();
    $image->load($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');
    $image->resize($width_S, $height_S);
    $image->save($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');

    if ($settings['water'] == 1)
	water($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/designs/water.png');
	
	$yd_sql = 0;
	
    if ($_POST['mode'] == '2') {

    $yd = yd_upload_file(trim($file[1]), 'mp4', $settings['OAuth']);
    $recoil = $yd;
    $yd_sql = 1;
	
    } else if ($_POST['mode'] == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents($file[1]));
    
    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }
        
    $sample = $description[1];     
        
    if ($_POST['selection'] == 0) $category = categories($sample); else $category = abs(intval($_POST['category']));

    if ($_POST['translate'] == 2) {
        
        $name[1] = translate($name[1], 'ru', 'en');
        $description[1] = translate(trim($description[1]), 'ru', 'en');
    }
    
    $mysqli -> query("INSERT INTO ero_files SET yd = '$yd_sql', category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = '".$file[1]."', server = 'vaginke.com', tags = '".tags($description[1])."', name = '".$name[1]."', description = '".trim($description[1])."', translit = '$translit', duration = '".$duration[1]."', date = '$publish'");
        
    $collected++;
    
    }
    
    sleep($_POST['delay']);
    
    }
    
    }
    
    } else if ($_POST['server'] == 15) {
    
    $collected = 0;
    
    for($go = 0; $go < $_POST['results']; $go++){
        
    $categories = array('mom', 
    'beautiful-girl', 
    'mature', 
    'teen', 
    'japanese', 
    'milf', 
    'young', 
    'russian', 
    'beauty', 
    'old-young', 
    'anal-fuck', 
    'housewife', 
    'orgasm',
    'amateur', 
    'wife', 
    'punishment', 
    'public', 
    'curvy', 
    'chubby', 
    'caught', 
    'shemale', 
    'asian', 
    'compilation', 
    'anal', 
    'big-pussy', 
    'big-ass', 
    'interracial', 
    'erotic', 
    'whore-wives', 
    'big-cock', 
    'gorgeous', 
    'gangbang', 
    'cuckold', 
    'tiny', 
    'creampie', 
    'threesome', 
    'casting', 
    'stockings', 
    'lesbian', 
    'dildo'
    );
    
    $array = array_rand($categories, 2);

    $_carry = file_get_contents('https://xcafe.com/videos/'.$categories[$array[0]].'/'.rand(2, 20).'/');
    
    preg_match_all('|<li data-video(.*?)<li data-video|is', $_carry, $video);
    
    $array = rand(2, 48);
    
    preg_match('|alt="(.*?)"|is', $video[1][$array], $name);
    preg_match('|<span class="time">(.*?)</span>|is', $video[1][$array], $duration);
    preg_match('|<a href="(.*?)"|is', $video[1][$array], $href);
    
    $_carry = file_get_contents('https://xcafe.com'.$href[1]);
    
    preg_match('|<meta property="og:image" content="(.*?)"|is', $_carry, $poster);
    preg_match('|<meta name="description" content="(.*?)"|is', $_carry, $description);
    preg_match('|<source id="video_source_1" src="(.*?)"|is', $_carry, $file);
    
    if (strripos($_carry, 'og:image') and $poster[1]){
        
    $md5 = md5(rand(1, 9999).$go);
    $translit = str_replace(' ', '_', transliterate($name[1])).'_'.rand(1, 9999);
    $uniqueness = md5(str_replace(' ', '_', transliterate($name[1])));
    
    $quantity = $mysqli -> query("select count(*) from ero_files where uniqueness = '$uniqueness' and server = 'xcafe.com'") -> fetch_row();
    
    if ($quantity[0] == 0) {
        
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', file_get_contents($poster[1]));

    $image = new SimpleImage();
    $image->load($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');
    $image->resize($width_S, $height_S);
    $image->save($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');

    if ($settings['water'] == 1)
	water($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/designs/water.png');
	
	$yd_sql = 0;
	
    if ($_POST['mode'] == '2') {

    $yd = yd_upload_file(trim($file[1]), 'mp4', $settings['OAuth']);
    $recoil = $yd;
    $yd_sql = 1;
	
    } else if ($_POST['mode'] == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents($file[1]));
    
    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }
        
    $sample = $description[1];     
        
    if ($_POST['selection'] == 0) $category = categories($sample); else $category = abs(intval($_POST['category']));

    if ($_POST['translate'] == 2) {
        
        $name[1] = translate($name[1], 'ru', 'en');
        $description[1] = translate(trim($description[1]), 'ru', 'en');
    }
    
    $mysqli -> query("INSERT INTO ero_files SET yd = '$yd_sql', category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = '".$file[1]."', server = 'xcafe.com', tags = '".tags($description[1])."', name = '".$name[1]."', description = '".trim($description[1])."', translit = '$translit', duration = '".$duration[1]."', date = '$publish'");
        
    $collected++;
    
    }
    
    sleep($_POST['delay']);
    
    }
    
    }
    
    }   else if ($_POST['server'] == 16) {
    
    $collected = 0;
    
    for($go = 0; $go < $_POST['results']; $go++){
        
	$categories = array('incest', 
    'big-tits', 
    'pussy-licking', 
    'teen', 
    'big-dick', 
    'squirting', 
    'hardcore', 
    'pornstars', 
    'sex-at-work', 
    'big-ass', 
    'romantic', 
    'mature', 
    'anal',
    'russian-porn', 
    'groupsex', 
    'creampie', 
    'ebony', 
    'cumshot', 
    'lesbian', 
    'blowjob', 
    'brunette', 
    'deep-throat', 
    'pov', 
    'blonde', 
    'handjob'
    );
    
    $array = array_rand($categories, 2);

    $_carry = file_get_contents('https://house.porn/category/'.$categories[$array[0]].'?page='.rand(2, 10));
    
    preg_match_all('|<div class="item">(.*?)<div class="item">|is', $_carry, $video);
    
    $array = rand(1, 23);
    
    preg_match('|alt="(.*?)"|is', $video[1][$array], $name);
    preg_match('|<img src="/themes/web/images/timer.png" alt=""/>(.*?)</div>|is', $video[1][$array], $duration);
    preg_match('|<a href="(.*?)"|is', $video[1][$array], $href);
    
    $_carry = file_get_contents('https://house.porn'.$href[1]);
    
    preg_match('|<meta property="og:image" content="(.*?)"|is', $_carry, $poster);
    preg_match('|itemprop="description">(.*?)<br/><br/>|is', $_carry, $description);
    preg_match('|<source data-fluid-hd src="(.*?)"|is', $_carry, $file);    #https://house.porn
    
    if (strripos($_carry, 'og:image') and $poster[1] and $name[1] and $description[1] and $file[1]){
        
    $md5 = md5(rand(1, 9999).$go);
    $translit = str_replace(' ', '_', transliterate($name[1])).'_'.rand(1, 9999);
    $uniqueness = md5(str_replace(' ', '_', transliterate($name[1])));
    
    $quantity = $mysqli -> query("select count(*) from ero_files where uniqueness = '$uniqueness' and server = 'house.porn'") -> fetch_row();
    
    if ($quantity[0] == 0) {
        
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', file_get_contents($poster[1]));

    $image = new SimpleImage();
    $image->load($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');
    $image->resize($width_S, $height_S);
    $image->save($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');

    if ($settings['water'] == 1)
	water($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/designs/water.png');
	
	$yd_sql = 0;
	
    if ($_POST['mode'] == '2') {

    $yd = yd_upload_file('https://house.porn'.trim($file[1]), 'mp4', $settings['OAuth']);
    $recoil = $yd;
    $yd_sql = 1;
	
    } else if ($_POST['mode'] == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents('https://house.porn'.$file[1]));
    
    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }
        
    $sample = $description[1];     
        
    if ($_POST['selection'] == 0) $category = categories($sample); else $category = abs(intval($_POST['category']));

    if ($_POST['translate'] == 2) {
        
        $name[1] = translate($name[1], 'ru', 'en');
        $description[1] = translate(trim($description[1]), 'ru', 'en');
    }
    
    $mysqli -> query("INSERT INTO ero_files SET yd = '$yd_sql', category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = 'https://house.porn".$file[1]."', server = 'house.porn', tags = '".tags($description[1])."', name = '".$name[1]."', description = '".trim($description[1])."', translit = '$translit', duration = '".trim($duration[1])."', date = '$publish'");
        
    $collected++;
    
    }
    
    sleep($_POST['delay']);
    
    }
    
    }
    
    }   else if ($_POST['server'] == 17) {
    
    $collected = 0;
    
    for($go = 0; $go < $_POST['results']; $go++){
        
    $categories = array('Aziatki', 
    'Anal', 
    'Armyanki', 
    'BDSM', 
    'Blondinki', 
    'Bolqshie_sisqki', 
    'Bolqshoj_chlen', 
    'Bryunetki', 
    'Veb-kamery', 
    'Vo_vse_mesta', 
    'Gej', 
    'Glotaet_spermu', 
    'Gruppovuha', 
    'MZHM', 
    'ZHMZH', 
    'Dvojnoe_proniknovenie', 
    'ZHeny', 
    'ZHestkij_seks', 
    'Zrelyee_zhenwincy', 
    'Seks_igrushki', 
    'Izmena', 
    'Incest', 
    'Konchayut_vnutrq', 
    'Krasivoe', 
    'Krupnym_planom', 
    'Kuni', 
    'Lesbi', 
    'Domashnee', 
    'Massazh_i_seks', 
    'Minet', 
    'Molodyee', 
    'Na_kablukah', 
    'Na_prirode', 
    'POV', 
    'Negry', 
    'Pqyanyee', 
    'CHulki', 
    'Hudyee', 
    'Seks_stoya', 
    'Transvestity', 
    'Tolstushki', 
    'Sportsmenki', 
    'Sperma_v_rot', 
    'Skvirtuyuwie', 
    'Ryzhenqkie', 
    'Russkoe');

    $array = array_rand($categories, 2);

    $_carry = file_get_contents('http://porno-kisa.com/category/'.$categories[$array[0]]);
    
    preg_match_all("|<div class='kisakshka'>(.*?)<div class='kisakshka'>|is", $_carry, $view);
    
    $array = rand(2, 20);
    
    preg_match("|<a href='(.*?)'>|is", $view[1][$array], $href);
    
    $_carry = file_get_contents('http://porno-kisa.com'.$href[1]);
    
    preg_match("|file:'(.*?)',|is", $_carry, $file);    #http://porno-kisa.com
    preg_match("|<title>(.*?)</title>|is", $_carry, $name);
    preg_match("|poster:'(.*?)',|is", $_carry, $poster);  #http://porno-kisa.com
    preg_match("|<p><span>Длительность:(.*?)</span><span>|is", $_carry, $duration);
    
    if (strripos($_carry, 'file:') and $poster[1] and $name[1] and $duration[1] and $file[1]){
        
    $md5 = md5(rand(1, 9999).$go);
    $translit = str_replace(' ', '_', transliterate($name[1])).'_'.rand(1, 9999);
    $uniqueness = md5(str_replace(' ', '_', transliterate($name[1])));
    
    $quantity = $mysqli -> query("select count(*) from ero_files where uniqueness = '$uniqueness' and server = 'porno-kisa.com'") -> fetch_row();
    
    if ($quantity[0] == 0) {
        
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', file_get_contents('http://porno-kisa.com'.$poster[1]));

    $image = new SimpleImage();
    $image->load($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');
    $image->resize($width_S, $height_S);
    $image->save($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');

    if ($settings['water'] == 1)
	water($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/designs/water.png');
	
	$yd_sql = 0;
	
    if ($_POST['mode'] == '2') {

    $yd = yd_upload_file('http://porno-kisa.com'.trim($file[1]), 'mp4', $settings['OAuth']);
    $recoil = $yd;
    $yd_sql = 1;
	
    } else if ($_POST['mode'] == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents('http://porno-kisa.com'.$file[1]));
    
    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }
        
    $sample = $name[1];     
        
    if ($_POST['selection'] == 0) $category = categories($sample); else $category = abs(intval($_POST['category']));

    if ($_POST['translate'] == 2) {
        
        $name[1] = translate($name[1], 'ru', 'en');
        
    }
    
    $mysqli -> query("INSERT INTO ero_files SET yd = '$yd_sql', category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = 'http://porno-kisa.com".$file[1]."', server = 'porno-kisa.com', tags = '".tags($name[1])."', name = '".$name[1]."', description = '".trim($name[1])."', translit = '$translit', duration = '".trim($duration[1])."', date = '$publish'");
        
    $collected++;
    
    }
    
    sleep($_POST['delay']);
    
    }
    
    }
    
    }   else if ($_POST['server'] == 18) {
    
    $collected = 0;
    
    for($go = 0; $go < $_POST['results']; $go++){
        
    $categories = array('Ferro-Network', 
    'HD', 
    'anal', 
    'bdsm', 
    'aziatki', 
    'bolshie-zadnitsyi', 
    'bolshie-siski', 
    'bolshie-chlenyi', 
    'gruppovoe', 
    'domashnee-porno', 
    'zrelyie', 
    'intsest', 
    'konchil-vnutr',
    'lesbiyanki', 
    'mamki', 
    'masturbatsiya', 
    'mejrassovoe-porno', 
    'minet', 
    'molodenkie-devochki', 
    'molodyie-i-zrelyie', 
    'nijnee-bele', 
    'chastnoe-porno', 
    'russkoe-porno', 
    'porno-s-igrushkami', 
    'russkoe-porno', 
    'chastnoe-porno', 
    'tolstuhi', 
    'cheshskoe-porno'
    );

    $pages = array('0', '12', '24', '36', '48', '60', '72', '84', '96', '108', '120');
    $array = array_rand($categories, 2);
    $page = array_rand($pages, 2);
    
    $_carry = file_get_contents('https://zajka.org/cat/'.$categories[$array[0]].'/'.$pages[$page[0]]);
    
    preg_match_all('|<div class="tumb"><div>(.*?)<div class="views">|is', $_carry, $previews);
    
    $_carry = $previews[1][rand(1, 11)];
    
    preg_match('|<a href="(.*?)"|is', $_carry, $href);
    preg_match('|<span>(.*?)</span>|is', $_carry, $name);
    preg_match('|<span class="duration">(.*?)</span>|is', $_carry, $duration);
    preg_match('|<img src="(.*?)"|is', $_carry, $poster);   #   https://zajka.org   $poster[1]
    
    $_carry = file_get_contents('https://zajka.org'.$href[1]);
    
    preg_match('|<source src="(.*?)"|is', $_carry, $file);
    preg_match_all('|<div class="main">(.*?)</div>|is', $_carry, $description); #   $description[1][1]
    
    if (strripos($_carry, '<source') and $poster[1] and $name[1] and $duration[1] and $file[1]){
        
    $md5 = md5(rand(1, 9999).$go);
    $translit = str_replace(' ', '_', transliterate($name[1])).'_'.rand(1, 9999);
    $uniqueness = md5(str_replace(' ', '_', transliterate($name[1])));
    
    $quantity = $mysqli -> query("select count(*) from ero_files where uniqueness = '$uniqueness' and server = 'zajka.org'") -> fetch_row();
    
    if ($quantity[0] == 0) {
        
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', file_get_contents('https://zajka.org'.$poster[1]));

    $image = new SimpleImage();
    $image->load($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');
    $image->resize($width_S, $height_S);
    $image->save($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');

    if ($settings['water'] == 1)
	water($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/designs/water.png');
	
	$yd_sql = 0;
	
    if ($_POST['mode'] == '2') {

    $yd = yd_upload_file('https://zajka.org'.trim($file[1]), 'mp4', $settings['OAuth']);
    $recoil = $yd;
    $yd_sql = 1;
	
    } else if ($_POST['mode'] == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents('https://zajka.org'.$file[1]));
    
    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }
        
    $sample = $description[1][1];     
        
    if ($_POST['selection'] == 0) $category = categories($sample); else $category = abs(intval($_POST['category']));

    if ($_POST['translate'] == 2) {
        
        $name[1] = translate($name[1], 'ru', 'en');
        $description[1][1] = translate($description[1][1], 'ru', 'en');
    }
    
    $mysqli -> query("INSERT INTO ero_files SET yd = '$yd_sql', category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = 'https://zajka.org".$file[1]."', server = 'zajka.org', tags = '".tags($description[1][1])."', name = '".$name[1]."', description = '".trim($description[1][1])."', translit = '$translit', duration = '".trim($duration[1])."', date = '$publish'");
        
    $collected++;
    
    }
    
    sleep($_POST['delay']);
    
    }
    
    }
    
    }   else if ($_POST['server'] == 19) {
    
    $collected = 0;
    
    for($go = 0; $go < $_POST['results']; $go++){
        
    $categories = array('52', '62', '72', '133', '59', '129', '17', '87', '98', '28', '27', '36', '114', '109', '104', '130', '94', '74', '54', '29', '24', '9', '5', '23', '10', '15', '135', '30', '40', '50', '60', '80', '85', '90', '110', '88', '124', '51', '6', '21', '46', '42', '26', '31');
    
    $array = array_rand($categories, 2);
    
    $_carry = file_get_contents('http://gig.porn/c/'.$categories[$array[0]].'/'.rand(2, 30).'/');
    $_carry = preg_replace('|<script(.*?)</script>|is', '', $_carry);
    
    preg_match_all('|<div class="v sz"(.*?)<div class="v sz"|is', $_carry, $previews);
    
    $_carry = $previews[0][rand(0, 11)];
    
    preg_match('|src="(.*?)"|is', $_carry, $poster);
    preg_match('|href="(.*?)"|is', $_carry, $href);
    preg_match('|</span>(.*?)</b>|is', $_carry, $duration);
    
    $duration[1] = str_replace('/', '', $duration[1]);
    
    $_carry = file_get_contents('http://gig.porn'.$href[1]);
    $_carry = preg_replace('|<script(.*?)</script>|is', '', $_carry);

    preg_match('|<h1>(.*?)</h1>|is', $_carry, $name);
    preg_match('|<source src="(.*?)"|is', $_carry, $file);
    
    $name[1] = preg_replace('/([0-9|#][\x{20E3}])|[\x{00ae}|\x{00a9}|\x{203C}|\x{2047}|\x{2048}|\x{2049}|\x{3030}|\x{303D}|\x{2139}|\x{2122}|\x{3297}|\x{3299}][\x{FE00}-\x{FEFF}]?|[\x{2190}-\x{21FF}][\x{FE00}-\x{FEFF}]?|[\x{2300}-\x{23FF}][\x{FE00}-\x{FEFF}]?|[\x{2460}-\x{24FF}][\x{FE00}-\x{FEFF}]?|[\x{25A0}-\x{25FF}][\x{FE00}-\x{FEFF}]?|[\x{2600}-\x{27BF}][\x{FE00}-\x{FEFF}]?|[\x{2900}-\x{297F}][\x{FE00}-\x{FEFF}]?|[\x{2B00}-\x{2BF0}][\x{FE00}-\x{FEFF}]?|[\x{1F000}-\x{1F6FF}][\x{FE00}-\x{FEFF}]?/u', '', $name[1]);
    $name[1] = str_replace('Видео: ', '', $name[1]);
    
    if (strripos($_carry, '<source') and $poster[1] and $name[1] and $duration[1] and $file[1] and $href[1]){
        
    $md5 = md5(rand(1, 9999).$go);
    $translit = str_replace(' ', '_', transliterate($name[1])).'_'.rand(1, 9999);
    $uniqueness = md5(str_replace(' ', '_', transliterate($name[1])));
    
    $quantity = $mysqli -> query("select count(*) from ero_files where uniqueness = '$uniqueness' and server = 'gig.porn'") -> fetch_row();
    
    if ($quantity[0] == 0) {
        
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', file_get_contents('http://gig.porn'.$poster[1]));

    $image = new SimpleImage();
    $image->load($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');
    $image->resize($width_S, $height_S);
    $image->save($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');

    if ($settings['water'] == 1)
	water($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/designs/water.png');
	
	$yd_sql = 0;
	
    if ($_POST['mode'] == '2') {

    $yd = yd_upload_file('https://zajka.org'.trim($file[1]), 'mp4', $settings['OAuth']);
    $recoil = $yd;
    $yd_sql = 1;
	
    } else if ($_POST['mode'] == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents($file[1]));
    
    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }
        
    $sample = $name[1];     
        
    if ($_POST['selection'] == 0) $category = categories($sample); else $category = abs(intval($_POST['category']));

    if ($_POST['translate'] == 2) {
        
        $name[1] = translate($name[1], 'ru', 'en');
        
    }
    
    $mysqli -> query("INSERT INTO ero_files SET yd = '$yd_sql', category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = '".$file[1]."', server = 'gig.porn', tags = '".tags($name[1])."', name = '".$name[1]."', description = '".trim($name[1])."', translit = '$translit', duration = '".trim($duration[1])."', date = '$publish'");
        
    $collected++;
    
    }
    
    sleep($_POST['delay']);
    
    }
    
    }
    
    }   else if ($_POST['server'] == 20) {
    
    $collected = 0;
    
    for($go = 0; $go < $_POST['results']; $go++){
        
    $categories = array('arabskoe_porno', 
    'armyanskoe_porno', 
    'gruzinskoe_porno', 
    'domashnee_porno', 
    'indiyskoe_porno', 
    'kavkazskoe_porno', 
    'kirgizskoe_porno', 
    'raznoe_porno', 
    'russkoe_porno', 
    'tadjikskoe_porno', 
    'turetskoe_porno', 
    'uzbekskoe_porno', 
    'tsyiganskoe_porno',
    'chechenskoe_porno', 
    'yaponskoe_porno'
    );

    $array = array_rand($categories, 2);

    $_carry = file_get_contents('https://uzbum.net/'.$categories[$array[0]].'/?page='.rand(1, 9));
    
    preg_match_all('|<div class="video"><div>(.*?)</div></div></div>|is', $_carry, $_url);
    
    $_carry = $_url[1][rand(0, 11)];
    
    preg_match('|<span>(.*?)</span>|is', $_carry, $name);
    preg_match('|<span class="duration">(.*?)</span>|is', $_carry, $duration);
    preg_match('|<a href="(.*?)">|is', $_carry, $href);
    
    $_carry = file_get_contents('https://uzbum.net'.$href[1]);
    
    preg_match('|<meta name="description" content="(.*?)"|is', $_carry, $description);
    preg_match('|poster="(.*?)"|is', $_carry, $poster);
    preg_match('|<source src="(.*?)"|is', $_carry, $file);
    
    if (strripos($_carry, '<source') and $poster[1] and $name[1] and $duration[1] and $file[1] and $href[1]){
        
    $md5 = md5(rand(1, 9999).$go);
    $translit = str_replace(' ', '_', transliterate($name[1])).'_'.rand(1, 9999);
    $uniqueness = md5(str_replace(' ', '_', transliterate($name[1])));
    
    $quantity = $mysqli -> query("select count(*) from ero_files where uniqueness = '$uniqueness' and server = 'uzbum.net'") -> fetch_row();
    
    if ($quantity[0] == 0) {
        
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', file_get_contents($poster[1]));

    $image = new SimpleImage();
    $image->load($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');
    $image->resize($width_S, $height_S);
    $image->save($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');

    if ($settings['water'] == 1)
	water($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/designs/water.png');
	
	$yd_sql = 0;
	
    if ($_POST['mode'] == '2') {

    $yd = yd_upload_file($file[1], 'mp4', $settings['OAuth']);
    $recoil = $yd;
    $yd_sql = 1;
	
    } else if ($_POST['mode'] == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents($file[1]));
    
    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }
        
    $sample = $name[1];     
        
    if ($_POST['selection'] == 0) $category = categories($sample); else $category = abs(intval($_POST['category']));

    if ($_POST['translate'] == 2) {
        
        $description[1] = translate($description[1], 'ru', 'en');
        $name[1] = translate($name[1], 'ru', 'en');
        
    }
    
    $mysqli -> query("INSERT INTO ero_files SET yd = '$yd_sql', category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = '".$file[1]."', server = 'uzbum.net', tags = '".tags($description[1])."', name = '".$name[1]."', description = '".trim($description[1])."', translit = '$translit', duration = '".trim($duration[1])."', date = '$publish'");
        
    $collected++;
    
    }
    
    sleep($_POST['delay']);
    
    }
    
    }
    
    }   else if ($_POST['server'] == 21) {
    
    $collected = 0;
    
    for($go = 0; $go < $_POST['results']; $go++){
        
    $categories = array('handjob', 'blonde', 'brunette', 'big-ass', 'big-tits', 'pov', 'interracial', 'deep-throat', 'sex-toys', 'students', 'tattoo', 'asian', 'redhead', 'romantic', 'outdoor', 'groupsex', 'masturbation', 'russian-porn', 'teen', 'family', 'big-dick', 'hardcore', 'cumshot', 'anal', 'pussy-licking', 'small-tits', 'blowjob', 'creampie', 'lesbian', 'mature', 'threesome', 'ebony');
    $view_cat = $categories[rand(0, 29)];
    $_carry = file_get_contents('https://lab.porn/category/'.$view_cat.'?page='.rand(1, 5));

    preg_match_all('|<div class="item">(.*?)<div class="name">|is', $_carry, $item);

    $_carry = $item[1][rand(1, 23)];

    preg_match('|<a href="(.*?)"|is', $_carry, $href);
            
    $_carry = file_get_contents('https://lab.porn'.$href[1]);
            
    preg_match('|poster="(.*?)"|is', $_carry, $poster);
    preg_match('|<title>(.*?)</title>|is', $_carry, $name);
    preg_match('|<div class="video_description" itemprop="description">(.*?)<br/>|is', $_carry, $description);
    preg_match('|<source data-fluid-hd src="(.*?)"|is', $_carry, $file);
    preg_match('|<div class="length"><img src="/themes/web/images/timer.png" alt=""/>(.*?)</div>|is', $_carry, $duration);
            
    $description[1]   = trim($description[1]);
            
    if (!$description[1]) $description[1]   = $title[1];
    
    if (strripos($_carry, '<source') and $poster[1] and $name[1] and $duration[1] and $file[1] and $href[1]){
        
    $md5 = md5(rand(1, 9999).$go);
    $translit = str_replace(' ', '_', transliterate($name[1])).'_'.rand(1, 9999);
    $uniqueness = md5(str_replace(' ', '_', transliterate($name[1])));
    
    $quantity = $mysqli -> query("select count(*) from ero_files where uniqueness = '$uniqueness' and server = 'lab.porn'") -> fetch_row();
    
    if ($quantity[0] == 0) {
        
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', file_get_contents('https://lab.porn'.$poster[1]));

    $image = new SimpleImage();
    $image->load($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');
    $image->resize($width_S, $height_S);
    $image->save($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');

    if ($settings['water'] == 1)
	water($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/designs/water.png');
	
	$yd_sql = 0;
	
	if (5 > strlen($description[1])) $description[1] = $name[1];
	
    if ($_POST['mode'] == '2') {

    $yd = yd_upload_file('https://lab.porn'.$file[1], 'mp4', $settings['OAuth']);
    $recoil = $yd;
    $yd_sql = 1;
	
    } else if ($_POST['mode'] == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents('https://lab.porn'.$file[1]));
    
    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }
        
    $sample = $description[1];     
        
    if ($_POST['selection'] == 0) $category = categories($sample); else $category = abs(intval($_POST['category']));

    if ($_POST['translate'] == 2) {
        
        $description[1] = translate($description[1], 'ru', 'en');
        $name[1] = translate($name[1], 'ru', 'en');
        
    }
    
    $mysqli -> query("INSERT INTO ero_files SET yd = '$yd_sql', category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = 'https://lab.porn".$file[1]."', server = 'lab.porn', tags = '".tags($description[1])."', name = '".$name[1]."', description = '".trim($description[1])."', translit = '$translit', duration = '".trim($duration[1])."', date = '$publish'");
        
    $collected++;
    
    }
    
    sleep($_POST['delay']);
    
    }
    
    }
    
    }   else if ($_POST['server'] == 22) {
    
    $collected = 0;
    
    for($go = 0; $go < $_POST['results']; $go++){
        
    $_carry = file_get_contents('https://krutoeporno.com/?page='.rand(1, 13));

    preg_match_all('|<div class="item">(.*?)<div class="item">|is', $_carry, $item);

    $array = rand(0, 19);

    preg_match('|<div class="h2">(.*?)</div>|is', $item[1][$array], $name);
    preg_match("|<a href='(.*?)'|is", $item[1][$array], $href);

    $_carry = file_get_contents('https://krutoeporno.com'.$href[1]);

    preg_match('|<source src="(.*?)"|is', $_carry, $file);
    preg_match('|poster="(.*?)"|is', $_carry, $poster);
    preg_match('|<img src="/style/ico/durdl.png"/>(.*?)<span|is', $_carry, $duration);

    $duration[1] = trim($duration[1]);
    
    if (strripos($_carry, '<source') and $poster[1] and $name[1] and $duration[1] and $file[1] and $href[1]){
        
    $md5 = md5(rand(1, 9999).$go);
    $translit = str_replace(' ', '_', transliterate($name[1])).'_'.rand(1, 9999);
    $uniqueness = md5(str_replace(' ', '_', transliterate($name[1])));
    
    $quantity = $mysqli -> query("select count(*) from ero_files where uniqueness = '$uniqueness' and server = 'krutoeporno.com'") -> fetch_row();
    
    if ($quantity[0] == 0) {
        
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', file_get_contents('https://krutoeporno.com'.$poster[1]));

    $image = new SimpleImage();
    $image->load($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');
    $image->resize($width_S, $height_S);
    $image->save($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');

    if ($settings['water'] == 1)
	water($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/designs/water.png');
	
	$yd_sql = 0;
	
    if ($_POST['mode'] == '2') {

    $yd = yd_upload_file('https://lab.porn'.$file[1], 'mp4', $settings['OAuth']);
    $recoil = $yd;
    $yd_sql = 1;
	
    } else if ($_POST['mode'] == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents('https://krutoeporno.com'.$file[1]));
    
    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }
        
    $sample = $name[1];     
        
    if ($_POST['selection'] == 0) $category = categories($sample); else $category = abs(intval($_POST['category']));

    if ($_POST['translate'] == 2) {
        
        $description[1] = translate($description[1], 'ru', 'en');
        $name[1] = translate($name[1], 'ru', 'en');
        
    }
    
    $mysqli -> query("INSERT INTO ero_files SET yd = '$yd_sql', category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = 'https://krutoeporno.com".$file[1]."', server = 'krutoeporno.com', tags = '".tags($name[1])."', name = '".$name[1]."', description = '".trim($name[1])."', translit = '$translit', duration = '".trim($duration[1])."', date = '$publish'");
        
    $collected++;
    
    }
    
    sleep($_POST['delay']);
    
    }
    
    }
    
    }   else if ($_POST['server'] == 23) {
    
    $collected = 0;
    
    for($go = 0; $go < $_POST['results']; $go++){
        
    $categories = array('aziatki', 
    'analnoe', 
    'bdsm-i-fetish', 
    'blondinki', 
    'bolshie-siski', 
    'bolshie-chleny', 
    'brjynetki', 
    'v-latekse', 
    'v-losinah', 
    'gruppovoj-seks', 
    'dvojnoe-proniknovenie', 
    'zhenskie-orgazmy', 
    'zhestkoe-porno',
    'zhopy', 
    'zrelye-zhenschiny', 
    'kastingi', 
    'krasivoe-porno', 
    'latinki', 
    'lesbijanki', 
    'ljybitelskoe', 
    'mamochki', 
    'massazh', 
    'masturbacija', 
    'minet', 
    'molodye-devushki', 
    'na-prirode', 
    'na-rabote', 
    'negry', 
    'ot-pervogo-lica', 
    'russkoe-porno', 
    'ryzhye', 
    'seks-igrushki', 
    'sperma-kamshoty', 
    'sportsmenki', 
    'tolstye-devushki', 
    'chulki'
    );
    
    $array = array_rand($categories, 2);

    $_carry = file_get_contents('https://pornopups.com/'.$categories[$array[0]].'/'.rand(1, 15).'/');
    
    preg_match_all('|<div class="thumb">(.*?)</span></span></a></div>|is', $_carry, $video);
    
    $array = rand(1, 10);
    
    preg_match('|title="(.*?)"|is', $video[1][$array], $name);
    preg_match('|<span class="dl">(.*?)</span>|is', $video[1][$array], $duration);
    preg_match('|href="(.*?)"|is', $video[1][$array], $href);
    preg_match('|src="(.*?)"|is', $video[1][$array], $poster);
    
    $_carry = file_get_contents($href[1]);
    
    preg_match_all('|<a class="gbutm" href="(.*?)"|is', $_carry, $file); # $file[1][2];
    
    if ($poster[1]){
        
    $md5 = md5(rand(1, 9999).$go);
    $translit = str_replace(' ', '_', transliterate($name[1])).'_'.rand(1, 9999);
    $uniqueness = md5(str_replace(' ', '_', transliterate($name[1])));
    
    $quantity = $mysqli -> query("select count(*) from ero_files where uniqueness = '$uniqueness' and server = 'pornopups.com'") -> fetch_row();
    
    if ($quantity[0] == 0) {
       
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', file_get_contents($poster[1]));

    if ($settings['water'] == 1)
	water($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', $_SERVER['DOCUMENT_ROOT'].'/designs/water.png');
	
    if ($_POST['mode'] == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents($file[1][2]));
    
    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }
    
	
    $sample = $name[1];     

    if ($_POST['selection'] == 0) $category = categories($sample); else $category = abs(intval($_POST['category']));

    if ($_POST['translate'] == 2) {
        
        $name[1] = translate($name[1], 'ru', 'en');
        
    }
    
    $category = categories($sample);
    
    $mysqli -> query("INSERT INTO ero_files SET category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = '".$file[1][2]."', server = 'pornopups.com', tags = '".tags($name[1])."', name = '".$name[1]."', description = '".trim($name[1])."', translit = '$translit', duration = '".$duration[1]."', date = '".time()."'");
        
    $collected++;
    
    }
    
    sleep($_POST['delay']);
    
    }
    
    }
    
    }
    
        $_SESSION['collected'] =   $collected;
        
        logs($user['id'], $lang['added'].' '.$collected.' '.$lang['video'].'.', 0);
        
    }
    
    unset($_SESSION['protective']);
    
    $_SESSION['protective'] = rand(10000, 999999);
    
    ?>
    
    <div class="functions_data">
    
    <?
    
    if ($_SESSION['collected']) {
        
    ?>

        <p><span class="sample"><?=$lang['last_parsing_added']?> <?=$_SESSION['collected'];?> <?=$lang['video']?></span></p>
        
    <?
    
    }
    
    ?>
    
    <p>
    
    <form method="post">
        
	<p><b><?=$lang['number_of_files']?></b> </p>
	
	    <p>
	    <input name="results" type="radio" value="1"> 1 
	    <input name="results" type="radio" value="3"> 3 
	    <input name="results" type="radio" value="5"> 5 
	    <input name="results" type="radio" value="10" checked> 10 
        <input name="results" type="radio" value="15"> 15 
        <input name="results" type="radio" value="30"> 30 
        </p>

	<p><b><?=$lang['date_of_publication']?></b> </p>
	
	    <p>
        <input class="injected" name="publish" type="date" value="<?=date('Y-m-d')?>" min="<?=date('Y-m-d', time())?>" max="2025-12-31">
        </p>
       
    <p><b><?=$lang['server']?></b> </p>
    
        <p>
	    <input name="server" type="radio" value="0" checked> <small>mobolto</small> 
	    <input name="server" type="radio" value="1"> <small>pornomir</small> 
        <input name="server" type="radio" value="2"> <small>pornoraketa</small> 
        <input name="server" type="radio" value="4"> <small>airporno</small> 
        <input name="server" type="radio" value="5"> <small>pizdauz</small> 
        <input name="server" type="radio" value="6"> <small>xnxx</small> 
        <input name="server" type="radio" value="7"> <small>pornolomka</small> 
        <input name="server" type="radio" value="8"> <small>pornosto</small> 
        <input name="server" type="radio" value="9"> <small>russpornotube</small> 
        <input name="server" type="radio" value="10"> <small>rsuka</small> 
        <input name="server" type="radio" value="11"> <small>oxtube</small> 
        <input name="server" type="radio" value="12"> <small>hentai-x</small> 
        <input name="server" type="radio" value="13"> <small>ebun</small> 
        <input name="server" type="radio" value="14"> <small>vaginke</small> 
        <input name="server" type="radio" value="15"> <small>xcafe</small> 
        <input name="server" type="radio" value="16"> <small>house</small>
        <input name="server" type="radio" value="17"> <small>porno-kisa</small>
        <input name="server" type="radio" value="18"> <small>zajka</small>
        <input name="server" type="radio" value="19"> <small>gig</small>
        <input name="server" type="radio" value="20"> <small>uzbum</small>
        <input name="server" type="radio" value="21"> <small>lab</small>
        <input name="server" type="radio" value="22"> <small>krutoeporno</small>
        <input name="server" type="radio" value="23"> <small>pornopups</small>
        </p>

    <p><b><?=$lang['category_selection']?></b> </p> 
    
        <p>
	    <input name="selection" type="radio" value="0" checked> <?=$lang['automatically']?> 
        <input name="selection" type="radio" value="1"> <?=$lang['select_manually']?>     
        </p>

	<p><b><?=$lang['category']?></b> </p>
	
	<p><select class="injected" name="category">
	    
	<?
	
    $query = $mysqli -> query("select id, name from ero_categories order by id asc");

    while($row = $query -> fetch_assoc()){
    
    ?>
        
	<option value="<?=$row['id']?>" <?=($row['id']==$view['category']?" selected='selected'":null)?>><?=$row['name']?></option>

    <?
    
    }
    
    ?>
    
	</select></p>
	
    <p><b><?=$lang['parsing']?>     </b> </p> 
    
        <p>
	    <input name="mode" type="radio" value="0"> <?=$lang['download_files']?> <font color="red">*</font>
        <input name="mode" type="radio" value="1" checked> <?=$lang['collect_links']?> 
        <input name="mode" type="radio" value="2"> <?=$lang['upload_to_yd']?>  <font color="red">*</font>
        </p>

    <p><b><?=$lang['translate']?></b> </p> 
    
        <p>
	    <input name="translate" type="radio" value="1" checked> <?=$lang['original']?>
        <input name="translate" type="radio" value="2"> <?=$lang['translate_into_english']?>
        </p>
        
    <p><b><?=$lang['delay']?></b> </p>
    
        <p>
        <input name="delay" type="radio" value="3"> 3 <?=$lang['sek']?>. 
        <input name="delay" type="radio" value="5"> 5 <?=$lang['sek']?>. 
        <input name="delay" type="radio" value="7" checked> 7 <?=$lang['sek']?>. 
        <input name="delay" type="radio" value="10"> 10 <?=$lang['sek']?>. 
        <input name="delay" type="radio" value="30"> 30 <?=$lang['sek']?>.
        </p>
    
    <p><b><?=$lang['code']?></b>  <small><?=abs(intval($_SESSION['protective']))?></small> </p>
    
	    <p><input type="number" name="protective" class="injected" /> </p>
	
	<input type="submit" class="byecos" value="<?=$lang['send']?>" />
	
	</form>

    </p>
    
    <div align="right"><p><font color="red">* <?=$lang['excessive']?></font></p></div>
    
    </div>