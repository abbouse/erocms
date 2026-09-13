<?php

/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/

    $cat_slug = mysqli_real_escape_string($mysqli, filter($_GET['translit'] ?? ''));
    $category = $mysqli->query("SELECT id, name, translit, description, keywords, meta FROM ero_categories WHERE translit = '$cat_slug' LIMIT 1")->fetch_assoc();
    
    if (!$category) {
        header('Location: /');
        exit;
    }
    
    if (isset($_GET['new'])) {
        $_SESSION['sorting'] = 0;
        @unlink($_SERVER['DOCUMENT_ROOT'].'/content/cache/'.$cat_slug.'.html');
    } elseif (isset($_GET['popular'])) {
        $_SESSION['sorting'] = 1;
        @unlink($_SERVER['DOCUMENT_ROOT'].'/content/cache/'.$cat_slug.'.html');
    }
    
    $cur_page = isset($_GET['page']) ? abs(intval($_GET['page'])) : 1;
    $caching = $_SERVER['DOCUMENT_ROOT'].'/content/cache/'.$cat_slug.'_'.$cur_page.'.html';
    
    if (file_exists($caching)) {
        if ((time() - $settings['cache']) < filemtime($caching)) {
            echo file_get_contents($caching); 
            foot();
            exit; 
        }
    }
  
    ob_start();
    
    $title = $category['name'].' - '.$lang['look'].' '.filter($_SERVER['HTTP_HOST']);
    $description = $category['meta'];
    $keywords = $category['keywords'];
    
    head();
    advertising();
    
    $mysqli->query("UPDATE ero_categories SET view = view + 1 WHERE id = '{$category['id']}'");
    $sorting = intval($_SESSION['sorting'] ?? 0);
?>

    <div class="xxxhd-title-top">
        <p style="font-size:13px; color:#959595; margin-bottom: 4px;">
            <a href="/"><i class="fa fa-home"></i> Bosh sahifa</a> &raquo; 
            <b style="color:#ff9900;"><?=$category['name']?></b>
        </p>
        <h1><i class="fa fa-folder-open-o" style="color:#ff9900;"></i> <?=$category['name']?></h1>
    </div>

    <!-- Sorting Filter Tabs -->
    <div style="padding: 8px 10px; background: #141312; border-bottom: 1px solid #323130; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
        <div style="display: flex; gap: 6px;">
            <a href="/<?=$category['translit']?>/?new" class="action-btn <?=($sorting == 0 ? 'active' : '')?>">
                <i class="fa fa-calendar"></i> Yangilar
            </a>
            <a href="/<?=$category['translit']?>/?popular" class="action-btn <?=($sorting == 1 ? 'active' : '')?>">
                <i class="fa fa-fire"></i> Ommabop
            </a>
        </div>

        <?php if ($user && $user['access'] == 1): ?>
        <div>
            <a href="/control.html?func=editCat&id=<?=$category['id']?>" class="action-btn" style="color: #28a745;">
                <i class="fa fa-edit"></i> Tahrirlash
            </a>
            <a href="/control.html?func=removeCat&id=<?=$category['id']?>" class="action-btn" style="color: #dc3545;" onclick="return confirm('O‘chirmoqchimisiz?');">
                <i class="fa fa-trash"></i> O‘chirish
            </a>
        </div>
        <?php endif; ?>
    </div>

    <div class="xxxhd-thumbs-content">
    <?php
    $quantity = $mysqli->query("SELECT COUNT(*) FROM ero_files WHERE category = '{$category['id']}' AND date < '".time()."'")->fetch_row();
    $k_page = k_page($quantity[0], 20);
    $page = page($k_page);
    $start = 20 * $page - 20;
    
    $order_sql = ($sorting == 1) ? 'ORDER BY view DESC' : 'ORDER BY date DESC';
    $query = $mysqli->query("SELECT id, screenshot, name, translit, duration, view, likes, dislikes FROM ero_files WHERE category = '{$category['id']}' AND date < '".time()."' $order_sql LIMIT $start, 20");
    
    if ($query && $query->num_rows > 0) {
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
    } else {
        echo '<div style="padding: 25px 15px; color: #777; width: 100%; text-align: center;">Bu bo‘limda hozircha videolar mavjud emas.</div>';
    }
    ?>
    </div>

    <?php
    if ($k_page > 1) str('/'.$category['translit'].'/?', $k_page, $page);

    $handle = fopen($caching, 'w'); 
    if ($handle) {
        fwrite($handle, ob_get_contents()); 
        fclose($handle); 
    }
    
    ob_end_flush();
    $query->free();
