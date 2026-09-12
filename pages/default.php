<?php

/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/

    $caching = $_SERVER['DOCUMENT_ROOT'].'/content/cache/default.html';
  
    if (file_exists($caching)) {

    if ((time() - $settings['cache']) < filemtime($caching)) {

        echo file_get_contents($caching); 
        
        foot();
        exit; 
        
        }
    }
  
    ob_start();
    
    $title = $settings['title'];
    $description = $settings['description'];
    $keywords = $settings['keywords'];
    
    head();
    advertising();
    
    #404 ошибка
    
    if (isset($_GET['error'])) error($lang['not_found']);

    #Новые видео
    
    ?>
    
    <p class="functions_data" align="center"><?=$lang['new_video']?></p>
    
    <style>

    .data_set {
    position: relative;
    width: 24.1%;
    display: inline-block;
    overflow: hidden;
    vertical-align: top;
    padding: 0;
    box-shadow: 0 0 12px #292929;
    margin: 5px 3px;
    transition: ease all .3s;
    }

    .width {
    position: relative;
    }

    .love {
    top: 0;
    right: 0;
    padding: 4px 5px 4px 9px;
    background: rgba(0,0,0,0.7);
    }

    h2 {
    display: block;
    font-size: 1.5em;
    margin-block-start: 0.83em;
    margin-block-end: 0.83em;
    margin-inline-start: 0px;
    margin-inline-end: 0px;
    font-weight: bold;
    }

    .appellative a {
    display: block;
    background-color: #444444;
    line-height: 22px;
    cursor: pointer;
    padding: 4px 3px 3px;
    height: 53px;
    color: #fff;
    overflow: hidden;
    }

    </style>
    
    <?

    $query = $mysqli -> query("select screenshot, name, duration, translit from ero_files where date < '".time()."' order by date desc limit 3");

    while($row = $query -> fetch_assoc())
    
    echo '<a href="/watch/'.$row['translit'].'.html" class="tach" title="'.$row['name'].'">
    <img class="screenshots" src="'. $row['screenshot'] .'" alt="'.$row['name'].'" />
    <span class="sample">'.$row['duration'].'</span>
    <h2 style="font-size: 12px;">'.$row['name'].'</h2></a>';
    
    #Вывод категорий
    
    $query = $mysqli -> query("select id, name, translit from ero_categories order by id asc");

    while($row = $query -> fetch_assoc()){
    
    $quantity = $mysqli -> query("select count(*) from ero_files where category = '$row[id]' and date < '".time()."'") -> fetch_row();

    $new = $mysqli -> query("select count(*) from ero_files where category = '$row[id]' and date > '".(time()-86400)."' and date < '".time()."'") -> fetch_row();

    if ($new[0] > 0) $new = '<span class="new">'.$new[0].'</span>'; else $new = false;
    
    echo '<a href="/'.$row['translit'].'/" class="tach" title="'.$row['name'].'">
    '.$new.' <span class="sample">'.$quantity[0].'</span>
    <h2 style="font-size: 12px;"> <img src="/designs/icons/view/category.png" width="16" height="16" /> '.$row['name'].'</h2></a>';
    
    }

    $handle = fopen($caching, 'w'); 
	
    fwrite($handle, ob_get_contents()); 
    fclose($handle); 
    
    ob_end_flush();
    
    $query -> free();