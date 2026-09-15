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
        'sticky_footer_enabled' => 0,
        'sticky_footer_desktop' => '',
        'sticky_footer_mobile' => '',
        'player_overlay_enabled' => 0,
        'player_overlay_url' => '',
        'native_grid_enabled' => 0,
        'native_grid_code' => '',
        'vast_preroll_enabled' => 0,
        'vast_preroll_url' => '',
        'text_ads_enabled' => 1,
        'hide_ads_for_members' => 0
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
    global $user, $member;
    // Admin yoki boshqaruv panelida reklama chiqarilmaydi
    if (($user && isset($user['access']) && $user['access'] == 1) || (strpos($_SERVER['REQUEST_URI'] ?? '', 'control') !== false)) {
        return;
    }

    $cfg = ads_get_config();
    if (!empty($cfg['hide_ads_for_members']) && !empty($member)) {
        return;
    }
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
    global $user, $member;
    if (($user && isset($user['access']) && $user['access'] == 1) || (strpos($_SERVER['REQUEST_URI'] ?? '', 'control') !== false)) {
        return;
    }

    $cfg = ads_get_config();
    if (!empty($cfg['hide_ads_for_members']) && !empty($member)) {
        return;
    }
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

    if ($mob_code === $desk_code || (empty($desk_code) && !empty($mob_code)) || (!empty($desk_code) && empty($mob_code))) {
        $single_code = !empty($desk_code) ? $desk_code : $mob_code;
        echo $single_code;
    } else {
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

/**
 * 1. Ekran pastida yopishib turuvchi Sticky Footer Banner
 */
function ads_render_sticky_footer() {
    global $user, $member;
    if (($user && isset($user['access']) && $user['access'] == 1) || (strpos($_SERVER['REQUEST_URI'] ?? '', 'control') !== false)) {
        return;
    }

    $cfg = ads_get_config();
    if (!empty($cfg['hide_ads_for_members']) && !empty($member)) {
        return;
    }
    if (empty($cfg['ads_enabled']) || empty($cfg['sticky_footer_enabled'])) {
        return;
    }

    $mob_code = !empty($cfg['sticky_footer_mobile']) ? trim($cfg['sticky_footer_mobile']) : '';
    $desk_code = !empty($cfg['sticky_footer_desktop']) ? trim($cfg['sticky_footer_desktop']) : '';

    if (empty($mob_code) && empty($desk_code)) {
        return;
    }

    echo '
    <div id="sticky-footer-ad" style="position:fixed; bottom:0; left:0; width:100%; z-index:99999; background:rgba(18,14,12,0.96); box-shadow:0 -4px 15px rgba(0,0,0,0.8); border-top:1px solid #383431; text-align:center; padding:5px 0;">
        <button onclick="document.getElementById(\'sticky-footer-ad\').style.display=\'none\';" style="position:absolute; top:-24px; right:10px; background:#222; color:#fff; border:1px solid #444; border-bottom:none; border-radius:4px 4px 0 0; padding:2px 8px; font-size:11px; cursor:pointer; font-weight:bold;">
            <i class="fa fa-times"></i> Yopish
        </button>
        <div class="sticky-ad-container" style="max-width:100%; overflow:hidden; display:flex; justify-content:center; align-items:center;">';

    if ($mob_code === $desk_code || (empty($desk_code) && !empty($mob_code)) || (!empty($desk_code) && empty($mob_code))) {
        echo !empty($desk_code) ? $desk_code : $mob_code;
    } else {
        echo '<div class="sticky-slot-desktop" style="display:none;">' . $desk_code . '</div>';
        echo '<div class="sticky-slot-mobile" style="display:none;">' . $mob_code . '</div>';
        echo '<style>
            @media (min-width: 768px) {
                #sticky-footer-ad .sticky-slot-desktop { display: block !important; }
                #sticky-footer-ad .sticky-slot-mobile { display: none !important; }
            }
            @media (max-width: 767px) {
                #sticky-footer-ad .sticky-slot-desktop { display: none !important; }
                #sticky-footer-ad .sticky-slot-mobile { display: block !important; }
            }
        </style>';
    }

    echo '
        </div>
    </div>';
}

/**
 * 2. Video Player Ustiga "Click-Overlay" (Play bosganda yangi oynada ochiluvchi reklama)
 */
function ads_render_player_overlay() {
    global $user, $member;
    if (($user && isset($user['access']) && $user['access'] == 1) || (strpos($_SERVER['REQUEST_URI'] ?? '', 'control') !== false)) {
        return;
    }

    $cfg = ads_get_config();
    if (!empty($cfg['hide_ads_for_members']) && !empty($member)) {
        return;
    }
    if (empty($cfg['ads_enabled']) || empty($cfg['player_overlay_enabled']) || empty($cfg['player_overlay_url'])) {
        return;
    }

    $url = htmlspecialchars($cfg['player_overlay_url'], ENT_QUOTES, 'UTF-8');

    echo '
    <div id="player-click-overlay" onclick="triggerPlayerAdOverlay(event)" style="position:absolute; top:0; left:0; width:100%; height:100%; z-index:999; cursor:pointer; background:rgba(0,0,0,0.01);">
        <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:68px; height:68px; background:rgba(0,0,0,0.7); border:2px solid var(--primary-accent, #ff9900); border-radius:50%; display:flex; align-items:center; justify-content:center; box-shadow:0 0 20px rgba(0,0,0,0.8); pointer-events:none;">
            <i class="fa fa-play" style="color:#fff; font-size:24px; margin-left:4px;"></i>
        </div>
    </div>
    <script>
    function triggerPlayerAdOverlay(e) {
        e.preventDefault();
        e.stopPropagation();
        var adUrl = "' . $url . '";
        window.open(adUrl, "_blank");
        var ov = document.getElementById("player-click-overlay");
        if (ov) ov.remove();
        // Videoni o‘ynatish
        var vid = document.getElementById("main-video-player");
        if (vid) {
            vid.play();
        }
    }
    </script>';
}

/**
 * 3. Videolar orasidagi Native Reklama vidjeti (Native Recommendation Grid)
 */
function ads_render_native_grid() {
    global $user, $member;
    if (($user && isset($user['access']) && $user['access'] == 1) || (strpos($_SERVER['REQUEST_URI'] ?? '', 'control') !== false)) {
        return;
    }

    $cfg = ads_get_config();
    if (!empty($cfg['hide_ads_for_members']) && !empty($member)) {
        return;
    }
    if (empty($cfg['ads_enabled']) || empty($cfg['native_grid_enabled']) || empty($cfg['native_grid_code'])) {
        return;
    }

    echo '<div class="xxxhd-native-ad-slot" style="margin:10px 5px; width:100%; clear:both; overflow:hidden;">' . $cfg['native_grid_code'] . '</div>';
}
