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

    if (isset($_GET['sitemap'])){
        
        $query = $mysqli -> query("select id, translit from ero_files order by id desc");

        while($row = $query -> fetch_assoc()) $additive .= '<url><loc>'.filter($protocol.$_SERVER['HTTP_HOST']).'/watch/'.$row['translit'].'.html</loc></url>';

        $query = $mysqli -> query("select id, translit from ero_categories order by id desc");

        while($row = $query -> fetch_assoc()) $additive .= '<url><loc>'.filter($protocol.$_SERVER['HTTP_HOST']).'/'.$row['translit'].'/</loc></url>';
        
        $content = '<?xml version="1.0" encoding="UTF-8"?>
        <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
        '.$additive.'
        </urlset>';
        
        file_put_contents('sitemap.xml', $content);
        logs($user['id'], $lang['updated_sitemap'], 0);
        header('Location: /control.html?func=functions_data&'.rand(1,9));
        exit;
    }
    
    
    if (isset($_GET['robots'])){

        $content = "User-agent: *\nHost: ".filter($_SERVER['HTTP_HOST'])."\nSitemap: ".filter($protocol.$_SERVER['HTTP_HOST'])."/sitemap.xml";
        
        file_put_contents('robots.txt', $content);
        logs($user['id'], $lang['robots'], 0);
        header('Location: /control.html?func=functions_data&'.rand(1,9));
        exit;
    }
    
    
    if (isset($_GET['cache'])){

        array_map('unlink' , glob($_SERVER['DOCUMENT_ROOT']."/content/cache/*.html"));
        logs($user['id'], $lang['cleared_the_cache'], 0);
        header('Location: /control.html?func=functions_data&'.rand(1,9));
        exit;
    }
    
   if (isset($_GET['verification'])){

    $uzbum = file_get_contents('https://uzbum.net');

    if (strripos($uzbum, 'description')) $uzbum = '<font color="green">is available</font>'; else $uzbum = '<font color="red">not available</font>';
    
    $mobolto = file_get_contents('http://mobolto.com');

    if (strripos($mobolto, 'description')) $mobolto = '<font color="green">is available</font>'; else $mobolto = '<font color="red">not available</font>';
    
    $pornomir = file_get_contents('https://pornomir.tv');

    if (strripos($pornomir, 'description')) $pornomir = '<font color="green">is available</font>'; else $pornomir = '<font color="red">not available</font>';
    
    $pornoraketa = file_get_contents('http://pornoraketa.tv');
        
    if (strripos($pornoraketa, 'description')) $pornoraketa = '<font color="green">is available</font>'; else $pornoraketa = '<font color="red">not available</font>';
    
    $airporno = file_get_contents('http://airporno.ru');
        
    if (strripos($airporno, 'description')) $airporno = '<font color="green">is available</font>'; else $airporno = '<font color="red">not available</font>';

    $pizdauz = file_get_contents('https://pizdauz.ru');
        
    if (strripos($pizdauz, 'description')) $pizdauz = '<font color="green">is available</font>'; else $pizdauz = '<font color="red">not available</font>';

    $xnxx = file_get_contents('https://www.xnxx.com');
        
    if (strripos($xnxx, 'description')) $xnxx = '<font color="green">is available</font>'; else $xnxx = '<font color="red">not available</font>';

    $pornolomka = file_get_contents('http://pornolomka.mobi');
        
    if (strripos($pornolomka, 'description')) $pornolomka = '<font color="green">is available</font>'; else $pornolomka = '<font color="red">not available</font>';

    $pornosto = file_get_contents('https://pornosto.com');
        
    if (strripos($pornosto, 'description')) $pornosto = '<font color="green">is available</font>'; else $pornosto = '<font color="red">not available</font>';

    $russpornotube = file_get_contents('http://www.russpornotube.com');
        
    if (strripos($russpornotube, 'description')) $russpornotube = '<font color="green">is available</font>'; else $russpornotube = '<font color="red">not available</font>';

    $rsuka = file_get_contents('http://rsuka.tv');
        
    if (strripos($rsuka, 'description')) $rsuka = '<font color="green">is available</font>'; else $rsuka = '<font color="red">not available</font>';

    $oxtube = file_get_contents('http://oxtube.tv/');
        
    if (strripos($oxtube, 'description')) $oxtube = '<font color="green">is available</font>'; else $oxtube = '<font color="red">not available</font>';

    $hentai = file_get_contents('http://hentai-x.ru/');
        
    if (strripos($hentai, 'description')) $hentai = '<font color="green">is available</font>'; else $hentai = '<font color="red">not available</font>';

    $ebun = file_get_contents('https://ebun.me');
        
    if (strripos($ebun, 'description')) $ebun = '<font color="green">is available</font>'; else $ebun = '<font color="red">not available</font>';

    $vaginke = file_get_contents('http://vaginke.com');
        
    if (strripos($vaginke, 'description')) $vaginke = '<font color="green">is available</font>'; else $vaginke = '<font color="red">not available</font>';

    $xcafe = file_get_contents('https://xcafe.com');
        
    if (strripos($xcafe, 'description')) $xcafe = '<font color="green">is available</font>'; else $xcafe = '<font color="red">not available</font>';

    $house = file_get_contents('https://house.porn');
        
    if (strripos($house, 'description')) $house = '<font color="green">is available</font>'; else $house = '<font color="red">not available</font>';

    $kisa = file_get_contents('http://porno-kisa.com');
        
    if (strripos($kisa, 'description')) $kisa = '<font color="green">is available</font>'; else $kisa = '<font color="red">not available</font>';

    $zajka = file_get_contents('https://zajka.org');
        
    if (strripos($zajka, 'description')) $zajka = '<font color="green">is available</font>'; else $zajka = '<font color="red">not available</font>';

    $gig = file_get_contents('http://gig.porn');
        
    if (strripos($gig, 'description')) $gig = '<font color="green">is available</font>'; else $gig = '<font color="red">not available</font>';

    $lab = file_get_contents('https://lab.porn');
        
    if (strripos($lab, 'description')) $lab = '<font color="green">is available</font>'; else $lab = '<font color="red">not available</font>';

    $krutoeporno = file_get_contents('https://krutoeporno.com');
        
    if (strripos($krutoeporno, 'description')) $krutoeporno = '<font color="green">is available</font>'; else $krutoeporno = '<font color="red">not available</font>';

    $pornopups = file_get_contents('https://pornopups.com');
        
    if (strripos($pornopups, 'description')) $pornopups = '<font color="green">is available</font>'; else $pornopups = '<font color="red">not available</font>';
    
    } else {
        
      $mobolto = '<font color="SteelBlue">not started</font>';
      
      $pornomir = '<font color="SteelBlue">not started</font>';
      
      $pornoraketa = '<font color="SteelBlue">not started</font>';
      
      $airporno = '<font color="SteelBlue">not started</font>';
      
      $pizdauz = '<font color="SteelBlue">not started</font>';
      
      $xnxx = '<font color="SteelBlue">not started</font>';
      
      $pornolomka = '<font color="SteelBlue">not started</font>';
      
      $pornosto = '<font color="SteelBlue">not started</font>';
      
      $russpornotube = '<font color="SteelBlue">not started</font>';
      
      $rsuka = '<font color="SteelBlue">not started</font>';
      
      $oxtube = '<font color="SteelBlue">not started</font>';
      
      $hentai = '<font color="SteelBlue">not started</font>';
      
      $ebun = '<font color="SteelBlue">not started</font>';
      
      $vaginke = '<font color="SteelBlue">not started</font>';
      
      $xcafe = '<font color="SteelBlue">not started</font>';
      
      $house = '<font color="SteelBlue">not started</font>';
      
      $kisa = '<font color="SteelBlue">not started</font>';
      
      $zajka = '<font color="SteelBlue">not started</font>';
      
      $gig = '<font color="SteelBlue">not started</font>';
      
      $uzbum = '<font color="SteelBlue">not started</font>';
      
      $lab = '<font color="SteelBlue">not started</font>';
      
      $krutoeporno = '<font color="SteelBlue">not started</font>';
      
      $pornopups = '<font color="SteelBlue">not started</font>';
      
    }

    if (isset($_GET['uzbum'])){
        
        $query = $mysqli -> query("select * from ero_files where server = 'uzbum.net' order by id desc");

        while($row = $query -> fetch_assoc()){
        
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['screenshot'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['screenshot']);
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['recoil'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['recoil']);
    
        $mysqli -> query("delete from ero_files where id = '$row[id]'");
        $mysqli -> query("delete from ero_favorites where id_video = '$row[id]'");
        
        }
    
        logs($user['id'], $lang['cleared_all_server'].' uzbum.net.', 0);
        header('Location: /control.html?func=functions_data&'.rand(1,9));
        exit;
        
    } else  if (isset($_GET['mobolto'])){
        
        $query = $mysqli -> query("select * from ero_files where server = 'mobolto.com' order by id desc");

        while($row = $query -> fetch_assoc()){
        
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['screenshot'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['screenshot']);
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['recoil'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['recoil']);
    
        $mysqli -> query("delete from ero_files where id = '$row[id]'");
        $mysqli -> query("delete from ero_favorites where id_video = '$row[id]'");
        
        }
    
        logs($user['id'], $lang['cleared_all_server'].' mobolto.com.', 0);
        header('Location: /control.html?func=functions_data&'.rand(1,9));
        exit;
        
    } else if (isset($_GET['pornomir'])){
        
        $query = $mysqli -> query("select * from ero_files where server = 'pornomir.tv' order by id desc");

        while($row = $query -> fetch_assoc()){
        
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['screenshot'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['screenshot']);
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['recoil'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['recoil']);
    
        $mysqli -> query("delete from ero_files where id = '$row[id]'");
        $mysqli -> query("delete from ero_favorites where id_video = '$row[id]'");
        
        }
        
        logs($user['id'], $lang['cleared_all_server'].' pornomir.tv.', 0);
        header('Location: /control.html?func=functions_data&'.rand(1,9));
        exit;
        
    } else if (isset($_GET['pornoraketa'])){
        
        $query = $mysqli -> query("select * from ero_files where server = 'pornoraketa.tv' order by id desc");

        while($row = $query -> fetch_assoc()){
        
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['screenshot'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['screenshot']);
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['recoil'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['recoil']);
    
        $mysqli -> query("delete from ero_files where id = '$row[id]'");
        $mysqli -> query("delete from ero_favorites where id_video = '$row[id]'");
        
        }
        
        logs($user['id'], $lang['cleared_all_server'].' pornoraketa.tv.', 0);
        header('Location: /control.html?func=functions_data&'.rand(1,9));
        exit;
    } else if (isset($_GET['airporno'])){
        
        $query = $mysqli -> query("select * from ero_files where server = 'airporno.ru' order by id desc");

        while($row = $query -> fetch_assoc()){
        
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['screenshot'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['screenshot']);
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['recoil'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['recoil']);
    
        $mysqli -> query("delete from ero_files where id = '$row[id]'");
        $mysqli -> query("delete from ero_favorites where id_video = '$row[id]'");
        
        }
        
        logs($user['id'], $lang['cleared_all_server'].' airporno.ru.', 0);
        header('Location: /control.html?func=functions_data&'.rand(1,9));
        exit;
    } else if (isset($_GET['pizdauz'])){
        
        $query = $mysqli -> query("select * from ero_files where server = 'pizdauz.ru' order by id desc");

        while($row = $query -> fetch_assoc()){
        
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['screenshot'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['screenshot']);
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['recoil'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['recoil']);
    
        $mysqli -> query("delete from ero_files where id = '$row[id]'");
        $mysqli -> query("delete from ero_favorites where id_video = '$row[id]'");
        
        }
        
        logs($user['id'], $lang['cleared_all_server'].' pizdauz.ru.', 0);
        header('Location: /control.html?func=functions_data&'.rand(1,9));
        exit;
    } else if (isset($_GET['xnxx'])){
        
        $query = $mysqli -> query("select * from ero_files where server = 'xnxx.com' order by id desc");

        while($row = $query -> fetch_assoc()){
        
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['screenshot'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['screenshot']);
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['recoil'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['recoil']);
    
        $mysqli -> query("delete from ero_files where id = '$row[id]'");
        $mysqli -> query("delete from ero_favorites where id_video = '$row[id]'");
        
        }
        
        logs($user['id'], $lang['cleared_all_server'].' xnxx.com.', 0);
        header('Location: /control.html?func=functions_data&'.rand(1,9));
        exit;
    } else if (isset($_GET['pornolomka'])){
        
        $query = $mysqli -> query("select * from ero_files where server = 'pornolomka.mobi' order by id desc");

        while($row = $query -> fetch_assoc()){
        
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['screenshot'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['screenshot']);
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['recoil'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['recoil']);
    
        $mysqli -> query("delete from ero_files where id = '$row[id]'");
        $mysqli -> query("delete from ero_favorites where id_video = '$row[id]'");
        
        }
        
        logs($user['id'], $lang['cleared_all_server'].' pornolomka.mobi.', 0);
        header('Location: /control.html?func=functions_data&'.rand(1,9));
        exit;
    } else if (isset($_GET['pornosto'])){
        
        $query = $mysqli -> query("select * from ero_files where server = 'pornosto.com' order by id desc");

        while($row = $query -> fetch_assoc()){
        
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['screenshot'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['screenshot']);
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['recoil'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['recoil']);
    
        $mysqli -> query("delete from ero_files where id = '$row[id]'");
        $mysqli -> query("delete from ero_favorites where id_video = '$row[id]'");
        
        }
        
        logs($user['id'], $lang['cleared_all_server'].' pornosto.com.', 0);
        header('Location: /control.html?func=functions_data&'.rand(1,9));
        exit;
    } else if (isset($_GET['russpornotube'])){
        
        
        $query = $mysqli -> query("select * from ero_files where server = 'russpornotube.com' order by id desc");

        while($row = $query -> fetch_assoc()){
        
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['screenshot'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['screenshot']);
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['recoil'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['recoil']);
    
        $mysqli -> query("delete from ero_files where id = '$row[id]'");
        $mysqli -> query("delete from ero_favorites where id_video = '$row[id]'");
        
        }
        
        logs($user['id'], $lang['cleared_all_server'].' russpornotube.com.', 0);
        header('Location: /control.html?func=functions_data&'.rand(1,9));
        exit;
    } else if (isset($_GET['rsuka'])){
        
        $query = $mysqli -> query("select * from ero_files where server = 'rsuka.tv' order by id desc");

        while($row = $query -> fetch_assoc()){
        
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['screenshot'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['screenshot']);
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['recoil'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['recoil']);
    
        $mysqli -> query("delete from ero_files where id = '$row[id]'");
        $mysqli -> query("delete from ero_favorites where id_video = '$row[id]'");
        
        }
        
        logs($user['id'], $lang['cleared_all_server'].' rsuka.tv.', 0);
        header('Location: /control.html?func=functions_data&'.rand(1,9));
        exit;
    } else if (isset($_GET['oxtube'])){
        
        $query = $mysqli -> query("select * from ero_files where server = 'oxtube.tv' order by id desc");

        while($row = $query -> fetch_assoc()){
        
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['screenshot'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['screenshot']);
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['recoil'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['recoil']);
    
        $mysqli -> query("delete from ero_files where id = '$row[id]'");
        $mysqli -> query("delete from ero_favorites where id_video = '$row[id]'");
        
        }
        
        logs($user['id'], $lang['cleared_all_server'].' oxtube.tv.', 0);
        header('Location: /control.html?func=functions_data&'.rand(1,9));
        exit;
    }else if (isset($_GET['hentai'])){
        
        $query = $mysqli -> query("select * from ero_files where server = 'hentai-x.ru' order by id desc");

        while($row = $query -> fetch_assoc()){
        
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['screenshot'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['screenshot']);
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['recoil'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['recoil']);
    
        $mysqli -> query("delete from ero_files where id = '$row[id]'");
        $mysqli -> query("delete from ero_favorites where id_video = '$row[id]'");
        
        }
        
        logs($user['id'], $lang['cleared_all_server'].' hentai-x.ru.', 0);
        header('Location: /control.html?func=functions_data&'.rand(1,9));
        exit;
    } else if (isset($_GET['ebun'])){
        
        $query = $mysqli -> query("select * from ero_files where server = 'ebun.me' order by id desc");

        while($row = $query -> fetch_assoc()){
        
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['screenshot'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['screenshot']);
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['recoil'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['recoil']);
    
        $mysqli -> query("delete from ero_files where id = '$row[id]'");
        $mysqli -> query("delete from ero_favorites where id_video = '$row[id]'");
        
        }
        
        logs($user['id'], $lang['cleared_all_server'].' ebun.me.', 0);
        header('Location: /control.html?func=functions_data&'.rand(1,9));
        exit;
    } else if (isset($_GET['vaginke'])){
        
        $query = $mysqli -> query("select * from ero_files where server = 'vaginke.com' order by id desc");

        while($row = $query -> fetch_assoc()){
        
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['screenshot'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['screenshot']);
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['recoil'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['recoil']);
    
        $mysqli -> query("delete from ero_files where id = '$row[id]'");
        $mysqli -> query("delete from ero_favorites where id_video = '$row[id]'");
        
        }
        
        logs($user['id'], $lang['cleared_all_server'].' vaginke.com.', 0);
        header('Location: /control.html?func=functions_data&'.rand(1,9));
        exit;
    }   else if (isset($_GET['xcafe'])){
        
        $query = $mysqli -> query("select * from ero_files where server = 'xcafe.com' order by id desc");

        while($row = $query -> fetch_assoc()){
        
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['screenshot'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['screenshot']);
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['recoil'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['recoil']);
    
        $mysqli -> query("delete from ero_files where id = '$row[id]'");
        $mysqli -> query("delete from ero_favorites where id_video = '$row[id]'");
        
        }
        
        logs($user['id'], $lang['cleared_all_server'].' xcafe.com.', 0);
        header('Location: /control.html?func=functions_data&'.rand(1,9));
        exit;
    }   else if (isset($_GET['house'])){
        
        $query = $mysqli -> query("select * from ero_files where server = 'house.porn' order by id desc");

        while($row = $query -> fetch_assoc()){
        
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['screenshot'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['screenshot']);
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['recoil'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['recoil']);
    
        $mysqli -> query("delete from ero_files where id = '$row[id]'");
        $mysqli -> query("delete from ero_favorites where id_video = '$row[id]'");
        
        }
        
        logs($user['id'], $lang['cleared_all_server'].' house.porn.', 0);
        header('Location: /control.html?func=functions_data&'.rand(1,9));
        exit;
    }   else if (isset($_GET['kisa'])){
        
        $query = $mysqli -> query("select * from ero_files where server = 'porno-kisa.com' order by id desc");

        while($row = $query -> fetch_assoc()){
        
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['screenshot'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['screenshot']);
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['recoil'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['recoil']);
    
        $mysqli -> query("delete from ero_files where id = '$row[id]'");
        $mysqli -> query("delete from ero_favorites where id_video = '$row[id]'");
        
        }
        
        logs($user['id'], $lang['cleared_all_server'].' porno-kisa.com.', 0);
        header('Location: /control.html?func=functions_data&'.rand(1,9));
        exit;
    }   else if (isset($_GET['zajka'])){
        
        $query = $mysqli -> query("select * from ero_files where server = 'zajka.org' order by id desc");

        while($row = $query -> fetch_assoc()){
        
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['screenshot'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['screenshot']);
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['recoil'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['recoil']);
    
        $mysqli -> query("delete from ero_files where id = '$row[id]'");
        $mysqli -> query("delete from ero_favorites where id_video = '$row[id]'");
        
        }
        
        logs($user['id'], $lang['cleared_all_server'].' zajka.org.', 0);
        header('Location: /control.html?func=functions_data&'.rand(1,9));
        exit;   
    }   else if (isset($_GET['gig'])){
        
        $query = $mysqli -> query("select * from ero_files where server = 'gig.porn' order by id desc");

        while($row = $query -> fetch_assoc()){
        
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['screenshot'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['screenshot']);
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['recoil'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['recoil']);
    
        $mysqli -> query("delete from ero_files where id = '$row[id]'");
        $mysqli -> query("delete from ero_favorites where id_video = '$row[id]'");
        
        }
        
        logs($user['id'], $lang['cleared_all_server'].' gig.porn.', 0);
        header('Location: /control.html?func=functions_data&'.rand(1,9));
        exit;
    }   else if (isset($_GET['lab'])){
        
        $query = $mysqli -> query("select * from ero_files where server = 'lab.porn' order by id desc");

        while($row = $query -> fetch_assoc()){
        
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['screenshot'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['screenshot']);
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['recoil'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['recoil']);
    
        $mysqli -> query("delete from ero_files where id = '$row[id]'");
        $mysqli -> query("delete from ero_favorites where id_video = '$row[id]'");
        
        }
        
        logs($user['id'], $lang['cleared_all_server'].' lab.porn.', 0);
        header('Location: /control.html?func=functions_data&'.rand(1,9));
        exit;
    }   else if (isset($_GET['krutoeporno'])){
        
        $query = $mysqli -> query("select * from ero_files where server = 'krutoeporno.com' order by id desc");

        while($row = $query -> fetch_assoc()){
        
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['screenshot'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['screenshot']);
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['recoil'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['recoil']);
    
        $mysqli -> query("delete from ero_files where id = '$row[id]'");
        $mysqli -> query("delete from ero_favorites where id_video = '$row[id]'");
        
        }
        
        logs($user['id'], $lang['cleared_all_server'].' krutoeporno.com.', 0);
        header('Location: /control.html?func=functions_data&'.rand(1,9));
        exit;
    }   else if (isset($_GET['pornopups'])){
        
        $query = $mysqli -> query("select * from ero_files where server = 'pornopups.com' order by id desc");

        while($row = $query -> fetch_assoc()){
        
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['screenshot'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['screenshot']);
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['recoil'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['recoil']);
    
        $mysqli -> query("delete from ero_files where id = '$row[id]'");
        $mysqli -> query("delete from ero_favorites where id_video = '$row[id]'");
        
        }
        
        logs($user['id'], $lang['cleared_all_server'].' pornopups.com.', 0);
        header('Location: /control.html?func=functions_data&'.rand(1,9));
        exit;
    }
	
	
	$files_mobolto = $mysqli -> query("select count(*) from ero_files where server = 'mobolto.com'") -> fetch_row();
    $files_pornomir = $mysqli -> query("select count(*) from ero_files where server = 'pornomir.tv'") -> fetch_row();
    $files_pornoraketa = $mysqli -> query("select count(*) from ero_files where server = 'pornoraketa.tv'") -> fetch_row();
    $files_airporno = $mysqli -> query("select count(*) from ero_files where server = 'airporno.ru'") -> fetch_row();
    $files_pizdauz = $mysqli -> query("select count(*) from ero_files where server = 'pizdauz.ru'") -> fetch_row();
    $files_xnxx = $mysqli -> query("select count(*) from ero_files where server = 'xnxx.com'") -> fetch_row();
    $files_pornolomka = $mysqli -> query("select count(*) from ero_files where server = 'pornolomka.mobi'") -> fetch_row();
    $files_pornosto = $mysqli -> query("select count(*) from ero_files where server = 'pornosto.com'") -> fetch_row();
    $files_russpornotube = $mysqli -> query("select count(*) from ero_files where server = 'russpornotube.com'") -> fetch_row();
    $files_rsuka = $mysqli -> query("select count(*) from ero_files where server = 'rsuka.tv'") -> fetch_row();
    $files_oxtube = $mysqli -> query("select count(*) from ero_files where server = 'oxtube.tv'") -> fetch_row();
    $files_hentai = $mysqli -> query("select count(*) from ero_files where server = 'hentai-x.ru'") -> fetch_row();
    $files_ebun = $mysqli -> query("select count(*) from ero_files where server = 'ebun.me'") -> fetch_row();
    $files_vaginke = $mysqli -> query("select count(*) from ero_files where server = 'vaginke.com'") -> fetch_row();
    $files_xcafe = $mysqli -> query("select count(*) from ero_files where server = 'xcafe.com'") -> fetch_row();
    $files_house = $mysqli -> query("select count(*) from ero_files where server = 'house.porn'") -> fetch_row();
    $files_kisa = $mysqli -> query("select count(*) from ero_files where server = 'porno-kisa.com'") -> fetch_row();
    $files_zajka = $mysqli -> query("select count(*) from ero_files where server = 'zajka.org'") -> fetch_row();
    $files_gig = $mysqli -> query("select count(*) from ero_files where server = 'gig.porn'") -> fetch_row();
    $files_uzbum = $mysqli -> query("select count(*) from ero_files where server = 'uzbum.net'") -> fetch_row();
    $files_lab = $mysqli -> query("select count(*) from ero_files where server = 'lab.porn'") -> fetch_row();
    $files_krutoeporno = $mysqli -> query("select count(*) from ero_files where server = 'krutoeporno.com'") -> fetch_row();
    $files_pornopups = $mysqli -> query("select count(*) from ero_files where server = 'pornopups.com'") -> fetch_row();
    
	$_get = file_get_contents('sitemap.xml');
    
    preg_match_all('|<loc>(.*?)</loc>|is', $_get, $links);

    if (isset($_GET['refresh']))    {
      
    $files_all = $mysqli -> query("select count(*) from ero_files") -> fetch_row();
    $screenshots = getFilesSize($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/');
    $video = getFilesSize($_SERVER['DOCUMENT_ROOT'].'/content/video/');    
    $yandex = round(yd_total_space() / 1024 / 1000, 2);
    $cache = getFilesSize($_SERVER['DOCUMENT_ROOT'].'/content/cache/');
    $ver = file_get_contents('https://erocms.ru/version.xml');
    
    $mysqli -> query("update ero_information set all_files = '$files_all[0]', screenshots = '$screenshots', video = '$video', yandex = '$yandex', cache = '$cache', ver = '$ver' where id = '1'");
    
    header('location: /control.html?func=functions_data'); 
    exit;
    }
    
    ?>
    
    <p>
        
    <table border="1" align="center" class="functions_data">
    	    
    <tr>
    <th><small><?=$lang['server']?></small></th>
    <th><small><?=$lang['files']?></small></th>
    <th><small><?=$lang['availability']?></small></th>
    <th></th>
    </tr>
   
    <tr>
    <td><a href="/control.html?func=server&i=mobolto.com">mobolto.com</a></td>
    <td><?=$files_mobolto[0]?></td>
    <td><?=$mobolto?></td>  
    <td><a href="?func=functions_data&mobolto"><?=$lang['clear']?></a></td>  
    </tr>	
    
    <tr>
    <td><a href="/control.html?func=server&i=pornomir.tv">pornomir.tv</a></td>
    <td><?=$files_pornomir[0]?></td>
    <td><?=$pornomir?></td> 
    <td><a href="?func=functions_data&pornomir"><?=$lang['clear']?></a></td>  
    </tr>
    
    <tr>
    <td><a href="/control.html?func=server&i=pornoraketa.tv">pornoraketa.tv</a></td>
    <td><?=$files_pornoraketa[0]?></td>
    <td><?=$pornoraketa?></td>  
    <td><a href="?func=functions_data&pornoraketa"><?=$lang['clear']?></a></td>  
    </tr>
    
    <tr>
    <td><a href="/control.html?func=server&i=airporno.ru">airporno.ru</a></td>
    <td><?=$files_airporno[0]?></td>
    <td><?=$airporno?></td>  
    <td><a href="?func=functions_data&airporno"><?=$lang['clear']?></a></td>  
    </tr>
    
    <tr>
    <td><a href="/control.html?func=server&i=pizdauz.ru">pizdauz.ru</a></td>
    <td><?=$files_pizdauz[0]?></td>
    <td><?=$pizdauz?></td>  
    <td><a href="?func=functions_data&pizdauz"><?=$lang['clear']?></a></td>  
    </tr>

    <tr>
    <td><a href="/control.html?func=server&i=xnxx.com">xnxx.com</a></td>
    <td><?=$files_xnxx[0]?></td>
    <td><?=$xnxx?></td>  
    <td><a href="?func=functions_data&xnxx"><?=$lang['clear']?></a></td>  
    </tr>

    <tr>
    <td><a href="/control.html?func=server&i=pornolomka.mobi">pornolomka.mobi</a></td>
    <td><?=$files_pornolomka[0]?></td>
    <td><?=$pornolomka?></td>  
    <td><a href="?func=functions_data&pornolomka"><?=$lang['clear']?></a></td>  
    </tr>

    <tr>
    <td><a href="/control.html?func=server&i=pornosto.com">pornosto.com</a></td>
    <td><?=$files_pornosto[0]?></td>
    <td><?=$pornosto?></td>  
    <td><a href="?func=functions_data&pornosto"><?=$lang['clear']?></a></td>  
    </tr>

    <tr>
    <td><a href="/control.html?func=server&i=russpornotube.com">russpornotube.com</a></td>
    <td><?=$files_russpornotube[0]?></td>
    <td><?=$russpornotube?></td>  
    <td><a href="?func=functions_data&russpornotube"><?=$lang['clear']?></a></td>  
    </tr>

    <tr>
    <td><a href="/control.html?func=server&i=rsuka.tv">rsuka.tv</a></td>
    <td><?=$files_rsuka[0]?></td>
    <td><?=$rsuka?></td>  
    <td><a href="?func=functions_data&rsuka"><?=$lang['clear']?></a></td>  
    </tr>

    <tr>
    <td><a href="/control.html?func=server&i=oxtube.tv">oxtube.tv</a></td>
    <td><?=$files_oxtube[0]?></td>
    <td><?=$oxtube?></td>  
    <td><a href="?func=functions_data&oxtube"><?=$lang['clear']?></a></td>  
    </tr>

    <tr>
    <td><a href="/control.html?func=server&i=hentai-x.ru">hentai-x.ru</a></td>
    <td><?=$files_hentai[0]?></td>
    <td><?=$hentai?></td>  
    <td><a href="?func=functions_data&hentai"><?=$lang['clear']?></a></td>  
    </tr>

    <tr>
    <td><a href="/control.html?func=server&i=ebun.me">ebun.me</a></td>
    <td><?=$files_ebun[0]?></td>
    <td><?=$ebun?></td>  
    <td><a href="?func=functions_data&ebun"><?=$lang['clear']?></a></td>  
    </tr>

    <tr>
    <td><a href="/control.html?func=server&i=vaginke.com">vaginke.com</a></td>
    <td><?=$files_vaginke[0]?></td>
    <td><?=$vaginke?></td>  
    <td><a href="?func=functions_data&vaginke"><?=$lang['clear']?></a></td>  
    </tr>

    <tr>
    <td><a href="/control.html?func=server&i=xcafe.com">xcafe.com</a></td>
    <td><?=$files_xcafe[0]?></td>
    <td><?=$xcafe?></td>  
    <td><a href="?func=functions_data&xcafe"><?=$lang['clear']?></a></td>  
    </tr>
  
    <tr>
    <td><a href="/control.html?func=server&i=house.porn">house.porn</a></td>
    <td><?=$files_house[0]?></td>
    <td><?=$house?></td>  
    <td><a href="?func=functions_data&house"><?=$lang['clear']?></a></td>  
    </tr>

    <tr>
    <td><a href="/control.html?func=server&i=porno-kisa.com">porno-kisa.com</a></td>
    <td><?=$files_kisa[0]?></td>
    <td><?=$kisa?></td>  
    <td><a href="?func=functions_data&kisa"><?=$lang['clear']?></a></td>  
    </tr>

    <tr>
    <td><a href="/control.html?func=server&i=zajka.org">zajka.org</a></td>
    <td><?=$files_zajka[0]?></td>
    <td><?=$zajka?></td>  
    <td><a href="?func=functions_data&zajka"><?=$lang['clear']?></a></td>  
    </tr>

    <tr>
    <td><a href="/control.html?func=server&i=gig.porn">gig.porn</a></td>
    <td><?=$files_gig[0]?></td>
    <td><?=$gig?></td>  
    <td><a href="?func=functions_data&gig"><?=$lang['clear']?></a></td>  
    </tr>

    <tr>
    <td><a href="/control.html?func=server&i=uzbum.net">uzbum.net</a></td>
    <td><?=$files_uzbum[0]?></td>
    <td><?=$uzbum?></td>  
    <td><a href="?func=functions_data&uzbum"><?=$lang['clear']?></a></td>  
    </tr>

    <tr>
    <td><a href="/control.html?func=server&i=lab.porn">lab.porn</a></td>
    <td><?=$files_lab[0]?></td>
    <td><?=$lab?></td>  
    <td><a href="?func=functions_data&lab"><?=$lang['clear']?></a></td>  
    </tr>

    <tr>
    <td><a href="/control.html?func=server&i=krutoeporno.com">krutoeporno.com</a></td>
    <td><?=$files_krutoeporno[0]?></td>
    <td><?=$krutoeporno?></td>  
    <td><a href="?func=functions_data&krutoeporno"><?=$lang['clear']?></a></td>  
    </tr>

    <tr>
    <td><a href="/control.html?func=server&i=pornopups.com">pornopups.com</a></td>
    <td><?=$files_pornopups[0]?></td>
    <td><?=$pornopups?></td>  
    <td><a href="?func=functions_data&pornopups"><?=$lang['clear']?></a></td>  
    </tr>
    
    </table>
    
    </p>

    <?
    
    $information = $mysqli -> query("select * from ero_information where id = '1'") -> fetch_assoc();
    
    ?>
    
    <?
    
    if ($information['ver'] > $version)    {
        
    ?>
    
    <script>
  alert("<?=$lang['new_version']?>");
    </script>
    
    <?
    
    $down = '<a href="http://4i4i.su/goods/2408" class="tach"><font color="red">'.$lang['get_scripts'].'</font></a> ';
    
    }   else $down = null;
    
    ?>
    
	<?=$down?>
	<a href="?func=functions_data&refresh" class="tach"><?=$lang['update']?></a> 
	<a href="?func=appeal" class="tach"><?=$lang['support']?></a> 
	<a href="?func=functions_data&verification" class="tach"><?=$lang['check_server']?></a> 
	<a href="?func=functions_data&cache" class="tach"><?=$lang['clear_cache']?></a>
    <a href="?func=functions_data&sitemap" class="tach"><?=$lang['refresh']?> sitemap.xml (<?=count($links[1])?>)</a>
    <a href="?func=functions_data&robots" class="tach"><?=$lang['add']?> robots.txt</a>
    
    <p class="functions_data"> 
    <?=$lang['all_video']?> <b><?=$information['all_files']?></b> <br />
    <?=$lang['folder_size']?> <u>/content/screenshots/</u> <b><?=$information['screenshots']?></b> <br />
    <?=$lang['folder_size']?> <u>/content/video/</u> <b><?=$information['video']?></b> <br />
    <?=$lang['limit_yandex']?> <b><?=$information['yandex']?> мб.</b> <br />
    <?=$lang['total_cache_size']?> <b><?=$information['cache']?></b> <br />
    PHP <b><?=phpversion()?></b><br />
    
    <?
    if (class_exists('ffmpeg_movie'))   {
    ?>
    FFmpeg <b><font color="green">is included</font></b><br />
    <?
    }   else    {
    ?>
    FFmpeg <b><font color="red">off</font></b><br />
    <?
    }
    ?>
    
    MySQL <b><?=$mysqli->server_info?></b><br />
    <?=$lang['version']?> EroCMS <b><?=$version?></b><br />
    <?=$lang['current_version']?> <b><?=$information['ver']?></b> <br />
    Telegram <a href="https://t.me/d1nka_try"><b>@d1nka_try</b></a><br />
    </p>