<?php

/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/

    if (isset($_POST['address'])) {
    
    $description = mysqli_real_escape_string($mysqli, filter($_POST['description']));
    $duration = mysqli_real_escape_string($mysqli, filter($_POST['duration']));
    $address = mysqli_real_escape_string($mysqli, filter($_POST['address']));
    $category =  mysqli_real_escape_string($mysqli, abs(intval($_POST['category'])));
     
    if (filter_var($address, FILTER_VALIDATE_URL) === FALSE)    $warning = $lang['string_is_not_a_link'];
    else if (!strripos($address, 'drive.google.com'))    $warning = $lang['link_not_from'];
    
    $md5 = md5(time());
    $preview = str_replace('view', 'preview', $address);
    $result = file_get_contents($address);
    
    preg_match('|<meta itemprop="name" content="(.*?)">|is', $result, $title);
    preg_match('|<meta property="og:type" content="(.*?)">|is', $result, $type);
    preg_match('|<meta property="og:image" content="(.*?)">|is', $result, $image);
    
    echo $type[1];
    #echo $result;
    #exit;
    
    $uniqueness = md5(str_replace(' ', '_', transliterate($title['1'])));

    $quantity = $mysqli -> query("select count(*) from ero_files where uniqueness = '$uniqueness' and server = 'drive.google.com'") -> fetch_row();
    
    if ($quantity[0] > 0)   $warning = $lang['file_already_exists'];

    if ($type[1] != 'video.other')   $warning = $lang['data_not_found'];

    if ($warning) error($warning);

    if (!$description)
    $description = $title['1'];
    
    $translit = str_replace(' ', '_', transliterate($title[1])).'_'.rand(1, 9999);
    
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', file_get_contents($image[1]));

    $image = new SimpleImage();
    $image->load($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');
    $image->resize($width_S, $height_S);
    $image->save($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');
    
    $mysqli -> query("INSERT INTO ero_files SET uniqueness = '$uniqueness', added = '$user[id]', category = '$category', recoil = '".$preview."', screenshot = '/content/screenshots/".$md5.".jpg', address = '".$preview."', server = 'drive.google.com', tags = '".tags($description)."', name = '".$title['1']."', description = '".$description."', translit = '$translit', duration = '".$duration."', date = '".time()."'");
    $id_file = $mysqli -> insert_id;
    logs($user['id'], $lang['added_video'].' '.$title['1'].'.', $id_file);
    header('location: /watch/'.$translit.'.html');
    exit;
    
    }
    
    ?>
    
    <div class="functions_data">
        
	<form method="post">
	    
	<p><b><?=$lang['url']?></b> <small>[https://drive.google.com/file/d/1B21KQWF8-hRJnwR8DfQWBlu2icGmjdDy/view]</small> </p>
	
	<p><input type="text" name="address" class="injected" /></p>
	
	<p><b><?=$lang['duration']?></b> <small>[0:54]</small></p>
	
	<p><input type="text" name="duration" class="injected" /></p>

	<p><b><?=$lang['description']?></b> </p>
	
    <p><textarea name="description" class="injected" rows="4" cols="47"></textarea></p>
    
	<p><b><?=$lang['category']?></b> </p>
	
	<p><select class="injected" name="category">
	    
	<?
	
    $query = $mysqli -> query("select id, name from ero_categories order by id asc");

    while($row = $query -> fetch_assoc()){
    
    ?>
        
	<option value="<?=$row['id']?>"><?=$row['name']?></option>

    <?
    
    }
    
    ?>
    
	</select></p>
    
    <input type="submit" class="byecos" value="<?=$lang['send']?>" />

    </form>

    </div>
    
    <?
    
    $query -> free();