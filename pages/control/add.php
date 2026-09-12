<?php

/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/

    if (!class_exists('ffmpeg_movie')) {

    ?>
        
    <script>
  alert("ffmpeg disabled");
  window.location.href = "/control.html"
    </script>
    
    <?
    
    exit;

    }
    
    if (isset($_POST['name'])) {
    
    $address = $_FILES['address']['name'];
    $resolution = strtolower(strrchr($address, '.'));
    $res_address = array('.mp4');
    
    if (empty($_FILES['address'])) $warning = $lang['select_file'];
    else if(preg_match('/.php/i', $address) || preg_match('/.pl/i', $address) || $address == '.htaccess' || !in_array($resolution, $res_address)) $warning = $lang['invalid_file'];
    else if (strlen($_POST['name']) > 128 or strlen($_POST['name']) < 16) $warning = $lang['short_or_long_name'];
    else if (strlen($_POST['description']) > 1024 or strlen($_POST['description']) < 64) $warning = $lang['short_long_description'];
    
    if ($warning) error($warning);
    
    $md5 = md5(time());
    $name =  mysqli_real_escape_string($mysqli, filter($_POST['name']));
    $description =  mysqli_real_escape_string($mysqli, filter($_POST['description']));
    $category =  mysqli_real_escape_string($mysqli, abs(intval($_POST['category'])));
    $translit = str_replace(' ', '_', transliterate($name)).'_'.rand(1, 9999);
        
    move_uploaded_file($_FILES['address']['tmp_name'], $_SERVER['DOCUMENT_ROOT'].'/content/video/'. $md5 .'.mp4');
    #move_uploaded_file($_FILES['screenshot']['tmp_name'], $_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'. $md5 .'.jpg');
    
    $movie = new ffmpeg_movie($_SERVER['DOCUMENT_ROOT'].'/content/video/'. $md5 .'.mp4');
    
    $frame = $movie->getFrame(rand(24, 72));
    
    if ($frame) {
        
        $toGDImage = $frame->toGDImage();
        
        if ($toGDImage) {
            
        imagepng($toGDImage, $_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'. $md5 .'.jpg');
        imagedestroy($toGDImage);
            
        }
        
    }
    
    if (intval($movie->getDuration()) > 3599)
	$duration = intval($movie->getDuration() / 3600).':'.date('s',fmod($movie->getDuration() / 60, 60)).':'.date('s',fmod($movie->getDuration(), 3600));
	elseif (intval($movie->getDuration()) > 59)
	$duration = intval($movie->getDuration() / 60).':'.date('s',fmod($movie->getDuration(), 60));
	else
	$duration = '00:'.intval($movie->getDuration());
					
    $mysqli -> query("INSERT INTO ero_files SET added = '$user[id]', category = '$category', recoil = '/content/video/".$md5.".mp4', screenshot = '/content/screenshots/".$md5.".jpg', address = '/content/video/".$md5.".mp4', server = '".filter($_SERVER['SERVER_NAME'])."', tags = '".tags($description)."', name = '$name', description = '$description', translit = '$translit', duration = '$duration', date = '".time()."'");
    $id_file = $mysqli -> insert_id;
    logs($user['id'], $lang['add_video'].' '.$name.'.', $id_file);
    header('location: /watch/'.$translit.'.html');
    exit;
    
    }
    
    ?>
    
    <div class="functions_data">
        
	<form method="post" enctype="multipart/form-data">
	    
	<p><b><?=$lang['file']?></b> </p>
	
	<p><input type="file" name="address" /></p>
	
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
	
	<p><b><?=$lang['name']?></b> </p>
	
	<p><input type="text" class="injected" name="name" /></p>
	
	<p><b><?=$lang['description']?></b> </p>
	
    <p><textarea name="description" class="injected" rows="4" cols="47"></textarea></p>
    
    <input type="submit" class="byecos" value="<?=$lang['send']?>" />

    </form>
    
    </div>
    
    <?
    
    $query -> free();