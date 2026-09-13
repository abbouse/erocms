<?php

/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/

    $search = mysqli_real_escape_string($mysqli, filter($_GET['i'] ?? ''));     
        
    $title = $lang['searching_results'].' "'.$search.'"';
    $description = $settings['description'];
    $keywords = $settings['keywords'];
        
    head();
    advertising();
    
    $where_sql = "WHERE (description LIKE '%$search%' OR name LIKE '%$search%') AND date < '".time()."'";
    $quantity = $mysqli->query("SELECT COUNT(*) FROM ero_files $where_sql")->fetch_row();
?>

    <div class="xxxhd-title-top">
        <h2><i class="fa fa-search" style="color:#ff9900;"></i> Qidiruv natijalari: "<?=htmlspecialchars($search, ENT_QUOTES, 'UTF-8')?>" (<?=$quantity[0]?> ta video)</h2>
    </div>

    <div class="xxxhd-thumbs-content">
    <?php
    $k_page = k_page($quantity[0], 20);
    $page = page($k_page);
    $start = 20 * $page - 20;
    
    if ($quantity[0] == 0) {
        echo '<div style="padding: 25px 15px; color: #777; width: 100%; text-align: center;">Hech qanday video topilmadi. Boshqa so‘z bilan qidirib ko‘ring.</div>';
    } else {
        $query = $mysqli->query("SELECT id, screenshot, name, translit, duration, view, likes, dislikes FROM ero_files $where_sql ORDER BY date DESC LIMIT $start, 20");
        
        while($row = $query->fetch_assoc()) {
            $tot = intval($row['likes']) + intval($row['dislikes']);
            $rate = $tot > 0 ? round((intval($row['likes']) / $tot) * 100) . '%' : '98%';

            echo '<div class="xxxhd-thumb-wr">
                <div class="xxxhd-thumb">
                    <a href="/watch/'.$row['translit'].'.html" title="'.htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8').'">
                        <div class="thumb-image-wrap">
                            <img src="'.$row['screenshot'].'" alt="'.htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8').'" loading="lazy" onerror="this.onerror=null; this.src=\'/designs/water.png\';" />
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
        $query->free();
    }
    ?>
    </div>

    <?php
    if ($k_page > 1) str('/search_?i='.urlencode($search).'&', $k_page, $page);
    foot();
