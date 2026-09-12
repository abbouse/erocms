<?php

/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/

    if (isset($_POST['address'])) {
        
    $address = mysqli_real_escape_string($mysqli, filter($_POST['address']));
    $category =  mysqli_real_escape_string($mysqli, abs(intval($_POST['category'])));
     
    if (filter_var($address, FILTER_VALIDATE_URL) === FALSE)    $warning = $lang['string_is_not_a_link'];
    else if (!strripos($address, 'vk.com'))    $warning = $lang['link_not_from_vk'];
    
    $md5 = md5(time());

    $query = str_replace('https://vk.com/video', '', $address);
    
    $result = json_decode(file_get_contents('https://api.vk.com/method/video.get?videos='.$query.'&access_token='.$settings['access_token'].'&v=5.124'), true);

    $uniqueness = md5(str_replace(' ', '_', transliterate($result['response']['items']['0']['title'])));

    $quantity = $mysqli -> query("select count(*) from ero_files where uniqueness = '$uniqueness' and server = 'vk.com'") -> fetch_row();
    
    if ($quantity[0] > 0)   $warning = $lang['already_been_added'];
    
    #   $result['response']['items']['0']['player'] получить ссылка на видео
    #   $result['response']['items']['0']['description'] описание
    #   $result['response']['items']['0']['title'] название
    #   $result['response']['items']['0']['image']['3']['url'] скриншот
    #   $result['response']['items']['0']['duration'] продолжительность

    if (!$result['response']['items']['0']['player'])   $warning = $lang['not_found'];  

    if ($warning) error($warning);

    if (!$result['response']['items']['0']['description'])
    $result['response']['items']['0']['description'] = $result['response']['items']['0']['title'];
    
    $translit = str_replace(' ', '_', transliterate($result['response']['items']['0']['title'])).'_'.rand(1, 9999);
    
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg', file_get_contents($result['response']['items']['0']['image']['3']['url']));

    $image = new SimpleImage();
    $image->load($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');
    $image->resize($width_S, $height_S);
    $image->save($_SERVER['DOCUMENT_ROOT'].'/content/screenshots/'.$md5.'.jpg');
    
    $mysqli -> query("INSERT INTO ero_files SET uniqueness = '$uniqueness', added = '$user[id]', category = '$category', recoil = '".$result['response']['items']['0']['player']."', screenshot = '/content/screenshots/".$md5.".jpg', address = '".$result['response']['items']['0']['player']."', server = 'vk.com', tags = '".tags($result['response']['items']['0']['description'])."', name = '".$result['response']['items']['0']['title']."', description = '".$result['response']['items']['0']['description']."', translit = '$translit', duration = '".date('H:i:s', $result['response']['items']['0']['duration'])."', date = '".time()."'");
    $id_file = $mysqli -> insert_id;
    logs($user['id'], $lang['added'].' '.$result['response']['items']['0']['title'].'.', $id_file);
    header('location: /watch/'.$translit.'.html');
    exit;
    
    }
    
    ?>
    
    <div class="functions_data">
        
	<form method="post">
	    
	<p><b><?=$lang['url']?></b> <small>[https://vk.com/video356108950_456239027]</small> </p>
	
	<p><input type="text" name="address" class="injected" /></p>
	
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