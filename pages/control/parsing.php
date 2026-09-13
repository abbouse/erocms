<?php

/*
 * erocms Ultra Universal Video Parser Engine 2.0
 * 
 * Qo'llab-quvvatlanuvchi donor saytlar:
 * 1. sexlar.link (Yangi o'zbek videolari - Tezkor va sifatli)
 * 2. arhivporno.watch (cat-uzbekskii-seks arxivi)
 * 3. uzbxx.ru
 * 4. uzporno.website
 * 
 * Imkoniyatlari:
 * - ⚡ 100% Asinxron AJAX Batch Runner (Nol Gateway Timeout kafolati!)
 * - 🎯 Aqlli Intellektual Toifaga Ajratish (27 ta bo'lim bo'yicha semantik tahlil)
 * - 🔄 Ko'p sahifali Pagination (1-sahifadan N-sahifagacha avtomatik aylanadi)
 * - 🏷️ Boyitilgan SEO Sarlavha, Meta Description va SEO Teglar avto-generatsiyasi
 * - ⏸️ Jonli pauza, to'xtatish va real-vaqt hisoblagichlari
 */

ini_set('display_errors', 0);
error_reporting(E_ALL);

if ($user['access'] < 1) {
    header('Location: /'); 
    exit;
}

// Parser dvijokini xavfsiz ulash
if (!function_exists('parse_video_sexlar')) {
    $engine_paths = [
        __DIR__ . '/../../core/ParserEngine.php',
        dirname(__DIR__, 2) . '/core/ParserEngine.php',
        ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/core/ParserEngine.php',
        'core/ParserEngine.php'
    ];
    foreach ($engine_paths as $p) {
        if (!empty($p) && file_exists($p)) {
            require_once $p;
            break;
        }
    }
}

