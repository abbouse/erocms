<?php

/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/

    $caching = $_SERVER['DOCUMENT_ROOT'].'/content/cache/default.html';
  
    if (file_exists($caching)) {

    if ((time() - $settings['cache']) < filemtime($caching)) {

        echo file_get_contents($caching); 
        
        foot();
        exit; 
        
        }
    }
  
    ob_start();
    
    $title = $settings['title'];
    $description = $settings['description'];
    $keywords = $settings['keywords'];
    
    head();
    advertising();
    
    #404 ошибка
    
    if (isset($_GET['error'])) error($lang['not_found']);

    #Новые видео
    
    ?>

    <div class="xxxhd-title-top"><h2><?=$lang['new_video']?></h2></div>
    <div class="xxxhd-thumbs-content">

    <?

    $query = $mysqli -> query("select screenshot, name, duration, translit from ero_files where date < '".time()."' order by date desc limit 3");

    while($row = $query -> fetch_assoc())
    echo '<div class="xxxhd-thumb-wr"><div class="xxxhd-thumb">
    <a href="/watch/'.$row['translit'].'.html" title="'.$row['name'].'">
    <img src="'.$row['screenshot'].'" alt="'.$row['name'].'" width="300" height="180" />
    <div class="xxxhd-thumb-name" title="'.$row['name'].'">'.$row['name'].'</div>
    </a>
    <span class="xxxhd-thumb-bottom bottom-right"><i class="fa fa-clock-o"></i> '.$row['duration'].'</span>
    </div></div>';
    
    #Вывод категорий
    
    $query = $mysqli -> query("select id, name, translit from ero_categories order by id asc");

    while($row = $query -> fetch_assoc()){
    
    $quantity = $mysqli -> query("select count(*) from ero_files where category = '$row[id]' and date < '".time()."'") -> fetch_row();

    $new = $mysqli -> query("select count(*) from ero_files where category = '$row[id]' and date > '".(time()-86400)."' and date < '".time()."'") -> fetch_row();

    if ($new[0] > 0) $new = '<span class="new">'.$new[0].'</span>'; else $new = false;
    
    echo '<div class="xxxhd-thumb-wr"><div class="xxxhd-thumb xxxhd-thumb-cat">
    <a href="/'.$row['translit'].'/" title="'.$row['name'].'">
    <div class="xxxhd-thumb-name">'.$row['name'].'</div>
    </a>
    <span class="xxxhd-thumb-top top-right">'.$quantity[0].'</span>
    '.$new.'
    </div></div>';
    
    }

    ?>
    </div><!-- xxxhd-thumbs-content -->
    <?

    $handle = fopen($caching, 'w'); 
	
    fwrite($handle, ob_get_contents()); 
    fclose($handle); 
    
    ob_end_flush();
    
    $query -> free();