<?php

/*
 * erocms Video Parser Moduli
 * Qo'llab-quvvatlaydi:
 * - uzbxx.ru
 * - uzporno.website
 * - arhivporno.watch (cat-uzbekskii-seks)
 */

if ($user['access'] < 1) {
    header('Location: /'); 
    exit;
}

$core_engine = dirname(__DIR__, 2) . '/core/ParserEngine.php';
if (file_exists($core_engine)) {
    require_once $core_engine;
}

$logs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action_type = filter($_POST['action_type'] ?? '');
    $category_choice = abs(intval($_POST['category'] ?? 0));
    $save_mode = filter($_POST['save_mode'] ?? 'stream');

    // 1. HECH QANDAY URLSIZ ENG YANGI VIDEOLARNI AVTO-YUKLASH
    if ($action_type === 'auto_all') {
        $limit_count = min(30, max(1, abs(intval($_POST['auto_count'] ?? 10))));
        $donor_choice = filter($_POST['auto_donor'] ?? 'all');

        $all_items = [];

        // uzbxx.ru
        if ($donor_choice === 'all' || $donor_choice === 'uzbxx') {
            $links1 = parser_get_catalog_links_uzbxx(1);
            foreach ($links1 as $link) {
                $all_items[] = ['donor' => 'uzbxx', 'url' => $link];
            }
        }

        // uzporno.website
        if ($donor_choice === 'all' || $donor_choice === 'uzporno') {
            $links2 = parser_get_catalog_links_uzporno(1);
            foreach ($links2 as $link) {
                $all_items[] = ['donor' => 'uzporno', 'url' => $link];
            }
        }

        // arhivporno.watch
        if ($donor_choice === 'all' || $donor_choice === 'arhivporno') {
            $links3 = parser_get_catalog_links_arhivporno(1);
            foreach ($links3 as $item) {
                $all_items[] = [
                    'donor' => 'arhivporno',
                    'url' => $item['url'],
                    'poster' => $item['poster'] ?? '',
                    'duration' => $item['duration'] ?? '05:00'
                ];
            }
        }

        if (empty($all_items)) {
            $logs[] = ['status' => 'error', 'message' => 'Donor saytlardan hech qanday yangi video topilmadi.'];
        } else {
            // Agar 'all' bo'lsa har bir donor saytdan aralash olamiz
            if ($donor_choice === 'all') {
                shuffle($all_items);
            }

            $added = 0;
            foreach ($all_items as $item) {
                if ($added >= $limit_count) break;

                if ($item['donor'] === 'uzbxx') {
                    $res = parse_video_uzbxx($item['url'], $category_choice, $save_mode, $mysqli, $settings, $width_S, $height_S);
                } elseif ($item['donor'] === 'uzporno') {
                    $res = parse_video_uzporno($item['url'], $category_choice, $save_mode, $mysqli, $settings, $width_S, $height_S);
                } elseif ($item['donor'] === 'arhivporno') {
                    $res = parse_video_arhivporno($item['url'], $category_choice, $save_mode, $mysqli, $settings, $width_S, $height_S, $item);
                }

                $logs[] = $res;
                if (!empty($res['status']) && $res['status'] === 'success') {
                    $added++;
                }
                usleep(250000); // 0.25 sek pauza
            }

            // Keshni tozalash
            $doc_root = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2);
            @array_map('unlink', glob($doc_root . '/content/cache/*.html'));
        }
    }

    // 2. KATALOG / BO'LIM SAHIFALARI BO'YICHA OMMAVIY PARSLASH
    elseif ($action_type === 'mass_parse') {
        $donor = filter($_POST['donor'] ?? 'uzbxx');
        $page_num = max(1, abs(intval($_POST['page_num'] ?? 1)));
        $limit_count = min(30, max(1, abs(intval($_POST['count'] ?? 10))));
        $custom_url = trim($_POST['custom_catalog_url'] ?? '');

        $items_to_parse = [];

        // Agar to'g'ridan-to'g'ri katalog havolasi kiritilgan bo'lsa
        if (!empty($custom_url)) {
            if (stripos($custom_url, 'arhivporno.watch') !== false) {
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
            if ($donor === 'uzbxx') {
                $raw_links = parser_get_catalog_links_uzbxx($page_num);
                foreach ($raw_links as $l) $items_to_parse[] = ['url' => $l];
            } elseif ($donor === 'uzporno') {
                $raw_links = parser_get_catalog_links_uzporno($page_num);
                foreach ($raw_links as $l) $items_to_parse[] = ['url' => $l];
            } elseif ($donor === 'arhivporno') {
                $items_to_parse = parser_get_catalog_links_arhivporno($page_num);
            }
        }

        if (empty($items_to_parse)) {
            $logs[] = ['status' => 'error', 'message' => "Ushbu sahifadan video havolalari topilmadi ($donor, sahifa $page_num)."];
        } else {
            $added = 0;
            foreach ($items_to_parse as $item) {
                if ($added >= $limit_count) break;
                $v_url = is_array($item) ? $item['url'] : $item;

                if ($donor === 'uzbxx') {
                    $res = parse_video_uzbxx($v_url, $category_choice, $save_mode, $mysqli, $settings, $width_S, $height_S);
                } elseif ($donor === 'uzporno') {
                    $res = parse_video_uzporno($v_url, $category_choice, $save_mode, $mysqli, $settings, $width_S, $height_S);
                } elseif ($donor === 'arhivporno') {
                    $res = parse_video_arhivporno($v_url, $category_choice, $save_mode, $mysqli, $settings, $width_S, $height_S, is_array($item) ? $item : []);
                }

                $logs[] = $res;
                if (!empty($res['status']) && $res['status'] === 'success') {
                    $added++;
                }
                usleep(250000);
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
            if (stripos($single_url, 'uzbxx.ru') !== false) {
                $logs[] = parse_video_uzbxx($single_url, $category_choice, $save_mode, $mysqli, $settings, $width_S, $height_S);
            } elseif (stripos($single_url, 'uzporno.website') !== false) {
                $logs[] = parse_video_uzporno($single_url, $category_choice, $save_mode, $mysqli, $settings, $width_S, $height_S);
            } elseif (stripos($single_url, 'arhivporno.watch') !== false) {
                $logs[] = parse_video_arhivporno($single_url, $category_choice, $save_mode, $mysqli, $settings, $width_S, $height_S);
            } else {
                $logs[] = ['status' => 'error', 'message' => 'Noma‘lum havola! Faqat uzbxx.ru, uzporno.website yoki arhivporno.watch havolalari qo‘llab-quvvatlanadi.'];
            }

            $doc_root = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2);
            @array_map('unlink', glob($doc_root . '/content/cache/*.html'));
        }
    }
}
?>

