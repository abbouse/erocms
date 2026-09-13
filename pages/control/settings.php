<?php
/**
 * EroCMS Sayt Sozlamalari (Settings)
 */

if (!defined('ADMIN_LOADED') && !isset($user)) {
    header('Location: /control.html');
    exit;
}

$msg = null;
$warning = null;

if (isset($_POST['save_settings'])) {
    $recoil = (int)($_POST['recoil'] ?? 0);
    $title = mysqli_real_escape_string($mysqli, filter($_POST['title'] ?? ''));
    $designs = mysqli_real_escape_string($mysqli, filter($_POST['designs'] ?? 'red'));
    $description = mysqli_real_escape_string($mysqli, filter($_POST['description'] ?? ''));
    $keywords = mysqli_real_escape_string($mysqli, filter($_POST['keywords'] ?? ''));
    $counter = mysqli_real_escape_string($mysqli, $_POST['counter'] ?? '');
    $player = (int)($_POST['player'] ?? 0);
    $water = (int)($_POST['water'] ?? 1);
    $cache = (int)($_POST['cache'] ?? 300);
    $disclosed = mysqli_real_escape_string($mysqli, filter($_POST['disclosed'] ?? ''));
    $alert = mysqli_real_escape_string($mysqli, filter($_POST['alert'] ?? ''));
    $cron = mysqli_real_escape_string($mysqli, filter($_POST['cron'] ?? ''));

    if (empty($title)) {
        $warning = "Sayt sarlavhasi (Title) bo‘sh bo‘lishi mumkin emas.";
    } elseif (!empty($disclosed) && (strlen($disclosed) < 4 || strlen($disclosed) > 32)) {
        $warning = "Admin paroli 4 tadan 32 tagacha belgidan iborat bo‘lishi kerak.";
    }

    if (!$warning) {
        if (!empty($disclosed) && $user['disclosed'] !== $disclosed) {
            $new_hash = md5(md5($disclosed));
            $mysqli->query("UPDATE ero_users SET disclosed = '$disclosed', password = '$new_hash' WHERE id = '{$user['id']}'");
            $_SESSION['password'] = $new_hash;
            setcookie('password', $new_hash, time() + (86400 * 30), '/');
        }

        $mysqli->query("
            UPDATE ero_settings SET 
                water = '$water', 
                cron = '$cron', 
                recoil = '$recoil', 
                designs = '$designs', 
                cache = '$cache', 
                alert = '$alert', 
                player = '$player', 
                title = '$title', 
                description = '$description', 
                keywords = '$keywords', 
                counter = '$counter' 
            WHERE id = '1'
        ");

        @array_map('unlink', glob($_SERVER['DOCUMENT_ROOT'] . '/content/cache/*.html'));

        logs($user['id'], 'Sayt sozlamalari yangilandi', 0);
        $msg = "Barcha sozlamalar muvaffaqiyatli saqlandi!";

        // Yangilangan ma'lumotlarni qayta o'qish
        $settings = $mysqli->query("SELECT * FROM ero_settings WHERE id = '1'")->fetch_assoc();
        $user = $mysqli->query("SELECT * FROM ero_users WHERE id = '{$user['id']}'")->fetch_assoc();
    }
}
?>

<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title"><i class="fa fa-cogs" style="color: #ff9900;"></i> Sayt Sozlamalari</h1>
        <p class="adm-page-subtitle">SEO parametrlari, dizayn mavzulari, pleer va xavfsizlik sozlamalari</p>
    </div>
</div>

<?php if ($msg): ?>
    <div class="adm-alert adm-alert-success"><i class="fa fa-check-circle"></i> <?=$msg?></div>
<?php endif; ?>

<?php if ($warning): ?>
    <div class="adm-alert adm-alert-danger"><i class="fa fa-exclamation-triangle"></i> <?=$warning?></div>
<?php endif; ?>

<form method="post">
    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
        <!-- 1. Asosiy SEO & Meta ma'lumotlari -->
        <div class="adm-card">
            <div class="adm-card-header">
                <h3 class="adm-card-title"><i class="fa fa-globe" style="color:#ff9900;"></i> Asosiy SEO va Sayt Ma’lumotlari</h3>
            </div>

            <div class="adm-form-group">
                <label class="adm-label">Sayt Sarlavhasi (Title):</label>
                <input type="text" name="title" class="adm-input" value="<?=htmlspecialchars($settings['title'])?>" required />
                <small style="color:#64748b; font-size:11px;">Brauzer tabida va qidiruv tizimlarida chiqadigan asosiy sarlavha</small>
            </div>

            <div class="adm-form-group">
                <label class="adm-label">Meta Tavsif (Description):</label>
                <textarea name="description" class="adm-textarea" rows="3"><?=htmlspecialchars($settings['description'])?></textarea>
                <small style="color:#64748b; font-size:11px;">Google va Yandex da sayt ostida chiqadigan qisqacha tavsif (120-250 belgi)</small>
            </div>

            <div class="adm-form-group">
                <label class="adm-label">SEO Kalit So‘zlar (Keywords):</label>
                <textarea name="keywords" class="adm-textarea" rows="3"><?=htmlspecialchars($settings['keywords'])?></textarea>
                <small style="color:#64748b; font-size:11px;">Vergul bilan ajratilgan kalit so‘zlar</small>
            </div>
        </div>

        <!-- 2. Tashqi Ko'rinish & Pleer -->
        <div class="adm-card">
            <div class="adm-card-header">
                <h3 class="adm-card-title"><i class="fa fa-paint-brush" style="color:#ff9900;"></i> Tashqi Ko‘rinish va Pleer</h3>
            </div>

            <div class="adm-form-group">
                <label class="adm-label">Sayt Dizayni (Mavzu):</label>
                <select name="designs" class="adm-select">
                    <option value="red" <?=($settings['designs'] === 'red' ? 'selected' : '')?>>Qizil / Red (Asosiy)</option>
                    <option value="pink" <?=($settings['designs'] === 'pink' ? 'selected' : '')?>>Pushti / Pink</option>
                    <option value="violet" <?=($settings['designs'] === 'violet' ? 'selected' : '')?>>Binafsharang / Violet</option>
                </select>
            </div>

            <div class="adm-form-group">
                <label class="adm-label">Video Pleyeri:</label>
                <select name="player" class="adm-select">
                    <option value="0" <?=($settings['player'] == 0 ? 'selected' : '')?>>HTML5 Native Player (Tavsiya etiladi)</option>
                    <option value="2" <?=($settings['player'] == 2 ? 'selected' : '')?>>PlayerJS</option>
                    <option value="1" <?=($settings['player'] == 1 ? 'selected' : '')?>>Uppod Player</option>
                </select>
            </div>

            <div class="adm-form-group">
                <label class="adm-label">Rasmlarga Suv Belgisi (Watermark):</label>
                <div style="display:flex; gap:20px; padding:8px 0;">
                    <label style="color:#e2e8f0; cursor:pointer;">
                        <input type="radio" name="water" value="1" <?=($settings['water'] == '1' ? 'checked' : '')?>>
                        <span style="margin-left:4px;">Yoqilgan (Tavsiya etiladi)</span>
                    </label>
                    <label style="color:#e2e8f0; cursor:pointer;">
                        <input type="radio" name="water" value="0" <?=($settings['water'] == '0' ? 'checked' : '')?>>
                        <span style="margin-left:4px;">O‘chirilgan</span>
                    </label>
                </div>
                <small style="color:#64748b; font-size:11px;">Suv belgisi fayli: <code>/designs/water.png</code></small>
            </div>

            <div class="adm-form-group">
                <label class="adm-label">HTML Kesh Vaqti (soniyalarda):</label>
                <input type="number" name="cache" class="adm-input" value="<?=htmlspecialchars($settings['cache'])?>" />
                <small style="color:#64748b; font-size:11px;">0 = kesh o‘chirilgan, 300 = 5 daqiqa</small>
            </div>
        </div>

        <!-- 3. Xavfsizlik & Kirish -->
        <div class="adm-card">
            <div class="adm-card-header">
                <h3 class="adm-card-title"><i class="fa fa-lock" style="color:#ff9900;"></i> Xavfsizlik va Admin Paroli</h3>
            </div>

            <div class="adm-form-group">
                <label class="adm-label">Admin Boshqaruv Paroli:</label>
                <input type="text" name="disclosed" class="adm-input" value="<?=htmlspecialchars($user['disclosed'] ?? '')?>" required />
                <small style="color:#64748b; font-size:11px;">Admin panelga kirish paroli. O‘zgartirsangiz yangi parol kuchga kiradi.</small>
            </div>

            <div class="adm-form-group">
                <label class="adm-label">E-pochta (Xabarnomalar uchun):</label>
                <input type="text" name="alert" class="adm-input" value="<?=htmlspecialchars($settings['alert'])?>" placeholder="admin@sekschi.online" />
                <small style="color:#64748b; font-size:11px;">Tizim xabarlari uchun administrator emaili</small>
            </div>

            <div class="adm-form-group">
                <label class="adm-label">Avtomatik Fon Kaliti (Cron Key):</label>
                <input type="text" name="cron" class="adm-input" value="<?=htmlspecialchars($settings['cron'])?>" />
                <small style="color:#64748b; font-size:11px;">Fon skripti (cron): <code>/autocomplete.php?key=<?=htmlspecialchars($settings['cron'])?></code></small>
            </div>
        </div>

        <!-- 4. Hisoblagichlar & Analitika -->
        <div class="adm-card">
            <div class="adm-card-header">
                <h3 class="adm-card-title"><i class="fa fa-line-chart" style="color:#ff9900;"></i> Analitika va Hisoblagichlar Kodi</h3>
            </div>

            <div class="adm-form-group">
                <label class="adm-label">Hisoblagich / Metrika HTML Kodi:</label>
                <textarea name="counter" class="adm-textarea" rows="6" placeholder="Google Analytics, Yandex Metrika yoki LiveInternet hisoblagich kodi..."><?=htmlspecialchars($settings['counter'])?></textarea>
                <small style="color:#64748b; font-size:11px;">Sayt footer qismida avtomatik aks etadigan hisoblagich teglari</small>
            </div>

            <div style="margin-top:24px;">
                <button type="submit" name="save_settings" value="1" class="adm-btn adm-btn-primary" style="width:100%; padding:12px; font-size:15px;">
                    <i class="fa fa-save"></i> Barcha Sozlamalarni Saqlash
                </button>
            </div>
        </div>
    </div>
</form>