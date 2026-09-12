<?php

/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/

    $title = $lang['chosen'];
    $description = $settings['description'];
    $keywords = $settings['keywords'];
        
    head();
    advertising();
    
    $quantity = $mysqli -> query("select count(*) from ero_favorites where data = '".mysqli_real_escape_string($mysqli, filter($_SERVER['REMOTE_ADDR']))."'") -> fetch_row();
    $k_page = k_page($quantity[0], 12);
    $page = page($k_page);
    $start = 12*$page-12;
    
    if ($quantity[0] == 0) echo '<div class="err">'.$lang['no_files_found'].'</div>';
    
    $query = $mysqli -> query("select id, data, id_video from ero_favorites where data = '".mysqli_real_escape_string($mysqli, filter($_SERVER['REMOTE_ADDR']))."' order by id desc limit $start, 12");
    
    while($row = $query -> fetch_assoc()) {
    
    $favorites = $mysqli -> query("select * from ero_files where id = '$row[id_video]'") -> fetch_assoc();
    
    if ($user['access'] == 1) 
    $edit = '<p align="right"><a href="/editing_'.$favorites['id'].'.html"><img src="/designs/icons/view/edit.png" width="16" height="16" /> '.$lang['edit'].'</a>
    <a href="/deletion_'.$favorites['id'].'.html"><img src="/designs/icons/view/remove.png" width="16" height="16" /> '.$lang['remove'].'</a></p>'; else $edit = false;
    
    echo '<a href="/watch/'.$favorites['translit'].'.html" class="tach" title="'.$favorites['name'].'">
    <img class="screenshots" src="'. $favorites['screenshot'] .'" alt="'.$favorites['name'].'" />
    <span class="sample">'.$favorites['duration'].'</span>
    <h2 style="font-size: 12px;">'.$favorites['name'].'</h2></a>'.$edit;
    
    }
    
    if ($k_page > 1) str('/favorites?', $k_page, $page);
    
    $query -> free();