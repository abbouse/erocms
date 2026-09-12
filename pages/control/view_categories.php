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
    
    $query = $mysqli -> query("select id, name, translit, view from ero_categories order by view desc");

    while($row = $query -> fetch_assoc()){
    
    $quantity = $mysqli -> query("select count(*) from ero_files where category = '$row[id]'") -> fetch_row();
        
    echo '<a href="/'.$row['translit'].'/" class="tach">
    <font color="DimGrey">'.$row['name'].'</font>
    <p align="right">
    <img src="/designs/icons/view/files.png" width="16" height="16" /> '.$quantity[0].'
    <img src="/designs/icons/view/view.png" width="16" height="16" /> '.$row['view'].'
    </p></a>';
    
    }
    
    $query -> free();