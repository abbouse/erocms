<?php
/**
 * EroCMS - Parolni Tiklash Sahifasi (Forgot Password)
 */

if ($member) {
    header('Location: /profile.html');
    exit;
}

$forgot_error = '';
$forgot_success = '';
$step = 1;
$reset_code = '';
$reset_user_id = 0;

// 1-bosqich: Login yoki email kiritildi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['act']) && $_POST['act'] === 'request_reset') {
    $login_or_email = trim(filter($_POST['identity'] ?? ''));

    if (empty($login_or_email)) {
        $forgot_error = 'Iltimos, username yoki email manzilingizni kiriting!';
    } else {
        $safe_id = mysqli_real_escape_string($mysqli, $login_or_email);
        $user_q = $mysqli->query("SELECT id, username, email FROM ero_members WHERE username = '$safe_id' OR email = '$safe_id' LIMIT 1");
        $u = $user_q ? $user_q->fetch_assoc() : null;

        if (!$u) {
            $forgot_error = 'Kiritilgan ma\'lumotlarga mos foydalanuvchi topilmadi!';
        } else {
            // 6 xonali tiklash kodi yaratish
            $code = strval(rand(100000, 999999));
            $token = bin2hex(random_bytes(16));
            $expiry = time() + 1800; // 30 daqiqa

            $mysqli->query("UPDATE ero_members SET reset_token = '$code', reset_expiry = '$expiry' WHERE id = '{$u['id']}'");

            $step = 2;
            $reset_user_id = $u['id'];
            $reset_code = $code; // Foydalanuvchiga qulay bo'lishi uchun xavfsizlik kodi ko'rsatiladi
            $forgot_success = 'Foydalanuvchi tasdiqlandi. Quyida yangi parolingizni o‘rnating!';
        }
    }
}

// 2-bosqich: Yangi parolni saqlash
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['act']) && $_POST['act'] === 'set_new_password') {
    $user_id = intval($_POST['user_id'] ?? 0);
    $entered_code = trim(filter($_POST['code'] ?? ''));
    $new_password = trim($_POST['new_password'] ?? '');
    $new_password_confirm = trim($_POST['new_password_confirm'] ?? '');

    $safe_code = mysqli_real_escape_string($mysqli, $entered_code);
    $user_q = $mysqli->query("SELECT id, reset_expiry FROM ero_members WHERE id = '$user_id' AND reset_token = '$safe_code' LIMIT 1");
    $u = $user_q ? $user_q->fetch_assoc() : null;

    if (!$u) {
        $step = 2;
        $reset_user_id = $user_id;
        $forgot_error = 'Xavfsizlik kodi noto‘g‘ri!';
    } elseif (time() > intval($u['reset_expiry'])) {
        $forgot_error = 'Ushbu kodning amal qilish muddati tugagan! Iltimos, qaytadan so‘rov yuboring.';
        $step = 1;
    } elseif (mb_strlen($new_password, 'UTF-8') < 6) {
        $step = 2;
        $reset_user_id = $user_id;
        $forgot_error = 'Yangi parol kamida 6 ta belgidan iborat bo‘lishi kerak!';
    } elseif ($new_password !== $new_password_confirm) {
        $step = 2;
        $reset_user_id = $user_id;
        $forgot_error = 'Kiritilgan parollar bir-biriga mos kelmadi!';
    } else {
        $new_hash = mysqli_real_escape_string($mysqli, password_hash($new_password, PASSWORD_DEFAULT));
        $mysqli->query("UPDATE ero_members SET password = '$new_hash', reset_token = '', reset_expiry = 0 WHERE id = '{$u['id']}'");

        header('Location: /login.html?reset_ok=1');
        exit;
    }
}

$title = ($lang['reset_password_title'] ?? 'Parolni Tiklash') . ' - ' . filter($_SERVER['HTTP_HOST'] ?? 'sekschi.online');
head();
?>

<div class="xxxhd-title-top">
    <h1><?=$lang['reset_password_title'] ?? 'Parolni Tiklash'?></h1>
</div>

<div class="site-form-minimal">
    <?php if (!empty($forgot_error)): ?>
        <div class="site-alert error">
            <i class="fa fa-exclamation-circle"></i> <?=$forgot_error?>
        </div>
    <?php endif; ?>

    <?php if (!empty($forgot_success)): ?>
        <div class="site-alert success">
            <i class="fa fa-check-circle"></i> <?=$forgot_success?>
        </div>
    <?php endif; ?>

    <?php if ($step === 1): ?>
        <!-- 1-Qadam: Foydalanuvchini topish -->
        <form action="/forgot-password.html" method="post">
            <input type="hidden" name="act" value="request_reset" />

            <div class="form-group-min">
                <label for="identity_input"><?=($lang['username_field'] ?? 'Login')?> / <?=($lang['email'] ?? 'Email')?>:</label>
                <input type="text" id="identity_input" name="identity" class="input-min" placeholder="<?=($lang['username_field'] ?? 'Login')?>" required autofocus />
            </div>

            <button type="submit" class="btn-min-submit" style="margin-top:10px;">
                <?=$lang['send'] ?? 'Davom etish'?>
            </button>
        </form>
    <?php else: ?>
        <!-- 2-Qadam: Yangi parolni kiritish -->
        <form action="/forgot-password.html" method="post">
            <input type="hidden" name="act" value="set_new_password" />
            <input type="hidden" name="user_id" value="<?=$reset_user_id?>" />

            <?php if (!empty($reset_code)): ?>
                <div style="background:#231713; border:1px solid #ff9900; padding:10px 12px; border-radius:3px; margin-bottom:14px; font-size:13px; color:#f0c080;">
                    <?=($lang['code'] ?? 'Kodingiz')?>: <b style="font-size:16px; color:#ff9900; letter-spacing:2px;"><?=$reset_code?></b>
                </div>
            <?php endif; ?>

            <div class="form-group-min">
                <label for="code_input"><?=($lang['code'] ?? 'Tasdiqlash kodi')?>:</label>
                <input type="text" id="code_input" name="code" class="input-min" placeholder="6 xonali kod" value="<?=$reset_code?>" required />
            </div>

            <div class="form-group-min">
                <label for="new_pass"><?=($lang['new_password'] ?? 'Yangi parol')?>:</label>
                <input type="password" id="new_pass" name="new_password" class="input-min" placeholder="Kamida 6 ta belgi" required />
            </div>

            <div class="form-group-min">
                <label for="new_pass_c"><?=($lang['repeat_new_password'] ?? 'Yangi parolni takrorlang')?>:</label>
                <input type="password" id="new_pass_c" name="new_password_confirm" class="input-min" placeholder="Parolni takrorlang" required />
            </div>

            <button type="submit" class="btn-min-submit" style="margin-top:10px;">
                <?=$lang['pass'] ?? 'Parolni saqlash'?>
            </button>
        </form>
    <?php endif; ?>

    <div class="form-min-footer">
        <a href="/login.html" class="link-min-accent">&larr; <?=$lang['back_to_login'] ?? 'Kirish sahifasiga qaytish'?></a>
    </div>
</div>

