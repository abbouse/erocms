<?php
/**
 * EroCMS - Foydalanuvchi Ro‘yxatdan O‘tish Sahifasi (Register)
 * Faqat login va parol talab qilinadi (minimalistic)
 */

if ($member) {
    header('Location: /profile.html');
    exit;
}

$reg_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['act']) && $_POST['act'] === 'register') {
    $username = trim(filter($_POST['username'] ?? ''));
    $password = trim($_POST['password'] ?? '');
    $password_confirm = trim($_POST['password_confirm'] ?? '');

    // Tekshiruvlar: faqat login va parol
    if (empty($username) || empty($password)) {
        $reg_error = 'Iltimos, login va parolni kiriting!';
    } elseif (!preg_match('/^[a-zA-Z0-9_\-\.]{3,30}$/u', $username)) {
        $reg_error = 'Login 3 dan 30 tagacha belgi (harf, son yoki chiziqcha) bo‘lishi kerak!';
    } elseif (mb_strlen($password, 'UTF-8') < 6) {
        $reg_error = 'Parol kamida 6 ta belgidan iborat bo‘lishi kerak!';
    } elseif ($password !== $password_confirm) {
        $reg_error = 'Kiritilgan parollar bir-biriga mos kelmadi!';
    } else {
        $safe_u = mysqli_real_escape_string($mysqli, $username);

        $exists_q = $mysqli->query("SELECT id, username FROM ero_members WHERE username = '$safe_u' LIMIT 1");
        if ($exists_q && $exists_q->num_rows > 0) {
            $reg_error = 'Ushbu login band qilingan. Iltimos, boshqa login tanlang!';
        } else {
            $pass_hash = mysqli_real_escape_string($mysqli, password_hash($password, PASSWORD_DEFAULT));
            $token = bin2hex(random_bytes(32));
            $safe_token = mysqli_real_escape_string($mysqli, $token);
            $ip = mysqli_real_escape_string($mysqli, filter($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'));
            $now = time();

            $ins = $mysqli->query("INSERT INTO ero_members (username, email, password, token, date, last_seen, ip) VALUES ('$safe_u', NULL, '$pass_hash', '$safe_token', '$now', '$now', '$ip')");

            if ($ins) {
                $_SESSION['member_token'] = $token;
                setcookie('member_token', $token, time() + (86400 * 30), '/');
                header('Location: /profile.html?welcome=1');
                exit;
            } else {
                $reg_error = 'Baza xatosi: ' . $mysqli->error;
            }
        }
    }
}

$title = $lang['register_title'] . ' - ' . filter($_SERVER['HTTP_HOST'] ?? 'sekschi.online');
head();
?>

<div class="xxxhd-title-top">
    <h1><?=$lang['register_title']?></h1>
</div>

<div class="site-form-minimal">
    <?php if (!empty($reg_error)): ?>
        <div class="site-alert error">
            <i class="fa fa-exclamation-circle"></i> <?=$reg_error?>
        </div>
    <?php endif; ?>

    <form action="/register.html" method="post">
        <input type="hidden" name="act" value="register" />

        <div class="form-group-min">
            <label for="reg_username"><?=$lang['username_field']?>:</label>
            <input type="text" id="reg_username" name="username" class="input-min" placeholder="<?=$lang['username_field']?>" value="<?=htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8')?>" required autofocus />
        </div>

        <div class="form-group-min">
            <label for="reg_password"><?=$lang['password_field']?>:</label>
            <input type="password" id="reg_password" name="password" class="input-min" placeholder="<?=$lang['password_field']?>" required />
        </div>

        <div class="form-group-min">
            <label for="reg_password_confirm"><?=$lang['confirm_password']?>:</label>
            <input type="password" id="reg_password_confirm" name="password_confirm" class="input-min" placeholder="<?=$lang['confirm_password']?>" required />
        </div>

        <button type="submit" class="btn-min-submit" style="margin-top:12px;">
            <?=$lang['btn_register']?>
        </button>
    </form>

    <div class="form-min-footer">
        <span><?=$lang['already_have_account']?></span>
        <a href="/login.html" class="link-min-accent"><?=$lang['btn_login']?></a>
    </div>
</div>

