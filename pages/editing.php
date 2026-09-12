<?php

/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/

    if ($user['access'] < 1) {
        header('location: /'); 
        exit;
    }
    
    $view = $mysqli -> query("select * from ero_files where id = '".abs(intval($_GET['id']))."'") -> fetch_assoc();
    
    if (!$view){
        header('location: /');
        exit;
    }
    
    $title = $lang['video_editing'].' '.$view['name'];
    $description = $settings['description'];
    $keywords = $settings['keywords'];
        
    head();
    
    if (isset($_POST['translit'])) {
        
        $publish = strtotime($_POST['publish']) + rand(300, 21600);
        $recoil =  mysqli_real_escape_string($mysqli, filter($_POST['recoil']));    
        $address =  mysqli_real_escape_string($mysqli, filter($_POST['address']));    
        $screenshot =  mysqli_real_escape_string($mysqli, filter($_POST['screenshot'])); 
        $translit =  mysqli_real_escape_string($mysqli, filter($_POST['translit']));
        $name =  mysqli_real_escape_string($mysqli, filter($_POST['name']));
        $tags =  mysqli_real_escape_string($mysqli, filter($_POST['tags']));
        $description =  mysqli_real_escape_string($mysqli, filter($_POST['description']));
        $category =  mysqli_real_escape_string($mysqli, abs(intval($_POST['category'])));
        
        $mysqli -> query("update ero_files set address = '$address', category = '$category', translit = '$translit', date = '$publish', recoil = '$recoil', screenshot = '$screenshot', name = '$name', tags = '$tags', description = '$description' where id = '$view[id]'");
        $mysqli -> query("INSERT INTO ero_logs SET id_user = '$user[id]', act = '$lang[сhanged_video] $view[name].', id_file = '$view[id]', date = '".time()."'");

        header('location: /watch/'.$translit.'.html');
        exit;
    }
    
    ?>
    
    <p><?=$lang['server']?> <a href="/control.html?func=server&i=<?=$view['server']?>" target="_blank"><u><b><?=$view['server']?></b></u></a> </p>
    
    <form method="post">   
    
    <p><b><?=$lang['name']?></b> </p>
    
    <p><input name="name" class="injected" type="text" value="<?=$view['name']?>"></p>

	<p><b><?=$lang['date_of_publication']?></b> </p>
	
	<p><input class="injected" name="publish" type="date" value="<?=date('Y-m-d', $view['date'])?>" min="<?=date('Y-m-d', time())?>" max="2025-12-31"></p>
        
    <p><b><?=$lang['url']?></b> </p>
    
    <p> <big><b>/watch/</b></big> 
    
    <input name="translit" class="injected" type="text" value="<?=$view['translit']?>">.html</p>
    
    <p><b><?=$lang['direct_link']?></b> [<a href="<?=$view['address']?>" target="_blank"><u><b><?=$lang['verify']?></b></u></a>]</p>
    
    <p><input name="address" class="injected" type="text" value="<?=$view['address']?>"></p>
    
	<p><b><?=$lang['category']?></b> </p>
	
	<p><select class="injected" name="category">
	    
	<?
	
    $query = $mysqli -> query("select id, name from ero_categories order by id asc");

    while($row = $query -> fetch_assoc()){
    
    ?>
        
	<option value="<?=$row['id']?>" <?=($row['id']==$view['category']?" selected='selected'":null)?>><?=$row['name']?></option>

    <?
    
    }
    
    ?>
    
	</select></p>
	
    <p><b><?=$lang['file']?></b> </p>
    
    <p><input name="recoil" class="injected" type="text" value="<?=$view['recoil']?>"></p>
    
    <p><b><?=$lang['photo']?></b> </p>
    
    <p><input name="screenshot" class="injected" type="text" value="<?=$view['screenshot']?>"></p>
    
    <p><b><?=$lang['tags']?></b> </p>
    
    <p><textarea name="tags" class="injected" rows="4" cols="47"><?=$view['tags']?></textarea></p>
    
    <p><b><?=$lang['description']?></b> </p>
    
    <p><textarea name="description" class="injected" rows="8" cols="47"><?=$view['description']?></textarea></p>
    
    <input type="submit" class="byecos" value="<?=$lang['edit']?>">   
    
    <a onclick="history.back(); return false;" class="byecos"><?=$lang['come_back']?></a>
    
    </form>
    
    <?
    
    $query -> free();