<div class="functions_data" style="border-left: 4px solid #ff9900; background: #1a1815; padding: 15px; margin-bottom: 20px;">
    <h2 style="color:#ff9900; margin-bottom: 6px;"><i class="fa fa-cloud-download"></i> Universal Video Parser</h2>
    <p style="color:#ccc; font-size:13px; line-height: 1.5; margin: 0;">
        Ushbu modul orqali <b>uzbxx.ru</b>, <b>uzporno.website</b> va <b>arhivporno.watch</b> saytlaridan eng so‘nggi videolarni avtomatik yoki qo‘lda saytingizdagi istalgan bo‘limlarga yuklab olishingiz mumkin.
    </p>
</div>

<?php if (!empty($logs)): ?>
<div class="functions_data" style="background:#111; border:1px solid #ff9900; margin-bottom:20px; padding: 15px;">
    <h3 style="color:#ff9900; margin-bottom:12px;"><i class="fa fa-list-alt"></i> Parslash natijalari:</h3>
    <div style="max-height: 280px; overflow-y: auto; font-family: monospace; font-size: 13px; background:#000; padding:10px; border-radius:4px;">
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

<!-- 1. URLSIZ BIR BOSISHDA AVTO-PARSER -->
<div class="functions_data" style="background:#1e1a17; border: 2px solid #ff9900; margin-bottom:20px; padding:15px;">
    <h3 style="color:#ff9900; margin-bottom: 8px;"><i class="fa fa-bolt"></i> 1. Bir bosishda yangi videolarni avto-yuklash (URL KERAK EMAS!)</h3>
    <p style="color:#bbb; font-size:13px; margin-bottom: 15px;">
        Hech qanday havola yozish shart emas! Donor saytni tanlang, kerakli bo'limni tanlang va tugmani bosing:
    </p>
    <form method="post">
        <input type="hidden" name="action_type" value="auto_all" />
        <p style="line-height: 1.8;">
            <b>Qaysi donor saytdan yuklansin:</b><br />
            <label style="margin-right:20px; cursor:pointer;">
                <input type="radio" name="auto_donor" value="all" checked /> <b>Barcha donorlardan (uzbxx + uzporno + arhivporno)</b>
            </label>
            <label style="margin-right:20px; cursor:pointer;">
                <input type="radio" name="auto_donor" value="arhivporno" /> <b>arhivporno.watch</b> (Узбекский секс)
            </label>
            <label style="margin-right:20px; cursor:pointer;">
                <input type="radio" name="auto_donor" value="uzbxx" /> <b>uzbxx.ru</b>
            </label>
            <label style="cursor:pointer;">
                <input type="radio" name="auto_donor" value="uzporno" /> <b>uzporno.website</b>
            </label>
        </p>
        <br />
        <div style="display: flex; flex-wrap: wrap; gap: 15px; align-items: center;">
            <div>
                <b>Videolar soni:</b><br />
                <select name="auto_count" class="injected" style="width:130px; margin-top:4px;">
                    <option value="5">5 ta video</option>
                    <option value="10" selected>10 ta video</option>
                    <option value="15">15 ta video</option>
                    <option value="20">20 ta video</option>
                    <option value="30">30 ta video</option>
                </select>
            </div>
            <div>
                <b>Saytimizdagi Bo‘lim (Kategoriya):</b><br />
                <select name="category" class="injected" style="width:260px; margin-top:4px;">
                    <option value="0">-- Avtomatik aniqlash (Tavsiya) --</option>
                    <?php
                    $cats_q = $mysqli->query("SELECT id, name FROM ero_categories ORDER BY id ASC");
                    while ($c = $cats_q->fetch_assoc()) {
                        $sel = ($c['name'] === 'Узбекский секс') ? ' style="font-weight:bold; color:#ff9900;"' : '';
                        echo '<option value="'.$c['id'].'"'.$sel.'>'.$c['name'].'</option>';
                    }
                    ?>
                </select>
            </div>
            <div>
                <b>Saqlash rejimi:</b><br />
                <select name="save_mode" class="injected" style="width:220px; margin-top:4px;">
                    <option value="stream" selected>Oqim / Embed (Tez, joy olmaydi)</option>
                    <option value="download">Serverga MP4 yuklash</option>
                </select>
            </div>
        </div>
        <br />
        <p>
            <button type="submit" class="byecos" style="font-size:15px; padding:12px 28px; background:#ff9900; color:#000; font-weight:bold; border:none; cursor:pointer;">
                <i class="fa fa-cloud-download"></i> 🚀 Yangi videolarni darhol yuklash
            </button>
        </p>
    </form>
