<?php
/**
 * EroCMS - Foydalanuvchi Ro‘yxatdan O‘tish Sahifasi (Register)
 */

if ($member) {
    header('Location: /profile.html');
    exit;
}

$reg_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['act']) && $_POST['act'] === 'register') {
    $username = trim(filter($_POST['username'] ?? ''));
    $email = trim(filter($_POST['email'] ?? ''));
    $password = trim($_POST['password'] ?? '');
    $password_confirm = trim($_POST['password_confirm'] ?? '');

    // Tekshiruvlar
    if (empty($username) || empty($email) || empty($password)) {
        $reg_error = 'Iltimos, barcha majburiy maydonlarni to‘ldiring!';
    } elseif (!preg_match('/^[a-zA-Z0-9_\-\.]{3,30}$/u', $username)) {
        $reg_error = 'Foydalanuvchi nomi 3 dan 30 tagacha belgi (harf, son, defis yoki chiziqcha) bo‘lishi kerak!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $reg_error = 'Email manzili noto‘g‘ri kiritildi!';
    } elseif (mb_strlen($password, 'UTF-8') < 6) {
        $reg_error = 'Parol kamida 6 ta belgidan iborat bo‘lishi kerak!';
    } elseif ($password !== $password_confirm) {
        $reg_error = 'Kiritilgan parollar bir-biriga mos kelmadi!';
    } else {
        $safe_u = mysqli_real_escape_string($mysqli, $username);
        $safe_e = mysqli_real_escape_string($mysqli, $email);

        $exists_q = $mysqli->query("SELECT id, username, email FROM ero_members WHERE username = '$safe_u' OR email = '$safe_e' LIMIT 1");
        if ($exists_q && $exists_q->num_rows > 0) {
            $row = $exists_q->fetch_assoc();
            if (strcasecmp($row['username'], $username) === 0) {
                $reg_error = 'Ushbu foydalanuvchi nomi (<b>'.htmlspecialchars($username).'</b>) allaqachon band qilingan!';
            } else {
                $reg_error = 'Ushbu email manzili bilan allaqachon hisob ochilgan!';
            }
        } else {
            $pass_hash = mysqli_real_escape_string($mysqli, password_hash($password, PASSWORD_DEFAULT));
            $token = bin2hex(random_bytes(32));
            $safe_token = mysqli_real_escape_string($mysqli, $token);
            $ip = mysqli_real_escape_string($mysqli, filter($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'));
            $now = time();

            $ins = $mysqli->query("INSERT INTO ero_members (username, email, password, token, date, last_seen, ip) VALUES ('$safe_u', '$safe_e', '$pass_hash', '$safe_token', '$now', '$now', '$ip')");

            if ($ins) {
                $_SESSION['member_token'] = $token;
                setcookie('member_token', $token, time() + (86400 * 30), '/');
                header('Location: /profile.html?welcome=1');
                exit;
            } else {
                $reg_error = 'Baza xatosi! Iltimos, keyinroq qayta urinib ko‘ring.';
            }
        }
    }
}

$title = 'Ro‘yxatdan O‘tish - ' . filter($_SERVER['HTTP_HOST'] ?? 'sekschi.online');
head();
?>

<div class="auth-page-container">
    <div class="auth-card">
        <div class="auth-card-header">
            <div class="auth-icon-circle">
                <i class="fa fa-user-plus"></i>
            </div>
            <h2>Ro‘yxatdan O‘tish</h2>
            <p>Oson va tezda hisob oching, o‘z videolaringizni yuklang</p>
        </div>

        <?php if (!empty($reg_error)): ?>
            <div class="auth-alert auth-alert-danger">
                <i class="fa fa-exclamation-circle"></i> <?=$reg_error?>
            </div>
        <?php endif; ?>

        <form action="/register.html" method="post" class="auth-form">
            <input type="hidden" name="act" value="register" />

            <div class="form-group-custom">
                <label for="reg_username"><i class="fa fa-user"></i> Foydalanuvchi nomi (Login):</label>
                <input type="text" id="reg_username" name="username" class="form-control-auth" placeholder="Masalan: jasur_77" value="<?=htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8')?>" required />
                <small style="color:#777; font-size:12px;">Faqat lotin harflari va raqamlar (kamida 3 ta belgi)</small>
            </div>

            <div class="form-group-custom">
                <label for="reg_email"><i class="fa fa-envelope"></i> Email manzilingiz:</label>
                <input type="email" id="reg_email" name="email" class="form-control-auth" placeholder="user@mail.uz" value="<?=htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8')?>" required />
            </div>

            <div class="form-group-custom">
                <label for="reg_password"><i class="fa fa-lock"></i> Parol o‘ylab toping:</label>
                <input type="password" id="reg_password" name="password" class="form-control-auth" placeholder="Kamida 6 ta belgi" required />
            </div>

            <div class="form-group-custom">
                <label for="reg_password_confirm"><i class="fa fa-shield"></i> Parolni tasdiqlang:</label>
                <input type="password" id="reg_password_confirm" name="password_confirm" class="form-control-auth" placeholder="Parolni qayta kiriting" required />
            </div>

            <button type="submit" class="btn-auth-submit">
                <i class="fa fa-check-circle"></i> Ro‘yxatdan o‘tish
            </button>
        </form>

        <div class="auth-card-footer">
            <span>Profilingiz bormi?</span>
            <a href="/login.html" class="auth-register-link"><i class="fa fa-sign-in"></i> Kirish</a>
        </div>
    </div>
</div>

<?php
foot();
