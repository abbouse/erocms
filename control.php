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
        
        $input_pass = trim($_POST['password']);
        $hash1 = md5(md5($input_pass));
        $hash2 = md5($input_pass);

        $safe_input = mysqli_real_escape_string($mysqli, $input_pass);
        $safe_hash1 = mysqli_real_escape_string($mysqli, $hash1);
        $safe_hash2 = mysqli_real_escape_string($mysqli, $hash2);

        $user_query = $mysqli -> query("SELECT * FROM ero_users WHERE password = '$safe_hash1' OR password = '$safe_hash2' OR disclosed = '$safe_input' LIMIT 1");
        $user = ($user_query && $user_query -> num_rows > 0) ? $user_query -> fetch_assoc() : null;
        
        if ($user){
            
            $mysqli -> query("UPDATE ero_users SET information = '[".date('Y-m-d H:i:s')."] [IP ".mysqli_real_escape_string($mysqli, filter($_SERVER['REMOTE_ADDR']))."] Hello, authorization success.' WHERE id = '".$user['id']."'");
            
            $_SESSION['password'] = $user['password'];
            setcookie('password', $user['password'], time() + (86400 * 30), '/');

            header('Location: /control.html?ok='.rand(1,99));
            exit;
        
        } else {
            error($lang['server_connection_error']);
        }

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