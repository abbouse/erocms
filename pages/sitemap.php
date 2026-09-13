<?php

/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/

    if (!isset($_GET['page'])) $caching = $_SERVER['DOCUMENT_ROOT'].'/content/cache/'.filter($_GET['translit']).'.html';
    else $caching = $_SERVER['DOCUMENT_ROOT'].'/content/cache/'.filter($_GET['translit']).'_'.abs(intval($_GET['page'])).'.html';
    
    if (file_exists($caching)) {

    if ((time() - $settings['cache']) < filemtime($caching)) {

        echo file_get_contents($caching); 
        
        foot();
        exit; 
        
        }
    }
  
    ob_start();
    
    $title = $lang['map'].' '.filter($_SERVER['HTTP_HOST']);
    $description = $settings['description'];
    $keywords = $settings['keywords'];
    
    head();
    advertising();
    
    $quantity = $mysqli -> query("select count(*) from ero_files where date < '".time()."'") -> fetch_row();
    $k_page = k_page($quantity[0], 20);
    $page = page($k_page);
    $start = 20*$page-20;
    
    echo '<h2 class="view">'.$lang['map'].' '.filter($_SERVER['HTTP_HOST']).' <img src="/designs/icons/view/files.png" width="16" height="16" /> '.$quantity[0].' '.$lang['video'].'</h2>';
    
    $query = $mysqli -> query("select id, screenshot, name, translit, duration, view from ero_files where date < '".time()."' order by date desc limit $start, 20");
    
    while($row = $query -> fetch_assoc()) {

    if ($user['access'] == 1) 
    $edit = '<p align="right"><a href="/editing_'.$row['id'].'.html"><img src="/designs/icons/view/edit.png" width="16" height="16" /> '.$lang['edit'].'.</a>
    <a href="/deletion_'.$row['id'].'.html"><img src="/designs/icons/view/remove.png" width="16" height="16" /> '.$lang['remove'].'.</a></p>'; else $edit = false;
    
    echo '<div class="xxxhd-thumb-wr"><div class="xxxhd-thumb">
    <a href="/watch/'.$row['translit'].'.html" title="'.$row['name'].'">
    <img src="'.$row['screenshot'].'" alt="'.$row['name'].'" width="300" height="180" />
    <div class="xxxhd-thumb-name" title="'.$row['name'].'">'.$row['name'].'</div>
    </a>
    <span class="xxxhd-thumb-top top-right"><i class="fa fa-eye"></i> '.$row['view'].'</span>
    <span class="xxxhd-thumb-bottom bottom-right"><i class="fa fa-clock-o"></i> '.$row['duration'].'</span>
    '.$edit.'
    </div></div>';
    
    }
    
    if ($k_page > 1) str('/sitemap.html?', $k_page, $page);

    $handle = fopen($caching, 'w'); 
	
    fwrite($handle, ob_get_contents()); 
    fclose($handle); 
    
    ob_end_flush();
    
    $query -> free();