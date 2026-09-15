<?php
/**
 * EroCMS - Foydalanuvchi Kirish Sahifasi (Login)
 */

if ($member) {
    header('Location: /profile.html');
    exit;
}

$login_error = '';
$login_success = '';

if (isset($_GET['registered'])) {
    $login_success = 'Ro‘yxatdan muvaffaqiyatli o‘tdingiz! Endi profilingizga kirishingiz mumkin.';
}
if (isset($_GET['reset_ok'])) {
    $login_success = 'Parolingiz muvaffaqiyatli yangilandi! Yangi parol bilan kiring.';
}
if (isset($_GET['need_auth'])) {
    $login_error = 'Ushbu amalni bajarish uchun avval profilingizga kiring yoki ro‘yxatdan o‘ting.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['act']) && $_POST['act'] === 'login') {
    $login_input = trim(filter($_POST['login'] ?? ''));
    $password_input = trim($_POST['password'] ?? '');
    $remember = !empty($_POST['remember']);

    if (empty($login_input) || empty($password_input)) {
        $login_error = 'Iltimos, login va parolni kiriting!';
    } else {
        $safe_login = mysqli_real_escape_string($mysqli, $login_input);
        $user_q = $mysqli->query("SELECT * FROM ero_members WHERE username = '$safe_login' OR email = '$safe_login' LIMIT 1");
        $user_data = $user_q ? $user_q->fetch_assoc() : null;

        if (!$user_data) {
            $login_error = 'Bunday foydalanuvchi topilmadi!';
        } elseif (intval($user_data['status']) === 0) {
            $login_error = 'Ushbu hisob ma\'muriyat tomonidan bloklangan!';
        } else {
            $pass_valid = false;
            if (password_verify($password_input, $user_data['password'])) {
                $pass_valid = true;
            } elseif (hash('sha256', $password_input) === $user_data['password'] || md5($password_input) === $user_data['password']) {
                $pass_valid = true;
                // Yangi xavfsiz password_hash ga yangilash
                $new_hash = mysqli_real_escape_string($mysqli, password_hash($password_input, PASSWORD_DEFAULT));
                $mysqli->query("UPDATE ero_members SET password = '$new_hash' WHERE id = '{$user_data['id']}'");
            }

            if ($pass_valid) {
                $new_token = bin2hex(random_bytes(32));
                $safe_token = mysqli_real_escape_string($mysqli, $new_token);
                $ip = mysqli_real_escape_string($mysqli, filter($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'));
                $now = time();

                $mysqli->query("UPDATE ero_members SET token = '$safe_token', last_seen = '$now', ip = '$ip' WHERE id = '{$user_data['id']}'");

                $_SESSION['member_token'] = $new_token;
                if ($remember) {
                    setcookie('member_token', $new_token, time() + (86400 * 30), '/');
                }

                $redir = filter($_GET['redirect'] ?? '/profile.html');
                if (empty($redir) || strpos($redir, '/') !== 0) $redir = '/profile.html';
                header('Location: ' . $redir);
                exit;
            } else {
                $login_error = 'Parol noto‘g‘ri kiritildi!';
            }
        }
    }
}

$title = 'Profilga Kirish - ' . filter($_SERVER['HTTP_HOST'] ?? 'sekschi.online');
head();
?>

<div class="auth-page-container">
    <div class="auth-card auth-minimal">
        <h2 class="auth-minimal-title">Kirish</h2>

        <?php if (!empty($login_error)): ?>
            <div class="auth-alert auth-alert-danger">
                <?=htmlspecialchars($login_error, ENT_QUOTES, 'UTF-8')?>
            </div>
        <?php endif; ?>

        <?php if (!empty($login_success)): ?>
            <div class="auth-alert auth-alert-success">
                <?=htmlspecialchars($login_success, ENT_QUOTES, 'UTF-8')?>
            </div>
        <?php endif; ?>

        <form action="/login.html<?=!empty($_GET['redirect']) ? '?redirect='.urlencode(filter($_GET['redirect'])) : ''?>" method="post" class="auth-form">
            <input type="hidden" name="act" value="login" />

            <div class="form-group-custom">
                <label for="login_input">Foydalanuvchi nomi:</label>
                <input type="text" id="login_input" name="login" class="form-control-auth" placeholder="Login" required autofocus />
            </div>

            <div class="form-group-custom">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:5px;">
                    <label for="pass_input" style="margin:0;">Parol:</label>
                    <a href="/forgot-password.html" class="auth-link-sm">Unutdingizmi?</a>
                </div>
                <input type="password" id="pass_input" name="password" class="form-control-auth" placeholder="Parol" required />
            </div>

            <div class="form-check-custom" style="margin: 12px 0 16px;">
                <label style="cursor:pointer; font-size:13px; color:#aaa; display:inline-flex; align-items:center; gap:6px;">
                    <input type="checkbox" name="remember" value="1" checked style="accent-color:var(--primary-accent, #ff9900);" />
                    <span>Eslab qolish</span>
                </label>
            </div>

            <button type="submit" class="btn-auth-submit">
                Kirish
            </button>
        </form>

        <div class="auth-card-footer" style="margin-top:16px; padding-top:14px; border-top:1px solid #2a2220; text-align:center; font-size:13px;">
            <span style="color:#888;">Hisobingiz yo‘qmi?</span>
            <a href="/register.html" class="auth-register-link" style="color:var(--primary-accent, #ff9900); font-weight:600; margin-left:6px;">Ro‘yxatdan o‘tish</a>
        </div>
    </div>
</div>
