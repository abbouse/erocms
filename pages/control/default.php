<?php

/*
 * erocms Boshqaruv Paneli (Admin Dashboard)
 */
    
if (isset($_GET['out'])) {
    setcookie('password', '', time() - 3600, '/');
    session_destroy();
    logs($user['id'], $lang['left_the_site'].'.', 0);
    header('Location: /control.html');
    exit;
}

$files_publish = $mysqli->query("SELECT count(*) FROM ero_files WHERE date > '".time()."'")->fetch_row();
$users = $mysqli->query("SELECT count(*) FROM ero_users")->fetch_row();
$dmca_unread = $mysqli->query("SELECT count(*) FROM ero_dmca WHERE status = 0")->fetch_row()[0] ?? 0;
$categories_count = $mysqli->query("SELECT count(*) FROM ero_categories")->fetch_row()[0] ?? 0;

// Onlayn foydalanuvchilar
$now = time();
$online_count = $mysqli->query("SELECT count(*) FROM ero_online WHERE date > '$now'")->fetch_row()[0] ?? 0;
$online_list = $mysqli->query("SELECT ip, date FROM ero_online WHERE date > '$now' ORDER BY date DESC LIMIT 15");
?>

<table cellpadding="7" width="100%" class="functions_data" style="margin-bottom: 20px;">
  <tr>
    <th>
        <div class="menu_j">
            <a href="?func=parsing" class="top_menu_j">
                <img src="/designs/icons/control/parsing.png" width="32" height="32" />
                <p>Universal Parser</p>
            </a>
        </div>
    </th>
    <th>
        <div class="menu_j">
            <a href="?func=add" class="top_menu_j">
                <img src="/designs/icons/control/add.png" width="32" height="32" />
                <p><?=$lang['add_video']?></p>
            </a>
        </div>
    </th>
    <th>
        <div class="menu_j">
            <a href="?func=import_main" class="top_menu_j">
                <img src="/designs/icons/control/import.png" width="32" height="32" />
                <p><?=$lang['import_video']?></p>
            </a>
        </div>
    </th>
    <th>
        <div class="menu_j">
            <a href="?func=users" class="top_menu_j">
                <img src="/designs/icons/control/users.png" width="32" height="32" />
                <p><?=$lang['users']?> (<?=$users[0]?>)</p>
            </a>
        </div>
    </th>
  </tr>
  
  <?php if ($user['access'] == 1): ?>
  <tr>
    <th>
        <div class="menu_j">
            <a href="?func=view_categories" class="top_menu_j">
                <img src="/designs/icons/control/addCat.png" width="32" height="32" />
                <p>Kategoriyalar & SEO (<?=$categories_count?>)</p>
            </a>
        </div>
    </th>
    <th>
        <div class="menu_j">
            <a href="?func=dmca" class="top_menu_j" style="position:relative;">
                <img src="/designs/icons/control/appeal.png" width="32" height="32" />
                <?php if ($dmca_unread > 0): ?>
                    <span style="position:absolute; top:2px; right:15px; background:#dc3545; color:#fff; font-size:11px; font-weight:bold; padding:2px 6px; border-radius:10px;"><?=$dmca_unread?></span>
                <?php endif; ?>
                <p>DMCA Shikoyatlar (<?=$dmca_unread?>)</p>
            </a>
        </div>
    </th>
    <th>
        <div class="menu_j">
            <a href="?func=publish" class="top_menu_j">
                <img src="/designs/icons/control/publish.png" width="32" height="32" />
                <p><?=$lang['deferred']?> (<?=$files_publish[0]?>)</p>
            </a>
        </div>
    </th>
    <th>
        <div class="menu_j">
            <a href="?func=settings" class="top_menu_j">
                <img src="/designs/icons/control/settings.png" width="32" height="32" />
                <p><?=$lang['settings']?></p>
            </a>
        </div>
    </th>
  </tr>

  <tr>
    <th>
        <div class="menu_j">
            <a href="?func=pages" class="top_menu_j">
                <img src="/designs/icons/control/pages.png" width="32" height="32" />
                <p><?=$lang['popular']?></p>
            </a>
        </div>
    </th>
    <th>
        <div class="menu_j">
            <a href="?func=rewriting" class="top_menu_j">
                <img src="/designs/icons/control/rewriting.png" width="32" height="32" />
                <p><?=$lang['rewriting']?></p>
            </a>
        </div>
    </th>
    <th>
        <div class="menu_j">
            <a href="?func=functions_data" class="top_menu_j">
                <img src="/designs/icons/control/functions_data.png" width="32" height="32" />
                <p><?=$lang['info']?></p>
            </a>
        </div>
    </th>
    <th>
        <div class="menu_j">
            <a href="?func=out" class="top_menu_j">
                <img src="/designs/icons/control/out.png" width="32" height="32" />
                <p><?=$lang['exit']?></p>
            </a>
        </div>
    </th>
  </tr>
  <?php else: ?>
  <tr>
    <th colspan="4">
        <div class="menu_j">
            <a href="?func=out" class="top_menu_j">
                <img src="/designs/icons/control/out.png" width="32" height="32" />
                <p><?=$lang['exit']?></p>
            </a>
        </div>
    </th>
  </tr>
  <?php endif; ?>
