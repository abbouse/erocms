<?php
/**
 * EroCMS Toifani O'chirish (Remove Category)
 */

if (!defined('ADMIN_LOADED') && !isset($user)) {
    header('Location: /control.html');
    exit;
}

if ($user['access'] < 1) {
    header('Location: /control.html'); 
    exit;
}

$id = abs(intval($_GET['id'] ?? 0));
$removeCat = $mysqli->query("SELECT * FROM ero_categories WHERE id = '$id'")->fetch_assoc();

if (!$removeCat) {
    echo '<div class="adm-alert adm-alert-danger"><i class="fa fa-exclamation-circle"></i> O‘chiriladigan toifa topilmadi!</div>';
    echo '<a href="/control.html?func=view_categories" class="adm-btn adm-btn-secondary">&larr; Toifalar ro‘yxatiga qaytish</a>';
    return;
}

// 1. Ushbu toifaga tegishli barcha videolarning media fayllarini tozalash
$query = $mysqli->query("SELECT id, screenshot, recoil FROM ero_files WHERE category = '{$removeCat['id']}'");
if ($query) {
    while ($row = $query->fetch_assoc()) {
        if (!empty($row['screenshot']) && file_exists($_SERVER['DOCUMENT_ROOT'] . $row['screenshot']) && strpos($row['screenshot'], 'no_poster') === false) {
            @unlink($_SERVER['DOCUMENT_ROOT'] . $row['screenshot']);
        }
        if (!empty($row['recoil']) && file_exists($_SERVER['DOCUMENT_ROOT'] . $row['recoil'])) {
            @unlink($_SERVER['DOCUMENT_ROOT'] . $row['recoil']);
        }
        $mysqli->query("DELETE FROM ero_favorites WHERE id_video = '{$row['id']}'");
        $mysqli->query("DELETE FROM ero_likes WHERE id_video = '{$row['id']}'");
        $mysqli->query("DELETE FROM ero_comments WHERE id_video = '{$row['id']}'");
    }
    $query->free();
}

// 2. Videolar va Toifani bazadan o'chirish
$mysqli->query("DELETE FROM ero_files WHERE category = '{$removeCat['id']}'");
$mysqli->query("DELETE FROM ero_categories WHERE id = '{$removeCat['id']}'");

logs($user['id'], "Toifa o‘chirildi: " . $removeCat['name'], 0);

// 3. HTML keshni tozalash
@array_map('unlink', glob($_SERVER['DOCUMENT_ROOT'] . '/content/cache/*.html'));

?>
<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title"><i class="fa fa-trash" style="color: #ef4444;"></i> Toifa O‘chirildi</h1>
        <p class="adm-page-subtitle">"<?=htmlspecialchars($removeCat['name'])?>" toifasi va unga tegishli barcha videolar muvaffaqiyatli o‘chirildi.</p>
    </div>
</div>

<div class="adm-alert adm-alert-success" style="margin-bottom: 20px;">
    <i class="fa fa-check-circle"></i> <b><?=htmlspecialchars($removeCat['name'])?></b> toifasi tizimdan butunlay olib tashlandi.
</div>

<a href="/control.html?func=view_categories" class="adm-btn adm-btn-primary">
    <i class="fa fa-arrow-left"></i> Toifalar ro‘yxatiga qaytish
</a>