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
    
	$removeCat = $mysqli -> query("select * from ero_categories where id = '".abs(intval($_GET['id']))."'") -> fetch_assoc();
	
    if (!$removeCat or $removeCat['id'] < 21){
        header('location: /');
        exit;
    }
    
    
    $query = $mysqli -> query("select * from ero_files where category = '$removeCat[id]' order by id desc");

    while($row = $query -> fetch_assoc()){
        
    if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['screenshot'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['screenshot']);
    if (file_exists($_SERVER['DOCUMENT_ROOT'].$row['recoil'])) unlink($_SERVER['DOCUMENT_ROOT'].$row['recoil']);
    
    $mysqli -> query("delete from ero_files where id = '$row[id]'");
    $mysqli -> query("delete from ero_favorites where id_video = '$row[id]'");
        
    }
    
    logs($user['id'], $lang['deleted_a_category'].' '.$removeCat['name'].'.', 0);
    
    $mysqli -> query("delete from ero_categories where id = '$removeCat[id]'");
    
    header('location: /'); 
    
    $removeCat -> free();