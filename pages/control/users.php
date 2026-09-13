<?php
/**
 * EroCMS Admin Foydalanuvchilari (Admin Users)
 */

if (!defined('ADMIN_LOADED') && !isset($user)) {
    header('Location: /control.html');
    exit;
}

$msg = null;
$error = null;

if (isset($_POST['add_user']) && !empty($_POST['new_password'])) {
    $new_pass = trim($_POST['new_password']);
    if (strlen($new_pass) < 4 || strlen($new_pass) > 32) {
        $error = "Parol 4 tadan 32 tagacha belgidan iborat bo‘lishi kerak.";
    } else {
        $safe_pass = mysqli_real_escape_string($mysqli, $new_pass);
        $hash = md5(md5($new_pass));
        $mysqli->query("INSERT INTO ero_users (password, disclosed, information, access) VALUES ('$hash', '$safe_pass', 'Yangi admin yaratildi', 1)");
        logs($user['id'], "Yangi admin foydalanuvchi qo‘shildi: $safe_pass", 0);
        $msg = "Yangi admin foydalanuvchi muvaffaqiyatli qo‘shildi!";
    }
}

if (isset($_GET['deletion'])) {
    $del_id = (int)$_GET['deletion'];
    if ($user['id'] != $del_id) {
        $mysqli->query("DELETE FROM ero_users WHERE id = '$del_id'");
        logs($user['id'], "Admin foydalanuvchi o‘chirildi: #$del_id", 0);
        $msg = "Admin foydalanuvchi o‘chirildi.";
    } else {
        $error = "Siz hozir tizimga kirib turgan o‘z hisobingizni o‘chira olmaysiz!";
    }
}

$users_q = $mysqli->query("SELECT * FROM ero_users ORDER BY id DESC");
$total_users = $users_q ? $users_q->num_rows : 0;
?>

<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title"><i class="fa fa-users" style="color: #ff9900;"></i> Admin Foydalanuvchilari</h1>
        <p class="adm-page-subtitle">Admin panelga kirish huquqiga ega bo‘lgan hisoblar boshqaruvi</p>
    </div>
</div>

<?php if ($msg): ?>
    <div class="adm-alert adm-alert-success"><i class="fa fa-check-circle"></i> <?=$msg?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="adm-alert adm-alert-danger"><i class="fa fa-exclamation-triangle"></i> <?=$error?></div>
<?php endif; ?>

<div style="display:grid; grid-template-columns: 2fr 1fr; gap:20px;">
    <!-- Foydalanuvchilar Jadvali -->
    <div class="adm-card">
        <div class="adm-card-header">
            <h3 class="adm-card-title"><i class="fa fa-list"></i> Adminlar Ro‘yxati (<?=$total_users?> ta)</h3>
        </div>

        <div class="adm-table-wrap">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th width="40">ID</th>
                        <th>Parol (Disclosed)</th>
                        <th>Oxirgi Kirish / Ma’lumot</th>
                        <th width="100" style="text-align:right;">Amallar</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($u = $users_q->fetch_assoc()): ?>
                    <?php $is_me = ($u['id'] == $user['id']); ?>
                    <tr>
                        <td style="color:#64748b; font-weight:700;">#<?=$u['id']?></td>
                        <td>
                            <span style="font-family:monospace; font-size:14px; font-weight:bold; color:#fff;">
                                <?=htmlspecialchars($u['disclosed'])?>
                            </span>
                            <?php if ($is_me): ?>
                                <span class="adm-badge adm-badge-warning" style="margin-left:6px;">Siz</span>
                            <?php endif; ?>
                        </td>
                        <td style="color:#94a3b8; font-size:12px;">
                            <?=htmlspecialchars($u['information'] ?? 'Ma’lumot yo‘q')?>
                        </td>
                        <td style="text-align:right; white-space:nowrap;">
                            <a href="/control.html?func=logs&id=<?=$u['id']?>" class="adm-btn adm-btn-secondary adm-btn-sm" title="Loglarni ko‘rish">
                                <i class="fa fa-history"></i>
                            </a>
                            <?php if (!$is_me): ?>
                                <a href="/control.html?func=users&deletion=<?=$u['id']?>" class="adm-btn adm-btn-danger adm-btn-sm" onclick="return confirm('Ushbu admin hisobini o‘chirmoqchimisiz?');" title="O‘chirish">
                                    <i class="fa fa-trash"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Yangi Admin Qo'shish -->
    <div class="adm-card">
        <div class="adm-card-header">
            <h3 class="adm-card-title"><i class="fa fa-user-plus" style="color: #ff9900;"></i> Yangi Admin Qo‘shish</h3>
        </div>

        <form method="post">
            <div class="adm-form-group">
                <label class="adm-label">Yangi Admin Paroli:</label>
                <input type="text" name="new_password" class="adm-input" placeholder="Parolni kiriting..." required />
                <small style="color:#64748b; font-size:11px;">Masalan: <code>Admin2026!</code></small>
            </div>

            <button type="submit" name="add_user" value="1" class="adm-btn adm-btn-primary" style="width:100%; margin-top:10px;">
                <i class="fa fa-plus"></i> Adminni Qo‘shish
            </button>
        </form>
    </div>
</div>