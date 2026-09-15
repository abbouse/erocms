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
    <div class="auth-card">
        <div class="auth-card-header">
            <div class="auth-icon-circle">
                <i class="fa fa-user-circle"></i>
            </div>
            <h2>Profilga Kirish</h2>
            <p>Hisobingizga kiring va videolarni boshqaring</p>
        </div>

        <?php if (!empty($login_error)): ?>
            <div class="auth-alert auth-alert-danger">
                <i class="fa fa-exclamation-circle"></i> <?=htmlspecialchars($login_error, ENT_QUOTES, 'UTF-8')?>
            </div>
        <?php endif; ?>

        <?php if (!empty($login_success)): ?>
            <div class="auth-alert auth-alert-success">
                <i class="fa fa-check-circle"></i> <?=htmlspecialchars($login_success, ENT_QUOTES, 'UTF-8')?>
            </div>
        <?php endif; ?>

        <form action="/login.html<?=!empty($_GET['redirect']) ? '?redirect='.urlencode(filter($_GET['redirect'])) : ''?>" method="post" class="auth-form">
            <input type="hidden" name="act" value="login" />

            <div class="form-group-custom">
                <label for="login_input"><i class="fa fa-user"></i> Foydalanuvchi nomi yoki Email:</label>
                <input type="text" id="login_input" name="login" class="form-control-auth" placeholder="Masalan: alisher yoki user@mail.uz" required autofocus />
            </div>

            <div class="form-group-custom">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <label for="pass_input"><i class="fa fa-lock"></i> Parol:</label>
                    <a href="/forgot-password.html" class="auth-link-sm">Parolni unutdingizmi?</a>
                </div>
                <input type="password" id="pass_input" name="password" class="form-control-auth" placeholder="Parolingizni kiriting" required />
            </div>

            <div class="form-check-custom">
                <label>
                    <input type="checkbox" name="remember" value="1" checked />
                    <span>Meni eslab qol (30 kun)</span>
                </label>
            </div>

            <button type="submit" class="btn-auth-submit">
                <i class="fa fa-sign-in"></i> Profilga kirish
            </button>
        </form>

        <div class="auth-card-footer">
            <span>Hali hisobingiz yo‘qmi?</span>
            <a href="/register.html" class="auth-register-link"><i class="fa fa-user-plus"></i> Ro‘yxatdan o‘tish</a>
        </div>
    </div>
</div>

<?php
foot();
