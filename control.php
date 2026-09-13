<?php

/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/

    #Откоментируйте строку для закрытия панели
    
    #exit('You are not authorized to access this page.');
    
    require 'core/Functions.php';

    register_shutdown_function(function() {
        $err = error_get_last();
        if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
            while (ob_get_level() > 0) {
                ob_end_flush();
            }
            echo "\n<!-- SHUTDOWN_FATAL_ERROR: " . json_encode($err) . " -->\n";
            echo "<div style='background:#220000; color:#ff6666; padding:20px; border:2px solid #ff0000; font-family:monospace; margin:20px; font-size:14px; position:relative; z-index:99999;'>";
            echo "<h3 style='color:#ff0000;'>PHP FATAL ERROR:</h3>";
            echo "<p><b>Message:</b> " . htmlspecialchars($err['message']) . "</p>";
            echo "<p><b>File:</b> " . htmlspecialchars($err['file']) . " (Line " . $err['line'] . ")</p>";
            echo "</div>";
        }
    });

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
    
    <?php
    
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