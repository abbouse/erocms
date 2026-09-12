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
    
    $view = $mysqli -> query("select id, screenshot, recoil, name, category from ero_files where id = '".abs(intval($_GET['id']))."'") -> fetch_assoc();
     
    if (!$view){
        header('location: /');
        exit;
    }
    
    $category = $mysqli -> query("select id, translit from ero_categories where id = '".$view['category']."'") -> fetch_assoc();
    
    $mysqli -> query("INSERT INTO ero_logs SET id_user = '$user[id]', act = 'Удалил видео $view[name].', id_file = '$view[id]', date = '".time()."'");
    
    if (file_exists($_SERVER['DOCUMENT_ROOT'].$view['screenshot'])) unlink($_SERVER['DOCUMENT_ROOT'].$view['screenshot']);
    if (file_exists($_SERVER['DOCUMENT_ROOT'].$view['recoil'])) unlink($_SERVER['DOCUMENT_ROOT'].$view['recoil']);
    
    $mysqli -> query("delete from ero_files where id = '$view[id]'");
 
    header('location: /'.$category['translit'].'/');
    
    $view -> free();