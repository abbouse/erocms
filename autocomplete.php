<?php

/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/

    require 'core/Functions.php';

    $server = rand(1, 16);#random серверов
    $sleep = 3;#задержка 
    $mode = 1;#0 - скачивать файлы, 1 - собирать ссылки
    $quantity = 3;#количество файлов 
    $key = $settings['cron'];#ключ
    
    if ($key != $_GET['key']) exit('The key is invalid');
    
    if ($server == 1) {
    
    $start = rand(1, 4960);
    
    for($go = $start; $go < ($start + $quantity); $go++){

    $_carry = file_get_contents('http://mobolto.com/porno/video-'.$go.'/');

    if (strripos($_carry, 'file:')){

    preg_match_all('|<h1>(.*?)</h1>|is', $_carry, $name);
    preg_match_all('|<div class="iblock4">(.*?)</div>|is', $_carry, $description);
    preg_match('|poster:"(.*?)"|is', $_carry, $poster);
    preg_match('|file:"(.*?)"|is', $_carry, $file);
    preg_match('|Длительность: <b>(.*?)</b>|is', $_carry, $duration);

    $name[1][0] = preg_replace("/('|\"|\r?\n)/", '', $name[1][0]);
    $description[1][1] = preg_replace("/('|\"|\r?\n)/", '', $description[1][1]);
    $md5 = md5(rand(1, 9999));
    $translit = str_replace(' ', '_', transliterate($name[1][0])).'_'.rand(1, 9999);
    $uniqueness = md5(str_replace(' ', '_', transliterate($name[1][0])));
    
    $quantity = $mysqli -> query("select count(*) from ero_files where uniqueness = '$uniqueness' and server = 'mobolto.com'") -> fetch_row();
    
    if ($quantity[0] == 0) {
        
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', file_get_contents($poster[1]));
    
    if ($mode == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents($file[1]));

    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }
    
    $sample = $description[1][1];
    
    $category = categories($sample);
    
    $mysqli -> query("INSERT INTO ero_files SET category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = '".$file[1]."', server = 'mobolto.com', tags = '".tags($description[1][1])."', name = '".$name[1][0]."', description = '".$description[1][1]."', translit = '$translit', duration = '".$duration[1]."', date = '".time()."'");
       
    }
    
    sleep($sleep);
    
    }
    
    }
    
    } else if ($server == 2) {
    
    for($go = 0; $go < $quantity; $go++){
        
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
        
    $md5 = md5(rand(1, 9999));
    $translit = str_replace(' ', '_', transliterate($name[1])).'_'.rand(1, 9999);
    $uniqueness = md5(str_replace(' ', '_', transliterate($name[1])));
    
    $quantity = $mysqli -> query("select count(*) from ero_files where uniqueness = '$uniqueness' and server = 'pornomir.tv'") -> fetch_row();
    
    if ($quantity[0] == 0) {
        
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', file_get_contents('https://pornomir.tv'.$poster[1]));
        
    if ($mode == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents($file[1]));
    
    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }
        
    $sample = $name[1];
    
    $category = categories($sample);
    
    $mysqli -> query("INSERT INTO ero_files SET category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = '".$file[1]."', server = 'pornomir.tv', tags = '".tags($name[1])."', name = '".$name[1]."', description = '".$name[1]."', translit = '$translit', duration = '".$duration[1]."', date = '".time()."'");
        
    }
    
    sleep($sleep);
    
    }
    
    }
    
    } else if ($server == 3) {
    
    for($go = 0; $go < $quantity; $go++){
      
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
    
    $md5 = md5(rand(1, 9999));
    $translit = str_replace(' ', '_', transliterate($name[1])).'_'.rand(1, 9999);
    $uniqueness = md5(str_replace(' ', '_', transliterate($name[1])));
    
    $quantity = $mysqli -> query("select count(*) from ero_files where uniqueness = '$uniqueness' and server = 'pornoraketa.tv'") -> fetch_row();
    
    if ($quantity[0] == 0) {
        
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', file_get_contents('http://pornoraketa.tv'.$poster[1][$array]));
    
    if ($mode == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents('http://pornoraketa.tv/'.$file[1]));
    
    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }

    $sample = $name[1];
    
    $category = categories($sample);
    
    $mysqli -> query("INSERT INTO ero_files SET category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = 'http://pornoraketa.tv/".$file[1]."', server = 'pornoraketa.tv', tags = '".tags($name[1])."', name = '".$name[1]."', description = '".$name[1]."', translit = '$translit', duration = '".$duration[1]."', date = '".time()."'");
    
    }
    
    sleep($sleep);

    }
        
    }
    
    } else if ($server == 4) {
    
    $start = rand(1, 900);
    
    for($go = $start; $go < ($start + $quantity); $go++){

    $_carry = file_get_contents('http://airporno.ru/views/'.$go.'/');
    
    preg_match('|<h1 class="htit">(.*?)</h1>|is', $_carry, $name);
    
    if (strripos($_carry, 'source') or !strripos($name[1], 'href')){
    
    preg_match('|<source src="(.*?)"|is', $_carry, $file);
    preg_match('|poster="(.*?)"|is', $_carry, $poster);
    preg_match('|Время:(.*?)<br />|is', $_carry, $duration);
    
    $md5 = md5(rand(1, 9999));
    $translit = str_replace(' ', '_', transliterate($name[1])).'_'.rand(1, 9999);
    $uniqueness = md5(str_replace(' ', '_', transliterate($name[1])));
    
    $quantity = $mysqli -> query("select count(*) from ero_files where uniqueness = '$uniqueness' and server = 'airporno.ru'") -> fetch_row();
    
    if ($quantity[0] == 0) {
        
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', file_get_contents($poster[1]));
    
    if ($mode == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents($file[1]));
    
    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }
    
    $duration = trim($duration[1]);
    
    if (strlen($duration) == 4) $duration = '0'.$duration;
    
    $sample = $name[1];
    
    $category = categories($sample);
    
    $mysqli -> query("INSERT INTO ero_files SET category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = '".$file[1]."', server = 'airporno.ru', tags = '".tags($name[1])."', name = '".$name[1]."', description = '".$name[1]."', translit = '$translit', duration = '$duration', date = '".time()."'");
    
    }
    
    sleep($sleep);    
    
    }
    
    }
    
    } else if ($server == 5) {
    
    $collected = 0;
    
    for($go = 0; $go < $quantity; $go++){

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

    $md5 = md5(rand(1, 9999));
    $translit = str_replace(' ', '_', transliterate($name[1])).'_'.rand(1, 9999);
    $uniqueness = md5(str_replace(' ', '_', transliterate($name[1])));
    
    $quantity = $mysqli -> query("select count(*) from ero_files where uniqueness = '$uniqueness' and server = 'pizdauz.ru'") -> fetch_row();
    
    if ($quantity[0] == 0) {
        
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', file_get_contents($poster[1]));
    
    if ($mode == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents($file[1]));
    
    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }
    
    $sample = trim($description[1]);
    
    $category = categories($sample);

    $mysqli -> query("INSERT INTO ero_files SET category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = '".$file[1]."', server = 'pizdauz.ru', tags = '".tags(trim($description[1]))."', name = '".$name[1]."', description = '".trim($description[1])."', translit = '$translit', duration = '".trim($duration[1])."', date = '".time()."'");
    
    $collected++;

    }
    
    }
    
    sleep($sleep);    
    
    }
    
    } else if ($server == 6) {
    
    $collected = 0;
    
    for($go = 0; $go < $quantity; $go++){

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
    
    $md5 = md5(rand(1, 9999));
    $translit = str_replace(' ', '_', transliterate($name[1])).'_'.rand(1, 9999);
    $uniqueness = md5(str_replace(' ', '_', transliterate($name[1])));
    
    $quantity = $mysqli -> query("select count(*) from ero_files where uniqueness = '$uniqueness' and server = 'xnxx.com'") -> fetch_row();
    
    if ($quantity[0] == 0) {
    
    if ($mode == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents(trim($file[0])));
    
    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }
    
    $sample = trim($name[1]);
    
    if (mb_strlen(trim($duration[1])) == 5) {
    
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', file_get_contents($poster[1]));
        
    $category = categories($sample);
    
    $mysqli -> query("INSERT INTO ero_files SET category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = '".trim($file[0])."', server = 'xnxx.com', tags = '".tags(trim($name[1]))."', name = '".$name[1]."', description = '".$name[1]."', translit = '$translit', duration = '".trim($duration[1])."', date = '".time()."'");
    
    $collected++;
        
    }

    }
    
    }
    
    sleep($sleep);    
    
    }
    
    } else if ($server == 7) {
    
    $collected = 0;
    
    for($go = 0; $go < $quantity; $go++){
	
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
    
    if ($mode == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents($file[1]));
    
    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }
    
    if (strripos($description[1], 'dle')) $description[1] = $name[1];
        
    $sample = $description[1];
    
    $category = categories($sample);
    
    $mysqli -> query("INSERT INTO ero_files SET category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = '".$file[1]."', server = 'pornolomka.mobi', tags = '".tags(trim($description[1]))."', name = '".$name[1]."', description = '".trim($description[1])."', translit = '$translit', duration = '".trim($duration[1])."', date = '".time()."'");
    
    $collected++;	
	
	}
	
	}
	
	sleep($sleep);
	
	}
	
	} else if ($server == 8) {
    
    $collected = 0;
    
    for($go = 0; $go < $quantity; $go++){
	
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
    
    if ($mode == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents($file[1]));
    
    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }
        
    $sample = $description[1];
    
    $category = categories($sample);
    
    $mysqli -> query("INSERT INTO ero_files SET category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = '".$file[1]."', server = 'pornosto.com', tags = '".tags(trim($description[1]))."', name = '".$name[1]."', description = '".trim($description[1])."', translit = '$translit', duration = '".trim($duration[1])."', date = '".time()."'");
    
    $collected++;	
	
	}
	
	}
	
	sleep($sleep);
	
	}
	
	} else if ($server == 9) {
    
    $collected = 0;
    
    for($go = 0; $go < $quantity; $go++){
	
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
    
    if ($mode == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents($file[0]));
    
    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }
        
    $sample = $description[1];
    
    $category = categories($sample);
    
    $mysqli -> query("INSERT INTO ero_files SET category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = '".$file[0]."', server = 'russpornotube.com', tags = '".tags(trim($description[1]))."', name = '".$name[1]."', description = '".trim($description[1])."', translit = '$translit', duration = '".trim($duration[1])."', date = '".time()."'");
    
    $collected++;	
	
	}
	
	}
	
	sleep($sleep);
	
	}
	
	} else if ($server == 10) {
    
    $collected = 0;
    
    for($go = 0; $go < $quantity; $go++){
	
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
    
    if ($mode == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents($file[1]));
    
    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }
        
    $sample = $name[1];
    
    if ($_POST['selection'] == 0) $category = categories($sample); else $category = abs(intval($_POST['category']));
    
    $mysqli -> query("INSERT INTO ero_files SET category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = '".$file[1]."', server = 'rsuka.tv', tags = '".tags(trim($name[1]))."', name = '".$name[1]."', description = '".trim($name[1])."', translit = '$translit', duration = '".trim($duration[1])."', date = '".time()."'");
    
    $collected++;	
	
	}
	
	}
	
	sleep($sleep);
	
	}
	
	} else if ($server == 11) {
    
    $collected = 0;
    
    for($go = 0; $go < $quantity; $go++){
	
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
    
    if ($mode == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents($file[1]));
    
    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }
        
    $sample = $name[1];
    
    if ($_POST['selection'] == 0) $category = categories($sample); else $category = abs(intval($_POST['category']));
    
    $mysqli -> query("INSERT INTO ero_files SET category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = '".$file[1]."', server = 'oxtube.tv', tags = '".tags(trim($name[1]))."', name = '".$name[1]."', description = '".trim($name[1])."', translit = '$translit', duration = '".trim($duration[1])."', date = '".time()."'");
    
    $collected++;	
	
	}
	
	}
	
	sleep($sleep);
	
	}
	
	} else if ($server == 12) {
    
    $collected = 0;
    
    for($go = 0; $go < $quantity; $go++){
	
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
    
    if ($mode == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents($file[1]));
    
    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }
        
    $sample = $name[1];
    
    $category = categories($sample);
    
    $mysqli -> query("INSERT INTO ero_files SET category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = '".$file[1]."', server = 'hentai-x.ru', tags = '".tags(trim($description[1]))."', name = '".$name[1]."', description = '".trim($description[1])."', translit = '$translit', duration = '".trim($duration[1])."', date = '".time()."'");
    
    $collected++;	
	
	}
	
	}
	
	sleep($sleep);
	
	}
	
	} else if ($server == 13) {
    
    $collected = 0;
    
    for($go = 0; $go < $quantity; $go++){
        
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

    if ($mode == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents('https://ebun.me'.$file[1]));
    
    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }
        
    $sample = $name[1];     
        
    $category = categories($sample);

    $mysqli -> query("INSERT INTO ero_files SET category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = 'https://ebun.me".$file[1]."', server = 'ebun.me', tags = '".tags($description[1])."', name = '".$name[1]."', description = '".trim($description[1])."', translit = '$translit', duration = '".$duration[1]."', date = '".time()."'");
        
    $collected++;
    
    }
    
    sleep($sleep);
    
    }
    
    }
	
	} else if ($server == 14) {
    
    $collected = 0;
    
    for($go = 0; $go < $quantity; $go++){
        
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

    if ($mode == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents($file[1]));
    
    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }
        
    $sample = $description[1];     
        
    $category = categories($sample);

    $mysqli -> query("INSERT INTO ero_files SET category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = '".$file[1]."', server = 'vaginke.com', tags = '".tags($description[1])."', name = '".$name[1]."', description = '".trim($description[1])."', translit = '$translit', duration = '".$duration[1]."', date = '".time()."'");
        
    $collected++;
    
    }
    
    sleep($sleep);
    
    }
    
    }
    
    } else if ($server == 15) {
    
    $collected = 0;
    
    for($go = 0; $go < $quantity; $go++){
        
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
    preg_match('|<a class="btn btn-primary btn-block" href="(.*?)"|is', $_carry, $file);
    
    if (strripos($_carry, 'og:image') and $poster[1]){
        
    $md5 = md5(rand(1, 9999).$go);
    $translit = str_replace(' ', '_', transliterate($name[1])).'_'.rand(1, 9999);
    $uniqueness = md5(str_replace(' ', '_', transliterate($name[1])));
    
    $quantity = $mysqli -> query("select count(*) from ero_files where uniqueness = '$uniqueness' and server = 'xcafe.com'") -> fetch_row();
    
    if ($quantity[0] == 0) {
        
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', file_get_contents($poster[1]));

    if ($mode == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents($file[1]));
    
    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/view_'. $translit;
    
    }
        
    $sample = $description[1];     
        
    $category = categories($sample);
    
    $mysqli -> query("INSERT INTO ero_files SET category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = '".$file[1]."', server = 'xcafe.com', tags = '".tags($description[1])."', name = '".$name[1]."', description = '".trim($description[1])."', translit = '$translit', duration = '".$duration[1]."', date = '".time()."'");
        
    $collected++;
    
    }
    
    sleep($sleep);
    
    }
    
    }
    
    } else if ($server == 16) {
    
    $collected = 0;
    
    for($go = 0; $go < $quantity; $go++){
        
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

    if ($mode == '0') {

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/video/'.$md5.'.mp4', file_get_contents('https://zajka.org'.$file[1]));
    
    $recoil = '/content/video/'.$md5.'.mp4';
    
    } else {
    
    $recoil = '/download/'. $translit.'.mp4';
    
    }
        
    $sample = $description[1][1];     
    categories($sample);

    $mysqli -> query("INSERT INTO ero_files SET category = '$category', recoil = '$recoil', uniqueness = '$uniqueness', screenshot = '/content/screenshots/".$md5.".jpg', address = 'https://zajka.org".$file[1]."', server = 'zajka.org', tags = '".tags($description[1][1])."', name = '".$name[1]."', description = '".trim($description[1][1])."', translit = '$translit', duration = '".trim($duration[1])."', date = '".time()."'");
        
    $collected++;
    
    }
    
    sleep($sleep);
    
    }
    
    }
    
    }
    
    array_map('unlink' , glob($_SERVER['DOCUMENT_ROOT']."/content/cache/*.html"));
    
    echo '<p align="center">Successfully added '.$collected.' videos</p>';
    
    $quantity -> free();
    $mysqli -> close();