<?php

/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/
    
    if (!isset($_GET['page'])) $caching = $_SERVER['DOCUMENT_ROOT'].'/content/cache/new.html';
    else $caching = $_SERVER['DOCUMENT_ROOT'].'/content/cache/new_'.abs(intval($_GET['page'])).'.html';
    
    if (file_exists($caching)) {

    if ((time() - $settings['cache']) < filemtime($caching)) {

        echo file_get_contents($caching); 
        
        foot();
        exit; 
        
        }
    }
  
    ob_start();
    
    $title = $lang['new_video'].' - '.filter($_SERVER['HTTP_HOST']);
    $description = $settings['description'];
    $keywords = $settings['keywords'];
    
    head();
    advertising();
    
    ?>
    
    <h2 class="view"><?=$lang['new_video']?></h2>
    
    <?
    
    $quantity = $mysqli -> query("select count(*) from ero_files where date < '".time()."'") -> fetch_row();
    $k_page = k_page($quantity[0], 20);
    $page = page($k_page);
    $start = 20*$page-20;
    
    $query = $mysqli -> query("select id, screenshot, name, translit, duration, view from ero_files where date < '".time()."' order by date desc limit $start, 20");
    
    while($row = $query -> fetch_assoc()) {

    if ($user['access'] == 1) 
    $edit = '<p align="right"><a href="/editing_'.$row['id'].'.html"><img src="/designs/icons/view/edit.png" width="16" height="16" /> '.$lang['edit'].'</a>
    <a href="/deletion_'.$row['id'].'.html"><img src="/designs/icons/view/remove.png" width="16" height="16" /> '.$lang['remove'].'</a></p>'; else $edit = false;
    
    echo '<a href="/watch/'.$row['translit'].'.html" class="tach" title="'.$row['name'].'">
    <img class="screenshots" src="'. $row['screenshot'] .'" alt="'.$row['name'].'" />
    <span class="sample">'.$row['duration'].'</span>
    <h2 style="font-size: 12px;">'.$row['name'].'</h2></a>'.$edit;
    
    }
    
    if ($k_page > 1) str('/new.html?', $k_page, $page);

    $handle = fopen($caching, 'w'); 
	
    fwrite($handle, ob_get_contents()); 
    fclose($handle); 
    
    ob_end_flush();
    
    $query -> free();