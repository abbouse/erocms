<?php

/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/

    #Откоментируйте строку для закрытия панели
    
    #exit('You are not authorized to access this page.');
    
    require 'core/Functions.php';

    $title = $settings['title'];
    $description = $settings['description'];
    $keywords = $settings['keywords'];
    
    head();

    if (!$user) {
    
    if (isset($_POST['password'])){
        
        $user = $mysqli -> query("select * from ero_users where password = '".mysqli_real_escape_string($mysqli, filter(md5(md5($_POST['password']))))."'") -> fetch_assoc();
        
        if ($user){
            
            $mysqli -> query("update ero_users set information = '[".date('Y-m-d H:i:s', time())."] [IP ".filter($_SERVER['REMOTE_ADDR'])."] Hello, on your project authorization attempt.' where id = '$user[id]'");
            
            $_SESSION['password'] = $user['password'];

            header('Location: /control.html?'.rand(1,9));
            exit;
        
        } else error($lang['server_connection_error']);

    }
    
    $_SESSION['protective'] = rand(10000, 999999);
    
    ?>
    
    <form method="post" class="decor">
	<p><b><?=$lang['add_pass']?></b> </p>
	<input type="password" name="password" class="injected" placeholder="<?=$lang['add_pass']?>" /> 
	<p><input type="submit" class="byecos" value="<?=$lang['send']?>" /></p>
	</form>
    
    <?
    
    foot();
    exit;
    
    }

    if (isset($_GET['func'])) $func = filter($_GET['func']);
    
    else 
    {

    require 'pages/control/default.php';
    
    foot();
    exit;
    
    }
    
    switch ($func) {
	
    #Страницы
    
    case $func:

    require 'core/ClassSimpleImage.php';
    require 'pages/control/'.$func.'.php';
    
    break;

    }
    
    $mysqli -> close();
    
    foot();