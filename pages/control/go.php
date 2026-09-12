<?php

/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/

    $view = $mysqli -> query("select * from ero_files where id = '".abs(intval($_GET['id_file']))."'") -> fetch_assoc();
    
    if (!$view) {
        header('location: /');
        exit;
    }
    
    header('location: /watch/'.$view['translit'].'.html');
    
    $view -> free();