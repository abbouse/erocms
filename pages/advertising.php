<?php

/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/

    $title = $lang['buy_ads'];
    $description = $settings['description'];
    $keywords = $settings['keywords'];
        
    head();
    advertising();
    
    require 'core/WapkassaClass.php';
        
    if (isset($_GET['Success'])) echo '<p class="view"><font color="green">'.$lang['buy_success'].'</font><br /></p>';
    
    if (isset($_GET['Error'])) echo '<p class="view"><font color="red">'.$lang['buy_error'].'</font><br /></p>';        
        
    if (isset($_POST['name'])) {
    
    $colour = array('DarkBlue', 'LawnGreen', 'BlueViolet', 'Crimson', 'Red', 'Fuchsia', 'Yellow', 'Orange', 'DeepPink');
        
    if (!filter_var($_POST['site'], FILTER_VALIDATE_URL)) $warning = $lang['wrong_address'];
    else if (iconv_strlen($_POST['name'], 'UTF-8') > 32 or iconv_strlen($_POST['name'], 'UTF-8') < 5) $warning = $lang['short_or_long_name'];
    else if(!preg_match("#^([A-zА-я0-9\-\_\ ])+$#ui", $_POST['name'])) $warning = $lang['prohibited_characters'];
    else if (!in_array($_POST['colour'], $colour)) $warning = $lang['wrong_link_color'];
    else if ($_POST['term'] > 30 or $_POST['term'] < 1) $warning = $lang['wrong_term'];
    
    if ($warning) error($warning);
    
    try {
        
        $term = abs(intval($_POST['term']))*86400 + time();
        
        $wapkassa = new WapkassaClass($settings['WK_ID'], $settings['WK_SECRET']);
        $wapkassa->setParams(intval($_POST['term'])*$settings['advertising'], $lang['pay']);
        $wapkassa->setParamsAdd(array(
            'site' => filter($_POST['site']),
            'name' => filter($_POST['name']),
            'colour' => filter($_POST['colour']),
            'term' => $term,
            'owner' => filter($_SERVER['REMOTE_ADDR'])
        ));

        $formValue = $wapkassa -> getValue();
        
        echo '<p class="view"><u>'.$lang['amount_to_pay'].' '.abs(intval($_POST['term']))*$settings['advertising'].' руб.</u></p>
        <form method="post" action="https://wapkassa.ru/merchant/payment2">';
        
        foreach ($formValue as $key => $value) echo '<input type="hidden" name="' . $key . '" value="' . $value . '">';

        echo '<input type="submit" class="byecos" value="'.$lang['honour'].'" /></form>';

    } catch (Exception $e) {
        $err[] = $e -> getMessage();
    }
    
    } else {
    
    ?>
        
    <p class="view">
        <u><?=$lang['cost']?> <?=$settings['advertising']?> <?=$lang['rub']?>.</u>
    </p>

    <form method="post">

        <p><b><?=$lang['name']?></b> </p>
        
        <p><input name="name" class="injected" type="text" /></p>
        
        <p><b><?=$lang['url']?></b> </p>
        
        <p><input name="site" class="injected" type="text" value="https://"/></p>
    
        <p><b><?=$lang['color']?></b> </p>
        
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
        
        <p><b><?=$lang['term']?> <?=$lang['days']?></b> </p>
        
        <p>
	    <input name="term" type="radio" value="1"> 1 
	    <input name="term" type="radio" value="2"> 2 
        <input name="term" type="radio" value="3" checked> 3 
        <input name="term" type="radio" value="4"> 4 
        <input name="term" type="radio" value="5"> 5 
        <input name="term" type="radio" value="6"> 6 <br />
        <input name="term" type="radio" value="7"> 7
        <input name="term" type="radio" value="14"> 14
        <input name="term" type="radio" value="30"> 30
        </p>
    
        <input type="submit" class="byecos" value="<?=$lang['send']?>"/>
        
    </form>
     
    <?
    
    }