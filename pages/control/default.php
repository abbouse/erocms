<?php

/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/
    
    if (isset($_GET['out'])){

	    setcookie('password');
	    session_destroy();
        logs($user['id'], $lang['left_the_site'].'.', 0);
        header('Location: /control.html?'.rand(1,9));
        exit;
    }

    $files_publish = $mysqli -> query("select count(*) from ero_files where date > '".time()."'") -> fetch_row();
    $advertising = $mysqli -> query("select count(*) from ero_advertising") -> fetch_row();
    $users = $mysqli -> query("select count(*) from ero_users") -> fetch_row();

    ?>

<table cellpadding="7" width="100%" class="functions_data">
  
  <tr>
    <th><div class="menu_j"><a href="?func=rewriting" class="top_menu_j"><img src="/designs/icons/control/rewriting.png" width="32" height="32" /><p><?=$lang['rewriting']?></p></a></div></th>
    <th><div class="menu_j"><a href="?func=add" class="top_menu_j"><img src="/designs/icons/control/add.png" width="32" height="32" /><p><?=$lang['add_video']?></p></a></div></th>
    <th><div class="menu_j"><a href="?func=import_main" class="top_menu_j"><img src="/designs/icons/control/import.png" width="32" height="32" /><p><?=$lang['import_video']?></p></a></div></th>
    <th><div class="menu_j"><a href="?func=users" class="top_menu_j"><img src="/designs/icons/control/users.png" width="32" height="32" /><p><?=$lang['users']?> (<?=$users[0]?>)</p></a></div></th>
  </tr>
  
  <?
 
    if ($user['access'] == 1) {
  
  ?>
  
  <tr>
    <th><div class="menu_j"><a href="?func=settings" class="top_menu_j"><img src="/designs/icons/control/settings.png" width="32" height="32" /><p><?=$lang['settings']?></p></a></div></th>
    <th><div class="menu_j"><a href="?func=pages" class="top_menu_j"><img src="/designs/icons/control/pages.png" width="32" height="32" /><p><?=$lang['popular']?></p></a></div></th>
    <th><div class="menu_j"><a href="?func=parsing" class="top_menu_j"><img src="/designs/icons/control/parsing.png" width="32" height="32" /><p><?=$lang['parsing_files']?></p></a></div></th>
    
    <?
    
    if ($user['access'] == 1) {
        
    ?>
    
    <th><div class="menu_j"><a href="?func=addCat" class="top_menu_j"><img src="/designs/icons/control/addCat.png" width="32" height="32" /><p><?=$lang['create_category']?></p></a></div></th>
     
  <?
  
    }
    
  ?>
  
  </tr>
 
 <?
 
    }
    
 ?>
 
  <tr>

  <?
 
    if ($user['access'] == 1) {
  
  ?>
  
    <th><div class="menu_j"><a href="?func=publish" class="top_menu_j"><img src="/designs/icons/control/publish.png" width="32" height="32" /><p><?=$lang['deferred']?> (<?=$files_publish[0]?>)</p></a></div></th>
    <th><div class="menu_j"><a href="?func=advertising" class="top_menu_j"><img src="/designs/icons/control/accommodation.png" width="32" height="32" /><p><?=$lang['buy_ads']?> (<?=$advertising[0]?>)</p></a></div></th>
    <th><div class="menu_j"><a href="?func=functions_data" class="top_menu_j"><img src="/designs/icons/control/functions_data.png" width="32" height="32" /><p><?=$lang['info']?></p></a></div></th>
    
    <?
    
    }
    
    ?>
    
    <th><div class="menu_j"><a href="?func=out" class="top_menu_j"><img src="/designs/icons/control/out.png" width="32" height="32" /><p><?=$lang['exit']?></p></a></div></th>
  </tr>
  
</table>