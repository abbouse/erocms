<?php

/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/

    $view = $mysqli -> query("select yd, id, address, translit, recoil from ero_files where translit = '".mysqli_real_escape_string($mysqli, filter($_GET['video']))."'") -> fetch_assoc();
   
    if (!$view){
        header('location: /');
        exit;
    }
    
    if ($view['yd'] == 0)
    header('location: '.$view['address']);
    else header('location: '.$view['recoil']);
    
    $view -> free();
    exit;