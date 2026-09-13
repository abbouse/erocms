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
        'banner_top' => '',
        'banner_bottom_enabled' => 1,
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

    if ($position === 'top' && !empty($cfg['banner_top_enabled']) && !empty($cfg['banner_top'])) {
        echo '<div class="ad-banner-slot ad-banner-top" style="text-align:center; margin:12px auto; max-width:100%; overflow:hidden;">' . $cfg['banner_top'] . '</div>';
    } elseif ($position === 'bottom' && !empty($cfg['banner_bottom_enabled']) && !empty($cfg['banner_bottom'])) {
        echo '<div class="ad-banner-slot ad-banner-bottom" style="text-align:center; margin:15px auto; max-width:100%; overflow:hidden;">' . $cfg['banner_bottom'] . '</div>';
    }
}
