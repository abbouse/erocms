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
    
        if (isset($_POST['title'])){
            
            $recoil =  mysqli_real_escape_string($mysqli, abs(intval($_POST['recoil'])));
            $title =  mysqli_real_escape_string($mysqli, filter($_POST['title']));
            $designs =  mysqli_real_escape_string($mysqli, filter($_POST['designs']));
            $description =  mysqli_real_escape_string($mysqli, filter($_POST['description']));
            $keywords =  mysqli_real_escape_string($mysqli, filter($_POST['keywords']));
            $counter =  mysqli_real_escape_string($mysqli, ($_POST['counter']));#filter() del.
            $advertising =  mysqli_real_escape_string($mysqli, abs(intval($_POST['advertising'])));
            $player =  mysqli_real_escape_string($mysqli, abs(intval($_POST['player'])));
            $water =  mysqli_real_escape_string($mysqli, abs(intval($_POST['water'])));
            $cache =  mysqli_real_escape_string($mysqli, abs(intval($_POST['cache'])));
            $WK_ID =  mysqli_real_escape_string($mysqli, abs(intval($_POST['WK_ID'])));
            $WK_SECRET =  mysqli_real_escape_string($mysqli, filter($_POST['WK_SECRET']));
            $disclosed =  mysqli_real_escape_string($mysqli, filter($_POST['disclosed']));
            $alert =  mysqli_real_escape_string($mysqli, filter($_POST['alert']));
            $cron =  mysqli_real_escape_string($mysqli, filter($_POST['cron']));
            $OAuth =  mysqli_real_escape_string($mysqli, filter($_POST['OAuth']));
            $access_token =  mysqli_real_escape_string($mysqli, filter($_POST['access_token']));
            
            if (!filter_var($alert, FILTER_VALIDATE_EMAIL)) $warning = $lang['email_address'];
            else if (strlen($_POST['title']) > 128 or strlen($_POST['title']) < 12) $warning = $lang['short_or_long_name'];
            else if (strlen($_POST['cron']) > 64 or strlen($_POST['cron']) < 8) $warning = $lang['short_or_long_key'];
            else if (strlen($_POST['description']) > 320 or strlen($_POST['description']) < 96) $warning = $lang['short_long_description'];
            else if (strlen($_POST['disclosed']) > 32 or strlen($_POST['disclosed']) < 4) $warning = $lang['short_or_long_password'];
            
            if ($warning) error($warning);
            
            if (filter_var($alert, FILTER_VALIDATE_EMAIL) and $user['disclosed'] != $disclosed) mail($alert, 'Password changed', 'Access password changed, new password '.$disclosed); 
    
            $mysqli -> query("update ero_users set disclosed = '$disclosed', password = '".md5(md5($disclosed))."' where id = '$user[id]'");
            $mysqli -> query("update ero_settings set water = '$water', access_token = '$access_token', OAuth = '$OAuth', cron = '$cron', recoil = '$recoil', designs = '$designs', cache = '$cache', alert = '$alert', WK_SECRET = '$WK_SECRET', WK_ID = '$WK_ID', player = '$player', title = '$title', description = '$description', keywords = '$keywords', counter = '$counter', advertising = '$advertising' where id = '1'");
            logs($user['id'], $lang['changed_system'], 0);
            
            header('Location: /control.html?func=settings');
            exit;
        }
    
    ?>
    
    <div class="functions_data">
        
    <form method="post">
    
	<p><b><?=$lang['name']?></b> </p>
	
	<p><textarea name="title" class="injected" rows="4" cols="47"><?=$settings['title']?></textarea></p>
	
	<p><b><?=$lang['description']?></b> </p>
	
	<p><textarea name="description" class="injected" rows="4" cols="47"><?=$settings['description']?></textarea></p>
	
	<p><b><?=$lang['tags']?></b> </p>
	
	<p><textarea name="keywords" class="injected" rows="4" cols="47"><?=$settings['keywords']?></textarea></p>
	
	<p><b><?=$lang['counters']?></b> </p>
	
	<p><textarea name="counter" class="injected" rows="4" cols="47"><?=$settings['counter']?></textarea></p>
    
    <p><b><?=$lang['advertising_cost']?></b> </p>
    
    <p><input name="advertising" class="injected" type="text" value="<?=$settings['advertising']?>" /></p>

    <p><b><?=$lang['autocomplete_key']?></b> </p>
    
    <p><input name="cron" class="injected" type="text" value="<?=$settings['cron']?>" /></p>

    <p><b>Access token</b> [<a href="https://vkhost.github.io">https://vkhost.github.io</a>] </p>
    
    <p><input name="access_token" class="injected" type="text" value="<?=$settings['access_token']?>" /></p>
    
    <p><b><?=$lang['time_of_cache_files']?></b> </p>
    
    <p><input name="cache" class="injected" type="text" value="<?=$settings['cache']?>" /></p>
    
    <p><b>WK_ID</b> </p>
    
    <p><input name="WK_ID" class="injected" type="text" value="<?=$settings['WK_ID']?>" /></p>

    <p><b>WK_SECRET</b> </p>
    
    <p><input name="WK_SECRET" class="injected" type="text" value="<?=$settings['WK_SECRET']?>" /></p>

    <p><b><?=$lang['email_notifications']?></b> </p>
    
    <p><input name="alert" class="injected" type="text" value="<?=$settings['alert']?>" /></p>
    
    <p><b><?=$lang['pass']?></b> </p>
    
    <p><input name="disclosed" class="injected" type="text" value="<?=$user['disclosed']?>" /></p>

    <p><b>OAuth Yandex Disk</b> </p>
    
    <p><input name="OAuth" class="injected" type="text" value="<?=$settings['OAuth']?>" /></p>
    
    <p><b><?=$lang['file_serving']?></b> </p>
    
    <p>
	<input name="recoil" type="radio" value="0" <?=($settings['recoil']=='0'?" checked":null)?>> <small><?=$lang['copyright_on_the_video']?></small> <font color="red"><big>*</big></font>
	<input name="recoil" type="radio" value="1" <?=($settings['recoil']=='1'?" checked":null)?>> <small><?=$lang['file_from_donor_nort']?></small>
	</p>

    <p><b><?=$lang['wateramk']?></b> [<a href="/designs/water.png">/designs/water.png</a>]</p>
    
    <p>
	<input name="water" type="radio" value="0" <?=($settings['water']=='0'?" checked":null)?>>  <?=$lang['off']?>
	<input name="water" type="radio" value="1" <?=($settings['water']=='1'?" checked":null)?>> <?=$lang['on']?>
	</p>
	
    <p><b><?=$lang['design']?></b> </p>
    
    <p>
    <select class="injected" name="designs">
	<option value='pink'<?=($settings['designs']=='pink'?" selected='selected'":null)?>>pink</option>
	<option value='red'<?=($settings['designs']=='red'?" selected='selected'":null)?>>red</option>
	<option value='violet'<?=($settings['designs']=='violet'?" selected='selected'":null)?>>violet</option>
	</select>
	</p>
	
    <p><b>Player</b> </p>
    
    <p>
    <select class="injected" name="player">
	<option value='0'<?=($settings['player']==0?" selected='selected'":null)?>>Html</option>
	<option value='1'<?=($settings['player']==1?" selected='selected'":null)?>>Uppod</option>
	<option value='2'<?=($settings['player']==2?" selected='selected'":null)?>>PlayerJs</option>
	</select>
	</p>
    
	<input type="submit" class="byecos" value="<?=$lang['send']?>" /> 
	
	<p>
	    
	    <font color="red"><big>*</big> <?=$lang['load_on_the_server']?></font>
	    
	</p>
	
	</form>
	
	</div>