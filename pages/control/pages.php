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
    
    <div class="functions_data">
        
    <a href="?func=view_video" class="tach"><?=$lang['popular_videos']?></a>
    <a href="?func=view_categories" class="tach"><?=$lang['popular_category']?></a>
    
    </div>