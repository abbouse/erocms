<?php

/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/

    $search =  mysqli_real_escape_string($mysqli, filter($_GET['i']));     
        
    $title = $lang['searching_results'].' "'.$search.'"';
    $description = $settings['description'];
    $keywords = $settings['keywords'];
        
    head();
    advertising();
    
    $quantity = $mysqli -> query("select count(*) from ero_files where description like '%".$search."%' or name like '%".$search."%' and date < '".time()."'") -> fetch_row();
    
    ?>
    
    <p class="view"><?=$lang['found']?> <b><?=$quantity[0];?></b> <?=$lang['video']?></p>
    
    <?
    
    $k_page = k_page($quantity[0], 12);
    $page = page($k_page);
    $start = 12*$page-12;
    
    if ($quantity[0] == 0) echo '<div class="err">'.$lang['no_files_found'].'</div>';
    
    $query = $mysqli -> query("select id, screenshot, name, translit, duration, view from ero_files where description like '%".$search."%' or name like '%".$search."%' and date < '".time()."' order by date desc limit $start, 12");
    
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
    
    if ($k_page > 1) str('/tag/'.$search.'&', $k_page, $page);
    
    $query -> free();