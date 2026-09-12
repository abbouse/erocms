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
    
    $quantity = $mysqli -> query("select count(*) from ero_files where date > '".time()."'") -> fetch_row();
    $k_page = k_page($quantity[0], 12);
    $page = page($k_page);
    $start = 12*$page-12;
    
    if ($quantity[0] == 0) echo '<div class="err">'.$lang['no_files_found'].'</div>';
    
    $query = $mysqli -> query("select id, screenshot, name, translit, view, duration, date from ero_files where date > '".time()."' order by id desc limit $start, 12");

    while($row = $query -> fetch_assoc()) {

    echo '<a href="/watch/'.$row['translit'].'.html" class="tach" title="'.$row['name'].'">
    <img class="screenshots" src="'. $row['screenshot'] .'" alt="'.$row['name'].'" />
    <span class="sample">'.$row['duration'].'</span>
    <h2 style="font-size: 12px;">'.$row['name'].'</h2></a>';
    
    }
    
    if ($k_page > 1) str('/control.html?func=publish&', $k_page, $page);    
    
    $query -> free();