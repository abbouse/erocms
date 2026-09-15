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
    
    // Qidiruv sahifalari indexlanmasligi kerak (duplicate content oldini olish)
    $seo_noindex = true;
        
    head();
    advertising();
    if (function_exists('ads_render_banner')) {
        ads_render_banner('top');
    }
    
    $where_sql = "WHERE (description LIKE '%$search%' OR name LIKE '%$search%') AND date < '".time()."'";
    $quantity = $mysqli->query("SELECT COUNT(*) FROM ero_files $where_sql")->fetch_row();

    if (!empty($search) && (!isset($_GET['page']) || (int)$_GET['page'] <= 1)) {
        track_activity('search', 0, $search);
    }
?>

    <div class="xxxhd-title-top">
        <h2><i class="fa fa-search" style="color:var(--primary-accent, #ff9900);"></i> <?=htmlspecialchars($lang['searching_results'] ?? 'Qidiruv natijalari')?>: "<?=htmlspecialchars($search, ENT_QUOTES, 'UTF-8')?>" (<?=$quantity[0]?> ta video)</h2>
    </div>

    <?php
    $k_page = k_page($quantity[0], 20);
    $page = page($k_page);
    $start = 20 * $page - 20;
    
    if ($quantity[0] == 0) {
        echo '<div style="padding: 28px 15px; margin: 15px auto; max-width: 600px; background: rgba(255,153,0,0.04); border: 1px dashed rgba(255,153,0,0.3); border-radius: 8px; text-align: center; color: #ddd;">
            <i class="fa fa-search" style="font-size: 38px; color: var(--primary-accent, #ff9900); margin-bottom: 10px; display: inline-block;"></i>
            <p style="font-size: 15px; font-weight: 600; margin-bottom: 6px; color: #fff;">"'.htmlspecialchars($search, ENT_QUOTES, 'UTF-8').'" bo‘yicha hech qanday video topilmadi</p>
            <p style="font-size: 13px; color: #999; margin: 0;">Iltimos, so‘zni to‘g‘ri yozganingizni tekshiring yoki quyida siz uchun tanlangan eng sara videolarni tomosha qiling!</p>
        </div>';
    } else {
        echo '<div class="xxxhd-thumbs-content">';
        $query = $mysqli->query("SELECT id, screenshot, name, translit, duration, view, likes, dislikes FROM ero_files $where_sql ORDER BY date DESC LIMIT $start, 20");
        
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
        $query->free();
        echo '</div>';

        if ($k_page > 1) {
            str('/search_?i='.urlencode($search).'&', $k_page, $page);
        }
    }

    // Native Ad Slot
    if (function_exists('ads_render_native_grid')) {
        ads_render_native_grid();
    }
    ?>

    <!-- Har doim chiqadigan Tavsiya etilgan videolar bo'limi -->
    <div class="xxxhd-title-top" style="margin-top: 25px;">
        <h2><i class="fa fa-fire" style="color:var(--primary-accent, #ff9900);"></i> Tavsiya etamiz (Sizga yoqishi mumkin bo‘lgan videolar)</h2>
    </div>

    <div class="xxxhd-thumbs-content">
    <?php
    $rec_query = $mysqli->query("SELECT id, screenshot, name, translit, duration, view, likes, dislikes FROM ero_files WHERE date < '".time()."' ORDER BY RAND() LIMIT 12");
    if ($rec_query) {
        while($r_row = $rec_query->fetch_assoc()) {
            $r_tot = intval($r_row['likes']) + intval($r_row['dislikes']);
            $r_rate = $r_tot > 0 ? round((intval($r_row['likes']) / $r_tot) * 100) . '%' : '98%';
            $r_img = (!empty($r_row['screenshot']) && $r_row['screenshot'] != '/designs/water.png') ? $r_row['screenshot'] : '/designs/no_poster.jpg';

            echo '<div class="xxxhd-thumb-wr">
                <div class="xxxhd-thumb">
                    <a href="/watch/'.$r_row['translit'].'.html" title="'.htmlspecialchars($r_row['name'], ENT_QUOTES, 'UTF-8').'">
                        <div class="thumb-image-wrap">
                            <img src="'.$r_img.'" alt="'.htmlspecialchars($r_row['name'], ENT_QUOTES, 'UTF-8').'" loading="lazy" onerror="this.onerror=null; this.src=\'/designs/no_poster.jpg\';" />
                        </div>
                        <div class="xxxhd-thumb-name" title="'.htmlspecialchars($r_row['name'], ENT_QUOTES, 'UTF-8').'">'.$r_row['name'].'</div>
                    </a>
                    <span class="xxxhd-thumb-top top-left">HD</span>
                    <span class="xxxhd-thumb-top top-right"><i class="fa fa-thumbs-o-up"></i> '.$r_rate.'</span>
                    <span class="xxxhd-thumb-bottom bottom-left"><i class="fa fa-eye"></i> '.intval($r_row['view']).'</span>
                    <span class="xxxhd-thumb-bottom bottom-right"><i class="fa fa-clock-o"></i> '.$r_row['duration'].'</span>
                </div>
            </div>';
        }
        $rec_query->free();
    }
    ?>
    </div>

    <?php
    if (function_exists('ads_render_banner')) {
        ads_render_banner('bottom');
    }
    ?>