</table>

<!-- Real-Time Online Monitoring Widget -->
<div class="functions_data" style="background:#191817; border:1px solid #383634; border-radius:4px; padding:16px; margin-bottom:20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; flex-wrap:wrap; gap:8px;">
        <h3 style="color:#ff9900; margin:0; display:flex; align-items:center; gap:8px; font-size:16px;">
            <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:#28a745; box-shadow:0 0 8px #28a745;"></span>
            Hozir saytda onlayn: <b style="color:#28a745; font-size:18px;"><?=$online_count?> nafar</b>
        </h3>
        <a href="/control.html" style="background:#2d2a28; color:#bbb; text-decoration:none; padding:4px 10px; font-size:12px; border-radius:3px; border:1px solid #444;">
            <i class="fa fa-refresh"></i> Yangilash
        </a>
    </div>

    <?php if ($online_list && $online_list->num_rows > 0): ?>
    <div style="overflow-x:auto;">
        <table width="100%" cellpadding="6" cellspacing="0" style="font-size:13px; color:#ccc; border-collapse:collapse;">
            <thead>
                <tr style="background:#111; color:#ff9900; text-align:left; border-bottom:1px solid #333;">
                    <th style="padding:8px 10px;">#</th>
                    <th style="padding:8px 10px;">IP Manzil</th>
                    <th style="padding:8px 10px;">Faollik holati</th>
                    <th style="padding:8px 10px;">Oxirgi so‘rov</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $i = 1;
                while ($on = $online_list->fetch_assoc()): 
                    $seconds_ago = max(0, 300 - ($on['date'] - $now));
                    if ($seconds_ago < 30) {
                        $act_text = 'Hozirgina faol';
                    } else {
                        $act_text = floor($seconds_ago / 60) . ' daqiqa oldin';
                    }
                    $is_me = ($on['ip'] === ($_SERVER['REMOTE_ADDR'] ?? ''));
                ?>
                <tr style="border-bottom:1px solid #222; <?=($is_me ? 'background:#24201a;' : '')?>">
                    <td style="padding:8px 10px; color:#777;"><?=$i++?></td>
                    <td style="padding:8px 10px; font-family:monospace; color:#fff;">
                        <?=$on['ip']?>
                        <?php if ($is_me): ?>
                            <span style="color:#ff9900; font-size:11px; margin-left:6px; font-weight:bold;">(Siz - Admin)</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:8px 10px;">
                        <span style="display:inline-flex; align-items:center; gap:5px; color:#28a745; font-size:12px;">
                            <span style="width:7px; height:7px; border-radius:50%; background:#28a745;"></span> Onlayn
                        </span>
                    </td>
                    <td style="padding:8px 10px; color:#888; font-size:12px;">
                        <?=$act_text?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div style="color:#777; padding:15px; text-align:center; font-style:italic;">
        Hozircha onlayn tashrif buyuruvchilar mavjud emas.
    </div>
    <?php endif; ?>
</div>