</div>

<!-- 2. KATALOG SAHIFALARI BO'YICHA OMMAVIY PARSLASH -->
<div class="functions_data" style="margin-bottom:20px; padding:15px;">
    <h3 style="color:#ff9900; margin-bottom: 8px;"><i class="fa fa-tasks"></i> 2. Katalog sahifalari bo‘yicha ommaviy parslash (Oldingi/Eski sahifalar)</h3>
    <p style="color:#888; font-size:12px; margin-bottom:12px;">Donor saytning istalgan sahifasidan (2, 3, 4...) yoki aniq kategoriya havolasidan videolarni ko‘chirib olish:</p>
    <form method="post">
        <input type="hidden" name="action_type" value="mass_parse" />
        <p style="line-height: 1.8;">
            <b>Donor sayt:</b><br />
            <label style="margin-right:20px; cursor:pointer;">
                <input type="radio" name="donor" value="arhivporno" checked /> <b>arhivporno.watch</b>
            </label>
            <label style="margin-right:20px; cursor:pointer;">
                <input type="radio" name="donor" value="uzbxx" /> <b>uzbxx.ru</b>
            </label>
            <label style="cursor:pointer;">
                <input type="radio" name="donor" value="uzporno" /> <b>uzporno.website</b>
            </label>
        </p>
        <br />
        <div style="display: flex; flex-wrap: wrap; gap: 15px; align-items: center;">
            <div>
                <b>Katalog sahifasi raqami:</b><br />
                <input type="number" name="page_num" value="2" min="1" max="200" class="injected" style="width:110px; margin-top:4px;" />
            </div>
            <div>
                <b>Videolar soni:</b><br />
                <select name="count" class="injected" style="width:130px; margin-top:4px;">
                    <option value="5">5 ta video</option>
                    <option value="10" selected>10 ta video</option>
                    <option value="20">20 ta video</option>
                    <option value="30">30 ta video</option>
                </select>
            </div>
            <div>
                <b>Bo‘lim (Kategoriya):</b><br />
                <select name="category" class="injected" style="width:240px; margin-top:4px;">
                    <option value="0">-- Avtomatik aniqlash --</option>
                    <?php
                    $cats_q2 = $mysqli->query("SELECT id, name FROM ero_categories ORDER BY id ASC");
                    while ($c = $cats_q2->fetch_assoc()) {
                        echo '<option value="'.$c['id'].'">'.$c['name'].'</option>';
                    }
                    ?>
                </select>
            </div>
            <div>
                <b>Rejim:</b><br />
                <select name="save_mode" class="injected" style="width:200px; margin-top:4px;">
                    <option value="stream" selected>Oqim / Embed</option>
                    <option value="download">Serverga MP4 yuklash</option>
                </select>
            </div>
        </div>
        <div style="margin-top:12px;">
            <b>Yoki to‘g‘ridan-to‘g‘ri katalog/bo‘lim URL havolasi (ixtiyoriy):</b><br />
            <input type="url" name="custom_catalog_url" class="injected" placeholder="Masalan: https://arhivporno.watch/cat-uzbekskii-seks/2/ yoki https://uzbxx.ru/3" style="width:100%; margin-top:4px;" />
        </div>
        <br />
        <p>
            <button type="submit" class="byecos" style="font-size:14px; padding:10px 22px;">
                <i class="fa fa-play"></i> Ushbu sahifani parslash
            </button>
        </p>
    </form>
