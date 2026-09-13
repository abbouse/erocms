<?php

/*
 * erocms Universal Video Parser Moduli
 * Qo'llab-quvvatlanuvchi donor saytlar:
 * 1. sexlar.link (O'zbek seks videolari - Yangi & Tezkor)
 * 2. arhivporno.watch (cat-uzbekskii-seks arxivi)
 * 3. uzbxx.ru
 * 4. uzporno.website
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

// Barcha kategoriyalar ro'yxatini olish
$cat_list = [];
$cats_query = $mysqli->query("SELECT id, name, translit FROM ero_categories ORDER BY name ASC");
if ($cats_query) {
    while ($r = $cats_query->fetch_assoc()) {
        $cat_list[] = $r;
    }
}

$logs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action_type = filter($_POST['action_type'] ?? '');
        $category_choice = abs(intval($_POST['category'] ?? 0));
        $save_mode = filter($_POST['save_mode'] ?? 'stream');

        // 1. HECH QANDAY URLSIZ ENG YANGI VIDEOLARNI AVTO-YUKLASH (1-BOSISHDA)
        if ($action_type === 'auto_all') {
            $limit_count = min(30, max(1, abs(intval($_POST['auto_count'] ?? 10))));
            $donor_choice = filter($_POST['auto_donor'] ?? 'sexlar');

            $all_items = [];

            // 1. sexlar.link
            if ($donor_choice === 'all' || $donor_choice === 'sexlar') {
                $links_sexlar = parser_get_catalog_links_sexlar(1);
                foreach ($links_sexlar as $item) {
                    $all_items[] = [
                        'donor' => 'sexlar',
                        'url' => $item['url'],
                        'poster' => $item['poster'] ?? '',
                        'duration' => $item['duration'] ?? '05:00'
                    ];
                }
            }

            // 2. arhivporno.watch
            if ($donor_choice === 'all' || $donor_choice === 'arhivporno') {
                $links_arhiv = parser_get_catalog_links_arhivporno(1);
                foreach ($links_arhiv as $item) {
                    $all_items[] = [
                        'donor' => 'arhivporno',
                        'url' => $item['url'],
                        'poster' => $item['poster'] ?? '',
                        'duration' => $item['duration'] ?? '05:00'
                    ];
                }
            }

            // 3. uzbxx.ru
            if ($donor_choice === 'all' || $donor_choice === 'uzbxx') {
                $links_uzbxx = parser_get_catalog_links_uzbxx(1);
                foreach ($links_uzbxx as $link) {
                    $all_items[] = ['donor' => 'uzbxx', 'url' => $link];
                }
            }

            // 4. uzporno.website
            if ($donor_choice === 'all' || $donor_choice === 'uzporno') {
                $links_uzporno = parser_get_catalog_links_uzporno(1);
                foreach ($links_uzporno as $link) {
                    $all_items[] = ['donor' => 'uzporno', 'url' => $link];
                }
            }

            if (empty($all_items)) {
                $logs[] = ['status' => 'error', 'message' => 'Donor saytlardan yangi videolar topilmadi.'];
            } else {
                if ($donor_choice === 'all') {
                    shuffle($all_items);
                }

                $added = 0;
                foreach ($all_items as $item) {
                    if ($added >= $limit_count) break;

                    $res = null;
                    if ($item['donor'] === 'sexlar') {
                        $res = parse_video_sexlar($item['url'], $category_choice, $save_mode, $mysqli, $settings, $width_S, $height_S, $item);
                    } elseif ($item['donor'] === 'arhivporno') {
                        $res = parse_video_arhivporno($item['url'], $category_choice, $save_mode, $mysqli, $settings, $width_S, $height_S, $item);
                    } elseif ($item['donor'] === 'uzbxx') {
                        $res = parse_video_uzbxx($item['url'], $category_choice, $save_mode, $mysqli, $settings, $width_S, $height_S);
                    } elseif ($item['donor'] === 'uzporno') {
                        $res = parse_video_uzporno($item['url'], $category_choice, $save_mode, $mysqli, $settings, $width_S, $height_S);
                    }

                    if ($res) {
                        $logs[] = $res;
                        if (!empty($res['status']) && $res['status'] === 'success') {
                            $added++;
                        }
                    }
                    usleep(200000); // 0.2 soniya pauza
                }

                // Keshni tozalash
                $doc_root = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2);
                @array_map('unlink', glob($doc_root . '/content/cache/*.html'));
            }
        }

        // 2. KATALOG / SAHIFA BO'YICHA OMMAVIY PARSLASH
        elseif ($action_type === 'mass_parse') {
            $donor = filter($_POST['donor'] ?? 'sexlar');
            $page_num = max(1, abs(intval($_POST['page_num'] ?? 1)));
            $limit_count = min(30, max(1, abs(intval($_POST['count'] ?? 10))));
            $custom_url = trim($_POST['custom_catalog_url'] ?? '');

            $items_to_parse = [];

            if (!empty($custom_url)) {
                if (stripos($custom_url, 'sexlar.link') !== false) {
                    $donor = 'sexlar';
                    $items_to_parse = parser_get_catalog_links_sexlar($custom_url);
                } elseif (stripos($custom_url, 'arhivporno.watch') !== false) {
                    $donor = 'arhivporno';
                    $items_to_parse = parser_get_catalog_links_arhivporno($custom_url);
                } elseif (stripos($custom_url, 'uzporno.website') !== false) {
                    $donor = 'uzporno';
                    $raw_links = parser_get_catalog_links_uzporno($page_num);
                    foreach ($raw_links as $l) $items_to_parse[] = ['url' => $l];
                } else {
                    $donor = 'uzbxx';
                    $raw_links = parser_get_catalog_links_uzbxx($page_num);
                    foreach ($raw_links as $l) $items_to_parse[] = ['url' => $l];
                }
            } else {
                if ($donor === 'sexlar') {
                    $items_to_parse = parser_get_catalog_links_sexlar($page_num);
                } elseif ($donor === 'arhivporno') {
                    $items_to_parse = parser_get_catalog_links_arhivporno($page_num);
                } elseif ($donor === 'uzbxx') {
                    $raw_links = parser_get_catalog_links_uzbxx($page_num);
                    foreach ($raw_links as $l) $items_to_parse[] = ['url' => $l];
                } elseif ($donor === 'uzporno') {
                    $raw_links = parser_get_catalog_links_uzporno($page_num);
                    foreach ($raw_links as $l) $items_to_parse[] = ['url' => $l];
                }
            }

            if (empty($items_to_parse)) {
                $logs[] = ['status' => 'error', 'message' => "Ushbu sahifadan video havolalari topilmadi ({$donor}, sahifa {$page_num})."];
            } else {
                $added = 0;
                foreach ($items_to_parse as $item) {
                    if ($added >= $limit_count) break;
                    $v_url = is_array($item) ? $item['url'] : $item;

                    $res = null;
                    if ($donor === 'sexlar') {
                        $res = parse_video_sexlar($v_url, $category_choice, $save_mode, $mysqli, $settings, $width_S, $height_S, is_array($item) ? $item : []);
                    } elseif ($donor === 'arhivporno') {
                        $res = parse_video_arhivporno($v_url, $category_choice, $save_mode, $mysqli, $settings, $width_S, $height_S, is_array($item) ? $item : []);
                    } elseif ($donor === 'uzbxx') {
                        $res = parse_video_uzbxx($v_url, $category_choice, $save_mode, $mysqli, $settings, $width_S, $height_S);
                    } elseif ($donor === 'uzporno') {
                        $res = parse_video_uzporno($v_url, $category_choice, $save_mode, $mysqli, $settings, $width_S, $height_S);
                    }

                    if ($res) {
                        $logs[] = $res;
                        if (!empty($res['status']) && $res['status'] === 'success') {
                            $added++;
                        }
                    }
                    usleep(200000);
                }

                $doc_root = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2);
                @array_map('unlink', glob($doc_root . '/content/cache/*.html'));
            }
        }

        // 3. YAGONA HAVOLA (URL) ORQALI VIDEO QO'SHISH
        elseif ($action_type === 'single_url') {
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
                    $logs[] = ['status' => 'error', 'message' => 'Noma‘lum havola! Faqat sexlar.link, arhivporno.watch, uzbxx.ru yoki uzporno.website havolalari qabul qilinadi.'];
                }

                $doc_root = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2);
                @array_map('unlink', glob($doc_root . '/content/cache/*.html'));
            }
        }
    } catch (Throwable $e) {
        $logs[] = ['status' => 'error', 'message' => 'Xatolik: ' . $e->getMessage() . ' (' . basename($e->getFile()) . ':' . $e->getLine() . ')'];
    }
}
?>

<div class="functions_data" style="border-left: 4px solid #ff9900; background: #1a1815; padding: 16px; margin-bottom: 20px;">
    <h2 style="color:#ff9900; margin: 0 0 6px 0;"><i class="fa fa-cloud-download"></i> Universal Video Parser</h2>
    <p style="color:#ccc; font-size:13px; line-height: 1.5; margin: 0;">
        Ushbu bo‘lim orqali <b>sexlar.link</b>, <b>arhivporno.watch</b>, <b>uzbxx.ru</b> va <b>uzporno.website</b> saytlaridan yangi videolarni bir bosishda saytingizdagi istalgan bo‘limga yoki avtomatik moslab yuklab olishingiz mumkin.
    </p>
</div>

<?php if (!empty($logs)): ?>
<div class="functions_data" style="background:#111; border:1px solid #ff9900; margin-bottom:20px; padding: 15px;">
    <h3 style="color:#ff9900; margin:0 0 12px 0;"><i class="fa fa-list-alt"></i> Parslash natijalari:</h3>
    <div style="max-height: 280px; overflow-y: auto; font-family: monospace; font-size: 13px; background:#000; padding:12px; border-radius:4px;">
    <?php foreach ($logs as $log): ?>
        <?php if (!empty($log['status']) && $log['status'] === 'success'): ?>
            <div style="color: #28a745; margin-bottom: 6px;"><i class="fa fa-check-circle"></i> [Qo‘shildi] <?=$log['message']?></div>
        <?php elseif (!empty($log['status']) && $log['status'] === 'skip'): ?>
            <div style="color: #ffc107; margin-bottom: 6px;"><i class="fa fa-info-circle"></i> [O‘tkazildi] <?=$log['message']?></div>
        <?php else: ?>
            <div style="color: #dc3545; margin-bottom: 6px;"><i class="fa fa-times-circle"></i> [Xatolik] <?=($log['message'] ?? 'Nomaʼlum xatolik')?></div>
        <?php endif; ?>
    <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- 1. URLSIZ BIR BOSISHDA TEZKOR AVTO-YUKLASH -->
<div class="functions_data" style="background:#1c1916; border: 2px solid #ff9900; margin-bottom:20px; padding:18px; border-radius: 4px;">
    <h3 style="color:#ff9900; margin:0 0 8px 0;"><i class="fa fa-bolt"></i> 1. Bir bosishda yangi videolarni avto-yuklash (URL KERAK EMAS!)</h3>
    <p style="color:#bbb; font-size:13px; margin: 0 0 15px 0;">
        Hech qanday havola kiritish shart emas! Donor saytni va bo‘limni tanlang, tugmani bosing — videolar darhol saytingizga yuklanadi:
    </p>
    <form method="post">
        <input type="hidden" name="action_type" value="auto_all" />
        <p style="line-height: 2; margin-bottom: 12px;">
            <b>Qaysi donor saytdan yuklansin:</b><br />
            <label style="margin-right:20px; cursor:pointer;">
                <input type="radio" name="auto_donor" value="sexlar" checked /> <b style="color:#28a745;">sexlar.link</b> (Yangi o‘zbek videolari - Tavsiya)
            </label>
            <label style="margin-right:20px; cursor:pointer;">
                <input type="radio" name="auto_donor" value="all" /> <b>Barcha donorlardan (sexlar + arhivporno + uzbxx + uzporno)</b>
            </label>
            <label style="margin-right:20px; cursor:pointer;">
                <input type="radio" name="auto_donor" value="arhivporno" /> <b>arhivporno.watch</b>
            </label>
            <label style="margin-right:20px; cursor:pointer;">
                <input type="radio" name="auto_donor" value="uzbxx" /> <b>uzbxx.ru</b>
            </label>
            <label style="cursor:pointer;">
                <input type="radio" name="auto_donor" value="uzporno" /> <b>uzporno.website</b>
            </label>
        </p>

        <div style="display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end;">
            <div style="flex: 1; min-width: 240px;">
                <b>Qaysi bo‘limga yuklansin:</b><br />
                <select name="category" class="injected" style="width: 100%; margin-top: 4px; padding: 7px; background: #222; color: #fff; border: 1px solid #555; border-radius: 4px;">
                    <option value="0" style="color: #ff9900; font-weight: bold;">🎯 Avtomatik (Mavzuga qarab: Minet, Rakom, Anal, Sperma yoki O‘zbek)</option>
                    <?php foreach ($cat_list as $c): ?>
                        <option value="<?=$c['id']?>" <?=($c['translit'] === 'uzbek' ? 'selected style="font-weight:bold; color:#ff9900;"' : '')?>>
                            <?=$c['name']?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="width: 130px;">
                <b>Videolar soni:</b><br />
                <select name="auto_count" class="injected" style="width: 100%; margin-top: 4px; padding: 7px; background: #222; color: #fff; border: 1px solid #555; border-radius: 4px;">
                    <option value="5">5 ta video</option>
                    <option value="10" selected>10 ta video</option>
                    <option value="15">15 ta video</option>
                    <option value="20">20 ta video</option>
                    <option value="30">30 ta video</option>
                </select>
            </div>

            <div style="width: 200px;">
                <b>Saqlash rejimi:</b><br />
                <select name="save_mode" class="injected" style="width: 100%; margin-top: 4px; padding: 7px; background: #222; color: #fff; border: 1px solid #555; border-radius: 4px;">
                    <option value="stream" selected>Oqim / Embed (Tavsiya - tezkor)</option>
                    <option value="server">Serverga MP4 yuklash</option>
                </select>
            </div>
        </div>

        <p style="margin-top: 18px; margin-bottom: 0;">
            <button type="submit" class="byecos" style="font-size:15px; padding:12px 30px; background:#ff9900; color:#000; font-weight:bold; border:none; cursor:pointer; border-radius: 4px;">
                <i class="fa fa-cloud-download"></i> 🚀 Videolarni yuklashni boshlash
            </button>
        </p>
    </form>
</div>

<!-- 2. YAGONA HAVOLA (URL) ORQALI VIDEO QO'SHISH -->
<div class="functions_data" style="margin-bottom:20px; padding:16px;">
    <h3 style="color:#ff9900; margin:0 0 8px 0;"><i class="fa fa-link"></i> 2. Yagona havola (URL) orqali bitta video qo‘shish</h3>
    <p style="color:#888; font-size:12px; margin:0 0 12px 0;">sexlar.link, arhivporno.watch, uzbxx.ru yoki uzporno.website dagi bitta video havolasini kiriting:</p>
    <form method="post">
        <input type="hidden" name="action_type" value="single_url" />
        <p style="margin-bottom: 12px;">
            <input type="url" name="single_url" class="injected" placeholder="Masalan: https://sexlar.link/sekis/rastyanul-chlenom-mokruyu-pisku-uzbechki/ yoki https://arhivporno.watch/video/..." required style="width:100%; font-size:14px; padding:8px;" />
        </p>
        <div style="display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end;">
            <div style="flex: 1; min-width: 240px;">
                <b>Qaysi bo‘limga tushsin:</b><br />
                <select name="category" class="injected" style="width:100%; margin-top:4px; padding:7px; background:#222; color:#fff; border:1px solid #555; border-radius:4px;">
                    <option value="0">🎯 Avtomatik aniqlash (Mavzuga qarab)</option>
                    <?php foreach ($cat_list as $c): ?>
                        <option value="<?=$c['id']?>" <?=($c['translit'] === 'uzbek' ? 'selected' : '')?>><?=$c['name']?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="width: 200px;">
                <b>Saqlash rejimi:</b><br />
                <select name="save_mode" class="injected" style="width:100%; margin-top:4px; padding:7px; background:#222; color:#fff; border:1px solid #555; border-radius:4px;">
                    <option value="stream" selected>Oqim / Embed (Tezkor)</option>
                    <option value="server">Serverga MP4 yuklash</option>
                </select>
            </div>
            <div>
                <button type="submit" class="byecos" style="font-size:14px; padding:8px 24px;">
                    <i class="fa fa-download"></i> Yuklash
                </button>
            </div>
        </div>
    </form>
</div>

<!-- 3. KATALOG SAHIFALARI BO'YICHA OMMAVIY PARSLASH -->
<div class="functions_data" style="margin-bottom:20px; padding:16px;">
    <h3 style="color:#ff9900; margin:0 0 8px 0;"><i class="fa fa-tasks"></i> 3. Katalog sahifalari bo‘yicha yuklash (Sahifa raqami bo‘yicha)</h3>
    <p style="color:#888; font-size:12px; margin:0 0 12px 0;">Donor saytning istalgan sahifasidagi (2, 3, 4...) barcha videolarni birdaniga ko‘chirib olish:</p>
    <form method="post">
        <input type="hidden" name="action_type" value="mass_parse" />
        <p style="line-height: 2; margin-bottom: 12px;">
            <b>Donor sayt:</b><br />
            <label style="margin-right:20px; cursor:pointer;">
                <input type="radio" name="donor" value="sexlar" checked /> <b style="color:#28a745;">sexlar.link</b>
            </label>
            <label style="margin-right:20px; cursor:pointer;">
                <input type="radio" name="donor" value="arhivporno" /> <b>arhivporno.watch</b>
            </label>
            <label style="margin-right:20px; cursor:pointer;">
                <input type="radio" name="donor" value="uzbxx" /> <b>uzbxx.ru</b>
            </label>
            <label style="cursor:pointer;">
                <input type="radio" name="donor" value="uzporno" /> <b>uzporno.website</b>
            </label>
        </p>
        <div style="display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end;">
            <div style="width: 110px;">
                <b>Sahifa raqami:</b><br />
                <input type="number" name="page_num" value="2" min="1" max="200" class="injected" style="width:100%; margin-top:4px; padding:7px; background:#222; color:#fff; border:1px solid #555; border-radius:4px;" />
            </div>
            <div style="width: 130px;">
                <b>Videolar soni:</b><br />
                <select name="count" class="injected" style="width:100%; margin-top:4px; padding:7px; background:#222; color:#fff; border:1px solid #555; border-radius:4px;">
                    <option value="5">5 ta video</option>
                    <option value="10" selected>10 ta video</option>
                    <option value="20">20 ta video</option>
                    <option value="30">30 ta video</option>
                </select>
            </div>
            <div style="flex: 1; min-width: 220px;">
                <b>Bo‘lim (Kategoriya):</b><br />
                <select name="category" class="injected" style="width:100%; margin-top:4px; padding:7px; background:#222; color:#fff; border:1px solid #555; border-radius:4px;">
                    <option value="0">🎯 Avtomatik aniqlash</option>
                    <?php foreach ($cat_list as $c): ?>
                        <option value="<?=$c['id']?>" <?=($c['translit'] === 'uzbek' ? 'selected' : '')?>><?=$c['name']?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="width: 180px;">
                <b>Rejim:</b><br />
                <select name="save_mode" class="injected" style="width:100%; margin-top:4px; padding:7px; background:#222; color:#fff; border:1px solid #555; border-radius:4px;">
                    <option value="stream" selected>Oqim / Embed</option>
                    <option value="server">Serverga MP4 yuklash</option>
                </select>
            </div>
            <div>
                <button type="submit" class="byecos" style="font-size:14px; padding:8px 22px;">
                    <i class="fa fa-play"></i> Ushbu sahifani parslash
                </button>
            </div>
        </div>
    </form>
</div>

<!-- 4. AVTOMATIK FON PARSERI (CRON TIZIMI) -->
<div class="functions_data" style="background:#15181a; border-left: 4px solid #17a2b8; padding:16px;">
    <h3 style="color:#17a2b8; margin:0 0 8px 0;"><i class="fa fa-clock-o"></i> 4. Avtomatik Fon Parseri (CRON)</h3>
    <p style="color:#bbb; font-size:13px; line-height: 1.5; margin:0 0 10px 0;">
        Saytingizga muntazam ravishda yangi videolarni fon rejimida avtomatik yuklab borishi uchun serveringizda (FastPanel, cPanel yoki crontab) quyidagi havola bo‘yicha Cron qo‘yishingiz mumkin:
    </p>
    <div style="background:#0a0c0e; border:1px solid #333; padding:10px 14px; border-radius:4px; font-family:monospace; color:#28a745; font-size:13px; word-break: break-all; margin-bottom: 10px;">
        <?=$protocol . filter($_SERVER['HTTP_HOST'] ?? 'sekschi.online')?>/autocomplete.php?key=<?=htmlspecialchars($settings['cron'] ?? '')?>
    </div>
    <p style="color:#aaa; font-size:12px; margin:0;">
        Har 20 daqiqada yangi videolarni tekshirib yuklash buyrug‘i:<br />
        <code style="background:#222; padding:3px 8px; color:#ff9900; border-radius:3px; display:inline-block; margin-top:4px;">
            */20 * * * * curl -s "<?=$protocol . filter($_SERVER['HTTP_HOST'] ?? 'sekschi.online')?>/autocomplete.php?key=<?=htmlspecialchars($settings['cron'] ?? '')?>" > /dev/null 2>&1
        </code>
    </p>
</div>
