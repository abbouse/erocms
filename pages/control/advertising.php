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
    
    if (isset($_GET['deletion'])){
        
        $mysqli -> query("delete from ero_advertising where id = '".abs(intval($_GET['deletion']))."'");
        logs($user['id'], $lang['removed_the_ad_site'], 0);
        header('location: /control.html?func=advertising');
        exit;
    }
    
    $quantity = $mysqli -> query("select count(*) from ero_advertising") -> fetch_row();
    $k_page = k_page($quantity[0], 10);
    $page = page($k_page);
    $start = 10*$page-10;    
    
    if (isset($_GET['edit'])){
    
    $editAdv = $mysqli -> query("select * from ero_advertising where id = '".abs(intval($_GET['edit']))."'") -> fetch_assoc();
    
    if (!$editAdv){
        header('location: /');
        exit;
    }
    
    if (isset($_POST['name'])){
    
    $term = strtotime($_POST['term']);    
    $name =  mysqli_real_escape_string($mysqli, filter($_POST['name']));
    $colour =  mysqli_real_escape_string($mysqli, filter($_POST['colour']));
    $site =  mysqli_real_escape_string($mysqli, filter($_POST['site']));
    
    $mysqli -> query("update ero_advertising set name = '$name', colour = '$colour', site = '$site', term = '$term' where id = '$editAdv[id]'");
    $mysqli -> query("INSERT INTO ero_logs SET id_user = '$user[id]', act = '$lang[changed_ad_space] $editAdv[name].', date = '".time()."'");
    
    header('location: /control.html?func=advertising');
    exit;
    
    }
    
    ?>
        
    <form method="post">
    
    <p><?=$lang['name']?></p>
    
    <p><input name="name" class="injected" type="text" value="<?=$editAdv['name']?>"></p>

    <p><?=$lang['url']?></p>
    
    <p><input name="site" class="injected" type="text" value="<?=$editAdv['site']?>"></p>

    <p><?=$lang['color']?></p>
    
        <p>
	    <input name="colour" type="radio" value="DarkBlue"> <font color="DarkBlue">DarkBlue</font>
	    <input name="colour" type="radio" value="LawnGreen"> <font color="LawnGreen">LawnGreen</font>
        <input name="colour" type="radio" value="BlueViolet"> <font color="BlueViolet">BlueViolet</font>
        <input name="colour" type="radio" value="Crimson"> <font color="Crimson">Crimson</font><br />
        <input name="colour" type="radio" value="Red"> <font color="Red">Red</font>
        <input name="colour" type="radio" value="Fuchsia" checked> <font color="Fuchsia">Fuchsia</font>
        <input name="colour" type="radio" value="Yellow"> <font color="Yellow">Yellow</font>
        <input name="colour" type="radio" value="Orange"> <font color="Orange">Orange</font>
        <input name="colour" type="radio" value="DeepPink"> <font color="DeepPink">DeepPink</font>
        </p>
 
	<p><?=$lang['term']?></p>
	
	<p><input class="injected" name="term" type="date" value="<?=date('Y-m-d', $editAdv['term'])?>" min="<?=date('Y-m-d', time())?>" max="2025-12-31"></p>
    
    <input type="submit" class="byecos" value="<?=$lang['edit']?>">
    
    </form>
    
    <?
    
    }
    
    if (isset($_GET['add'])){
    
    if (isset($_POST['name'])){
    
    $term = strtotime($_POST['term']);    
    $name =  mysqli_real_escape_string($mysqli, filter($_POST['name']));
    $colour =  mysqli_real_escape_string($mysqli, filter($_POST['colour']));
    $site =  mysqli_real_escape_string($mysqli, filter($_POST['site']));
    
    $mysqli -> query("INSERT INTO ero_advertising SET name = '$name', colour = '$colour', site = '$site', term = '$term'");
    $mysqli -> query("INSERT INTO ero_logs SET id_user = '$user[id]', act = '$lang[added_ad_space] $name.', date = '".time()."'");
    
    header('location: /control.html?func=advertising');
    exit;
    
    }
    
    ?>
        
    <form method="post">
    
    <p><?=$lang['name']?></p>
    
    <p><input name="name" class="injected" type="text"></p>

    <p><?=$lang['url']?></p>
    
    <p><input name="site" class="injected" type="text"></p>

    <p><?=$lang['color']?></p>

        <p>
	    <input name="colour" type="radio" value="DarkBlue"> <font color="DarkBlue">DarkBlue</font>
	    <input name="colour" type="radio" value="LawnGreen"> <font color="LawnGreen">LawnGreen</font>
        <input name="colour" type="radio" value="BlueViolet"> <font color="BlueViolet">BlueViolet</font>
        <input name="colour" type="radio" value="Crimson"> <font color="Crimson">Crimson</font><br />
        <input name="colour" type="radio" value="Red"> <font color="Red">Red</font>
        <input name="colour" type="radio" value="Fuchsia" checked> <font color="Fuchsia">Fuchsia</font>
        <input name="colour" type="radio" value="Yellow"> <font color="Yellow">Yellow</font>
        <input name="colour" type="radio" value="Orange"> <font color="Orange">Orange</font>
        <input name="colour" type="radio" value="DeepPink"> <font color="DeepPink">DeepPink</font>
        </p>
        
	<p><?=$lang['term']?></p>
	
	<p><input class="injected" name="term" type="date" value="<?=date('Y-m-d', time())?>" min="<?=date('Y-m-d', time())?>" max="2025-12-31"></p>
    
    <input type="submit" class="byecos" value="<?=$lang['send']?>">
    
    </form>
    
    <?
    
    }
    
    ?>
    
    <a href="/control.html?func=advertising&add" class="tach"><?=$lang['add_site']?></a>
    
    <?
    
    if ($quantity[0] == 0) echo '<div class="err">'.$lang['no_sites_found'].'</div>';

    $query = $mysqli -> query("select * from ero_advertising order by term desc limit $start, 10");

    while($row = $query -> fetch_assoc()){
        
        if ($row['owner'] == null)  $row['owner'] = 'Null';
        
    ?>
       <p class="functions_data"> 
       <?=$lang['url']?>: <a href="<?= $row['site']?>"><b><?= $row['site']?></b></a> 
       <a href="/control.html?func=advertising&edit=<?=$row['id'];?>"><img src="/designs/icons/view/edit.png" width="16" height="16" /></a>
       <a href="/control.html?func=advertising&deletion=<?=$row['id'];?>"><img src="/designs/icons/view/remove.png" width="16" height="16" /></a> <br />
       <?=$lang['term']?>: <b><?= date('Y-m-d H:i:s', $row['term']);?> </b><br />
       <?=$lang['color']?>: <b><font color="<?= $row['colour'];?>"><?= $row['colour'];?></font></b> <br />
       <?=$lang['added']?>: <b><?=$row['owner'];?></b> <br />
       </p>
    
    <? 
        
        } 
    
    if ($k_page > 1) str('/control.html?func=advertising&', $k_page, $page);

    $query -> free();