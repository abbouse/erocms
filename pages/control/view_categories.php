<?php

/*
 * Kategoriyalar va SEO Boshqaruvi
 * erocms
 */

if ($user['access'] < 1) {
    header('location: /'); 
    exit;
}

$seo_msg = '';

if (isset($_GET['update_seo']) && function_exists('seo_update_all_categories')) {
    $count = seo_update_all_categories($mysqli);
    $seo_msg = '<div class="functions_data" style="background:#15291b; border:1px solid #28a745; color:#75e691; padding:14px; margin-bottom:20px; border-radius:4px; font-size:14px;"><i class="fa fa-check-circle" style="font-size:18px;"></i> Muvaffaqiyatli: Jami <b>'.$count.' ta</b> kategoriya uchun O‘zbek va Rus tillaridagi SEO meta tavsiflar va kalit so‘zlar bazaga kiritildi va kesh tozalandi!</div>';
}

$query = $mysqli->query("SELECT id, name, translit, view, keywords, meta FROM ero_categories ORDER BY id ASC");
$total_cats = $query ? $query->num_rows : 0;
?>

<div class="functions_data" style="border-left: 4px solid #ff9900; background: #1a1815; padding: 16px; margin-bottom: 20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
        <div>
            <h2 style="color:#ff9900; margin: 0 0 6px 0;"><i class="fa fa-th-large"></i> Bo‘limlar va SEO Boshqaruvi (<?=$total_cats?> ta)</h2>
            <p style="color:#ccc; font-size:13px; line-height: 1.5; margin: 0;">
                Barcha kategoriyalarni ko‘rish, tahrirlash hamda Google va Yandex qidiruv tizimlari uchun SEO kalit so‘zlarini boshqarish.
            </p>
        </div>
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <a href="?func=view_categories&update_seo=1" onclick="return confirm('Barcha 27 ta kategoriyaning SEO kalit so‘zlarini avtomatik yangilashni xohlaysizmi?');" style="background:#28a745; color:#fff; font-weight:bold; text-decoration:none; padding:8px 14px; font-size:13px; border-radius:4px; display:inline-flex; align-items:center; gap:6px;">
                <i class="fa fa-rocket"></i> Barcha toifalarga SEO kalit so‘zlarni kiritish
            </a>
            <a href="?func=addCat" style="background:#ff9900; color:#000; font-weight:bold; text-decoration:none; padding:8px 14px; font-size:13px; border-radius:4px; display:inline-flex; align-items:center; gap:6px;">
                <i class="fa fa-plus"></i> Yangi toifa qo‘shish
            </a>
        </div>
    </div>
</div>

<?=$seo_msg?>

<div class="functions_data" style="padding: 10px; background:#181615;">
    <table width="100%" cellpadding="10" cellspacing="0" style="border-collapse:collapse; font-size:13px; color:#ddd;">
        <thead>
            <tr style="background:#111; color:#ff9900; text-align:left; border-bottom:2px solid #333;">
                <th style="width:40px;">ID</th>
                <th>Toifa nomi va Translit</th>
                <th>SEO Kalit so‘zlar (Keywords)</th>
                <th style="width:80px; text-align:center;">Videolar</th>
                <th style="width:80px; text-align:center;">Ko‘rishlar</th>
                <th style="width:140px; text-align:right;">Amallar</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($query && $query->num_rows > 0): ?>
            <?php while($row = $query->fetch_assoc()): ?>
                <?php
                    $quantity = $mysqli->query("SELECT COUNT(*) FROM ero_files WHERE category = '{$row['id']}'")->fetch_row()[0] ?? 0;
                    $keys_snippet = !empty($row['keywords']) ? htmlspecialchars(mb_substr($row['keywords'], 0, 75) . '...', ENT_QUOTES, 'UTF-8') : '<span style="color:#888; font-style:italic;">Kiritilmagan</span>';
                ?>
                <tr style="border-bottom:1px solid #282625;">
                    <td style="color:#777;"><?=$row['id']?></td>
                    <td>
                        <a href="/<?=$row['translit']?>/" target="_blank" style="color:#ff9900; font-weight:bold; text-decoration:none; font-size:14px;">
                            <?=$row['name']?> <i class="fa fa-external-link" style="font-size:11px;"></i>
                        </a>
                        <div style="font-size:11px; color:#888; margin-top:2px;">
                            Slug: <code>/<?=$row['translit']?>/</code>
                        </div>
                    </td>
                    <td style="color:#bbb; font-size:12px; line-height:1.4;">
                        <?=$keys_snippet?>
                    </td>
                    <td style="text-align:center; font-weight:bold; color:#fff;">
                        <?=$quantity?>
                    </td>
                    <td style="text-align:center; color:#888;">
                        <?=$row['view']?>
                    </td>
                    <td style="text-align:right; white-space:nowrap;">
                        <a href="?func=editCat&id=<?=$row['id']?>" style="background:#333; color:#ff9900; text-decoration:none; padding:4px 8px; border-radius:3px; font-size:12px; margin-right:4px;">
                            <i class="fa fa-pencil"></i> Tahrirlash
                        </a>
                        <a href="?func=removeCat&id=<?=$row['id']?>" onclick="return confirm('Rostdan ham o‘chirmoqchimisiz?');" style="background:#333; color:#dc3545; text-decoration:none; padding:4px 8px; border-radius:3px; font-size:12px;">
                            <i class="fa fa-trash"></i>
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" style="text-align:center; padding:20px; color:#888;">
                    Kategoriyalar mavjud emas.
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$query->free();