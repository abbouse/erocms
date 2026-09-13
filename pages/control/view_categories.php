<?php
/**
 * EroCMS Kategoriyalar va SEO Boshqaruvi
 */

if (!defined('ADMIN_LOADED') && !isset($user)) {
    header('Location: /control.html');
    exit;
}

$seo_msg = '';

if (isset($_GET['update_seo']) && function_exists('seo_update_all_categories')) {
    $count = seo_update_all_categories($mysqli);
    $seo_msg = '<div class="adm-alert adm-alert-success"><i class="fa fa-check-circle"></i> Muvaffaqiyatli: Jami <b>'.$count.' ta</b> kategoriya uchun O‘zbek va Rus tillaridagi SEO meta tavsiflar va kalit so‘zlar bazaga kiritildi va kesh tozalandi!</div>';
}

$query = $mysqli->query("SELECT id, name, translit, view, keywords, meta FROM ero_categories ORDER BY id ASC");
$total_cats = $query ? $query->num_rows : 0;
?>

<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title"><i class="fa fa-folder-open" style="color: #ff9900;"></i> Bo‘limlar & SEO Kalit So‘zlar</h1>
        <p class="adm-page-subtitle">Jami <b><?=$total_cats?> ta</b> toifa mavjud. Qidiruv tizimlari (Google, Yandex) uchun metateglar boshqaruvi.</p>
    </div>
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
        <a href="?func=view_categories&update_seo=1" onclick="return confirm('Barcha toifalarning SEO kalit so‘zlarini avtomatik yangilashni xohlaysizmi?');" class="adm-btn adm-btn-success adm-btn-sm">
            <i class="fa fa-rocket"></i> Barcha toifalarga SEO kalit so‘zlarni kiritish
        </a>
        <a href="?func=addCat" class="adm-btn adm-btn-primary adm-btn-sm">
            <i class="fa fa-plus"></i> Yangi toifa qo‘shish
        </a>
    </div>
</div>

<?=$seo_msg?>

<div class="adm-card">
    <div class="adm-card-header">
        <h3 class="adm-card-title"><i class="fa fa-list"></i> Barcha Toifalar Ro‘yxati</h3>
    </div>
    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead>
                <tr>
                    <th width="40">ID</th>
                    <th>Toifa Nomi va Slug</th>
                    <th>SEO Kalit So‘zlar (Keywords)</th>
                    <th width="80" style="text-align:center;">Videolar</th>
                    <th width="80" style="text-align:center;">Ko‘rishlar</th>
                    <th width="120" style="text-align:right;">Amallar</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($query && $query->num_rows > 0): ?>
                <?php while($row = $query->fetch_assoc()): ?>
                    <?php
                        $quantity = $mysqli->query("SELECT COUNT(*) FROM ero_files WHERE category = '{$row['id']}'")->fetch_row()[0] ?? 0;
                        $keys_snippet = !empty($row['keywords']) ? htmlspecialchars(mb_substr($row['keywords'], 0, 75) . '...') : '<span style="color:#64748b; font-style:italic;">Kiritilmagan</span>';
                    ?>
                    <tr>
                        <td style="color:#64748b; font-weight:700;">#<?=$row['id']?></td>
                        <td>
                            <a href="/<?=$row['translit']?>/" target="_blank" style="color:#ff9900; font-weight:700; text-decoration:none; font-size:14px;">
                                <?=$row['name']?> <i class="fa fa-external-link" style="font-size:11px;"></i>
                            </a>
                            <div style="font-size:11px; color:#64748b; margin-top:2px;">
                                Slug: <code>/<?=$row['translit']?>/</code>
                            </div>
                        </td>
                        <td style="color:#cbd5e1; font-size:12px; line-height:1.4;">
                            <?=$keys_snippet?>
                        </td>
                        <td style="text-align:center;">
                            <span class="adm-badge adm-badge-warning" style="font-size:12px;">
                                <?=$quantity?> ta
                            </span>
                        </td>
                        <td style="text-align:center; color:#94a3b8; font-size:12px;">
                            <?=number_format($row['view'])?>
                        </td>
                        <td style="text-align:right; white-space:nowrap;">
                            <a href="?func=editCat&id=<?=$row['id']?>" class="adm-btn adm-btn-secondary adm-btn-sm" title="Tahrirlash">
                                <i class="fa fa-pencil"></i>
                            </a>
                            <a href="?func=removeCat&id=<?=$row['id']?>" onclick="return confirm('Rostdan ham ushbu toifani o‘chirmoqchimisiz?');" class="adm-btn adm-btn-danger adm-btn-sm" title="O‘chirish">
                                <i class="fa fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding:30px; color:#64748b;">
                        Kategoriyalar mavjud emas.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$query->free();