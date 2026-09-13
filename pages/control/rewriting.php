<?php

/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/

    $view = $mysqli -> query("select * from ero_files where rewriting = '0' order by id asc limit 1") -> fetch_assoc();

    if (isset($_POST['name'])) {
        
        $name =  mysqli_real_escape_string($mysqli, filter($_POST['name']));
        $tags =  mysqli_real_escape_string($mysqli, filter($_POST['tags']));
        $description =  mysqli_real_escape_string($mysqli, filter($_POST['description']));
        $category =  mysqli_real_escape_string($mysqli, abs(intval($_POST['category'])));
        
        if (strlen($_POST['name']) > 128 or strlen($_POST['name']) < 5) $warning = $lang['short_or_long_name'];
        else if(!preg_match("#^([A-zА-я0-9\-\_\ ])+$#ui", $_POST['name'])) $warning = $lang['prohibited_characters'];
        else if (strlen($_POST['description']) > 2048 or strlen($_POST['description']) < 256) $warning = $lang['short_long_description'];
        else if (strlen($_POST['tags']) > 256 or strlen($_POST['tags']) < 16) $warning = $lang['short_long_tags'];
        
        if ($warning) error($warning);
        
        $mysqli -> query("update ero_files set category = '$category', name = '$name', tags = '$tags', description = '$description', rewriting = '1' where id = '$view[id]'");
        logs($user['id'], $lang['сhanged_video'].' '.$name.'.', $view['id']);
        header('location: /watch/'.$view['translit'].'.html'); 
        exit;
    }
    
    ?>
    
    <script language="javascript">
    
    function characters() {
    var s, c;
    s = description.value;
    c = s.length;
    view.innerText = s.length;
    }
    
    </script>
    
    <div class="functions_data">
        
    <p align="center"><a href="/watch/<?=$view['translit']?>.html"><img class="screenshots" src="<?=$view['screenshot']?>" alt="<?=$view['name']?>" /></a></p>
    
    <p><?=$lang['server']?> <a href="/control.html?func=server&i=<?=$view['server']?>"><u><b><?=$view['server']?></b></u></a> </p>

    <form method="post">   
    
    <p><b><?=$lang['name']?></b> </p>
    
    <p><input name="name" class="injected" type="text" value="<?=$view['name']?>"></p>
    
	<p><b><?=$lang['category']?></b> </p>
	
	<p><select class="injected" name="category">
	    
	<?php 
	
    $query = $mysqli -> query("select id, name from ero_categories order by id asc");

    while($row = $query -> fetch_assoc()){
    
    ?>
        
	<option value="<?=$row['id']?>" <?=($row['id']==$view['category']?" selected='selected'":null)?>><?=$row['name']?></option>

    <?php 
    
    }
    
    ?>
    
	</select></p>
    
    <p><b><?=$lang['tags']?></b> </p>
    
    <p><textarea name="tags" class="injected" rows="4" cols="47"><?=$view['tags']?></textarea></p>
    
    <p><b><?=$lang['description']?></b> </p>
    
    <p align="center"><?=$lang['total_characters']?> <b><span id="view">0</span></b> </p>
    
    <p><textarea name="description" class="injected" rows="8" cols="47" id="description" onkeyup="characters()"><?=$view['description']?></textarea></p>
    
    <p align="center"><a href="https://text.ru/antiplagiat"><?=$lang['uniqueness_text']?></a> </p>
    
    <input type="submit" class="byecos" value="<?=$lang['edit']?>">   
    
    </form>
    
    </div>
    
    <?php 
    
    $query -> free();