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
    
    # 404 ошибка
    if (isset($_GET['error'])) error($lang['not_found']);
    ?>

    <div class="xxxhd-title-top">
        <h2><i class="fa fa-play-circle"></i> <?=$lang['new_video']?></h2>
    </div>
    
    <div class="xxxhd-thumbs-content">
    <?php
    $query = $mysqli->query("SELECT id, screenshot, name, duration, translit, view, likes, dislikes FROM ero_files WHERE date < '".time()."' ORDER BY date DESC LIMIT 16");

    while($row = $query->fetch_assoc()) {
        $tot = intval($row['likes']) + intval($row['dislikes']);
        $rate = $tot > 0 ? round((intval($row['likes']) / $tot) * 100) . '%' : '98%';
        
        echo '<div class="xxxhd-thumb-wr">
            <div class="xxxhd-thumb">
                <a href="/watch/'.$row['translit'].'.html" title="'.htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8').'">
                    <div class="thumb-image-wrap">
                        <img src="'.$row['screenshot'].'" alt="'.htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8').'" loading="lazy" />
                    </div>
                    <div class="xxxhd-thumb-name" title="'.htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8').'">'.$row['name'].'</div>
                </a>
                <span class="xxxhd-thumb-top top-left">HD</span>
                <span class="xxxhd-thumb-top top-right"><i class="fa fa-thumbs-o-up"></i> '.$rate.'</span>
                <span class="xxxhd-thumb-bottom bottom-left"><i class="fa fa-eye"></i> '.intval($row['view']).'</span>
                <span class="xxxhd-thumb-bottom bottom-right"><i class="fa fa-clock-o"></i> '.$row['duration'].'</span>
            </div>
        </div>';
    }
    ?>
    </div>

    <div class="xxxhd-title-top" style="margin-top: 15px;">
        <h2><i class="fa fa-th-large"></i> Bo‘limlar va Kategoriyalar</h2>
    </div>

    <div class="xxxhd-thumbs-content">
    <?php
    $query_cats = $mysqli->query("SELECT id, name, translit FROM ero_categories ORDER BY id ASC");

    while($row = $query_cats->fetch_assoc()){
        $quantity = $mysqli->query("SELECT COUNT(*) FROM ero_files WHERE category = '{$row['id']}' AND date < '".time()."'")->fetch_row();
        $new_q = $mysqli->query("SELECT COUNT(*) FROM ero_files WHERE category = '{$row['id']}' AND date > '".(time()-86400)."' AND date < '".time()."'")->fetch_row();

        $new_badge = ($new_q[0] > 0) ? '<span class="new">+'.intval($new_q[0]).'</span>' : '';
        
        echo '<div class="xxxhd-thumb-wr">
            <div class="xxxhd-thumb xxxhd-thumb-cat">
                <a href="/'.$row['translit'].'/" title="'.htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8').'">
                    <div class="thumb-cat-wrap">
                        <i class="fa fa-folder-open-o" style="font-size: 28px; color: #ff9900; margin-bottom: 6px; display: block;"></i>
                        <div class="xxxhd-thumb-name">'.$row['name'].'</div>
                    </div>
                </a>
                <span class="xxxhd-thumb-top top-right"><i class="fa fa-film"></i> '.$quantity[0].'</span>
                '.$new_badge.'
            </div>
        </div>';
    }
    ?>
    </div>

    <?php
    $handle = fopen($caching, 'w'); 
    if ($handle) {
        fwrite($handle, ob_get_contents()); 
        fclose($handle); 
    }
    
    ob_end_flush();
    $query->free();
    $query_cats->free();
