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
    
    ?>
        
    <a href="?func=users&add_user" class="tach"><?=$lang['add_user']?></a>
    
    <?
    
    if (isset($_GET['add_user'])) {
        
        $password = md5(rand(100000, 99999999));
        $mysqli -> query("INSERT INTO ero_users SET password = '".md5(md5($password))."', disclosed = '$password'");
        logs($user['id'], $lang['added_user'].' '.$password.'.', 0);
        header('location: /control.html?func=users');     
        exit;
    }
    
    if (isset($_GET['deletion'])) {
        
        if ($user['id'] != $_GET['deletion']) {
            
        $mysqli -> query("delete from ero_users where id = '".abs(intval($_GET['deletion']))."'");
        logs($user['id'], $lang['deleted_the_user'], 0);
        header('location: /control.html?func=users');     
        exit;
        
        } else error($lang['this_user_cannot_be_deleted']);
    }
    
    $quantity = $mysqli -> query("select count(*) from ero_users") -> fetch_row();
    $k_page = k_page($quantity[0], 10);
    $page = page($k_page);
    $start = 10*$page-10;
    
    if ($quantity[0] == 0) echo '<div class="err">'.$lang['users_not_found'].'</div>';
    
    $query = $mysqli -> query("select id, disclosed, information from ero_users order by id desc limit $start, 10");

    while($row = $query -> fetch_assoc()) {

    ?>
       <p class="functions_data"> 
       <?=$lang['pass']?>: <a href="?func=logs&id=<?=$row['id'];?>"><b><?= $row['disclosed']?></b></a> <a href="/control.html?func=users&deletion=<?=$row['id'];?>"><img src="/designs/icons/view/remove.png" width="16" height="16" /></a>
       <b> <? if ($row['information']) echo $row['information']; else echo 'Data not updated'; ?> </b>
       </p>
    
    <? 
    
    }
    
    $query -> free();