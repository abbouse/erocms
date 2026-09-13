<?php
/**
 * EroCMS Toifani Tahrirlash (Edit Category)
 */

if (!defined('ADMIN_LOADED') && !isset($user)) {
    header('Location: /control.html');
    exit;
}

$cat_id = abs(intval($_GET['id'] ?? 0));
$editCat = $mysqli->query("SELECT * FROM ero_categories WHERE id = '$cat_id'")->fetch_assoc();

if (!$editCat) {
    echo '<div class="adm-alert adm-alert-danger"><i class="fa fa-exclamation-circle"></i> Toifa topilmadi!</div>';
    echo '<a href="/control.html?func=view_categories" class="adm-btn adm-btn-secondary">&larr; Toifalar ro‘yxatiga qaytish</a>';
    return;
}

$msg = null;
$error = null;

if (isset($_POST['save_cat'])) {
    $name = mysqli_real_escape_string($mysqli, filter($_POST['name'] ?? ''));
    $translit = mysqli_real_escape_string($mysqli, filter($_POST['translit'] ?? ''));
    $keywords = mysqli_real_escape_string($mysqli, filter($_POST['keywords'] ?? ''));
    $description = mysqli_real_escape_string($mysqli, filter($_POST['description'] ?? ''));
    $meta = mysqli_real_escape_string($mysqli, filter($_POST['meta'] ?? ''));

    if (empty($name) || empty($translit)) {
        $error = "Toifa nomi va translit (slug) bo‘sh bo‘lishi mumkin emas.";
    } else {
        $mysqli->query("
            UPDATE ero_categories SET 
                name = '$name', 
                description = '$description', 
                meta = '$meta', 
                keywords = '$keywords', 
                translit = '$translit' 
            WHERE id = '{$editCat['id']}'
        ");
        logs($user['id'], "Toifa tahrirlandi: $name", 0);
        
        // Keshni tozalash
        @array_map('unlink', glob($_SERVER['DOCUMENT_ROOT'] . '/content/cache/*.html'));
        
        $msg = "Toifa muvaffaqiyatli saqlandi!";
        $editCat = $mysqli->query("SELECT * FROM ero_categories WHERE id = '$cat_id'")->fetch_assoc();
    }
}
?>

<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title"><i class="fa fa-pencil-square-o" style="color: #ff9900;"></i> Toifani Tahrirlash: <?=htmlspecialchars($editCat['name'])?></h1>
        <p class="adm-page-subtitle">Toifa nomi, URL manzili va qidiruv tizimlari (SEO) kalit so‘zlarini tahrirlash</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="/<?=$editCat['translit']?>/" target="_blank" class="adm-btn adm-btn-secondary adm-btn-sm">
            <i class="fa fa-external-link"></i> Saytda ko‘rish
        </a>
        <a href="/control.html?func=view_categories" class="adm-btn adm-btn-secondary adm-btn-sm">&larr; Toifalar ro‘yxati</a>
    </div>
</div>

<?php if ($msg): ?>
    <div class="adm-alert adm-alert-success"><i class="fa fa-check-circle"></i> <?=$msg?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="adm-alert adm-alert-danger"><i class="fa fa-exclamation-triangle"></i> <?=$error?></div>
<?php endif; ?>

<div class="adm-card" style="max-width:800px;">
    <form method="post">
        <div class="adm-form-group">
            <label class="adm-label">Toifa Nomi:</label>
            <input type="text" name="name" class="adm-input" value="<?=htmlspecialchars($editCat['name'])?>" required />
        </div>

        <div class="adm-form-group">
            <label class="adm-label">Translit / Slug (URL manzil):</label>
            <div style="display:flex; align-items:center; gap:6px;">
                <span style="color:#64748b; font-weight:bold;">/</span>
                <input type="text" name="translit" class="adm-input" value="<?=htmlspecialchars($editCat['translit'])?>" required style="font-family:monospace;" />
                <span style="color:#64748b; font-weight:bold;">/</span>
            </div>
            <small style="color:#64748b; font-size:11px;">Sayt manzili: <code>/<?=htmlspecialchars($editCat['translit'])?>/</code></small>
        </div>

        <div class="adm-form-group">
            <label class="adm-label">SEO Kalit So‘zlar (Keywords):</label>
            <textarea name="keywords" class="adm-textarea" rows="3"><?=htmlspecialchars($editCat['keywords'])?></textarea>
            <small style="color:#64748b; font-size:11px;">Vergul bilan ajratilgan kalit so‘zlar</small>
        </div>

        <div class="adm-form-group">
            <label class="adm-label">Meta Tavsif (Description):</label>
            <textarea name="meta" class="adm-textarea" rows="3"><?=htmlspecialchars($editCat['meta'])?></textarea>
        </div>

        <div class="adm-form-group">
            <label class="adm-label">Toifa Sahifasi Matni (Description):</label>
            <textarea name="description" class="adm-textarea" rows="4"><?=htmlspecialchars($editCat['description'])?></textarea>
        </div>

        <button type="submit" name="save_cat" value="1" class="adm-btn adm-btn-primary" style="padding:10px 20px;">
            <i class="fa fa-save"></i> O‘zgarishlarni Saqlash
        </button>
    </form>
</div>