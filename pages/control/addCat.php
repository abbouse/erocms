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
    
    if (isset($_POST['translit'])) {
        
        $translit =  mysqli_real_escape_string($mysqli, filter($_POST['translit']));
        $name =  mysqli_real_escape_string($mysqli, filter($_POST['name']));
        $keywords =  mysqli_real_escape_string($mysqli, filter($_POST['keywords']));
        $description =  mysqli_real_escape_string($mysqli, filter($_POST['description']));
        $meta =  mysqli_real_escape_string($mysqli, filter($_POST['meta']));
	
        if (strlen($_POST['name']) > 64 or strlen($_POST['name']) < 4) $warning = $lang['short_or_long_name'];
        else if (strlen($_POST['translit']) > 64 or strlen($_POST['translit']) < 4) $warning = $lang['short_long_address'];
        
        if ($warning) error($warning);
    
        $mysqli -> query("INSERT INTO ero_categories set name = '$name', description = '$description', meta = '$meta', keywords = '$keywords', translit = '$translit'");
        
        logs($user['id'], $lang['created_a_category'].' '.$name.'.', 0);
        
        header('location: /'.$translit.'/'); 
        exit;
    }
    
    ?>
    
    <div class="functions_data">
        
    <form method="post">   
    
    <p><?=$lang['name']?></p>
    
    <p><input name="name" class="injected" type="text"></p>
    
    <p><?=$lang['url']?></p>
    
    <p> <big><b>/</b></big> <input name="translit" class="injected" type="text"> <big><b>/</b></big> </p>
    
    <p><?=$lang['tags']?></p>
    
    <p><textarea name="keywords" class="injected" rows="4" cols="47"></textarea></p>
    
    <p><?=$lang['description']?></p>
    
    <p><textarea name="description" class="injected" rows="8" cols="47"></textarea></p>

    <p><?=$lang['description']?> [meta]</p>
    
    <p><textarea name="meta" class="injected" rows="8" cols="47"></textarea></p>
	
    <input type="submit" class="byecos" value="<?=$lang['send']?>">
    
    </form>
    
    </div>