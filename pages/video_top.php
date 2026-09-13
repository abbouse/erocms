<?php

/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/
    
    $cur_page = isset($_GET['page']) ? abs(intval($_GET['page'])) : 1;
    $caching = $_SERVER['DOCUMENT_ROOT'].'/content/cache/top_'.$cur_page.'.html';
    
    if (file_exists($caching)) {
        if ((time() - $settings['cache']) < filemtime($caching)) {
            echo file_get_contents($caching); 
            foot();
            exit; 
        }
    }
  
    ob_start();
    
    $title = $lang['popular'].' - '.filter($_SERVER['HTTP_HOST']);
    $description = $settings['description'];
    $keywords = $settings['keywords'];
    
    head();
    advertising();
?>

    <div class="xxxhd-title-top">
        <h2><i class="fa fa-fire" style="color:var(--primary-accent, #ff9900);"></i> <?=$lang['popular']?></h2>
    </div>

    <div class="xxxhd-thumbs-content">
    <?php
    $quantity = $mysqli->query("SELECT COUNT(*) FROM ero_files WHERE date < '".time()."'")->fetch_row();
    $k_page = k_page($quantity[0], 20);
    $page = page($k_page);
    $start = 20 * $page - 20;
    
    $query = $mysqli->query("SELECT id, screenshot, name, translit, duration, view, likes, dislikes FROM ero_files WHERE date < '".time()."' ORDER BY view DESC LIMIT $start, 20");
    
    while($row = $query->fetch_assoc()) {
        $tot = intval($row['likes']) + intval($row['dislikes']);
        $rate = $tot > 0 ? round((intval($row['likes']) / $tot) * 100) . '%' : '98%';

        $img_src = (!empty($row['screenshot']) && $row['screenshot'] != '/designs/water.png') ? $row['screenshot'] : '/designs/no_poster.jpg';

        echo '<div class="xxxhd-thumb-wr">
            <div class="xxxhd-thumb">
                <a href="/watch/'.$row['translit'].'.html" title="'.htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8').'">
                    <div class="thumb-image-wrap">
                        <img src="'.$img_src.'" alt="'.htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8').'" loading="lazy" onerror="this.onerror=null; this.src=\'/designs/no_poster.jpg\';" />
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

    <?php
    if ($k_page > 1) str('/top?', $k_page, $page);

    $handle = fopen($caching, 'w'); 
    if ($handle) {
        fwrite($handle, ob_get_contents()); 
        fclose($handle); 
    }
    
    ob_end_flush();
    $query->free();
