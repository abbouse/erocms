<?php
/**
 * EroCMS Videoni Tahrirlash (Video Edit & Rewriting)
 */

if (!defined('ADMIN_LOADED') && !isset($user)) {
    header('Location: /control.html');
    exit;
}

$target_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($target_id > 0) {
    $view = $mysqli->query("SELECT * FROM ero_files WHERE id = '$target_id'")->fetch_assoc();
} else {
    $view = $mysqli->query("SELECT * FROM ero_files WHERE rewriting = '0' ORDER BY id ASC LIMIT 1")->fetch_assoc();
}

if (!$view) {
    echo '<div class="adm-alert adm-alert-danger"><i class="fa fa-exclamation-circle"></i> Tahrirlash uchun video topilmadi!</div>';
    echo '<a href="/control.html?func=view_video" class="adm-btn adm-btn-secondary">&larr; Videolar ro‘yxatiga qaytish</a>';
    return;
}

$msg = null;
$error = null;

if (isset($_POST['save_video'])) {
    $name = mysqli_real_escape_string($mysqli, filter($_POST['name'] ?? ''));
    $tags = mysqli_real_escape_string($mysqli, filter($_POST['tags'] ?? ''));
    $description = mysqli_real_escape_string($mysqli, filter($_POST['description'] ?? ''));
    $category = (int)($_POST['category'] ?? $view['category']);

    if (empty($name)) {
        $error = "Video nomi bo‘sh bo‘lishi mumkin emas.";
    } else {
        $mysqli->query("
            UPDATE ero_files SET 
                category = '$category', 
                name = '$name', 
                tags = '$tags', 
                description = '$description', 
                rewriting = '1' 
            WHERE id = '{$view['id']}'
        ");
        
        logs($user['id'], "Video tahrirlandi: {$name}", $view['id']);
        
        // Keshni tozalash
        @array_map('unlink', glob($_SERVER['DOCUMENT_ROOT'] . '/content/cache/*.html'));
        
        $msg = "Video muvaffaqiyatli saqlandi!";
        $view = $mysqli->query("SELECT * FROM ero_files WHERE id = '{$view['id']}'")->fetch_assoc();
    }
}

$categories_q = $mysqli->query("SELECT id, name FROM ero_categories ORDER BY name ASC");
?>

<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title"><i class="fa fa-pencil-square-o" style="color: #ff9900;"></i> Videoni Tahrirlash (ID: #<?=$view['id']?>)</h1>
        <p class="adm-page-subtitle">Video nomi, bo‘limi, teglari va tavsifini o‘zgartirish</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="/watch/<?=$view['translit']?>.html" target="_blank" class="adm-btn adm-btn-secondary adm-btn-sm">
            <i class="fa fa-external-link"></i> Saytda ko‘rish
        </a>
        <a href="/control.html?func=view_video" class="adm-btn adm-btn-secondary adm-btn-sm">
            &larr; Videolar ro‘yxati
        </a>
    </div>
</div>

<?php if ($msg): ?>
    <div class="adm-alert adm-alert-success"><i class="fa fa-check-circle"></i> <?=$msg?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="adm-alert adm-alert-danger"><i class="fa fa-exclamation-triangle"></i> <?=$error?></div>
<?php endif; ?>

<div style="display:grid; grid-template-columns: 2fr 1fr; gap:20px;">
    <!-- Tahrirlash formasi -->
    <div class="adm-card">
        <form method="post">
            <div class="adm-form-group">
                <label class="adm-label">Video Sarlavhasi (Nomi):</label>
                <input type="text" name="name" class="adm-input" value="<?=htmlspecialchars($view['name'])?>" required />
            </div>

            <div class="adm-form-group">
                <label class="adm-label">Bo‘lim / Kategoriya:</label>
                <select name="category" class="adm-select">
                    <?php while ($cat = $categories_q->fetch_assoc()): ?>
                        <option value="<?=$cat['id']?>" <?=($cat['id'] == $view['category'] ? 'selected' : '')?>>
                            <?=htmlspecialchars($cat['name'])?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="adm-form-group">
                <label class="adm-label">Teglar (Kalit so‘zlar):</label>
                <textarea name="tags" class="adm-textarea" rows="3"><?=htmlspecialchars($view['tags'])?></textarea>
                <small style="color:#64748b; font-size:11px;">Vergul bilan ajratilgan teglarni yozing</small>
            </div>

            <div class="adm-form-group">
                <label class="adm-label">Video Tavsifi (Description):</label>
                <textarea name="description" class="adm-textarea" rows="5" id="videoDesc" onkeyup="document.getElementById('charCount').innerText = this.value.length;"><?=htmlspecialchars($view['description'])?></textarea>
                <small style="color:#64748b; font-size:11px;">Belgilar soni: <b id="charCount" style="color:#fff;"><?=mb_strlen($view['description'])?></b> ta</small>
            </div>

            <button type="submit" name="save_video" value="1" class="adm-btn adm-btn-primary" style="padding:10px 20px;">
                <i class="fa fa-save"></i> O‘zgarishlarni Saqlash
            </button>
        </form>
    </div>

    <!-- Video Preview Card -->
    <div class="adm-card">
        <div class="adm-card-header">
            <h3 class="adm-card-title"><i class="fa fa-eye"></i> Video Ko‘rinishi</h3>
        </div>

        <div style="position:relative; margin-bottom:14px; border-radius:6px; overflow:hidden; border:1px solid #333;">
            <img src="<?=$view['screenshot']?>" style="width:100%; height:auto; display:block;" onerror="this.src='/designs/no_poster.jpg';" />
            <?php if (!empty($view['duration'])): ?>
                <span style="position:absolute; bottom:6px; right:6px; background:rgba(0,0,0,0.85); color:#fff; font-size:11px; font-weight:bold; padding:2px 6px; border-radius:3px;">
                    <?=$view['duration']?>
                </span>
            <?php endif; ?>
        </div>

        <div style="font-size:13px; color:#cbd5e1; line-height:1.6;">
            <div><b>Translit (Slug):</b> <code><?=$view['translit']?></code></div>
            <div><b>Ko‘rishlar:</b> <?=number_format($view['view'])?> ta</div>
            <div><b>Yuklab olishlar:</b> <?=number_format($view['downloads'])?> ta</div>
            <div><b>Yuklangan sana:</b> <?=date('d.m.Y H:i', $view['date'])?></div>
            <?php if (!empty($view['server'])): ?>
                <div><b>Donor server:</b> <?=htmlspecialchars($view['server'])?></div>
            <?php endif; ?>
        </div>

        <div style="margin-top:20px; padding-top:14px; border-top:1px solid #282c37;">
            <a href="/control.html?func=view_video&action=delete&id=<?=$view['id']?>" class="adm-btn adm-btn-danger adm-btn-sm" onclick="return confirm('Haqiqatan ham ushbu videoni o‘chirmoqchimisiz?');" style="width:100%;">
                <i class="fa fa-trash"></i> Ushbu videoni o‘chirish
            </a>
        </div>
    </div>
</div>