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
    
	$editCat = $mysqli -> query("select * from ero_categories where id = '".abs(intval($_GET['id']))."'") -> fetch_assoc();
	
    if (!$editCat){
        header('location: /');
        exit;
    }
    
    if (isset($_POST['translit'])) {
        
        $translit =  mysqli_real_escape_string($mysqli, filter($_POST['translit']));
        $name =  mysqli_real_escape_string($mysqli, filter($_POST['name']));
        $keywords =  mysqli_real_escape_string($mysqli, filter($_POST['keywords']));
        $description =  mysqli_real_escape_string($mysqli, filter($_POST['description']));
        $meta =  mysqli_real_escape_string($mysqli, filter($_POST['meta']));
        
        $mysqli -> query("update ero_categories set name = '$name', description = '$description', meta = '$meta', keywords = '$keywords', translit = '$translit' where id = '$editCat[id]'");
        logs($user['id'], $lang['changed_category'].' '.$name.'.', 0);
        header('location: /'.$translit.'/'); 
        exit;
    }
    
    ?>
        
    <h2 class="view"> 
   <?=$lang['note_that_the_function']?> <b>categories()</b> <?=$lang['automatically']?>
    </h2>
    
    <form method="post">   
    
    <p><?=$lang['name']?></p>
    
    <p><input name="name" class="injected" type="text" value="<?=$editCat['name']?>"></p>
    
    <p><?=$lang['url']?></p>
    
    <p> <big><b>/</b></big> <input name="translit" class="injected" type="text" value="<?=$editCat['translit']?>"> <big><b>/</b></big> </p>
    
    <p><?=$lang['tags']?></p>
    
    <p><textarea name="keywords" class="injected" rows="4" cols="47"><?=$editCat['keywords']?></textarea></p>
    
    <p><?=$lang['description']?></p>
    
    <p><textarea name="description" class="injected" rows="8" cols="47"><?=$editCat['description']?></textarea></p>

    <p><?=$lang['description']?> [meta]</p>
    
    <p><textarea name="meta" class="injected" rows="8" cols="47"><?=$editCat['meta']?></textarea></p>
    
    <input type="submit" class="byecos" value="<?=$lang['edit']?>">
    
    </form>