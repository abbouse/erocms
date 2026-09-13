<?php
/**
 * EroCMS Yangi Kategoriya Qo'shish (Add Category)
 */

if (!defined('ADMIN_LOADED') && !isset($user)) {
    header('Location: /control.html');
    exit;
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
        $error = "Toifa nomi va translit (slug) maydonlari to‘ldirilishi shart.";
    } else {
        $mysqli->query("
            INSERT INTO ero_categories (name, description, meta, keywords, translit, view) 
            VALUES ('$name', '$description', '$meta', '$keywords', '$translit', 0)
        ");
        logs($user['id'], "Yangi toifa yaratildi: $name", 0);
        
        // Keshni tozalash
        @array_map('unlink', glob($_SERVER['DOCUMENT_ROOT'] . '/content/cache/*.html'));
        
        header('Location: /control.html?func=view_categories');
        exit;
    }
}
?>

<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title"><i class="fa fa-folder-open" style="color: #ff9900;"></i> Yangi Toifa Qo‘shish</h1>
        <p class="adm-page-subtitle">Saytga yangi bo‘lim va uning SEO ma’lumotlarini kiritish</p>
    </div>
    <a href="/control.html?func=view_categories" class="adm-btn adm-btn-secondary adm-btn-sm">&larr; Toifalar ro‘yxati</a>
</div>

<?php if ($error): ?>
    <div class="adm-alert adm-alert-danger"><i class="fa fa-exclamation-triangle"></i> <?=$error?></div>
<?php endif; ?>

<div class="adm-card" style="max-width:800px;">
    <form method="post">
        <div class="adm-form-group">
            <label class="adm-label">Toifa Nomi:</label>
            <input type="text" name="name" class="adm-input" placeholder="Masalan: O‘zbekcha Seks" required />
        </div>

        <div class="adm-form-group">
            <label class="adm-label">Translit / Slug (URL manzil):</label>
            <div style="display:flex; align-items:center; gap:6px;">
                <span style="color:#64748b; font-weight:bold;">/</span>
                <input type="text" name="translit" class="adm-input" placeholder="uzbek" required style="font-family:monospace;" />
                <span style="color:#64748b; font-weight:bold;">/</span>
            </div>
            <small style="color:#64748b; font-size:11px;">Faqat lotin harflari va defis (masalan: <code>uzbek-seks</code>)</small>
        </div>

        <div class="adm-form-group">
            <label class="adm-label">SEO Kalit So‘zlar (Keywords):</label>
            <textarea name="keywords" class="adm-textarea" rows="3" placeholder="uzbek sex, o'zbekcha porno, skachat..."></textarea>
            <small style="color:#64748b; font-size:11px;">Vergul bilan ajratilgan kalit so‘zlar</small>
        </div>

        <div class="adm-form-group">
            <label class="adm-label">Meta Tavsif (Description):</label>
            <textarea name="meta" class="adm-textarea" rows="3" placeholder="Qidiruv tizimlari (Google, Yandex) uchun meta description..."></textarea>
        </div>

        <div class="adm-form-group">
            <label class="adm-label">Toifa Sahifasi Matni (Description):</label>
            <textarea name="description" class="adm-textarea" rows="4" placeholder="Toifa sahifasida foydalanuvchilarga ko‘rinadigan kirish matni..."></textarea>
        </div>

        <button type="submit" name="save_cat" value="1" class="adm-btn adm-btn-primary" style="padding:10px 20px;">
            <i class="fa fa-plus"></i> Toifani Saqlash
        </button>
    </form>
</div>