// =========================================================================
// 1. ASINXRON AJAX ENDPOINTLAR (GATEWAY TIMEOUT 100% OLDINI OLADI)
// =========================================================================
$ajax_action = filter($_REQUEST['ajax_action'] ?? '');
if (!empty($ajax_action)) {
    while (ob_get_level()) { ob_end_clean(); }
    header('Content-Type: application/json; charset=UTF-8');
    @set_time_limit(120);

    // 1.1. Katalogdagi videolarni sahifa bo'yicha olish
    if ($ajax_action === 'get_catalog') {
        $donor = filter($_POST['donor'] ?? 'sexlar');
        $page = max(1, abs(intval($_POST['page'] ?? 1)));
        $custom_url = trim($_POST['custom_url'] ?? '');
        
        $items = [];
        try {
            if (!empty($custom_url)) {
                if (stripos($custom_url, 'sexlar.link') !== false) {
                    $donor = 'sexlar';
                    $items = parser_get_catalog_links_sexlar($page, $custom_url);
                } elseif (stripos($custom_url, 'arhivporno.watch') !== false) {
                    $donor = 'arhivporno';
                    $items = parser_get_catalog_links_arhivporno($page, $custom_url);
                } elseif (stripos($custom_url, 'uzporno.website') !== false) {
                    $donor = 'uzporno';
                    $items = parser_get_catalog_links_uzporno($page, $custom_url);
                } else {
                    $donor = 'uzbxx';
                    $items = parser_get_catalog_links_uzbxx($page, $custom_url);
                }
            } else {
                if ($donor === 'sexlar') {
                    $items = parser_get_catalog_links_sexlar($page);
                } elseif ($donor === 'arhivporno') {
                    $items = parser_get_catalog_links_arhivporno($page);
                } elseif ($donor === 'uzbxx') {
                    $items = parser_get_catalog_links_uzbxx($page);
                } elseif ($donor === 'uzporno') {
                    $items = parser_get_catalog_links_uzporno($page);
                }
            }
            
            echo json_encode([
                'status' => 'success',
                'donor'  => $donor,
                'page'   => $page,
                'count'  => count($items),
                'items'  => $items
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Katalog yuklashda xatolik: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    // 1.2. Bitta videoni parslash va bazaga yozish
    if ($ajax_action === 'parse_video') {
        $donor = filter($_POST['donor'] ?? 'sexlar');
        $video_url = trim($_POST['url'] ?? '');
        $category = abs(intval($_POST['category'] ?? 0));
        $save_mode = filter($_POST['save_mode'] ?? 'stream');
        $cat_context = [
            'poster'        => trim($_POST['poster'] ?? ''),
            'duration'      => trim($_POST['duration'] ?? ''),
            'title'         => trim($_POST['title'] ?? ''),
            'category_hint' => trim($_POST['category_hint'] ?? '')
        ];

        if (empty($video_url)) {
            echo json_encode(['status' => 'error', 'message' => 'Video manzili kiritilmadi!'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        try {
            $res = null;
            if ($donor === 'sexlar' || stripos($video_url, 'sexlar.link') !== false) {
                $res = parse_video_sexlar($video_url, $category, $save_mode, $mysqli, $settings, $width_S, $height_S, $cat_context);
            } elseif ($donor === 'arhivporno' || stripos($video_url, 'arhivporno.watch') !== false) {
                $res = parse_video_arhivporno($video_url, $category, $save_mode, $mysqli, $settings, $width_S, $height_S, $cat_context);
            } elseif ($donor === 'uzbxx' || stripos($video_url, 'uzbxx.ru') !== false) {
                $res = parse_video_uzbxx($video_url, $category, $save_mode, $mysqli, $settings, $width_S, $height_S, $cat_context);
            } elseif ($donor === 'uzporno' || stripos($video_url, 'uzporno.website') !== false) {
                $res = parse_video_uzporno($video_url, $category, $save_mode, $mysqli, $settings, $width_S, $height_S, $cat_context);
            } else {
                $res = ['status' => 'error', 'message' => 'Noma‘lum donor video manzili!'];
            }

            // Keshni tozalash
            if (!empty($res['status']) && $res['status'] === 'success') {
                $doc_root = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2);
                @array_map('unlink', glob($doc_root . '/content/cache/*.html'));
            }

            echo json_encode($res ?: ['status' => 'error', 'message' => 'Parslashda xatolik yuz berdi'], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            echo json_encode(['status' => 'error', 'message' => 'Server xatosi: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    // 1.3. 404 / Singan rasmlarni avto-tuzatish
    if ($ajax_action === 'repair_screenshots') {
        try {
            $count = function_exists('parser_repair_broken_screenshots') ? parser_repair_broken_screenshots($mysqli) : 0;
            echo json_encode([
                'status'  => 'success',
                'count'   => $count,
                'message' => "Jami {$count} ta singan yoki 404 skrinshot tekshirilib, to‘g‘ri CDN havolasiga tiklandi."
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }
}

// Barcha mavjud kategoriyalar
$cat_list = [];
$cats_query = $mysqli->query("SELECT id, name, translit FROM ero_categories ORDER BY name ASC");
if ($cats_query) {
    while ($r = $cats_query->fetch_assoc()) {
        $cat_list[] = $r;
    }
}

$logs = [];
$repair_info = '';

// Standart POST so'rovlari (eski usul yoki fallback uchun)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($ajax_action)) {
    try {
        $action_type = filter($_POST['action_type'] ?? '');
        $category_choice = abs(intval($_POST['category'] ?? 0));
        $save_mode = filter($_POST['save_mode'] ?? 'stream');

        if ($action_type === 'single_url') {
            $single_url = trim($_POST['single_url'] ?? '');
            if (empty($single_url)) {
                $logs[] = ['status' => 'error', 'message' => 'Video havolasi kiritilmadi!'];
            } else {
                if (stripos($single_url, 'sexlar.link') !== false) {
                    $logs[] = parse_video_sexlar($single_url, $category_choice, $save_mode, $mysqli, $settings, $width_S, $height_S);
                } elseif (stripos($single_url, 'arhivporno.watch') !== false) {
                    $logs[] = parse_video_arhivporno($single_url, $category_choice, $save_mode, $mysqli, $settings, $width_S, $height_S);
                } elseif (stripos($single_url, 'uzbxx.ru') !== false) {
                    $logs[] = parse_video_uzbxx($single_url, $category_choice, $save_mode, $mysqli, $settings, $width_S, $height_S);
                } elseif (stripos($single_url, 'uzporno.website') !== false) {
                    $logs[] = parse_video_uzporno($single_url, $category_choice, $save_mode, $mysqli, $settings, $width_S, $height_S);
                } else {
                    $logs[] = ['status' => 'error', 'message' => 'Noma‘lum havola!'];
                }
                $doc_root = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2);
                @array_map('unlink', glob($doc_root . '/content/cache/*.html'));
            }
        }
    } catch (Throwable $e) {
        $logs[] = ['status' => 'error', 'message' => 'Xatolik: ' . $e->getMessage()];
    }
}

if (isset($_GET['repair']) && function_exists('parser_repair_broken_screenshots')) {
    $rep_c = parser_repair_broken_screenshots($mysqli);
    $repair_info = "Tekshirildi: jami {$rep_c} ta singan/404 rasm avtomatik aniqlanib, to‘g‘ri CDN havolalariga tiklandi!";
}
?>

<div class="adm-card" style="border-left: 4px solid #ff9900; background: #16181e; margin-bottom: 24px; padding: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div>
            <h2 style="color: #ff9900; margin: 0 0 6px 0; font-size: 20px; display: flex; align-items: center; gap: 10px;">
                <i class="fa fa-bolt"></i> Ultra Universal Video Parser 2.0
                <span style="background: #10b981; color: #000; font-size: 11px; padding: 2px 8px; border-radius: 12px; font-weight: bold;">AJAX BATCH RUNNER</span>
            </h2>
            <p style="color: #94a3b8; font-size: 13px; margin: 0; line-height: 1.5;">
                Donor saytlardan (<b>sexlar.link</b>, <b>arhivporno.watch</b>, <b>uzbxx.ru</b>, <b>uzporno.website</b>) barcha videolarni sahifalab, to‘liq SEO teglar va tavsiflari bilan <b>gateway timeout bo‘lmasdan</b> yuklab olish tizimi.
            </p>
        </div>
        <div>
            <button type="button" id="btnRepairScreenshots" class="adm-btn" style="background: #059669; color: #fff; border: none; padding: 9px 16px; border-radius: 6px; font-weight: bold; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fa fa-wrench"></i> Singan / 404 rasmlarni tuzatish
            </button>
        </div>
    </div>
</div>

<?php if (!empty($repair_info)): ?>
<div class="adm-alert adm-alert-success" style="margin-bottom: 20px;">
    <i class="fa fa-check-circle"></i> <?=$repair_info?>
</div>
<?php endif; ?>

<?php if (!empty($logs)): ?>
<div class="adm-card" style="margin-bottom: 24px; background: #11141a; border: 1px solid #334155;">
    <h3 style="color: #ff9900; margin: 0 0 12px 0; font-size: 15px;"><i class="fa fa-list-alt"></i> Parslash natijasi:</h3>
    <div style="max-height: 200px; overflow-y: auto; font-family: monospace; font-size: 13px; background: #000; padding: 12px; border-radius: 4px;">
        <?php foreach ($logs as $l): ?>
            <div style="color: <?=($l['status'] === 'success' ? '#10b981' : ($l['status'] === 'skip' ? '#f59e0b' : '#ef4444'))?>; margin-bottom: 4px;">
                [<?=strtoupper($l['status'])?>] <?=$l['message']?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- ========================================================================= -->
<!-- 1. ASOSIY ASINXRON CANLI PARSER (TIMEOUTSIZ, PROGRESS BAR VA JONLI LOG)   -->
<!-- ========================================================================= -->
<style>
.adm-preset-btn {
    background: #1e293b;
    color: #cbd5e1;
    border: 1px solid #334155;
    padding: 7px 13px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.adm-preset-btn:hover {
    background: #334155;
    color: #ff9900;
    border-color: #ff9900;
    transform: translateY(-1px);
}
.adm-preset-btn.active {
    background: rgba(255, 153, 0, 0.15);
    color: #ff9900;
    border-color: #ff9900;
    box-shadow: 0 0 10px rgba(255, 153, 0, 0.3);
}
.donor-option {
    transition: all 0.2s ease;
}
.donor-option:hover {
    border-color: #ff9900 !important;
}
.donor-option input:checked + div b {
    text-shadow: 0 0 8px currentColor;
}
</style>

<!-- ========================================================================= -->
<!-- 1. ASOSIY ASINXRON JONLI PARSER (TIMEOUTSIZ, PROGRESS BAR VA JONLI LOG)   -->
<!-- ========================================================================= -->
<div class="adm-card" style="background: #181b24; border: 2px solid #ff9900; border-radius: 8px; padding: 24px; margin-bottom: 24px; box-shadow: 0 8px 24px rgba(0,0,0,0.4);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid #282d3d; padding-bottom: 14px; flex-wrap: wrap; gap: 10px;">
        <div>
            <h3 style="color: #ff9900; margin: 0 0 4px 0; font-size: 18px; display: flex; align-items: center; gap: 8px;">
                <i class="fa fa-play-circle"></i> 1. Jonli Asinxron Parser (Nol Timeout Kafolati)
            </h3>
            <span style="color: #94a3b8; font-size: 12px;">Har bir video alohida so‘rovda o‘tadi — server hech qachon qotib qolmaydi va 504 Gateway Timeout bermaydi.</span>
        </div>
        <div id="liveStatusBadge" style="display: none; background: #1e293b; color: #38bdf8; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: bold; border: 1px solid #38bdf8; align-items: center; gap: 8px;">
            <span class="adm-pulse-dot" style="background: #38bdf8;"></span> <span id="liveStatusText">Tayyor</span>
        </div>
    </div>

    <!-- Tezkor 1-Klik Shablonlar (Presets) -->
    <div style="margin-bottom: 20px; background: rgba(255, 153, 0, 0.04); border: 1px solid rgba(255, 153, 0, 0.2); border-radius: 8px; padding: 14px 16px;">
        <div style="color: #ff9900; font-weight: bold; font-size: 13px; margin-bottom: 10px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
            <span><i class="fa fa-magic"></i> ⚡ Tezkor 1-Klik Shablonlar (Presets):</span>
            <span style="font-size: 11px; color: #94a3b8; font-weight: normal;">Bitta bosishda mos donor, sahifalar va toifani avtomatik sozlaydi</span>
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 8px;" id="parserPresetsList">
            <button type="button" class="adm-preset-btn active" data-donor="sexlar" data-url="" data-from="1" data-to="3" title="sexlar.link dan eng yangi o‘zbekcha videolar">
                🇺🇿 Yangi O‘zbek (sexlar)
            </button>
            <button type="button" class="adm-preset-btn" data-donor="arhivporno" data-url="https://arhivporno.watch/" data-from="1" data-to="3" title="arhivporno.watch (barcha yangi videolar)">
                🌟 Barcha Yangi (arhivporno)
            </button>
            <button type="button" class="adm-preset-btn" data-donor="arhivporno" data-url="https://arhivporno.watch/cat-anal-porno/" data-from="1" data-to="3" title="Anal seks bo‘limi">
                🍑 Anal Seksi
            </button>
            <button type="button" class="adm-preset-btn" data-donor="arhivporno" data-url="https://arhivporno.watch/cat-minet/" data-from="1" data-to="3" title="Minet / Oral seks bo‘limi">
                👄 Minet / Oral
            </button>
            <button type="button" class="adm-preset-btn" data-donor="arhivporno" data-url="https://arhivporno.watch/cat-porno-rakom/" data-from="1" data-to="3" title="Rakom / Doggystyle bo‘limi">
                🐕 Rakom (Doggystyle)
            </button>
            <button type="button" class="adm-preset-btn" data-donor="uzbxx" data-url="https://uzbxx.ru/category/Domashnee/" data-from="1" data-to="3" title="Uyda olingan / Domashnee">
                🏠 Domashnee (uzbxx)
            </button>
            <button type="button" class="adm-preset-btn" data-donor="arhivporno" data-url="https://arhivporno.watch/cat-bolshie-siski/" data-from="1" data-to="3" title="Katta emchaklar bo‘limi">
                🍒 Katta Ko‘kraklar (Siski)
            </button>
            <button type="button" class="adm-preset-btn" data-donor="arhivporno" data-url="https://arhivporno.watch/cat-porno-studentov/" data-from="1" data-to="3" title="Talabalar bo‘limi">
                🎓 Talabalar
            </button>
            <button type="button" class="adm-preset-btn" data-donor="arhivporno" data-url="https://arhivporno.watch/cat-porno-molodih/" data-from="1" data-to="3" title="Yosh qizlar bo‘limi">
                🔞 Yosh Qizlar (18+)
            </button>
            <button type="button" class="adm-preset-btn" data-donor="uzbxx" data-url="" data-from="1" data-to="3" title="uzbxx.ru bosh sahifasi">
                🇺🇿 Katta O‘zbek Bazasi (uzbxx)
            </button>
            <button type="button" class="adm-preset-btn" data-donor="uzporno" data-url="" data-from="1" data-to="3" title="uzporno.website">
                🌐 uzporno.website
            </button>
            <button type="button" class="adm-preset-btn" data-donor="all" data-url="" data-from="1" data-to="2" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #fff; font-weight: bold; border-color: #10b981;" title="Barcha 4 donor ketma-ket">
                🔥 BARCHA DONORLAR (Multi-Donor)
            </button>
        </div>
    </div>

    <!-- Sozlamalar Formasi -->
    <div id="parserFormArea">
        <div style="margin-bottom: 18px;">
            <label style="color: #e2e8f0; font-weight: bold; font-size: 13px; display: block; margin-bottom: 8px;">
                <i class="fa fa-globe" style="color: #ff9900;"></i> Donor Saytni Tanlang:
            </label>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px;">
                <label style="background: #0f172a; border: 1px solid #334155; padding: 12px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 10px;" class="donor-option">
                    <input type="radio" name="p_donor" value="sexlar" checked />
                    <div>
                        <b style="color: #10b981; font-size: 14px;">sexlar.link</b>
                        <div style="color: #64748b; font-size: 11px;">Yangi o‘zbekcha videolar</div>
                    </div>
                </label>
                <label style="background: #0f172a; border: 1px solid #334155; padding: 12px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 10px;" class="donor-option">
                    <input type="radio" name="p_donor" value="arhivporno" />
                    <div>
                        <b style="color: #38bdf8; font-size: 14px;">arhivporno.watch</b>
                        <div style="color: #64748b; font-size: 11px;">Barcha yangi & tematik arxiv</div>
                    </div>
                </label>
                <label style="background: #0f172a; border: 1px solid #334155; padding: 12px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 10px;" class="donor-option">
                    <input type="radio" name="p_donor" value="uzbxx" />
                    <div>
                        <b style="color: #f59e0b; font-size: 14px;">uzbxx.ru</b>
                        <div style="color: #64748b; font-size: 11px;">Katta o‘zbek bazasi</div>
                    </div>
                </label>
                <label style="background: #0f172a; border: 1px solid #334155; padding: 12px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 10px;" class="donor-option">
                    <input type="radio" name="p_donor" value="uzporno" />
                    <div>
                        <b style="color: #ec4899; font-size: 14px;">uzporno.website</b>
                        <div style="color: #64748b; font-size: 11px;">O‘zbek & sharq videolari</div>
                    </div>
                </label>
                <label style="background: #0f172a; border: 1px solid #334155; padding: 12px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 10px;" class="donor-option">
                    <input type="radio" name="p_donor" value="all" />
                    <div>
                        <b style="color: #a855f7; font-size: 14px;">🔥 BARCHA DONORLAR</b>
                        <div style="color: #64748b; font-size: 11px;">4 ta donordan ketma-ket</div>
                    </div>
                </label>
            </div>
        </div>

        <!-- Maxsus Bo'lim / Kategoriya URLi (Ixtiyoriy) -->
        <div style="margin-bottom: 18px;">
            <label style="color: #94a3b8; font-size: 12px; display: block; margin-bottom: 6px;">
                <i class="fa fa-link" style="color: #ff9900;"></i> Maxsus donor bo‘lim/katalog havolasi (Ixtiyoriy — masalan faqat ma'lum toifani parslash uchun):
            </label>
            <input type="text" id="p_custom_url" placeholder="Bo‘sh qoldiring yoki masalan: https://arhivporno.watch/cat-anal-porno/ yoki https://uzbxx.ru/category/Domashnee/" style="width: 100%; box-sizing: border-box; background: #0f172a; border: 1px solid #334155; color: #fff; padding: 10px 14px; border-radius: 6px; font-size: 13px;" />
        </div>

        <!-- Parametrlar Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 14px; margin-bottom: 20px;">
            <div>
                <label style="color: #cbd5e1; font-size: 12px; font-weight: bold; display: block; margin-bottom: 6px;">
                    <i class="fa fa-step-backward" style="color: #ff9900;"></i> Boshlang‘ich sahifa:
                </label>
                <input type="number" id="p_page_from" value="1" min="1" max="999" style="width: 100%; box-sizing: border-box; background: #0f172a; border: 1px solid #334155; color: #38bdf8; font-weight: bold; font-size: 15px; padding: 9px; border-radius: 6px;" />
            </div>

            <div>
                <label style="color: #cbd5e1; font-size: 12px; font-weight: bold; display: block; margin-bottom: 6px;">
                    <i class="fa fa-step-forward" style="color: #ff9900;"></i> Oxirgi sahifa:
                </label>
                <input type="number" id="p_page_to" value="3" min="1" max="999" style="width: 100%; box-sizing: border-box; background: #0f172a; border: 1px solid #334155; color: #38bdf8; font-weight: bold; font-size: 15px; padding: 9px; border-radius: 6px;" />
            </div>

            <div>
                <label style="color: #cbd5e1; font-size: 12px; font-weight: bold; display: block; margin-bottom: 6px;">
                    <i class="fa fa-filter" style="color: #ff9900;"></i> Sahifa limiti:
                </label>
                <select id="p_limit_per_page" style="width: 100%; box-sizing: border-box; background: #0f172a; border: 1px solid #334155; color: #fff; padding: 10px; border-radius: 6px; font-size: 13px;">
                    <option value="10">10 ta video / sahifa</option>
                    <option value="20" selected>20 ta video / sahifa</option>
                    <option value="35">35 ta video / sahifa</option>
                    <option value="999">Barchasi (All)</option>
                </select>
            </div>

            <div>
                <label style="color: #cbd5e1; font-size: 12px; font-weight: bold; display: block; margin-bottom: 6px;">
                    <i class="fa fa-folder-open" style="color: #ff9900;"></i> Bo‘lim (Toifa):
                </label>
                <select id="p_category" style="width: 100%; box-sizing: border-box; background: #0f172a; border: 1px solid #334155; color: #fff; padding: 10px; border-radius: 6px; font-size: 13px;">
                    <option value="0" style="color: #10b981; font-weight: bold;">🎯 100% Intellektual Avto-Saralash (Tavsiya)</option>
                    <?php foreach ($cat_list as $c): ?>
                        <option value="<?=$c['id']?>"><?=$c['name']?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label style="color: #cbd5e1; font-size: 12px; font-weight: bold; display: block; margin-bottom: 6px;">
                    <i class="fa fa-tachometer" style="color: #ff9900;"></i> Tezlik rejimi:
                </label>
                <select id="p_speed" style="width: 100%; box-sizing: border-box; background: #0f172a; border: 1px solid #334155; color: #fff; padding: 10px; border-radius: 6px; font-size: 13px;">
                    <option value="250" selected>⚡ Tezkor (0.25s pauza)</option>
                    <option value="100">🚀 Turbo (0.1s pauza)</option>
                    <option value="700">🛡️ Ehtiyotkor (0.7s pauza)</option>
                </select>
            </div>

            <div>
                <label style="color: #cbd5e1; font-size: 12px; font-weight: bold; display: block; margin-bottom: 6px;">
                    <i class="fa fa-hdd-o" style="color: #ff9900;"></i> Saqlash Rejimi:
                </label>
                <select id="p_save_mode" style="width: 100%; box-sizing: border-box; background: #0f172a; border: 1px solid #334155; color: #fff; padding: 10px; border-radius: 6px; font-size: 13px;">
                    <option value="stream" selected>⚡ Oqim / Embed (Tezkor va xavfsiz)</option>
                    <option value="server">💾 Serverga MP4 yuklab olish</option>
                </select>
            </div>
        </div>

        <!-- Boshqaruv Tugmalari -->
        <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <button type="button" id="btnStartParse" style="background: linear-gradient(135deg, #ff9900 0%, #e68a00 100%); color: #000; font-weight: bold; border: none; padding: 12px 28px; border-radius: 6px; font-size: 15px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(255,153,0,0.3);">
                <i class="fa fa-rocket"></i> 🚀 Parslashni Boshlash
            </button>
            <button type="button" id="btnPauseParse" style="display: none; background: #f59e0b; color: #000; font-weight: bold; border: none; padding: 12px 20px; border-radius: 6px; font-size: 14px; cursor: pointer; align-items: center; gap: 6px;">
                <i class="fa fa-pause"></i> ⏸️ Pauza
            </button>
            <button type="button" id="btnStopParse" style="display: none; background: #ef4444; color: #fff; font-weight: bold; border: none; padding: 12px 20px; border-radius: 6px; font-size: 14px; cursor: pointer; align-items: center; gap: 6px;">
                <i class="fa fa-stop"></i> ⏹️ To‘xtatish
            </button>
            <span style="color: #64748b; font-size: 12px;">
                <i class="fa fa-shield"></i> Cloudflare / Nginx 504 Timeout xatosi mutlaqo chiqarilmaydi.
            </span>
        </div>
    </div>

    <!-- Jonli Natijalar va Progress Qismi -->
    <div id="liveProgressArea" style="margin-top: 24px; display: none;">
        <!-- Statistika Vidjeti -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 12px; margin-bottom: 16px;">
            <div style="background: #0f172a; border: 1px solid #1e293b; padding: 12px; border-radius: 6px; text-align: center;">
                <div style="color: #94a3b8; font-size: 11px;">Jami Topilgan</div>
                <div id="statTotal" style="color: #38bdf8; font-size: 20px; font-weight: bold; margin-top: 4px;">0</div>
            </div>
            <div style="background: #0f172a; border: 1px solid #1e293b; padding: 12px; border-radius: 6px; text-align: center;">
                <div style="color: #94a3b8; font-size: 11px;">Yangi Qo‘shildi</div>
                <div id="statAdded" style="color: #10b981; font-size: 20px; font-weight: bold; margin-top: 4px;">0</div>
            </div>
            <div style="background: #0f172a; border: 1px solid #1e293b; padding: 12px; border-radius: 6px; text-align: center;">
                <div style="color: #94a3b8; font-size: 11px;">O‘tkazildi (Bor)</div>
                <div id="statSkipped" style="color: #f59e0b; font-size: 20px; font-weight: bold; margin-top: 4px;">0</div>
            </div>
            <div style="background: #0f172a; border: 1px solid #1e293b; padding: 12px; border-radius: 6px; text-align: center;">
                <div style="color: #94a3b8; font-size: 11px;">Xatolar</div>
                <div id="statErrors" style="color: #ef4444; font-size: 20px; font-weight: bold; margin-top: 4px;">0</div>
            </div>
            <div style="background: #0f172a; border: 1px solid #1e293b; padding: 12px; border-radius: 6px; text-align: center;">
                <div style="color: #94a3b8; font-size: 11px;">Hozirgi Donor / Sahifa</div>
                <div id="statCurPage" style="color: #ff9900; font-size: 16px; font-weight: bold; margin-top: 6px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">-</div>
            </div>
        </div>

        <!-- Progress Bar -->
        <div style="margin-bottom: 16px;">
            <div style="display: flex; justify-content: space-between; font-size: 12px; color: #cbd5e1; margin-bottom: 6px;">
                <span id="progressLabel">Jarayon tayyorlanmoqda...</span>
                <span id="progressPercent" style="font-weight: bold; color: #ff9900;">0%</span>
            </div>
            <div style="background: #0f172a; border-radius: 10px; height: 12px; overflow: hidden; border: 1px solid #334155;">
                <div id="progressBarFill" style="background: linear-gradient(90deg, #ff9900, #10b981); height: 100%; width: 0%; transition: width 0.3s ease;"></div>
            </div>
        </div>

        <!-- Jonli Video Preview Kartochkasi (Oxirgi qayta ishlangan video) -->
        <div id="liveVideoPreviewCard" style="display: none; background: #0f172a; border: 1px solid #1e293b; border-radius: 8px; padding: 12px 14px; margin-bottom: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
            <div style="display: flex; gap: 14px; align-items: center;">
                <img id="previewThumb" src="/designs/no_poster.jpg" style="width: 120px; height: 75px; object-fit: cover; border-radius: 6px; border: 1px solid #334155; flex-shrink: 0;" />
                <div style="flex: 1; min-width: 0;">
                    <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 5px; flex-wrap: wrap;">
                        <span id="previewStatusBadge" style="background: #10b981; color: #000; font-size: 10px; font-weight: bold; padding: 2px 7px; border-radius: 4px;">QO‘SHILDI</span>
                        <span id="previewCatBadge" style="background: #1e293b; color: #ff9900; border: 1px solid #ff9900; font-size: 11px; padding: 2px 8px; border-radius: 10px; font-weight: bold;">Toifa</span>
                        <span id="previewDurationBadge" style="color: #94a3b8; font-size: 11px;"><i class="fa fa-clock-o"></i> 05:00</span>
                        <span id="previewDonorBadge" style="color: #38bdf8; font-size: 11px;"><i class="fa fa-globe"></i> donor</span>
                    </div>
                    <a id="previewTitleLink" href="#" target="_blank" style="color: #fff; font-size: 13px; font-weight: bold; text-decoration: none; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Video nomi</a>
                    <div id="previewTagsLine" style="color: #64748b; font-size: 11px; margin-top: 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Teglar avtomatik shakllantirildi</div>
                </div>
            </div>
        </div>

        <!-- Jonli Terminal Console -->
        <div style="background: #090b10; border: 1px solid #1e293b; border-radius: 6px; overflow: hidden;">
            <div style="background: #11141d; padding: 8px 14px; border-bottom: 1px solid #1e293b; display: flex; justify-content: space-between; align-items: center;">
                <span style="color: #94a3b8; font-size: 12px; font-family: monospace;">
                    <i class="fa fa-terminal" style="color: #10b981;"></i> Jonli Terminal Jurnali
                </span>
                <div style="display: flex; gap: 8px;">
                    <button type="button" id="btnClearConsole" style="background: transparent; color: #64748b; border: none; font-size: 11px; cursor: pointer;">Tozalash</button>
                    <label style="color: #64748b; font-size: 11px; cursor: pointer;">
                        <input type="checkbox" id="chkAutoScroll" checked /> Avto-scroll
                    </label>
                </div>
            </div>
            <div id="liveConsole" style="height: 280px; overflow-y: auto; padding: 12px; font-family: 'Courier New', Courier, monospace; font-size: 12px; line-height: 1.6; color: #cbd5e1;">
                <div style="color: #64748b;">[Tizim] Dvigatel tayyor. Parslashni boshlash uchun tugmani bosing...</div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 2. YAGONA HAVOLA (URL) ORQALI VIDEO QO'SHISH                              -->
<!-- ========================================================================= -->
<div class="adm-card" style="background: #16181e; border: 1px solid #282d3d; border-radius: 8px; padding: 20px; margin-bottom: 24px;">
    <h3 style="color: #ff9900; margin: 0 0 8px 0; font-size: 16px;">
        <i class="fa fa-link"></i> 2. Yagona Havola Orqali Tezkor Qo‘shish
    </h3>
    <p style="color: #94a3b8; font-size: 12px; margin: 0 0 14px 0;">
        Istalgan bitta videoni havolasini kiriting (sexlar.link, arhivporno.watch, uzbxx.ru, uzporno.website):
    </p>

    <div style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center;">
        <input type="url" id="singleVideoUrl" placeholder="https://sexlar.link/sekis/video-nomi/ yoki https://arhivporno.watch/..." style="flex: 1; min-width: 280px; background: #0f172a; border: 1px solid #334155; color: #fff; padding: 10px 14px; border-radius: 6px; font-size: 13px;" />
        
        <select id="singleCategory" style="width: 220px; background: #0f172a; border: 1px solid #334155; color: #fff; padding: 10px; border-radius: 6px; font-size: 13px;">
            <option value="0">🎯 Avtomatik (Mavzuga qarab)</option>
            <?php foreach ($cat_list as $c): ?>
                <option value="<?=$c['id']?>"><?=$c['name']?></option>
            <?php endforeach; ?>
        </select>

        <select id="singleSaveMode" style="width: 170px; background: #0f172a; border: 1px solid #334155; color: #fff; padding: 10px; border-radius: 6px; font-size: 13px;">
            <option value="stream" selected>⚡ Oqim / Embed</option>
            <option value="server">💾 Serverga MP4</option>
        </select>

        <button type="button" id="btnSingleParse" style="background: #2563eb; color: #fff; font-weight: bold; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-size: 13px; display: inline-flex; align-items: center; gap: 6px;">
            <i class="fa fa-download"></i> Yuklash
        </button>
    </div>
    <div id="singleResultBox" style="margin-top: 12px; display: none;"></div>
</div>

<!-- ========================================================================= -->
<!-- 3. AVTOMATIK FON PARSERI (CRON TIZIMI)                                    -->
<!-- ========================================================================= -->
<div class="adm-card" style="background: #111827; border-left: 4px solid #38bdf8; border-radius: 8px; padding: 20px; margin-bottom: 24px;">
    <h3 style="color: #38bdf8; margin: 0 0 8px 0; font-size: 16px;">
        <i class="fa fa-clock-o"></i> 3. Avtomatik Fon Parseri (CRON)
    </h3>
    <p style="color: #94a3b8; font-size: 13px; line-height: 1.5; margin: 0 0 10px 0;">
        Serveringizda (FastPanel, cPanel yoki crontab) muntazam fon yuklashini yoqish uchun quyidagi buyruqdan foydalaning:
    </p>
    <div style="background: #030712; border: 1px solid #1f2937; padding: 10px 14px; border-radius: 6px; font-family: monospace; color: #10b981; font-size: 12px; word-break: break-all; margin-bottom: 8px;">
        */20 * * * * curl -s "<?=$protocol . filter($_SERVER['HTTP_HOST'] ?? 'sekschi.online')?>/autocomplete.php?key=<?=htmlspecialchars($settings['cron'] ?? '')?>" > /dev/null 2>&1
    </div>
    <span style="color: #64748b; font-size: 12px;">Har 20 daqiqada avtomatik donorlarni aylanib yangi videolarni bazaga qo‘shib boradi.</span>
</div>

<!-- ========================================================================= -->
<!-- CLIENT-SIDE ASINXRON RUNNER SCRIPT                                        -->
<!-- ========================================================================= -->
<script>
$(document).ready(function() {
    let isRunning = false;
    let isPaused = false;
    let shouldStop = false;

    let queue = [];
    let curIndex = 0;
    let totalAdded = 0;
    let totalSkipped = 0;
    let totalErrors = 0;
    let startTime = null;

    function getNowTime() {
        const d = new Date();
        return d.toTimeString().split(' ')[0];
    }

    function playSuccessChime() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(587.33, ctx.currentTime);
            osc.frequency.setValueAtTime(880, ctx.currentTime + 0.15);
            gain.gain.setValueAtTime(0.15, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.4);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.4);
        } catch (e) {}
    }

    function addLog(type, message) {
        const timeStr = getNowTime();
        let color = '#cbd5e1';
        let icon = 'ℹ️';

        if (type === 'success') {
            color = '#10b981';
            icon = '✅ [Qo‘shildi]';
        } else if (type === 'skip') {
            color = '#f59e0b';
            icon = '⚠️ [O‘tkazildi]';
        } else if (type === 'error') {
            color = '#ef4444';
            icon = '❌ [Xatolik]';
        } else if (type === 'page') {
            color = '#38bdf8';
            icon = '📄 [Sahifa]';
        } else if (type === 'summary') {
            color = '#a855f7';
            icon = '🏁 [Yakun]';
        }

        const line = $('<div style="color: ' + color + '; margin-bottom: 3px;">' +
            '<span style="color:#64748b;">[' + timeStr + ']</span> ' + icon + ' ' + message +
        '</div>');

        $('#liveConsole').append(line);

        if ($('#chkAutoScroll').is(':checked')) {
            const el = document.getElementById('liveConsole');
            if (el) el.scrollTop = el.scrollHeight;
        }
    }

    $('#btnClearConsole').on('click', function() {
        $('#liveConsole').empty();
    });

    // 0. PRESETS BOSILGANDA AVTO-TO'LDIRISH
    $('.adm-preset-btn').on('click', function() {
        $('.adm-preset-btn').removeClass('active');
        $(this).addClass('active');

        const donor = $(this).data('donor');
        const customUrl = $(this).data('url') || '';
        const pageFrom = $(this).data('from') || 1;
        const pageTo = $(this).data('to') || 3;

        $('input[name="p_donor"][value="' + donor + '"]').prop('checked', true);
        $('#p_custom_url').val(customUrl);
        $('#p_page_from').val(pageFrom);
        $('#p_page_to').val(pageTo);
        $('#p_category').val(0); // 100% Intellektual avto-saralash

        addLog('info', 'Shablon tanlandi: <b>' + $(this).text().trim() + '</b>');
    });

    // Tarmoq uzilishlariga chidamli AJAX chaqiruvi (3 marta avto-qayta urinish)
    async function ajaxWithRetry(ajaxOptions, maxRetries = 3, delayMs = 1200) {
        for (let attempt = 1; attempt <= maxRetries; attempt++) {
            try {
                return await $.ajax(ajaxOptions);
            } catch (err) {
                if (attempt >= maxRetries || shouldStop) {
                    throw err;
                }
                addLog('info', '⚠️ Tarmoq xatosi, ' + attempt + '-qayta urinish (' + (delayMs / 1000) + 's dan so‘ng)...');
                await new Promise(r => setTimeout(r, delayMs));
            }
        }
    }

    // 1. ASOSIY PARSER BOSHLASH
    $('#btnStartParse').on('click', async function() {
        if (isRunning && isPaused) {
            isPaused = false;
            $('#liveStatusText').text('Davom etmoqda...');
            $('#btnPauseParse').html('<i class="fa fa-pause"></i> ⏸️ Pauza');
            addLog('info', 'Parslash davom ettirildi.');
            return;
        }

        if (isRunning) return;

        const selectedDonor = $('input[name="p_donor"]:checked').val() || 'sexlar';
        const customUrl = $('#p_custom_url').val().trim();
        const pageFrom = parseInt($('#p_page_from').val()) || 1;
        const pageTo = parseInt($('#p_page_to').val()) || 1;
        const limitPerPage = parseInt($('#p_limit_per_page').val()) || 20;
        const category = parseInt($('#p_category').val()) || 0;
        const saveMode = $('#p_save_mode').val() || 'stream';
        const pauseDelay = parseInt($('#p_speed').val()) || 250;

        if (pageTo < pageFrom) {
            alert("Oxirgi sahifa boshlang‘ich sahifadan kichik bo‘lishi mumkin emas!");
            return;
        }

        const donorsToRun = (selectedDonor === 'all') ? ['sexlar', 'arhivporno', 'uzbxx', 'uzporno'] : [selectedDonor];

        isRunning = true;
        isPaused = false;
        shouldStop = false;
        queue = [];
        curIndex = 0;
        totalAdded = 0;
        totalSkipped = 0;
        totalErrors = 0;
        startTime = new Date();

        $('#statTotal').text('0');
        $('#statAdded').text('0');
        $('#statSkipped').text('0');
        $('#statErrors').text('0');
        $('#statCurPage').text(pageFrom);

        $('#liveProgressArea').slideDown();
        $('#liveStatusBadge').show().css('display', 'inline-flex');
        $('#liveStatusText').text('Ishlamoqda...');
        $('#btnStartParse').prop('disabled', true).css('opacity', '0.6');
        $('#btnPauseParse').show().css('display', 'inline-flex').html('<i class="fa fa-pause"></i> ⏸️ Pauza');
        $('#btnStopParse').show().css('display', 'inline-flex');

        addLog('info', '🚀 <b>Parslash boshlandi!</b> Donorlar: <b>' + donorsToRun.join(', ') + '</b>, Sahifalar: ' + pageFrom + ' – ' + pageTo);

        // Barcha tanlangan donorlar bo'yicha ketma-ket parslash
        for (const curDonor of donorsToRun) {
            if (shouldStop) break;

            addLog('page', '🌐 Donorni yuklash boshlanmoqda: <b>' + curDonor + '</b>');

            for (let pg = pageFrom; pg <= pageTo; pg++) {
                if (shouldStop) break;
                while (isPaused) {
                    await new Promise(r => setTimeout(r, 500));
                    if (shouldStop) break;
                }
                if (shouldStop) break;

                $('#statCurPage').text(curDonor + ' (p' + pg + '/' + pageTo + ')');
                $('#progressLabel').text(curDonor + ' [Sahifa ' + pg + '] katalogi tekshirilmoqda...');
                addLog('page', '<b>[' + curDonor + ']</b> Sahifa <b>' + pg + '</b> havolalari olinmoqda...');

                let pageItems = [];
                try {
                    const catRes = await ajaxWithRetry({
                        url: '/control.html?func=parsing',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            ajax_action: 'get_catalog',
                            donor: curDonor,
                            page: pg,
                            custom_url: (curDonor === selectedDonor ? customUrl : '')
                        }
                    });

                    if (catRes && catRes.status === 'success' && catRes.items && catRes.items.length > 0) {
                        pageItems = catRes.items;
                        addLog('info', '<b>[' + curDonor + ']</b> Sahifa ' + pg + ': <b>' + pageItems.length + '</b> ta video topildi.');
                    } else {
                        addLog('error', '<b>[' + curDonor + ']</b> Sahifa ' + pg + ': video havolalari topilmadi yoki sahifalar tugadi.');
                        break;
                    }
                } catch (err) {
                    addLog('error', '<b>[' + curDonor + ']</b> Sahifa ' + pg + ' yuklashda xatolik: ' + err.statusText);
                    break;
                }

                // Navbatga qo'shish
                const sliceCount = Math.min(limitPerPage, pageItems.length);
                for (let i = 0; i < sliceCount; i++) {
                    const itm = pageItems[i];
                    itm._donor = curDonor;
                    queue.push(itm);
                }
                $('#statTotal').text(queue.length);

                // Ushbu sahifadagi videolarni birma-bir parslash
                while (curIndex < queue.length) {
                    if (shouldStop) break;
                    while (isPaused) {
                        await new Promise(r => setTimeout(r, 500));
                        if (shouldStop) break;
                    }
                    if (shouldStop) break;

                    const item = queue[curIndex];
                    const itemNum = curIndex + 1;
                    const percent = Math.round((itemNum / queue.length) * 100);

                    $('#progressPercent').text(percent + '%');
                    $('#progressBarFill').css('width', percent + '%');
                    $('#progressLabel').text('[' + item._donor + '] Parslanmoqda: ' + itemNum + ' / ' + queue.length + ' (' + (item.title || 'Video') + ')');

                    try {
                        const parseRes = await ajaxWithRetry({
                            url: '/control.html?func=parsing',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                ajax_action: 'parse_video',
                                donor: item._donor,
                                url: item.url,
                                poster: item.poster || '',
                                duration: item.duration || '',
                                title: item.title || '',
                                category_hint: item.category_hint || '',
                                category: category,
                                save_mode: saveMode
                            }
                        }, 2, 1000);

                        if (parseRes && parseRes.status === 'success') {
                            totalAdded++;
                            $('#statAdded').text(totalAdded);
                            addLog('success', (parseRes.message || parseRes.title));

                            // Jonli Video Preview Kartochkasini yangilash
                            $('#liveVideoPreviewCard').slideDown();
                            if (parseRes.screenshot) {
                                $('#previewThumb').attr('src', parseRes.screenshot);
                            } else if (item.poster) {
                                $('#previewThumb').attr('src', item.poster);
                            }
                            $('#previewTitleLink').text(parseRes.title || item.title || 'Video').attr('href', '/watch/' + (parseRes.translit || '') + '.html');
                            $('#previewCatBadge').text(parseRes.category_name || 'Avtomatik');
                            $('#previewDurationBadge').html('<i class="fa fa-clock-o"></i> ' + (parseRes.duration || item.duration || '05:00'));
                            $('#previewDonorBadge').html('<i class="fa fa-globe"></i> ' + item._donor);
                            $('#previewStatusBadge').css({background: '#10b981', color: '#000'}).text('QO‘SHILDI');
                        } else if (parseRes && parseRes.status === 'skip') {
                            totalSkipped++;
                            $('#statSkipped').text(totalSkipped);
                            addLog('skip', (parseRes.message || 'Allaqachon mavjud'));

                            $('#liveVideoPreviewCard').slideDown();
                            if (item.poster) $('#previewThumb').attr('src', item.poster);
                            $('#previewTitleLink').text(item.title || 'Video').attr('href', '#');
                            $('#previewStatusBadge').css({background: '#f59e0b', color: '#000'}).text('ALLAQACHON BOR');
                            $('#previewDonorBadge').html('<i class="fa fa-globe"></i> ' + item._donor);
                        } else {
                            totalErrors++;
                            $('#statErrors').text(totalErrors);
                            addLog('error', (parseRes ? parseRes.message : 'Xatolik yuz berdi'));
                        }
                    } catch (parseErr) {
                        totalErrors++;
                        $('#statErrors').text(totalErrors);
                        addLog('error', 'Tarmoq xatosi: ' + parseErr.statusText + ' (' + item.url + ')');
                    }

                    curIndex++;
                    await new Promise(r => setTimeout(r, pauseDelay));
                }

                await new Promise(r => setTimeout(r, 600));
            }
        }

        // Yakunlash
        isRunning = false;
        isPaused = false;
        $('#liveStatusText').text('Yakunlandi');
        $('#btnStartParse').prop('disabled', false).css('opacity', '1');
        $('#btnPauseParse').hide();
        $('#btnStopParse').hide();
        $('#progressBarFill').css('width', '100%');
        $('#progressPercent').text('100%');
        $('#progressLabel').text('Barcha vazifalar muvaffaqiyatli yakunlandi!');

        if (totalAdded > 0) {
            playSuccessChime();
        }

        addLog('summary', '🏁 <b>PARSLASH YAKUNLANDI!</b> Jami: <b>' + totalAdded + '</b> ta yangi qo‘shildi, <b>' + totalSkipped + '</b> ta o‘tkazildi, <b>' + totalErrors + '</b> ta xatolik.');
    });

    // 2. PAUZA TUGMASI
    $('#btnPauseParse').on('click', function() {
        if (!isRunning) return;
        isPaused = !isPaused;
        if (isPaused) {
            $(this).html('<i class="fa fa-play"></i> ▶️ Davom ettirish');
            $('#liveStatusText').text('Pauza');
            addLog('info', '⏸️ Parslash vaqtincha to‘xtatildi (Pauza).');
        } else {
            $(this).html('<i class="fa fa-pause"></i> ⏸️ Pauza');
            $('#liveStatusText').text('Ishlamoqda...');
            addLog('info', '▶️ Parslash qayta tiklandi.');
        }
    });

    // 3. TO'XTATISH TUGMASI
    $('#btnStopParse').on('click', function() {
        if (!confirm('Parslashni haqiqatan ham to‘xtatmoqchimisiz?')) return;
        shouldStop = true;
        isPaused = false;
        addLog('error', '⏹️ Foydalanuvchi tomonidan to‘xtatildi.');
    });

    // 4. BITTA VIDEO HAVOLASINI YUKLASH (SINGLE URL)
    $('#btnSingleParse').on('click', async function() {
        const url = $('#singleVideoUrl').val().trim();
        if (!url) {
            alert('Iltimos, video havolasini kiriting!');
            return;
        }

        const category = $('#singleCategory').val();
        const saveMode = $('#singleSaveMode').val();
        const btn = $(this);

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Yuklanmoqda...');
        $('#singleResultBox').show().html('<span style="color:#38bdf8;"><i class="fa fa-spinner fa-spin"></i> Video ma’lumotlari tahlil qilinmoqda va yuklanmoqda...</span>');

        try {
            const res = await ajaxWithRetry({
                url: '/control.html?func=parsing',
                type: 'POST',
                dataType: 'json',
                data: {
                    ajax_action: 'parse_video',
                    url: url,
                    category: category,
                    save_mode: saveMode
                }
            }, 2, 1000);

            if (res && res.status === 'success') {
                $('#singleResultBox').html('<div style="background:#064e3b; border:1px solid #10b981; color:#34d399; padding:12px; border-radius:6px; display:flex; gap:10px; align-items:center;">' +
                    (res.screenshot ? '<img src="' + res.screenshot + '" style="width:70px; height:45px; object-fit:cover; border-radius:4px;" />' : '') +
                    '<div><i class="fa fa-check-circle"></i> ' + (res.message || 'Muvaffaqiyatli qo‘shildi!') + '</div>' +
                '</div>');
                $('#singleVideoUrl').val('');
                playSuccessChime();
            } else if (res && res.status === 'skip') {
                $('#singleResultBox').html('<div style="background:#451a03; border:1px solid #f59e0b; color:#fbbf24; padding:12px; border-radius:6px;">' +
                    '<i class="fa fa-info-circle"></i> ' + (res.message || 'Allaqachon mavjud!') +
                '</div>');
            } else {
                $('#singleResultBox').html('<div style="background:#450a0a; border:1px solid #ef4444; color:#f87171; padding:12px; border-radius:6px;">' +
                    '<i class="fa fa-times-circle"></i> ' + (res.message || 'Xatolik yuz berdi') +
                '</div>');
            }
        } catch (e) {
            $('#singleResultBox').html('<div style="background:#450a0a; border:1px solid #ef4444; color:#f87171; padding:12px; border-radius:6px;">' +
                '<i class="fa fa-times-circle"></i> Tarmoq xatoligi: ' + e.statusText +
            '</div>');
        } finally {
            btn.prop('disabled', false).html('<i class="fa fa-download"></i> Yuklash');
        }
    });

    // 5. 404 RASMLARNI TUZATISH TUGMASI
    $('#btnRepairScreenshots').on('click', async function() {
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Tekshirilmoqda...');
        try {
            const res = await ajaxWithRetry({
                url: '/control.html?func=parsing',
                type: 'POST',
                dataType: 'json',
                data: { ajax_action: 'repair_screenshots' }
            }, 2, 1000);
            alert(res.message || 'Tuzatildi!');
        } catch (e) {
            alert('Xatolik: ' + e.statusText);
        } finally {
            btn.prop('disabled', false).html('<i class="fa fa-wrench"></i> Singan / 404 rasmlarni tuzatish');
        }
    });
});
</script>
