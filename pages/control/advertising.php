<?php
/**
 * EroCMS Faqat Admin Boshqaradigan Reklama Tizimi (Advertising Management)
 */

if (!defined('ADMIN_LOADED') && !isset($user)) {
    header('Location: /control.html');
    exit;
}

$msg = null;
$error = null;

// 1. Reklamani o'chirish
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $del_id = (int)$_GET['id'];
    $mysqli->query("DELETE FROM ero_advertising WHERE id = '$del_id'");
    @array_map('unlink', glob($_SERVER['DOCUMENT_ROOT'] . '/content/cache/*.html'));
    $msg = "Reklama muvaffaqiyatli o‘chirildi.";
    logs($user['id'], "Reklama o‘chirildi #$del_id", 0);
}

// 2. Muddatini 30 kunga uzaytirish
if (isset($_GET['action']) && $_GET['action'] === 'extend' && isset($_GET['id'])) {
    $ext_id = (int)$_GET['id'];
    $ad_row = $mysqli->query("SELECT * FROM ero_advertising WHERE id = '$ext_id'")->fetch_assoc();
    if ($ad_row) {
        $current_term = max(time(), (int)$ad_row['term']);
        $new_term = $current_term + (86400 * 30);
        $mysqli->query("UPDATE ero_advertising SET term = '$new_term' WHERE id = '$ext_id'");
        @array_map('unlink', glob($_SERVER['DOCUMENT_ROOT'] . '/content/cache/*.html'));
        $msg = "Reklama muddati 30 kunga uzaytirildi.";
    }
}

