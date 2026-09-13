<?php

/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/

    $view = $mysqli -> query("select * from ero_files where translit = '".mysqli_real_escape_string($mysqli, filter($_GET['translit']))."'") -> fetch_assoc();
  
    if (!$view){
        header('location: /');
        exit;
    }
    
    if (isset($_GET['refresh']))    {
        
    if (10000000 > size($view['address']))  {
        
        $refresh = $mysqli -> query("select * from ero_files where server != '$view[server]' order by rand() limit 1") -> fetch_assoc();
        $mysqli -> query("update ero_files set address = '$refresh[address]' where id = '$view[id]'");
        
        array_map('unlink' , glob($_SERVER['DOCUMENT_ROOT']."/content/cache/*.html"));
    }

        header('location: /watch/'.$view['translit'].'.html');     
        exit;
    }
    
    $category = $mysqli -> query("select * from ero_categories where id = '$view[category]'") -> fetch_assoc();
    $favorites = $mysqli -> query("select count(*) from ero_favorites where id_video = '$view[id]'") -> fetch_row();

    function sec($var) {
        list($i, $s) = explode(':', $var);
        return $i*60 + $s;
    }

    if (isset($_GET['favorites'])){
    
        $favorites_my = $mysqli -> query("select count(*) from ero_favorites where id_video = '$view[id]' and data = '".mysqli_real_escape_string($mysqli, filter($_SERVER['REMOTE_ADDR']))."'") -> fetch_row();
        
        if ($favorites_my[0] == 0)
        $mysqli -> query("INSERT INTO ero_favorites SET id_video = '$view[id]', data = '".mysqli_real_escape_string($mysqli, filter($_SERVER['REMOTE_ADDR']))."'");
        else
        $mysqli -> query("delete from ero_favorites where id_video = '$view[id]' and data = '".mysqli_real_escape_string($mysqli, filter($_SERVER['REMOTE_ADDR']))."'");
        
        header('location: /watch/'.$view['translit'].'.html');     
        exit;
        
    }
    

    $title = $view['name'];
    $description = mb_substr($view['description'], 0, 156, 'UTF-8').'..';
    $keywords = str_replace(' ', ', ', $view['tags']);
        
    head(sec($view['duration']));
    advertising();
    
    $mysqli -> query("update ero_files set view = view + '1' where id = '$view[id]'");
    
    $tags = explode(' ', tags($view['tags']));
        
    ?>
        
<div class="xxxhd-title-top">
<p style="font-size:13px;color:#959595">
<a href="/"><?=$lang['home']?></a> &raquo;
<a href="/<?=$category['translit']?>/"><?=$category['name']?></a> &raquo;
<b style="color:#ff9900"><?=$view['name']?></b>
</p>
<p style="font-size:12px;color:#595a5c;margin-top:4px">
<i class="fa fa-calendar"></i> <?=date('Y-m-d', $view['date']);?>
&nbsp;<i class="fa fa-eye"></i> <?=$view['view'];?>
&nbsp;<i class="fa fa-clock-o"></i> <?=($view['duration']);?>
</p>
</div>
<?php
    
    if ($view['server'] == 'vk.com' or $view['server'] == 'drive.google.com')    {
        
    echo '<div class="xxxhd-player" style="position:relative;width:100%;background:#000;">';

    echo '<iframe src="'.$view['address'].'" width="100%" height="320" frameborder="0"></iframe>';

    }   else    {
    
    echo '<div class="xxxhd-player" style="position:relative;width:100%;background:#000;">';

    if ($settings['player'] == 0) echo '
    <video width="100%" controls="controls" poster="'.$view['screenshot'].'">
    <source src="/view_'.$view['translit'].'">
    </video>'; 
    
    if ($settings['player'] == 1) echo '
    <script src="/core/javascript/uppod-0.5.32.js" type="text/javascript"></script>
	<div class="player" id="porno_video"></div>
    <script type="text/javascript">
    document.getElementById("porno_video").style.height = document.getElementById("porno_video").offsetWidth/1.666+ "px";
    this.player = new Uppod({m:"video",uid:"porno_video",file:"/view_'.$view['translit'].'",poster:"'.$view['screenshot'].'"});
    </script>'; 
    
    if ($settings['player'] == 2) echo '
    <script src="/core/javascript/playerjs.js" type="text/javascript"></script>
    <div id="player"></div>
    <script>
    var player = new Playerjs({id:"player", file:"/view_'.$view['translit'].'", 
    title:"'.filter($_SERVER['HTTP_HOST']).'"
    });
    </script>';
    
    echo '</div><!-- xxxhd-player -->';

    }
    
    ?>
    
    <p>
	    <div align="center">
	       
	       <script src="https://yastatic.net/share2/share.js"></script>
	       
            <div class="ya-share2" data-curtain data-shape="round" data-services="messenger,vkontakte,facebook,odnoklassniki,telegram,viber,whatsapp"></div><br>

	  <big><?=$lang['file_not_available']?> <a href="/watch/<?=$view['translit'];?>.html?refresh"><font color="blue"><?=$lang['here']?></font></a></big>
	  
	  </div>
	  
	  </p>
	  
    <p align="center">
    <span class="likebtn-wrapper" data-theme="drop" data-lang="ru" data-ef_voting="heartbeat" data-rich_snippet="true"></span>
    <script>(function(d,e,s){if(d.getElementById("likebtn_wjs"))return;a=d.createElement(e);m=d.getElementsByTagName(e)[0];a.async=1;a.id="likebtn_wjs";a.src=s;m.parentNode.insertBefore(a, m)})(document,"script","//w.likebtn.com/js/w/widget.js");</script>
    </p>

    <h2 class="functions_data"><font color="DimGrey"><?=$view['description'];?></font></h2>

    <div class="functions_data">
    <img src="/designs/icons/view/category.png" width="16" height="16" /> <?=$lang['category']?>: <a href="/<?=$category['translit'];?>/" title="<?=$category['name'];?>"><?=$category['name'];?></a><br />
    <img src="/designs/icons/view/tags.png" width="16" height="16" /> <?=$lang['tags']?>: 
    
    <?
    
    for($i = 0; $i < 5; $i++) echo '<a href="/tag/'.$tags[$i].'"><span style="font-size: '.rand(10, 18).'px;"><b>'.$tags[$i].'</b></span></a> ';

    ?>

    </div>
    
    <p>
        
    <div align="center">
       
