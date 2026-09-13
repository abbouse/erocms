<?php

/**
 * EroCMS Reklama Boshqaruvi Tizimi (ExoClick, Popunder, Bannerlar)
 */

define('ADS_CONFIG_FILE', __DIR__ . '/ads_config.json');

function ads_get_config() {
    $defaults = [
        'ads_enabled' => 1,
        'popunder_enabled' => 1,
        'popunder_mobile' => '',
        'popunder_desktop' => '',
        'banner_top_enabled' => 1,
        'banner_top_desktop' => '',
        'banner_top_mobile' => '',
        'banner_top' => '',
        'banner_bottom_enabled' => 1,
        'banner_bottom_desktop' => '',
        'banner_bottom_mobile' => '',
        'banner_bottom' => '',
        'text_ads_enabled' => 1
    ];

    if (file_exists(ADS_CONFIG_FILE)) {
        $content = @file_get_contents(ADS_CONFIG_FILE);
        $json = @json_decode($content, true);
        if (is_array($json)) {
            return array_merge($defaults, $json);
        }
    }

    return $defaults;
}

function ads_save_config($data) {
    $current = ads_get_config();
    $merged = array_merge($current, $data);
    return (bool)@file_put_contents(ADS_CONFIG_FILE, json_encode($merged, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

function is_mobile_visitor() {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    return (bool)preg_match('/(android|iphone|ipad|ipod|blackberry|iemobile|opera mini|mobile|tablet|webos|windows phone)/i', $ua);
}

function ads_render_popunder() {
    global $user;
    // Admin yoki boshqaruv panelida reklama chiqarilmaydi
    if (($user && isset($user['access']) && $user['access'] == 1) || (strpos($_SERVER['REQUEST_URI'] ?? '', 'control') !== false)) {
        return;
    }

    $cfg = ads_get_config();
    if (empty($cfg['ads_enabled']) || empty($cfg['popunder_enabled'])) {
        return;
    }

    $is_mob = is_mobile_visitor();

    if ($is_mob && !empty($cfg['popunder_mobile'])) {
        echo "\n<!-- ExoClick Mobile Popunder -->\n" . $cfg['popunder_mobile'] . "\n";
    } elseif (!$is_mob && !empty($cfg['popunder_desktop'])) {
        echo "\n<!-- ExoClick Desktop Popunder -->\n" . $cfg['popunder_desktop'] . "\n";
    } elseif (!empty($cfg['popunder_mobile'])) {
        // Desktop alohida berilmagan bo'lsa, mobil kod zaxira sifatida ishlaydi
        echo "\n<!-- ExoClick Popunder -->\n" . $cfg['popunder_mobile'] . "\n";
    }
}

function ads_render_banner($position = 'top') {
    global $user;
    if (($user && isset($user['access']) && $user['access'] == 1) || (strpos($_SERVER['REQUEST_URI'] ?? '', 'control') !== false)) {
        return;
    }

    $cfg = ads_get_config();
    if (empty($cfg['ads_enabled'])) {
        return;
    }

    $prefix = ($position === 'top') ? 'banner_top' : 'banner_bottom';
    if (empty($cfg[$prefix . '_enabled'])) {
        return;
    }

    $mob_code = !empty($cfg[$prefix . '_mobile']) ? trim($cfg[$prefix . '_mobile']) : (!empty($cfg[$prefix]) ? trim($cfg[$prefix]) : '');
    $desk_code = !empty($cfg[$prefix . '_desktop']) ? trim($cfg[$prefix . '_desktop']) : (!empty($cfg[$prefix]) ? trim($cfg[$prefix]) : '');

    if (empty($mob_code) && empty($desk_code)) {
        return;
    }

    $slot_class = ($position === 'top') ? 'ad-banner-top' : 'ad-banner-bottom';
    $margin = ($position === 'top') ? '10px auto' : '14px auto';

    echo '<div class="ad-banner-slot ' . $slot_class . '" style="text-align:center; margin:' . $margin . '; max-width:100%; overflow:hidden;">';

    // Agar ikkalasi ham bir xil kod bo'lsa (yoki bittasi to'ldirilgan bo'lsa)
    if ($mob_code === $desk_code || (empty($desk_code) && !empty($mob_code)) || (!empty($desk_code) && empty($mob_code))) {
        $single_code = !empty($desk_code) ? $desk_code : $mob_code;
        echo $single_code;
    } else {
        // Ham kompyuter, ham mobil uchun alohida kodlar kiritilgan bo'lsa:
        // Server-side (PHP) va CSS media-query orqali ikkalasi bir vaqtda chiqib ketishini 100% to'samiz!
        echo '<div class="ad-slot-desktop" style="display:none;">' . $desk_code . '</div>';
        echo '<div class="ad-slot-mobile" style="display:none;">' . $mob_code . '</div>';
        echo '<style>
            @media (min-width: 768px) {
                .ad-banner-slot .ad-slot-desktop { display: block !important; }
                .ad-banner-slot .ad-slot-mobile { display: none !important; }
            }
            @media (max-width: 767px) {
                .ad-banner-slot .ad-slot-desktop { display: none !important; }
                .ad-banner-slot .ad-slot-mobile { display: block !important; }
            }
        </style>';
    }

    echo '</div>';
}