// 3. Yangi reklama qo'shish
if (isset($_POST['add_ad'])) {
    $name = mysqli_real_escape_string($mysqli, filter($_POST['name'] ?? ''));
    $site = mysqli_real_escape_string($mysqli, filter($_POST['site'] ?? ''));
    $colour = mysqli_real_escape_string($mysqli, filter($_POST['colour'] ?? '#ff9900'));
    $duration_days = (int)($_POST['duration_days'] ?? 30);
    $position = mysqli_real_escape_string($mysqli, filter($_POST['position'] ?? 'all'));

    if (empty($name) || empty($site)) {
        $error = "Reklama nomi va o‘tish havolasi (URL) to‘ldirilishi shart.";
    } else {
        $term = ($duration_days === 0) ? 0 : (time() + ($duration_days * 86400));
        $mysqli->query("
            INSERT INTO ero_advertising (name, site, colour, term, owner, position, clicks) 
            VALUES ('$name', '$site', '$colour', '$term', 'admin', '$position', 0)
        ");
        
        // Keshni tozalash
        @array_map('unlink', glob($_SERVER['DOCUMENT_ROOT'] . '/content/cache/*.html'));
        
        $msg = "Yangi reklama muvaffaqiyatli qo‘shildi va saytda faollashtirildi!";
        logs($user['id'], "Yangi reklama qo‘shildi: $name", 0);
    }
}

$now = time();
$ads_query = $mysqli->query("SELECT * FROM ero_advertising ORDER BY id DESC");
$total_ads = $ads_query ? $ads_query->num_rows : 0;
?>

<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title"><i class="fa fa-bullhorn" style="color: #ff9900;"></i> Reklama Boshqaruvi</h1>
        <p class="adm-page-subtitle">Saytda chiqadigan reklama bannerlari va havolalarini qo‘shish, rangini tanlash va muddatini boshqarish (faqat admin uchun)</p>
    </div>
</div>

<?php if ($msg): ?>
    <div class="adm-alert adm-alert-success"><i class="fa fa-check-circle"></i> <?=$msg?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="adm-alert adm-alert-danger"><i class="fa fa-exclamation-triangle"></i> <?=$error?></div>
<?php endif; ?>

<div style="display:grid; grid-template-columns: 2fr 1fr; gap:20px;">
    <!-- Mavjud Reklamalar Jadvali -->
    <div class="adm-card">
        <div class="adm-card-header">
            <h3 class="adm-card-title"><i class="fa fa-list"></i> Mavjud Reklamalar (<?=$total_ads?> ta)</h3>
        </div>

        <div class="adm-table-wrap">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th width="40">ID</th>
                        <th>Reklama Nomi va Ko‘rinishi</th>
                        <th>Havola (URL)</th>
                        <th>Muddati</th>
                        <th width="120" style="text-align:right;">Amallar</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($ads_query && $ads_query->num_rows > 0): ?>
                    <?php while ($ad = $ads_query->fetch_assoc()): ?>
                        <?php
                            $is_infinite = ($ad['term'] == 0);
                            $is_expired = (!$is_infinite && $ad['term'] < $now);
                            $days_left = !$is_infinite ? ceil(($ad['term'] - $now) / 86400) : 0;
                            $col = !empty($ad['colour']) ? $ad['colour'] : '#ff9900';
                        ?>
                        <tr>
                            <td style="color:#64748b; font-weight:700;">#<?=$ad['id']?></td>
                            <td>
                                <a href="<?=$ad['site']?>" target="_blank" rel="noopener nofollow" style="display:inline-flex; align-items:center; gap:6px; text-decoration:none; padding:4px 8px; border-radius:4px; font-weight:bold; font-size:12px; color:<?=$col?>; background:#111318; border:1px solid rgba(255,255,255,0.08);">
                                    <i class="fa fa-bullhorn" style="color:<?=$col?>;"></i>
                                    <span><?=htmlspecialchars($ad['name'])?></span>
                                </a>
                            </td>
                            <td>
                                <a href="<?=$ad['site']?>" target="_blank" style="color:#60a5fa; font-size:12px; text-decoration:none; max-width:180px; overflow:hidden; text-overflow:ellipsis; display:inline-block; white-space:nowrap;">
                                    <?=htmlspecialchars($ad['site'])?> <i class="fa fa-external-link" style="font-size:10px;"></i>
                                </a>
                            </td>
                            <td>
                                <?php if ($is_infinite): ?>
                                    <span class="adm-badge adm-badge-success">Cheksiz / Doimiy</span>
                                <?php elseif ($is_expired): ?>
                                    <span class="adm-badge adm-badge-danger">Muddati tugagan</span>
                                <?php else: ?>
                                    <span class="adm-badge adm-badge-warning"><?=$days_left?> kun qoldi</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:right; white-space:nowrap;">
                                <?php if (!$is_infinite): ?>
                                    <a href="/control.html?func=advertising&action=extend&id=<?=$ad['id']?>" class="adm-btn adm-btn-secondary adm-btn-sm" title="Muddatini +30 kunga uzaytirish">
                                        <i class="fa fa-calendar-plus-o"></i> +30 kun
                                    </a>
                                <?php endif; ?>
                                <a href="/control.html?func=advertising&action=delete&id=<?=$ad['id']?>" class="adm-btn adm-btn-danger adm-btn-sm" onclick="return confirm('Ushbu reklamani o‘chirmoqchimisiz?');" title="O‘chirish">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding:30px; color:#64748b;">
                            <i class="fa fa-bullhorn" style="font-size:32px; display:block; margin-bottom:10px; color:#383e50;"></i>
                            Hozircha saytda reklama qo‘yilmagan.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Yangi Reklama Qo'shish -->
    <div class="adm-card">
        <div class="adm-card-header">
            <h3 class="adm-card-title"><i class="fa fa-plus-circle" style="color: #ff9900;"></i> Yangi Reklama Qo‘shish</h3>
        </div>

        <form method="post">
            <div class="adm-form-group">
                <label class="adm-label">Reklama Matni / Nomi:</label>
                <input type="text" name="name" class="adm-input" placeholder="Masalan: 🔥 Rasmiy Telegram Kanalimizga Ulaning!" required />
            </div>

            <div class="adm-form-group">
                <label class="adm-label">O‘tish Havolasi (URL):</label>
                <input type="url" name="site" class="adm-input" placeholder="https://t.me/sekschi_online" required />
            </div>

            <div class="adm-form-group">
                <label class="adm-label">Matn Rangi:</label>
                <div style="display:flex; align-items:center; gap:8px;">
                    <input type="color" name="colour" value="#ff9900" id="colorPicker" style="width:40px; height:36px; border:none; border-radius:4px; cursor:pointer; background:transparent;" onchange="document.getElementById('colorInput').value = this.value;" />
                    <input type="text" id="colorInput" name="colour" class="adm-input" value="#ff9900" style="width:120px;" onkeyup="document.getElementById('colorPicker').value = this.value;" />
                </div>
            </div>

            <div class="adm-form-group">
                <label class="adm-label">Amal Qilish Muddati:</label>
                <select name="duration_days" class="adm-select">
                    <option value="7">7 kun (1 hafta)</option>
                    <option value="14">14 kun (2 hafta)</option>
                    <option value="30" selected>30 kun (1 oy)</option>
                    <option value="90">90 kun (3 oy)</option>
                    <option value="365">365 kun (1 yil)</option>
                    <option value="0">Doimiy (Cheksiz muddat)</option>
                </select>
            </div>

            <button type="submit" name="add_ad" value="1" class="adm-btn adm-btn-primary" style="width:100%; margin-top:10px; padding:11px;">
                <i class="fa fa-plus"></i> Reklamani Joylashtirish
            </button>
        </form>
    </div>
</div>