<?php

$title = $lang['online'];
$description = $settings['description'];
$keywords = $settings['keywords'];
    
head();
advertising();

$now = time();
$quantity = $mysqli->query("SELECT count(*) FROM ero_online WHERE date > '$now'")->fetch_row();
$total_online = (int)($quantity[0] ?? 0);

$per_page = 20;
$k_page = k_page($total_online, $per_page);
$page = page($k_page);
$start = $per_page * $page - $per_page;

$query = $mysqli->query("SELECT id, ip, date, page_url, user_agent, last_seen FROM ero_online WHERE date > '$now' ORDER BY date DESC LIMIT $start, $per_page");
$my_ip = $_SERVER['REMOTE_ADDR'] ?? '';
?>

<div class="xxxhd-title-top" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
    <h2><i class="fa fa-users" style="color:var(--primary-accent, #ff9900);"></i> <?=$lang['online']?> (<?=$total_online?> nafar)</h2>
    <span style="font-size:12px; color:#22c55e;"><i class="fa fa-circle"></i> Real vaqtdagi tashrifchilar</span>
</div>

<?php if ($total_online == 0): ?>
    <div class="err" style="margin:15px;"><?=$lang['users_not_found']?></div>
<?php else: ?>
    <div style="overflow-x:auto; padding:5px;">
        <table class="functions_data" style="width:100%; border-collapse:collapse; background:#1a1919; border:1px solid #323130; border-radius:4px;">
            <thead>
                <tr style="background:#23130e; border-bottom:1px solid #4b3b36; color:#ebdbd6; text-align:left; font-size:12px;">
                    <th style="padding:10px 8px; width:40px;">#</th>
                    <th style="padding:10px 8px;">Foydalanuvchi</th>
                    <th style="padding:10px 8px;">Hozirgi Sahifa</th>
                    <th style="padding:10px 8px;">Qurilma / Brauzer</th>
                    <th style="padding:10px 8px; width:110px;">Oxirgi faollik</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            $num = $start + 1;
            while ($row = $query->fetch_assoc()): 
                $is_me = ($row['ip'] === $my_ip);
                $u_info = function_exists('parse_user_agent_details') ? parse_user_agent_details($row['user_agent'] ?? '') : ['device' => 'Noma’lum', 'device_icon' => 'fa-desktop', 'badge_color' => '#888', 'browser' => 'Brauzer'];
                
                // IP manzilni maxfiylik uchun qisman maskalash (agar admin bo'lmasa)
                $is_admin = ($user && isset($user['access']) && $user['access'] == 1);
                $display_ip = $row['ip'];
                if (!$is_admin && !$is_me) {
                    $parts = explode('.', $row['ip']);
                    if (count($parts) === 4) {
                        $display_ip = $parts[0] . '.' . $parts[1] . '.***.***';
                    } else {
                        $display_ip = substr($row['ip'], 0, 8) . '***';
                    }
                }

                $page_url = !empty($row['page_url']) ? $row['page_url'] : '/';
                $seconds_ago = max(0, 300 - ($row['date'] - $now));
                $time_text = ($seconds_ago < 30) ? 'Hozirgina' : floor($seconds_ago / 60) . ' daq. oldin';
            ?>
                <tr style="border-bottom:1px solid #282625; font-size:13px; color:#959595; <?=($is_me ? 'background:rgba(255,153,0,0.06);' : '')?>">
                    <td style="padding:10px 8px; color:#64748b; font-weight:bold;"><?=$num++?></td>
                    <td style="padding:10px 8px;">
                        <?=function_exists('get_country_flag_badge') ? get_country_flag_badge($row['country_code'] ?? null) : ''?><code style="color:#e2e8f0; font-size:12px;"><?=$display_ip?></code>
                        <?php if ($is_me): ?>
                            <span style="background:#ff9900; color:#000; font-size:10px; font-weight:bold; padding:2px 6px; border-radius:3px; margin-left:4px;">Siz</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:10px 8px;">
                        <a href="<?=$page_url?>" style="color:var(--primary-accent, #ff9900); font-size:12px; text-decoration:none; display:inline-block; max-width:240px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                            <?=htmlspecialchars($page_url)?>
                        </a>
                    </td>
                    <td style="padding:10px 8px;">
                        <span style="color:<?=$u_info['badge_color']?>; font-size:12px; font-weight:600;">
                            <i class="fa <?=$u_info['device_icon']?>"></i> <?=$u_info['device']?>
                        </span>
                        <span style="color:#64748b; font-size:11px; margin-left:4px;">(<?=$u_info['browser']?>)</span>
                    </td>
                    <td style="padding:10px 8px; font-size:12px; color:#888;">
                        <i class="fa fa-clock-o" style="color:#22c55e;"></i> <?=$time_text?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php 
    if ($k_page > 1) str('/online.html?', $k_page, $page);
    ?>
<?php endif; ?>

<?php
if ($query) $query->free();
foot();