<table cellpadding="7" width="100%">
  
  <tr>
    <th><div class="menu_j"><a href="/download/<?=$view['translit'];?>.mp4" class="tach" title="<?=$lang['download']?> <?=$view['name'];?>"><img src="/designs/icons/view/download.png" width="16" height="16" /> <?=$lang['download']?> (<?=$view['downloads'];?>)</a></div></th>
    <th><div class="menu_j">    <a href="/watch/<?=$view['translit'];?>.html?favorites" class="tach" title="<?=$lang['to_favorites']?> <?=$view['name'];?>"><img src="/designs/icons/view/my_favorites.png" width="16" height="16" /> <?=$lang['to_favorites']?> (<?=$favorites[0];?>)</a></div></th>
  </tr>
  
</table>

	</div>
	
    <?
    
    if ($user['access'] == 1)   {
        
    ?>
    
    <div align="center">
       
<table cellpadding="7" width="100%">
  
  <tr>
    <th><div class="menu_j"><a href="/editing_<?=$view['id']?>.html" class="tach"><img src="/designs/icons/view/edit.png" width="16" height="16" /> <?=$lang['edit']?></a></div></th>
    <th><div class="menu_j">   <a href="/deletion_<?=$view['id']?>.html" class="tach"><img src="/designs/icons/view/remove.png" width="16" height="16" /> <?=$lang['remove']?></a></div></th>
  </tr>
  
</table>

    </div>

    <?
    
    }
    
    ?>
    
    </p>
    
    <div class="xxxhd-title-top"><h2>O'xshash videolar</h2></div>
    <div class="xxxhd-thumbs-content">

    <?
    
    $query = $mysqli -> query("select id, screenshot, name, translit, duration, view from ero_files where category = '$category[id]' and date < '".time()."' and id != '$category[id]' order by rand() desc limit 3");

    while($row = $query -> fetch_assoc()) {
    
    if ($user['access'] == 1) 
    $edit = '<p align="right"><a href="/editing_'.$row['id'].'.html"><img src="/designs/icons/view/edit.png" width="16" height="16" /> '.$lang['edit'].'</a>
    <a href="/deletion_'.$row['id'].'.html"><img src="/designs/icons/view/remove.png" width="16" height="16" /> '.$lang['remove'].'</a></p>'; else $edit = false;
    
    echo '<div class="xxxhd-thumb-wr"><div class="xxxhd-thumb">
    <a href="/watch/'.$row['translit'].'.html" title="'.$row['name'].'">
    <img src="'.$row['screenshot'].'" alt="'.$row['name'].'" />
    <div class="xxxhd-thumb-name">'.$row['name'].'</div>
    </a>
    <span class="xxxhd-thumb-bottom bottom-right"><i class="fa fa-clock-o"></i> '.$row['duration'].'</span>
    </div></div>';
    
    }

    ?>
    </div><!-- xxxhd-thumbs-content -->
    <?

    $query -> free();