<?php

/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/

    // Bosh sahifa kesh muddati: ko'pi bilan 30 daqiqa (rotatsiya jonli ishlashi uchun)
    $home_cache_time = (!empty($settings['cache']) && intval($settings['cache']) > 0) ? min(intval($settings['cache']), 1800) : 900;
    $sess_lang_c = $sess_lang ?? ($_SESSION['lang'] ?? 'uz');
    $caching = $_SERVER['DOCUMENT_ROOT'].'/content/cache/default_' . $sess_lang_c . '.html';
    $can_cache = empty($user) && empty($member);
  
    if ($can_cache && file_exists($caching)) {
        if ((time() - $home_cache_time) < filemtime($caching)) {
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
    if (function_exists('ads_render_banner')) {
        ads_render_banner('top');
    }
    
    # 404 ошибка
    if (isset($_GET['error'])) error($lang['not_found']);
    ?>

    <div class="xxxhd-title-top" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
        <h2><i class="fa fa-fire" style="color:var(--primary-accent, #ff9900);"></i> Tavsiya etilgan videolar</h2>
        <div class="xxxhd-feed-tabs" style="display:flex; gap:6px;">
            <a href="/new.html" style="font-size:11px; padding:3px 8px; border-radius:3px; background:rgba(255,255,255,0.06); color:#ccc; border:1px solid #444;"><i class="fa fa-calendar"></i> Yangilari</a>
            <a href="/top.html" style="font-size:11px; padding:3px 8px; border-radius:3px; background:rgba(255,255,255,0.06); color:#ccc; border:1px solid #444;"><i class="fa fa-star"></i> Ommabop</a>
        </div>
    </div>
    
    <div class="xxxhd-thumbs-content">
    <?php
    $feed_videos = function_exists('get_smart_feed_videos') ? get_smart_feed_videos($mysqli, 20) : [];

    foreach ($feed_videos as $row) {
        $tot = intval($row['likes']) + intval($row['dislikes']);
        $rate = $tot > 0 ? round((intval($row['likes']) / $tot) * 100) . '%' : '98%';
        
        $img_src = (!empty($row['screenshot']) && $row['screenshot'] != '/designs/water.png') ? $row['screenshot'] : '/designs/no_poster.jpg';
        
        $is_brand_new = (isset($row['date']) && $row['date'] > (time() - 86400 * 2));
        $top_badge = $is_brand_new 
            ? '<span class="xxxhd-thumb-top top-left" style="background:rgba(220,20,60,0.9); color:#fff; font-weight:bold;">YANGI</span>' 
            : '<span class="xxxhd-thumb-top top-left">HD</span>';

        echo '<div class="xxxhd-thumb-wr">
            <div class="xxxhd-thumb">
                <a href="/watch/'.$row['translit'].'.html" title="'.htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8').'">
                    <div class="thumb-image-wrap">
                        <img src="'.$img_src.'" alt="'.htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8').'" loading="lazy" onerror="this.onerror=null; this.src=\'/designs/no_poster.jpg\';" />
                    </div>
                    <div class="xxxhd-thumb-name" title="'.htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8').'">'.$row['name'].'</div>
                </a>
                '.$top_badge.'
                <span class="xxxhd-thumb-top top-right"><i class="fa fa-thumbs-o-up"></i> '.$rate.'</span>
                <span class="xxxhd-thumb-bottom bottom-left"><i class="fa fa-eye"></i> '.intval($row['view']).'</span>
                <span class="xxxhd-thumb-bottom bottom-right"><i class="fa fa-clock-o"></i> '.$row['duration'].'</span>
            </div>
        </div>';
    }
    ?>
    </div>
    <?php if (function_exists('ads_render_native_grid')) ads_render_native_grid(); ?>

    <div class="xxxhd-title-top" style="margin-top: 15px;">
        <h2><i class="fa fa-th-large"></i> Bo‘limlar va Kategoriyalar</h2>
    </div>

    <div class="xxxhd-thumbs-content">
    <?php
    // Bazaga og'irlik tushirmaslik uchun barcha kategoriyalar hisobini bitta so'rovda olish
    $cat_counts = [];
    $cat_new_counts = [];
    $cq = $mysqli->query("SELECT category, COUNT(*) as total, SUM(CASE WHEN date > '".(time()-86400)."' THEN 1 ELSE 0 END) as new_total FROM ero_files WHERE date < '".time()."' GROUP BY category");
    if ($cq) {
        while($cr = $cq->fetch_assoc()) {
            $cat_counts[$cr['category']] = intval($cr['total']);
            $cat_new_counts[$cr['category']] = intval($cr['new_total']);
        }
        $cq->free();
    }

    $query_cats = $mysqli->query("SELECT id, name, translit FROM ero_categories ORDER BY id ASC");

    if ($query_cats) {
        while($row = $query_cats->fetch_assoc()){
            $tot_cnt = $cat_counts[$row['id']] ?? 0;
            $new_cnt = $cat_new_counts[$row['id']] ?? 0;

            $new_badge = ($new_cnt > 0) ? '<span class="cat-new-badge"><i class="fa fa-bolt"></i> +'.$new_cnt.'</span>' : '';
            
            echo '<div class="xxxhd-thumb-wr">
                <div class="xxxhd-thumb xxxhd-thumb-cat">
                    '.$new_badge.'
                    <span class="cat-total-badge"><i class="fa fa-film"></i> '.$tot_cnt.'</span>
                    <a href="/'.$row['translit'].'/" title="'.htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8').'">
                        <div class="thumb-cat-wrap">
                            <i class="fa fa-folder-open-o" style="font-size: 28px; color: var(--primary-accent, #ff9900); margin-bottom: 6px; display: block;"></i>
                            <div class="xxxhd-thumb-name">'.$row['name'].'</div>
                        </div>
                    </a>
                </div>
            </div>';
        }
        $query_cats->free();
    }
    ?>
    </div>

    <?php
    if (function_exists('ads_render_banner')) {
        ads_render_banner('bottom');
    }

    if ($can_cache) {
        $handle = fopen($caching, 'w'); 
        if ($handle) {
            fwrite($handle, ob_get_contents()); 
            fclose($handle); 
        }
    }
    
    ob_end_flush();
