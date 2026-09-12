<?php

/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/

    $title = $lang['online'];
    $description = $settings['description'];
    $keywords = $settings['keywords'];
        
    head();
    advertising();
    
    $quantity = $mysqli -> query("select count(*) from ero_online") -> fetch_row();
    $k_page = k_page($quantity[0], 12);
    $page = page($k_page);
    $start = 12*$page-12;
    
    if ($quantity[0] == 0) echo '<div class="err">'.$lang['users_not_found'].'</div>';
    
    $query = $mysqli -> query("select id, ip, date from ero_online order by id desc limit $start, 12");
    
    ?>
    
    <table class="functions_data" border="3" align="center" cellspacing="5" cellpadding="10" border="1" width="100%">
   <tr>
    <th><img src="/designs/icons/view/view.png" width="16" height="16" /> <?=$lang['user']?></th>
    <th><img src="/designs/icons/view/added.png" width="16" height="16" /> <?=$lang['visit']?></th>
   </tr>
   
   <?
   
    while($row = $query -> fetch_assoc()) {
    
    ?>
    
    <tr>
        <td><?=$row['ip'];?></td>
        <td><?=date('H:i:s', $row['date']);?></td>
    </tr>
    
    <?
    
    }
    
    ?>
    
    </table>
    
    <?

    if ($k_page > 1) str('/online.html?', $k_page, $page);
    
    $query -> free();