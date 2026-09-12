<?php

/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/

    $search =  mysqli_real_escape_string($mysqli, filter($_GET['i']));     
    $quantity = $mysqli -> query("select count(*) from ero_files where server like '%".$search."%'") -> fetch_row();
    $k_page = k_page($quantity[0], 12);
    $page = page($k_page);
    $start = 12*$page-12;
    
    if ($quantity[0] == 0) echo '<div class="err">'.$lang['no_files_found'].'</div>';
    
    $query = $mysqli -> query("select id, screenshot, name, translit, duration, view from ero_files where server like '%".$search."%' order by id desc limit $start, 12");

    while($row = $query -> fetch_assoc()) {
        
    if ($user['access'] == 1) 
    $edit = '<p align="right"><a href="/editing_'.$row['id'].'.html"><img src="/designs/icons/view/edit.png" width="16" height="16" /> '.$lang['edit'].'</a>
    <a href="/deletion_'.$row['id'].'.html"><img src="/designs/icons/view/remove.png" width="16" height="16" /> '.$lang['remove'].'</a></p>'; else $edit = false;
    
    echo '<a href="/watch/'.$row['translit'].'.html" class="tach" title="'.$row['name'].'">
    <img class="screenshots" src="'. $row['screenshot'] .'" alt="'.$row['name'].'" />
    <span class="sample">'.$row['duration'].'</span>
    <h2 style="font-size: 12px;">'.$row['name'].'</h2></a>'.$edit;
    
    }
    
    if ($k_page > 1) str('/control.html?func=server&i='.$search.'&', $k_page, $page); 
    
    $query -> free();