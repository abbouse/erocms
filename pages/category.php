<?php

/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/

    $category = $mysqli -> query("select id, name, translit, description, keywords, meta from ero_categories where translit = '".mysqli_real_escape_string($mysqli, filter($_GET['translit']))."'") -> fetch_assoc();
    
    if (!$category){
        header('location: /');
        exit;
    }
    
    if (isset($_GET['new'])) {
        
        $_SESSION['sorting'] = 0;
        unlink($_SERVER['DOCUMENT_ROOT'].'/content/cache/'.filter($_GET['translit']).'.html');
        
    }   elseif (isset($_GET['popular'])) {
        
        $_SESSION['sorting'] = 1;
        unlink($_SERVER['DOCUMENT_ROOT'].'/content/cache/'.filter($_GET['translit']).'.html');
    }
    
    if (!isset($_GET['page'])) $caching = $_SERVER['DOCUMENT_ROOT'].'/content/cache/'.filter($_GET['translit']).'.html';
    else $caching = $_SERVER['DOCUMENT_ROOT'].'/content/cache/'.filter($_GET['translit']).'_'.abs(intval($_GET['page'])).'.html';
    
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
    
    $mysqli -> query("update ero_categories set view = view + '1' where id = '$category[id]'");
    
    ?>
    
    <h2 class="view"><?=$category['description']?></h2>
    
    <div align="center">
       
<table cellpadding="7" width="100%">
  
  <tr>
    <th><div class="menu_j"><a href="/<?=$category['translit']?>/?new" class="tach"><img src="/designs/icons/view/new.png" width="16" height="16" /> <?=$lang['new']?></a> </div></th>
    <th><div class="menu_j">       <a href="/<?=$category['translit']?>/?popular" class="tach"><img src="/designs/icons/view/popular.png" width="16" height="16" /> <?=$lang['popular']?></a></div></th>
  </tr>
  
</table>

	</div>
	
    <?
    
    if ($user['access'] == 1) {
        
    ?>
    
    <div align="center">
       
<table cellpadding="7" width="100%">
  
  <tr>
    <th><div class="menu_j"><a href="/control.html?func=editCat&id=<?=$category['id']?>" class="tach"><img src="/designs/icons/view/edit.png" width="16" height="16" /> <?=$lang['edit']?></a></div></th>
    <th><div class="menu_j">       <a href="/control.html?func=removeCat&id=<?=$category['id']?>" class="tach"><img src="/designs/icons/view/remove.png" width="16" height="16" /> <?=$lang['remove']?></a></div></th>
  </tr>
  
</table>

	</div>
	
    <?
    
    }
    
    $quantity = $mysqli -> query("select count(*) from ero_files where category = '$category[id]' and date < '".time()."'") -> fetch_row();
    $k_page = k_page($quantity[0], 20);
    $page = page($k_page);
    $start = 20*$page-20;
    
    if ($_SESSION['sorting'] == 0)
    $query = $mysqli -> query("select id, screenshot, name, translit, duration, view from ero_files where category = '$category[id]' and date < '".time()."' order by date desc limit $start, 20");
    else if ($_SESSION['sorting'] == 1)
    $query = $mysqli -> query("select id, screenshot, name, translit, duration, view from ero_files where category = '$category[id]' and date < '".time()."' order by view desc limit $start, 20");
    else
    $query = $mysqli -> query("select id, screenshot, name, translit, duration, view from ero_files where category = '$category[id]' and date < '".time()."' order by date desc limit $start, 20");
    
    while($row = $query -> fetch_assoc()) {

    if ($user['access'] == 1) 
    $edit = '<p align="right"><a href="/editing_'.$row['id'].'.html"><img src="/designs/icons/view/edit.png" width="16" height="16" /> '.$lang['edit'].'</a>
    <a href="/deletion_'.$row['id'].'.html"><img src="/designs/icons/view/remove.png" width="16" height="16" /> '.$lang['remove'].'</a></p>'; else $edit = false;
    
    echo '<a href="/watch/'.$row['translit'].'.html" class="tach" title="'.$row['name'].'">
    <img class="screenshots" src="'. $row['screenshot'] .'" alt="'.$row['name'].'" />
    <span class="sample">'.$row['duration'].'</span>
    <h2 style="font-size: 12px;">'.$row['name'].'</h2></a>'.$edit;
    
    }
    
    if ($k_page > 1) str('/'.$category['translit'].'/?', $k_page, $page);

    $handle = fopen($caching, 'w'); 
	
    fwrite($handle, ob_get_contents()); 
    fclose($handle); 
    
    ob_end_flush();
    
    $query -> free();