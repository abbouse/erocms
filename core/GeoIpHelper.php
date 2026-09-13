<?php

/**
 * EroCMS GeoIP & Country Flag Helper
 */

// Davlatlar nomlari o'zbekcha lug'ati
function get_country_name_uz($code) {
    $code = strtoupper(trim($code));
    $names = [
        'UZ' => 'O‘zbekiston',
        'RU' => 'Rossiya',
        'KZ' => 'Qozog‘iston',
        'KG' => 'Qirg‘iziston',
        'TJ' => 'Tojikiston',
        'TM' => 'Turkmaniston',
        'TR' => 'Turkiya',
        'US' => 'AQSH',
        'DE' => 'Germaniya',
        'GB' => 'Buyuk Britaniya',
        'FR' => 'Fransiya',
        'UA' => 'Ukraina',
        'BY' => 'Belarus',
        'AZ' => 'Ozarbayjon',
        'KR' => 'Janubiy Koreya',
        'CN' => 'Xitoy',
        'JP' => 'Yaponiya',
        'AE' => 'BAA (Dubay)',
        'PL' => 'Polsha',
        'IT' => 'Italiya',
        'ES' => 'Ispaniya',
        'NL' => 'Niderlandiya',
        'SE' => 'Shvetsiya',
        'CH' => 'Shveysariya',
        'IN' => 'Hindiston',
        'CA' => 'Kanada',
        'BR' => 'Braziliya',
        'ID' => 'Indoneziya',
        'MY' => 'Malayziya',
        'SA' => 'Saudiya Arabistoni',
        'GE' => 'Gruziya',
        'AM' => 'Armaniston',
        'MD' => 'Moldova',
        'LV' => 'Latviya',
        'LT' => 'Litva',
        'EE' => 'Estoniya',
        'IL' => 'Isroil'
    ];

    return $names[$code] ?? $code;
}

// IP manzil bo'yicha davlatni aniqlash (kuchsiz serverlar uchun kesh va non-blocking)
function get_ip_country_info($ip) {
    global $mysqli;
    $ip = trim($ip);

    if (empty($ip) || $ip === '127.0.0.1' || $ip === '::1' || strpos($ip, '192.168.') === 0 || strpos($ip, '10.') === 0) {
        return ['code' => 'UZ', 'name' => 'O‘zbekiston (Lokal)'];
    }

    // 1. Cloudflare orqali aniqlash
    if (!empty($_SERVER['HTTP_CF_IPCOUNTRY']) && strlen($_SERVER['HTTP_CF_IPCOUNTRY']) === 2 && $ip === ($_SERVER['REMOTE_ADDR'] ?? '')) {
        $c_code = strtoupper($_SERVER['HTTP_CF_IPCOUNTRY']);
        return ['code' => $c_code, 'name' => get_country_name_uz($c_code)];
    }

    // 2. Baza keshini tekshirish
    if ($mysqli instanceof mysqli) {
        $check = @$mysqli->query("SELECT country_code, country_name FROM `ero_geoip_cache` WHERE `ip` = '".mysqli_real_escape_string($mysqli, $ip)."' LIMIT 1");
        if ($check && $check->num_rows > 0) {
            $row = $check->fetch_assoc();
            return [
                'code' => $row['country_code'],
                'name' => !empty($row['country_name']) ? $row['country_name'] : get_country_name_uz($row['country_code'])
            ];
        }
    }

    // 3. Tashqi tezkor API orqali so'rash (1 soniya timeout bilan)
    $detected_code = 'UZ'; // Standart O'zbekiston
    $detected_name = 'O‘zbekiston';

    try {
        $ctx = stream_context_create([
            'http' => [
                'timeout' => 1.0,
                'ignore_errors' => true,
                'header' => "User-Agent: EroCMS-GeoIP/1.0\r\n"
            ]
        ]);

        $api_res = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,country,countryCode", false, $ctx);
        if ($api_res) {
            $data = @json_decode($api_res, true);
            if (isset($data['status']) && $data['status'] === 'success' && !empty($data['countryCode'])) {
                $detected_code = strtoupper($data['countryCode']);
                $detected_name = get_country_name_uz($detected_code);
            }
        }
    } catch (\Throwable $e) {
        // Xatolik bo'lsa standart davlat olinadi
    }

    // Keshga yozish
    if ($mysqli instanceof mysqli) {
        $safe_ip = mysqli_real_escape_string($mysqli, $ip);
        $safe_code = mysqli_real_escape_string($mysqli, $detected_code);
        $safe_name = mysqli_real_escape_string($mysqli, $detected_name);
        $time_now = time();
        @$mysqli->query("INSERT INTO `ero_geoip_cache` (`ip`, `country_code`, `country_name`, `date`) 
            VALUES ('$safe_ip', '$safe_code', '$safe_name', '$time_now') 
            ON DUPLICATE KEY UPDATE `country_code` = '$safe_code', `country_name` = '$safe_name', `date` = '$time_now'");
    }

    return ['code' => $detected_code, 'name' => $detected_name];
}

// Davlat bayrog'i HTML belgisini yaratish
function get_country_flag_badge($country_code, $country_name = '', $with_code = false) {
    $code = strtolower(trim($country_code));
    if (empty($code) || $code === 'xx') {
        $code = 'uz'; // standart
    }

    if (empty($country_name)) {
        $country_name = get_country_name_uz($code);
    }

    $c_upper = strtoupper($code);

    $html = '<span class="country-flag-badge" title="'.htmlspecialchars($country_name).'" style="display:inline-flex; align-items:center; vertical-align:middle; gap:4px; margin-right:4px;">';
    $html .= '<img src="https://flagcdn.com/16x12/'.$code.'.png" srcset="https://flagcdn.com/32x24/'.$code.'.png 2x" width="16" height="12" alt="'.$c_upper.'" style="border-radius:2px; box-shadow:0 0 2px rgba(0,0,0,0.5); vertical-align:middle;" onerror="this.style.display=\'none\'" />';
    if ($with_code) {
        $html .= '<span style="font-size:10px; font-weight:700; color:#94a3b8;">'.$c_upper.'</span>';
    }
    $html .= '</span>';

    return $html;
}

// IP bilan birga bayroq chiqaruvchi yordamchi funksiya
function render_ip_with_flag($ip, $country_code = null, $country_name = null) {
    if (empty($country_code) || $country_code === 'XX') {
        $info = get_ip_country_info($ip);
        $country_code = $info['code'];
        $country_name = $info['name'];
    }

    return get_country_flag_badge($country_code, $country_name) . '<code style="color:#e2e8f0; font-size:12px;">' . htmlspecialchars($ip) . '</code>';
}
