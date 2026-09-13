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

    require_once 'core/AdminLayout.php';

    if (isset($_GET['func']) && $_GET['func'] === 'out') {
        setcookie('password', '', time() - 3600, '/');
        session_destroy();
        if ($user) {
            logs($user['id'], $lang['left_the_site'].'.', 0);
        }
        header('Location: /control.html');
        exit;
    }

    if (!$user) {
        $login_error = null;
        if (isset($_POST['password'])) {
            $input_pass = trim($_POST['password']);
            $hash1 = md5(md5($input_pass));
            $hash2 = md5($input_pass);

            $safe_input = mysqli_real_escape_string($mysqli, $input_pass);
            $safe_hash1 = mysqli_real_escape_string($mysqli, $hash1);
            $safe_hash2 = mysqli_real_escape_string($mysqli, $hash2);

            $user_query = $mysqli->query("SELECT * FROM ero_users WHERE password = '$safe_hash1' OR password = '$safe_hash2' OR disclosed = '$safe_input' LIMIT 1");
            $user = ($user_query && $user_query->num_rows > 0) ? $user_query->fetch_assoc() : null;
            
            if ($user) {
                $mysqli->query("UPDATE ero_users SET information = '[".date('Y-m-d H:i:s')."] [IP ".mysqli_real_escape_string($mysqli, filter($_SERVER['REMOTE_ADDR']))."] Hello, authorization success.' WHERE id = '".$user['id']."'");
                
                $_SESSION['password'] = $user['password'];
                setcookie('password', $user['password'], time() + (86400 * 30), '/');

                header('Location: /control.html?ok='.rand(1,99));
                exit;
            } else {
                $login_error = $lang['server_connection_error'] ?? 'Parol noto‘g‘ri kiritildi!';
            }
        }
        
        $host = filter($_SERVER['HTTP_HOST'] ?? 'sekschi.online');
        $css_file = $_SERVER['DOCUMENT_ROOT'].'/designs/admin.css';
        $css_v = file_exists($css_file) ? filemtime($css_file) : time();
        ?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kirish | EroCMS Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="/designs/admin.css?v=<?=$css_v?>" />
    <style>
        body.adm-login-body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: radial-gradient(circle at 50% 30%, #1a1e29 0%, #0d0f14 100%);
            margin: 0;
            padding: 20px;
        }
        .adm-login-card {
            width: 100%;
            max-width: 400px;
            background: #161820;
            border: 1px solid #282c37;
            border-radius: 12px;
            padding: 32px 28px;
            box-shadow: 0 12px 36px rgba(0,0,0,0.6);
            text-align: center;
        }
        .adm-login-logo {
            font-size: 26px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
    </style>
</head>
<body class="adm-login-body">
    <div class="adm-login-card">
        <div class="adm-login-logo">
            <i class="fa fa-play-circle" style="color: #ff9900; font-size: 32px;"></i>
            <span><?=htmlspecialchars($host)?></span>
        </div>
        <p style="color: #94a3b8; font-size: 13px; margin: 0 0 24px;">Admin Boshqaruv Paneliga Kirish</p>
        
        <?php if ($login_error): ?>
            <div class="adm-alert adm-alert-danger" style="margin-bottom: 20px; text-align: left;">
                <i class="fa fa-exclamation-triangle"></i>
                <span><?=$login_error?></span>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="adm-form-group" style="text-align: left;">
                <label class="adm-label"><i class="fa fa-lock" style="color: #ff9900;"></i> Parolni kiriting:</label>
                <input type="password" name="password" class="adm-input" placeholder="Admin parolini yozing..." required autofocus style="padding: 12px;" />
            </div>
            <button type="submit" class="adm-btn adm-btn-primary" style="width: 100%; padding: 12px; font-size: 14px; margin-top: 8px;">
                <i class="fa fa-sign-in"></i> Tizimga kirish
            </button>
        </form>
        
        <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #252834; font-size: 12px; color: #64748b;">
            <a href="/" style="color: #94a3b8; text-decoration: none;"><i class="fa fa-arrow-left"></i> Asosiy saytga qaytish</a>
        </div>
    </div>
</body>
</html>
        <?php
        exit;
    }

    $func = isset($_GET['func']) ? filter($_GET['func']) : 'default';

    $page_titles = [
        'default'         => 'Boshqaruv Paneli (Dashboard)',
        'parsing'         => 'Universal Parser',
        'view_video'      => 'Videolar Boshqaruvi',
        'stats'           => 'Statistika & Mehmonlar Harakatlari',
        'advertising'     => 'Reklama Boshqaruvi',
        'view_categories' => 'Toifalar & SEO Kalit So‘zlar',
        'dmca'            => 'DMCA Mualliflik Shikoyatlari',
        'comments'        => 'Izohlar Moderatsiyasi',
        'tools'           => 'Tizim Vositalari & Optimizatsiya',
        'settings'        => 'Sayt Sozlamalari',
        'users'           => 'Admin Foydalanuvchilar',
        'logs'            => 'Admin Harakatlar Jurnali',
        'add'             => 'Video Qo‘shish',
        'import'          => 'To‘g‘ridan-to‘g‘ri Video Yuklash',
        'publish'         => 'Rejalashtirilgan Videolar',
        'addCat'          => 'Yangi Toifa Qo‘shish',
        'editCat'         => 'Toifani Tahrirlash',
        'removeCat'       => 'Toifani O‘chirish',
        'rewriting'       => 'Videoni Qayta Yozish'
    ];
    $current_title = $page_titles[$func] ?? 'Boshqaruv Paneli';

    // Tezkor AJAX so'rovlari uchun (Header/Footer yuklanmasdan toza JSON javob berish)
    if (!empty($_REQUEST['ajax_action']) || !empty($_GET['ajax'])) {
        require_once 'core/ClassSimpleImage.php';
        $subpage_file = 'pages/control/' . $func . '.php';
        if (file_exists($subpage_file)) {
            require $subpage_file;
        }
        if ($mysqli instanceof mysqli) {
            @$mysqli->close();
        }
        exit;
    }

    admin_head($current_title, $func);

    require_once 'core/ClassSimpleImage.php';

    $subpage_file = 'pages/control/' . $func . '.php';
    if (file_exists($subpage_file)) {
        require $subpage_file;
    } else {
        echo '<div class="adm-alert adm-alert-danger"><i class="fa fa-exclamation-circle"></i> Sahifa topilmadi: ' . htmlspecialchars($func) . '</div>';
        require 'pages/control/default.php';
    }

    admin_foot();

    if ($mysqli instanceof mysqli) {
        @$mysqli->close();
    }