</div>

<!-- 3. YAGONA HAVOLA (URL) ORQALI VIDEO QO'SHISH -->
<div class="functions_data" style="margin-bottom:20px; padding:15px;">
    <h3 style="color:#ff9900; margin-bottom: 8px;"><i class="fa fa-link"></i> 3. Yagona havola (URL) orqali bitta video qo‘shish</h3>
    <p style="color:#888; font-size:12px; margin-bottom:12px;">Aniq bitta videoning to‘liq havolasini kiriting (uzbxx.ru, uzporno.website yoki arhivporno.watch):</p>
    <form method="post">
        <input type="hidden" name="action_type" value="single_url" />
        <p>
            <input type="url" name="single_url" class="injected" placeholder="Masalan: https://arhivporno.watch/paren-iznasiloval-pyanuu-uzbechku-doma-posle-vecherinki/ yoki https://uzbxx.ru/video/... yoki https://uzporno.website/video/..." required style="width:100%; font-size:14px;" />
        </p>
        <div style="display: flex; flex-wrap: wrap; gap: 15px; align-items: center; margin-top: 12px;">
            <div>
                <b>Bo‘lim (Kategoriya):</b><br />
                <select name="category" class="injected" style="width:240px; margin-top:4px;">
                    <option value="0">-- Avtomatik aniqlash --</option>
                    <?php
                    $cats_q3 = $mysqli->query("SELECT id, name FROM ero_categories ORDER BY id ASC");
                    while ($c = $cats_q3->fetch_assoc()) {
                        echo '<option value="'.$c['id'].'">'.$c['name'].'</option>';
                    }
                    ?>
                </select>
            </div>
            <div>
                <b>Rejim:</b><br />
                <select name="save_mode" class="injected" style="width:200px; margin-top:4px;">
                    <option value="stream" selected>Oqim / Embed</option>
                    <option value="download">Serverga MP4 yuklash</option>
                </select>
            </div>
        </div>
        <p style="margin-top:15px;">
            <button type="submit" class="byecos" style="font-size:14px; padding:10px 22px;">
                <i class="fa fa-download"></i> Havoladan import qilish
            </button>
        </p>
    </form>
</div>

<!-- 4. AVTOMATIK FON PARSERI (CRON TIZIMI) -->
<div class="functions_data" style="background:#15181a; border-left: 4px solid #17a2b8; padding:15px;">
    <h3 style="color:#17a2b8; margin-bottom: 8px;"><i class="fa fa-clock-o"></i> 4. Avtomatik Fon Parseri (CRON)</h3>
    <p style="color:#bbb; font-size:13px; line-height: 1.5; margin-bottom: 10px;">
        Saytingizga har kuni eng yangi videolarni o‘zi avtomatik yuklab borishi uchun serveringizda (cPanel, FastPanel yoki crontab) quyidagi havola bo‘yicha Cron-job qo‘yishingiz mumkin:
    </p>
    <div style="background:#0a0c0e; border:1px solid #333; padding:10px 14px; border-radius:4px; font-family:monospace; color:#28a745; font-size:13px; word-break: break-all; margin-bottom: 10px;">
        <?=$protocol . filter($_SERVER['HTTP_HOST'] ?? 'sekschi.online')?>/autocomplete.php?key=<?=htmlspecialchars($settings['cron'] ?? '')?>
    </div>
    <p style="color:#aaa; font-size:12px; margin:0;">
        Server Crontab namunasi (har 20 daqiqada yangi videolarni tekshirish):<br />
        <code style="background:#222; padding:3px 8px; color:#ff9900; border-radius:3px; display:inline-block; margin-top:4px;">
            */20 * * * * curl -s "<?=$protocol . filter($_SERVER['HTTP_HOST'] ?? 'sekschi.online')?>/autocomplete.php?key=<?=htmlspecialchars($settings['cron'] ?? '')?>" > /dev/null 2>&1
        </code>
    </p>
</div>
