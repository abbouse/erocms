<?php

/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/

    $view = $mysqli -> query("select address, id from ero_files where translit = '".mysqli_real_escape_string($mysqli, filter($_GET['translit']))."'") -> fetch_assoc();
    
    if (!$view){
        header('location: /');
        exit;
    }
    
    $mysqli -> query("update ero_files set downloads = downloads + '1' where id = '$view[id]'");
    
    if ($settings['recoil'] == 1)   {
        
        header('location: '.$view['address']);
        exit;
    }
    
    $jump = $view['address'];

    $filesize = size($jump);
    $cargo = $jump; 
    $jump = $jump; 

    $pump = new SplFileInfo($cargo); 
    $compos = $pump->getFilename(); 

    if ($swings = get_headers($jump, 1)): 
    
    if (!empty($swings['Location'])): 
    $jump = $swings['Location']; 
    $swings = get_headers($jump, 1); 
    endif; 

    if (ob_get_level()) 
    ob_end_clean(); 

    header('Content-Description: File Transfer'); 
    header('Content-Type: application/octet-stream'); 
    header('Content-Disposition: attachment; filename='.filter($_GET['translit']).'_'.filter($_SERVER['SERVER_NAME']).'.mp4'); 
    header('Content-Transfer-Encoding: binary'); 
    header('Expires: 0'); 
    header('Cache-Control: must-revalidate'); 
    header('Pragma: public'); 
    header('Content-Length: ' . $filesize); 
    readfile($cargo) . exit; 
    else: 
    header('location: ' . $cargo) . exit; 
    endif; 

    $view -> free();