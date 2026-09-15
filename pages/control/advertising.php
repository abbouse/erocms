<?php
/**
 * EroCMS To'liq Reklama Boshqaruvi (ExoClick, Popunder, Bannerlar va Homiy Havolalari)
 */

if (!defined('ADMIN_LOADED') && !isset($user)) {
    header('Location: /control.html');
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/core/ads_helper.php';

$msg = null;
$error = null;

// 1. ExoClick va Banner Sozlamalarini Saqlash
if (isset($_POST['save_ad_settings'])) {
    $ads_enabled = isset($_POST['ads_enabled']) ? 1 : 0;
    $popunder_enabled = isset($_POST['popunder_enabled']) ? 1 : 0;
    $popunder_mobile = trim($_POST['popunder_mobile'] ?? '');
    $popunder_desktop = trim($_POST['popunder_desktop'] ?? '');
    
    $banner_top_enabled = isset($_POST['banner_top_enabled']) ? 1 : 0;
    $banner_top_desktop = trim($_POST['banner_top_desktop'] ?? '');
    $banner_top_mobile = trim($_POST['banner_top_mobile'] ?? '');
    
    $banner_bottom_enabled = isset($_POST['banner_bottom_enabled']) ? 1 : 0;
    $banner_bottom_desktop = trim($_POST['banner_bottom_desktop'] ?? '');
    $banner_bottom_mobile = trim($_POST['banner_bottom_mobile'] ?? '');
    
    $sticky_footer_enabled = isset($_POST['sticky_footer_enabled']) ? 1 : 0;
    $sticky_footer_desktop = trim($_POST['sticky_footer_desktop'] ?? '');
    $sticky_footer_mobile = trim($_POST['sticky_footer_mobile'] ?? '');

    $player_overlay_enabled = isset($_POST['player_overlay_enabled']) ? 1 : 0;
    $player_overlay_url = trim($_POST['player_overlay_url'] ?? '');

    $native_grid_enabled = isset($_POST['native_grid_enabled']) ? 1 : 0;
    $native_grid_code = trim($_POST['native_grid_code'] ?? '');

    $vast_preroll_enabled = isset($_POST['vast_preroll_enabled']) ? 1 : 0;
    $vast_preroll_url = trim($_POST['vast_preroll_url'] ?? '');

    $text_ads_enabled = isset($_POST['text_ads_enabled']) ? 1 : 0;

    $new_config = [
        'ads_enabled' => $ads_enabled,
        'popunder_enabled' => $popunder_enabled,
        'popunder_mobile' => $popunder_mobile,
        'popunder_desktop' => $popunder_desktop,
        'banner_top_enabled' => $banner_top_enabled,
        'banner_top_desktop' => $banner_top_desktop,
        'banner_top_mobile' => $banner_top_mobile,
        'banner_top' => $banner_top_desktop ?: $banner_top_mobile,
        'banner_bottom_enabled' => $banner_bottom_enabled,
        'banner_bottom_desktop' => $banner_bottom_desktop,
        'banner_bottom_mobile' => $banner_bottom_mobile,
        'banner_bottom' => $banner_bottom_desktop ?: $banner_bottom_mobile,
        'sticky_footer_enabled' => $sticky_footer_enabled,
        'sticky_footer_desktop' => $sticky_footer_desktop,
        'sticky_footer_mobile' => $sticky_footer_mobile,
        'player_overlay_enabled' => $player_overlay_enabled,
        'player_overlay_url' => $player_overlay_url,
        'native_grid_enabled' => $native_grid_enabled,
        'native_grid_code' => $native_grid_code,
        'vast_preroll_enabled' => $vast_preroll_enabled,
        'vast_preroll_url' => $vast_preroll_url,
        'text_ads_enabled' => $text_ads_enabled
    ];

    if (ads_save_config($new_config)) {
        @array_map('unlink', glob($_SERVER['DOCUMENT_ROOT'] . '/content/cache/*.html'));
        $msg = "Reklama sozlamalari va kodlari muvaffaqiyatli saqlandi!";
        logs($user['id'], "Reklama sozlamalari yangilandi", 0);
    } else {
        $error = "Sozlamalarni saqlashda xatolik yuz berdi (fayl huquqlarini tekshiring).";
    }
}

// 2. Homiy havolasini o'chirish
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $del_id = (int)$_GET['id'];
    $mysqli->query("DELETE FROM ero_advertising WHERE id = '$del_id'");
    @array_map('unlink', glob($_SERVER['DOCUMENT_ROOT'] . '/content/cache/*.html'));
    $msg = "Homiy havolasi muvaffaqiyatli o‘chirildi.";
    logs($user['id'], "Reklama o‘chirildi #$del_id", 0);
}

