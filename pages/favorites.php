<?php

/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/

    $title = $lang['chosen'].' - '.filter($_SERVER['HTTP_HOST']);
    $description = $settings['description'];
    $keywords = $settings['keywords'];
        
    head();
    advertising();
    
    $user_ip = mysqli_real_escape_string($mysqli, filter($_SERVER['REMOTE_ADDR']));
    $quantity = $mysqli->query("SELECT COUNT(*) FROM ero_favorites WHERE data = '$user_ip'")->fetch_row();
    $k_page = k_page($quantity[0], 20);
    $page = page($k_page);
    $start = 20 * $page - 20;
?>

    <div class="xxxhd-title-top">
        <h2><i class="fa fa-star" style="color:#ff9900;"></i> Sevimli videolar (<?=$quantity[0]?>)</h2>
    </div>

    <div class="xxxhd-thumbs-content">
    <?php
    if ($quantity[0] == 0) {
        echo '<div style="padding: 25px 15px; color: #777; width: 100%; text-align: center;">Siz hali hech qanday videoni sevimlilarga qo‘shmagansiz. Videolarni ko‘rishda yulduzcha tugmasini bosing!</div>';
    } else {
        $query = $mysqli->query("SELECT f.* FROM ero_favorites fav JOIN ero_files f ON fav.id_video = f.id WHERE fav.data = '$user_ip' ORDER BY fav.id DESC LIMIT $start, 20");
        
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
        $query->free();
    }
    ?>
    </div>

    <?php
    if ($k_page > 1) str('/favorites?', $k_page, $page);
