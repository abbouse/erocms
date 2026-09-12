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

    $logs = $mysqli -> query("select * from ero_logs where id_user = '".abs(intval($_GET['id']))."'") -> fetch_assoc();
    
    if (!$logs){
        header('location: /');
        exit;
    }

    if (isset($_GET['clear'])){
        
        $mysqli -> query("delete from ero_logs where id_user = '$logs[id_user]'");
        logs($user['id'], $lang['cleared_user_logs'], 0);
        header('location: /control.html?func=users');
        exit;
    }
    
    ?>
        
    <a href="/control.html?func=logs&id=<?=$logs['id_user']?>&clear" class="tach"><?=$lang['clear_all_logs']?></a>
    
    <?
    
    $quantity = $mysqli -> query("select count(*) from ero_logs where id_user = '$logs[id_user]'") -> fetch_row();
    $k_page = k_page($quantity[0], 10);
    $page = page($k_page);
    $start = 10*$page-10;

    if ($quantity == 0) echo '<div class="err">'.$lang['data_not_found'].'</div>';

    $query = $mysqli -> query("select id, id_file, act, date from ero_logs where id_user = '$logs[id_user]' order by id desc limit $start, 10");

    while($row = $query -> fetch_assoc()) {

    ?>
    
    <p class="functions_data"> 
    <? if ($row['id_file'] > 0) { ?> <a href="?func=go&id_file=<?=$row['id_file']?>"> <? } ?>
    <?=$row['act']?> <small><b>[<?=date('Y-m-d H:i:s', $row['date'])?>]</b></small>
    <? if ($row['id_file'] > 0) { ?> </a> <? } ?>
    </p>
    
    <?
    
    }
    
    if ($k_page > 1) str('/control.html?func=logs&id='.$logs['id_user'].'&', $k_page, $page);    

    $query -> free();