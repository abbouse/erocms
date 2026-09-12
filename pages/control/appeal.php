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
    
        if (isset($_POST['appeal'])){
            
            $appeal =  mysqli_real_escape_string($mysqli, filter($_POST['appeal']));
            $title =  mysqli_real_escape_string($mysqli, filter($_POST['title']));
            $email =  mysqli_real_escape_string($mysqli, filter($_POST['email']));
            
            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) $warning = $lang['email_is_not_a_valid'];
            else if (strlen($_POST['title']) > 64 or strlen($_POST['title']) < 12) $warning = $lang['short_or_long_topic'];
            else if (strlen($_POST['appeal']) > 512 or strlen($_POST['appeal']) < 12) $warning = $lang['short_or_long_appeal'];
            else if ($_SESSION['protective'] != $_POST['protective']) $warning = $lang['wrong_code'];
            
            if ($warning) error($warning);
            
            mail('droditeleva@internet.ru', $title, "$appeal \n EroCms $version \n $email $_SERVER[SERVER_NAME] ($_SERVER[REMOTE_ADDR])");
            
            header('Location: /control.php?func=appeal&sent');
            exit;
            
        }
        
    unset($_SESSION['protective']);

    $_SESSION['protective'] = rand(1000, 99999);
    
    if (isset($_GET['sent'])) echo '<p class="view"><font color="green">'.$lang['thanks'].'</font></p>';
    
    ?>
    
    <form method="post">
	<p><b><?=$lang['subject']?></b> </p>
	<input type="text" name="title" class="injected" /> 
	<p><b><?=$lang['msg']?></b> </p>
    <p><textarea name="appeal" class="injected" rows="3" cols="33"></textarea></p>
	<p><b><?=$lang['email']?></b> </p>
	<input type="email" name="email" class="injected" /> 
	<p><b><?=$lang['code']?></b>  <small><?=abs(intval($_SESSION['protective']))?></small> </p>
	<p><input type="number" name="protective" class="injected" /> </p>
	<input type="submit" class="byecos" value="<?=$lang['send']?>" />
	</form>