// 3. Muddatini 30 kunga uzaytirish
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

// 4. Yangi homiy havolasi qo'shish
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
        
        @array_map('unlink', glob($_SERVER['DOCUMENT_ROOT'] . '/content/cache/*.html'));
        $msg = "Yangi reklama muvaffaqiyatli qo‘shildi va saytda faollashtirildi!";
        logs($user['id'], "Yangi reklama qo‘shildi: $name", 0);
    }
}

$cfg = ads_get_config();
$now = time();
$ads_query = $mysqli->query("SELECT * FROM ero_advertising ORDER BY id DESC");
$total_ads = $ads_query ? $ads_query->num_rows : 0;
?>

<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title"><i class="fa fa-bullhorn" style="color: #ff9900;"></i> Reklama Boshqaruvi</h1>
        <p class="adm-page-subtitle">ExoClick popunderlari, video bannerlari va homiy havolalarini yoqish, o‘chirish hamda kodlarini tahrirlash</p>
    </div>
</div>

<?php if ($msg): ?>
    <div class="adm-alert adm-alert-success"><i class="fa fa-check-circle"></i> <?=$msg?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="adm-alert adm-alert-danger"><i class="fa fa-exclamation-triangle"></i> <?=$error?></div>
<?php endif; ?>

<!-- 1. EXOCLICK VA BANNERLARNI YOQISH / O'CHIRISH VA KODLAR -->
<div class="adm-card" style="margin-bottom: 25px;">
    <div class="adm-card-header" style="display:flex; justify-content:space-between; align-items:center;">
        <h3 class="adm-card-title"><i class="fa fa-sliders" style="color:#ff9900;"></i> ExoClick & Tarmoq Reklamalari (Yoqish / O‘chirish)</h3>
        <span class="adm-badge <?=($cfg['ads_enabled'] ? 'adm-badge-success' : 'adm-badge-danger')?>">
            <?=($cfg['ads_enabled'] ? '<i class="fa fa-check"></i> Saytda reklamalar YOQIQ' : '<i class="fa fa-power-off"></i> Barcha reklamalar O‘CHIRILGAN')?>
        </span>
    </div>

    <form method="post" style="padding: 5px;">
        <!-- Global va bo'limlar switchlari -->
        <div style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; padding: 18px; margin-bottom: 20px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px;">
                
                <!-- Barcha reklamalar Master Switch -->
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 10px 14px; background: #151821; border-radius: 6px; border: 1px solid rgba(255,153,0,0.3);">
                    <input type="checkbox" name="ads_enabled" value="1" <?=($cfg['ads_enabled'] ? 'checked' : '')?> style="width:18px; height:18px; accent-color:#ff9900;" />
                    <div>
                        <strong style="color: #fff; font-size: 13px; display: block;">Barcha reklamalar (Master)</strong>
                        <small style="color: #94a3b8; font-size: 11px;">Saytdagi hamma reklamani yoqish/o‘chirish</small>
                    </div>
                </label>

                <!-- Popunder Switch -->
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 10px 14px; background: #151821; border-radius: 6px; border: 1px solid rgba(255,255,255,0.08);">
                    <input type="checkbox" name="popunder_enabled" value="1" <?=($cfg['popunder_enabled'] ? 'checked' : '')?> style="width:18px; height:18px; accent-color:#ff9900;" />
                    <div>
                        <strong style="color: #fff; font-size: 13px; display: block;">Popunder reklamasi</strong>
                        <small style="color: #94a3b8; font-size: 11px;">Foydalanuvchi bosganda yangi oynada ochilish</small>
                    </div>
                </label>

                <!-- Player ustidagi banner Switch -->
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 10px 14px; background: #151821; border-radius: 6px; border: 1px solid rgba(255,255,255,0.08);">
                    <input type="checkbox" name="banner_top_enabled" value="1" <?=($cfg['banner_top_enabled'] ? 'checked' : '')?> style="width:18px; height:18px; accent-color:#ff9900;" />
                    <div>
                        <strong style="color: #fff; font-size: 13px; display: block;">Player ustidagi banner</strong>
                        <small style="color: #94a3b8; font-size: 11px;">Video player tepasida ko‘rinadigan banner</small>
                    </div>
                </label>

                <!-- Player ostidagi banner Switch -->
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 10px 14px; background: #151821; border-radius: 6px; border: 1px solid rgba(255,255,255,0.08);">
                    <input type="checkbox" name="banner_bottom_enabled" value="1" <?=($cfg['banner_bottom_enabled'] ? 'checked' : '')?> style="width:18px; height:18px; accent-color:#ff9900;" />
                    <div>
                        <strong style="color: #fff; font-size: 13px; display: block;">Player ostidagi banner</strong>
                        <small style="color: #94a3b8; font-size: 11px;">Ovoz berish tugmalari ostidagi banner</small>
                    </div>
                </label>

                <!-- Sticky Footer Switch -->
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 10px 14px; background: #151821; border-radius: 6px; border: 1px solid rgba(255,255,255,0.08);">
                    <input type="checkbox" name="sticky_footer_enabled" value="1" <?=(!empty($cfg['sticky_footer_enabled']) ? 'checked' : '')?> style="width:18px; height:18px; accent-color:#ff9900;" />
                    <div>
                        <strong style="color: #fff; font-size: 13px; display: block;"><i class="fa fa-anchor" style="color:#38bdf8;"></i> Sticky Footer Banner</strong>
                        <small style="color: #94a3b8; font-size: 11px;">Ekran pastida yopishib turuvchi reklama</small>
                    </div>
                </label>

                <!-- In-Player Click Overlay Switch -->
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 10px 14px; background: #151821; border-radius: 6px; border: 1px solid rgba(255,255,255,0.08);">
                    <input type="checkbox" name="player_overlay_enabled" value="1" <?=(!empty($cfg['player_overlay_enabled']) ? 'checked' : '')?> style="width:18px; height:18px; accent-color:#ff9900;" />
                    <div>
                        <strong style="color: #fff; font-size: 13px; display: block;"><i class="fa fa-play-circle" style="color:#ef4444;"></i> In-Player Click Overlay</strong>
                        <small style="color: #94a3b8; font-size: 11px;">Play bosganda yangi oynada ochilish</small>
                    </div>
                </label>

                <!-- Native Grid Switch -->
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 10px 14px; background: #151821; border-radius: 6px; border: 1px solid rgba(255,255,255,0.08);">
                    <input type="checkbox" name="native_grid_enabled" value="1" <?=(!empty($cfg['native_grid_enabled']) ? 'checked' : '')?> style="width:18px; height:18px; accent-color:#ff9900;" />
                    <div>
                        <strong style="color: #fff; font-size: 13px; display: block;"><i class="fa fa-th" style="color:#a855f7;"></i> Native Video Grid</strong>
                        <small style="color: #94a3b8; font-size: 11px;">Videolar orasidagi tavsiya reklamasi</small>
                    </div>
                </label>

                <!-- VAST Pre-Roll Switch -->
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 10px 14px; background: #151821; border-radius: 6px; border: 1px solid rgba(255,255,255,0.08);">
                    <input type="checkbox" name="vast_preroll_enabled" value="1" <?=(!empty($cfg['vast_preroll_enabled']) ? 'checked' : '')?> style="width:18px; height:18px; accent-color:#ff9900;" />
                    <div>
                        <strong style="color: #fff; font-size: 13px; display: block;"><i class="fa fa-film" style="color:#ec4899;"></i> VAST Video Pre-Roll</strong>
                        <small style="color: #94a3b8; font-size: 11px;">Video oldidan 5s video reklama</small>
                    </div>
                </label>

                <!-- Matnli homiy havolalari Switch -->
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 10px 14px; background: #151821; border-radius: 6px; border: 1px solid rgba(255,255,255,0.08);">
                    <input type="checkbox" name="text_ads_enabled" value="1" <?=($cfg['text_ads_enabled'] ? 'checked' : '')?> style="width:18px; height:18px; accent-color:#ff9900;" />
                    <div>
                        <strong style="color: #fff; font-size: 13px; display: block;">Homiy havolalari</strong>
                        <small style="color: #94a3b8; font-size: 11px;">Sayt tepasidagi qisqa reklama tugmalari</small>
                    </div>
                </label>
            </div>
        </div>

        <!-- Reklama kodlari textarea maydonlari -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <!-- Popunder Mobile -->
            <div class="adm-form-group">
                <label class="adm-label"><i class="fa fa-mobile" style="color:#ff9900; font-size:16px;"></i> Mobil Popunder Kodi (ExoClick Mobile):</label>
                <textarea name="popunder_mobile" class="adm-input" style="height: 120px; font-family: monospace; font-size: 11px; line-height: 1.4; resize: vertical;" placeholder="<script ...> ExoClick mobil popunder kodi"><?=htmlspecialchars($cfg['popunder_mobile'] ?? '')?></textarea>
                <small style="color: #64748b; font-size: 11px;">Smartfon va planshetlardan kirganlar uchun ishlaydi.</small>
            </div>

            <!-- Popunder Desktop -->
            <div class="adm-form-group">
                <label class="adm-label"><i class="fa fa-desktop" style="color:#60a5fa; font-size:14px;"></i> Kompyuter (Desktop) Popunder Kodi:</label>
                <textarea name="popunder_desktop" class="adm-input" style="height: 120px; font-family: monospace; font-size: 11px; line-height: 1.4; resize: vertical;" placeholder="<script ...> ExoClick desktop popunder kodi"><?=htmlspecialchars($cfg['popunder_desktop'] ?? '')?></textarea>
                <small style="color: #64748b; font-size: 11px;">Bo‘sh qoldirilsa, avtomatik tarzda yuqoridagi mobil popunder ishlayveradi.</small>
            </div>
        </div>

        <!-- 1. Player Ustidagi Banner (Kompyuter va Mobil Alohida) -->
        <div style="background: rgba(34,197,94,0.05); border: 1px solid rgba(34,197,94,0.2); border-radius: 8px; padding: 15px; margin-bottom: 20px;">
            <div style="font-weight: 700; color: #22c55e; font-size: 13px; margin-bottom: 12px; display:flex; align-items:center; gap:8px;">
                <i class="fa fa-picture-o"></i> Player Ustidagi Banner (728x90 Kompyuter va 300x250/300x100 Mobil):
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="adm-form-group" style="margin-bottom:0;">
                    <label class="adm-label"><i class="fa fa-desktop" style="color:#60a5fa;"></i> Kompyuter (Desktop) uchun (728x90 yoki 900x250):</label>
                    <textarea name="banner_top_desktop" class="adm-input" style="height: 100px; font-family: monospace; font-size: 11px; resize: vertical;" placeholder="<script ...> yoki <iframe> kompyuter banner kodi"><?=htmlspecialchars($cfg['banner_top_desktop'] ?? $cfg['banner_top'] ?? '')?></textarea>
                    <small style="color: #64748b; font-size: 11px;">Faqat monitor va noutbuklarda (keng ekranda) ko‘rinadi.</small>
                </div>
                <div class="adm-form-group" style="margin-bottom:0;">
                    <label class="adm-label"><i class="fa fa-mobile" style="color:#ff9900; font-size:15px;"></i> Mobil (Telefon) uchun (300x250 yoki 300x100):</label>
                    <textarea name="banner_top_mobile" class="adm-input" style="height: 100px; font-family: monospace; font-size: 11px; resize: vertical;" placeholder="<script ...> yoki <iframe> mobil banner kodi"><?=htmlspecialchars($cfg['banner_top_mobile'] ?? '')?></textarea>
                    <small style="color: #64748b; font-size: 11px;">Faqat smartfonlarda chiqadi, ekran o‘lchamiga sig‘adi.</small>
                </div>
            </div>
        </div>

        <!-- 2. Player Ostidagi Banner (Kompyuter va Mobil Alohida) -->
        <div style="background: rgba(245,158,11,0.05); border: 1px solid rgba(245,158,11,0.2); border-radius: 8px; padding: 15px; margin-bottom: 20px;">
            <div style="font-weight: 700; color: #f59e0b; font-size: 13px; margin-bottom: 12px; display:flex; align-items:center; gap:8px;">
                <i class="fa fa-picture-o"></i> Player Ostidagi Banner (300x250 yoki 728x90):
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="adm-form-group" style="margin-bottom:0;">
                    <label class="adm-label"><i class="fa fa-desktop" style="color:#60a5fa;"></i> Kompyuter (Desktop) uchun:</label>
                    <textarea name="banner_bottom_desktop" class="adm-input" style="height: 100px; font-family: monospace; font-size: 11px; resize: vertical;" placeholder="<script ...> yoki <iframe> kompyuter banner kodi"><?=htmlspecialchars($cfg['banner_bottom_desktop'] ?? $cfg['banner_bottom'] ?? '')?></textarea>
                    <small style="color: #64748b; font-size: 11px;">Video ostidagi keng ekranli reklama joyi.</small>
                </div>
                <div class="adm-form-group" style="margin-bottom:0;">
                    <label class="adm-label"><i class="fa fa-mobile" style="color:#ff9900; font-size:15px;"></i> Mobil (Telefon) uchun (300x250):</label>
                    <textarea name="banner_bottom_mobile" class="adm-input" style="height: 100px; font-family: monospace; font-size: 11px; resize: vertical;" placeholder="<script ...> yoki <iframe> mobil banner kodi"><?=htmlspecialchars($cfg['banner_bottom_mobile'] ?? '')?></textarea>
                    <small style="color: #64748b; font-size: 11px;">Mobil telefonlarda video ostida chiqadigan reklama.</small>
                </div>
            </div>
        </div>

        <!-- 3. Ekran Pastidagi Sticky Footer Banner (Kompyuter va Mobil) -->
        <div style="background: rgba(56,189,248,0.05); border: 1px solid rgba(56,189,248,0.2); border-radius: 8px; padding: 15px; margin-bottom: 20px;">
            <div style="font-weight: 700; color: #38bdf8; font-size: 13px; margin-bottom: 12px; display:flex; align-items:center; gap:8px;">
                <i class="fa fa-anchor"></i> Ekran Pastidagi Yopishqoq Banner (Sticky Footer - Eng Yuqori CTR & Daromad):
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="adm-form-group" style="margin-bottom:0;">
                    <label class="adm-label"><i class="fa fa-desktop" style="color:#60a5fa;"></i> Kompyuter (Desktop) uchun (728x90 yoki 900x90):</label>
                    <textarea name="sticky_footer_desktop" class="adm-input" style="height: 90px; font-family: monospace; font-size: 11px; resize: vertical;" placeholder="<script ...> ExoClick 728x90 banner kodi"><?=htmlspecialchars($cfg['sticky_footer_desktop'] ?? '')?></textarea>
                    <small style="color: #64748b; font-size: 11px;">Monitor ekrani pastida doim ko‘rinib turadi.</small>
                </div>
                <div class="adm-form-group" style="margin-bottom:0;">
                    <label class="adm-label"><i class="fa fa-mobile" style="color:#ff9900; font-size:15px;"></i> Mobil (Telefon) uchun (300x50 yoki 300x100):</label>
                    <textarea name="sticky_footer_mobile" class="adm-input" style="height: 90px; font-family: monospace; font-size: 11px; resize: vertical;" placeholder="<script ...> ExoClick 300x50 yoki 300x100 banner kodi"><?=htmlspecialchars($cfg['sticky_footer_mobile'] ?? '')?></textarea>
                    <small style="color: #64748b; font-size: 11px;">Smartfon ekrani tagida yopishib turadi ("Yopish" tugmasi mavjud).</small>
                </div>
            </div>
        </div>

        <!-- 4. Video Player Ustiga Click-Overlay & VAST Video Pre-Roll -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <!-- Player Overlay Direct Link -->
            <div style="background: rgba(239,68,68,0.05); border: 1px solid rgba(239,68,68,0.2); border-radius: 8px; padding: 15px;">
                <div style="font-weight: 700; color: #ef4444; font-size: 13px; margin-bottom: 8px; display:flex; align-items:center; gap:8px;">
                    <i class="fa fa-play-circle"></i> In-Player Click Overlay (Direct Link):
                </div>
                <div class="adm-form-group" style="margin-bottom:0;">
                    <label class="adm-label">ExoClick Direct Link (To‘g‘ridan-to‘g‘ri Reklama URL):</label>
                    <input type="text" name="player_overlay_url" class="adm-input" value="<?=htmlspecialchars($cfg['player_overlay_url'] ?? '')?>" placeholder="https://syndication.exoclick.com/tag.php?goal=..." />
                    <small style="color: #64748b; font-size: 11px; display:block; margin-top:4px;">Foydalanuvchi videoni yoqish uchun birinchi marta "Play" bosganda ochiladi.</small>
                </div>
            </div>

            <!-- VAST Pre-Roll URL -->
            <div style="background: rgba(236,72,153,0.05); border: 1px solid rgba(236,72,153,0.2); border-radius: 8px; padding: 15px;">
                <div style="font-weight: 700; color: #ec4899; font-size: 13px; margin-bottom: 8px; display:flex; align-items:center; gap:8px;">
                    <i class="fa fa-film"></i> VAST In-Stream Video Ad URL (Pre-Roll):
                </div>
                <div class="adm-form-group" style="margin-bottom:0;">
                    <label class="adm-label">ExoClick VAST XML / URL manzili:</label>
                    <input type="text" name="vast_preroll_url" class="adm-input" value="<?=htmlspecialchars($cfg['vast_preroll_url'] ?? '')?>" placeholder="https://syndication.exoclick.com/splash.php?idzone=...&type=3" />
                    <small style="color: #64748b; font-size: 11px; display:block; margin-top:4px;">Video boshlanishidan avval 5s o‘ynaladigan qimmatbaho video reklama.</small>
                </div>
            </div>
        </div>

        <!-- 5. Videolar Orasidagi Native Video Grid -->
        <div style="background: rgba(168,85,247,0.05); border: 1px solid rgba(168,85,247,0.2); border-radius: 8px; padding: 15px; margin-bottom: 20px;">
            <div style="font-weight: 700; color: #a855f7; font-size: 13px; margin-bottom: 8px; display:flex; align-items:center; gap:8px;">
                <i class="fa fa-th"></i> Native Video Grid (Tavsiya Videolari Ko‘rinishidagi Reklama):
            </div>
            <div class="adm-form-group" style="margin-bottom:0;">
                <label class="adm-label">ExoClick Native Ad Widget Kodi:</label>
                <textarea name="native_grid_code" class="adm-input" style="height: 80px; font-family: monospace; font-size: 11px; resize: vertical;" placeholder="<script ...> ExoClick Native Ad widget kodi"><?=htmlspecialchars($cfg['native_grid_code'] ?? '')?></textarea>
                <small style="color: #64748b; font-size: 11px;">Bosh sahifada va video ostidagi "O‘xshash videolar" ro‘yxatida videolarga o‘xshab chiqadi.</small>
            </div>
        </div>

        <div style="margin-top: 15px; text-align: right;">
            <button type="submit" name="save_ad_settings" value="1" class="adm-btn adm-btn-primary" style="padding: 12px 24px; font-size: 14px;">
                <i class="fa fa-save"></i> Reklama Sozlamalari va Kodlarini Saqlash
            </button>
        </div>
    </form>
</div>

<!-- 2. TO‘G‘RIDAN-TO‘G‘RI HOMIY HAVOLALARI (TELEGRAM KANALLAR VA HAMKORLAR) -->
<div style="display:grid; grid-template-columns: 2fr 1fr; gap:20px;">
    <!-- Mavjud Reklamalar Jadvali -->
    <div class="adm-card">
        <div class="adm-card-header">
            <h3 class="adm-card-title"><i class="fa fa-list"></i> Mavjud Homiy Havolalari (<?=$total_ads?> ta)</h3>
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
                            Hozircha saytda homiy havolalari yo‘q.
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
            <h3 class="adm-card-title"><i class="fa fa-plus-circle" style="color: #ff9900;"></i> Yangi Homiy Havolasi</h3>
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
                <i class="fa fa-plus"></i> Homiy Havolasini Joylashtirish
            </button>
        </form>
    </div>
</div>
