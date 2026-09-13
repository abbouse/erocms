<?php

/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/

    $title = 'Bo‘limlar va Kategoriyalar - '.filter($_SERVER['HTTP_HOST']);
    $description = $settings['description'];
    $keywords = $settings['keywords'];
    
    head();
    advertising();
?>

    <div class="xxxhd-title-top">
        <h2><i class="fa fa-th-large" style="color:#ff9900;"></i> Barcha bo‘limlar va kategoriyalar</h2>
    </div>

    <div class="xxxhd-thumbs-content">
    <?php
    $query = $mysqli->query("SELECT id, name, translit FROM ero_categories ORDER BY id ASC");

    while($row = $query->fetch_assoc()){
        $quantity = $mysqli->query("SELECT COUNT(*) FROM ero_files WHERE category = '{$row['id']}' AND date < '".time()."'")->fetch_row();
        $new_q = $mysqli->query("SELECT COUNT(*) FROM ero_files WHERE category = '{$row['id']}' AND date > '".(time()-86400)."' AND date < '".time()."'")->fetch_row();

        $new_badge = ($new_q[0] > 0) ? '<span class="cat-new-badge"><i class="fa fa-bolt"></i> +'.intval($new_q[0]).'</span>' : '';
        
        echo '<div class="xxxhd-thumb-wr">
            <div class="xxxhd-thumb xxxhd-thumb-cat">
                '.$new_badge.'
                <span class="cat-total-badge"><i class="fa fa-film"></i> '.$quantity[0].'</span>
                <a href="/'.$row['translit'].'/" title="'.htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8').'">
                    <div class="thumb-cat-wrap">
                        <i class="fa fa-folder-open-o" style="font-size: 32px; color: #ff9900; margin-bottom: 8px; display: block;"></i>
                        <div class="xxxhd-thumb-name">'.$row['name'].'</div>
                    </div>
                </a>
            </div>
        </div>';
    }
    $query->free();
    ?>
    </div>

<?php
    foot();
