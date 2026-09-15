<?php

/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/

    $translit = mysqli_real_escape_string($mysqli, filter($_GET['translit'] ?? ''));
    $view = $mysqli->query("SELECT * FROM ero_files WHERE translit = '$translit' LIMIT 1")->fetch_assoc();
  
    if (!$view) {
        header('Location: /');
        exit;
    }
    
    // Yangilash
    if (isset($_GET['refresh'])) {
        if (10000000 > size($view['address'])) {
            $refresh = $mysqli->query("SELECT * FROM ero_files WHERE server != '{$view['server']}' ORDER BY RAND() LIMIT 1")->fetch_assoc();
            if ($refresh) {
                $mysqli->query("UPDATE ero_files SET address = '{$refresh['address']}' WHERE id = '{$view['id']}'");
                array_map('unlink', glob($_SERVER['DOCUMENT_ROOT']."/content/cache/*.html"));
            }
        }
        header('Location: /watch/'.$view['translit'].'.html');     
        exit;
    }
    
    $category = $mysqli->query("SELECT * FROM ero_categories WHERE id = '{$view['category']}'")->fetch_assoc();
    $favorites = $mysqli->query("SELECT COUNT(*) FROM ero_favorites WHERE id_video = '{$view['id']}'")->fetch_row();

    function sec($var) {
        $parts = explode(':', $var);
        if (count($parts) == 3) {
            return intval($parts[0]) * 3600 + intval($parts[1]) * 60 + intval($parts[2]);
        }
        return (intval($parts[0] ?? 0) * 60) + intval($parts[1] ?? 0);
    }

    // Sevimlilar toggle
    if (isset($_GET['favorites'])) {
        $user_ip = mysqli_real_escape_string($mysqli, filter($_SERVER['REMOTE_ADDR']));
        $fav_my = $mysqli->query("SELECT COUNT(*) FROM ero_favorites WHERE id_video = '{$view['id']}' AND data = '$user_ip'")->fetch_row();
        
        if ($fav_my[0] == 0) {
            $mysqli->query("INSERT INTO ero_favorites (id_video, data) VALUES ('{$view['id']}', '$user_ip')");
            track_activity('favorite', $view['id']);
        } else {
            $mysqli->query("DELETE FROM ero_favorites WHERE id_video = '{$view['id']}' AND data = '$user_ip'");
            track_activity('unfavorite', $view['id']);
        }
        header('Location: /watch/'.$view['translit'].'.html');     
        exit;
    }

    $title = $view['name'].' - '.filter($_SERVER['HTTP_HOST']);
    $description = !empty($view['description']) ? mb_substr(strip_tags($view['description']), 0, 160, 'UTF-8') : $view['name'];
    $keywords = !empty($view['tags']) ? htmlspecialchars(trim($view['tags'], ', ')) : $view['name'];
    // Ko'rishlar sonini oshirish (Faqat haqiqiy tashrif buyuruvchilar, botlar va reload spamdan himoyalangan)
    $video_id = intval($view['id']);
    if (!is_crawler_or_bot()) {
        if (!isset($_SESSION['viewed_videos'])) {
            $_SESSION['viewed_videos'] = [];
        }
        
        $cookie_name = 'v_seen_' . $video_id;
        $already_viewed = isset($_SESSION['viewed_videos'][$video_id]) || isset($_COOKIE[$cookie_name]);
        
        if (!$already_viewed) {
            $_SESSION['viewed_videos'][$video_id] = time();
            @setcookie($cookie_name, '1', time() + 86400, '/');
            $mysqli->query("UPDATE ero_files SET view = view + 1 WHERE id = '{$video_id}'");
            $view['view'] = intval($view['view']) + 1;
            track_activity('view', $video_id);
        }
    }
    
    $has_valid_thumb = (!empty($view['screenshot']) && $view['screenshot'] != '/designs/water.png');
    $video_poster = $has_valid_thumb ? $view['screenshot'] : '/designs/no_poster.jpg';
        
    head(sec($view['duration']), $video_poster, 'video.other');
    advertising();
    
    if (!empty($view['tags'])) {
        if (strpos($view['tags'], ',') !== false) {
            $tags = array_filter(array_map('trim', explode(',', $view['tags'])));
        } else {
            $tags_raw = tags($view['tags']);
            $tags = !empty($tags_raw) ? explode(' ', $tags_raw) : [];
        }
    } else {
        $tags = [];
    }

    // Ovozlar va foiz
    $likes_count = intval($view['likes']);
    $dislikes_count = intval($view['dislikes']);
    $total_votes = $likes_count + $dislikes_count;
    $rating_percent = $total_votes > 0 ? round(($likes_count / $total_votes) * 100) : 98;
    
    // Foydalanuvchi ovozi
    $client_ip = mysqli_real_escape_string($mysqli, filter($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'));
    $user_vote_q = $mysqli->query("SELECT type FROM ero_likes WHERE id_video = '{$view['id']}' AND ip = '$client_ip' LIMIT 1")->fetch_assoc();
    $user_vote = $user_vote_q['type'] ?? '';

    $full_host = $protocol . filter($_SERVER['HTTP_HOST']);
    $thumb_full = (strpos($video_poster, 'http') === 0) ? $video_poster : $full_host . $video_poster;
    $page_url = $full_host . '/watch/' . $view['translit'] . '.html';
    
    // JSON-LD uchun contentUrl / embedUrl aniqlash
    $has_embed_ld = !empty($view['embed']) && (strpos($view['embed'], 'http://') === 0 || strpos($view['embed'], 'https://') === 0);
    $has_iframe_ld = !$has_embed_ld && strpos($view['address'] ?? '', '<iframe') !== false;
    
    if ($has_embed_ld) {
        $content_url_ld = htmlspecialchars(trim($view['embed']), ENT_QUOTES, 'UTF-8');
        $embed_url_ld = $content_url_ld;
    } elseif ($has_iframe_ld) {
        if (preg_match('/src=[\'"]([^\'"]+)[\'"]/', $view['address'], $iframe_m)) {
            $content_url_ld = htmlspecialchars(trim($iframe_m[1]), ENT_QUOTES, 'UTF-8');
            $embed_url_ld = $content_url_ld;
        } else {
            $content_url_ld = htmlspecialchars($page_url, ENT_QUOTES, 'UTF-8');
            $embed_url_ld = $content_url_ld;
        }
    } else {
        // Yerli video — /view_ stream URL
        $content_url_ld = htmlspecialchars($full_host . '/view_' . $view['translit'], ENT_QUOTES, 'UTF-8');
        $embed_url_ld = htmlspecialchars($page_url, ENT_QUOTES, 'UTF-8');
    }
?>

<!-- Schema.org JSON-LD (BreadcrumbList & VideoObject) -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "BreadcrumbList",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "Bosh sahifa",
          "item": "<?=$full_host?>/"
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": "<?=htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8')?>",
          "item": "<?=$full_host?>/<?=$category['translit']?>/"
        },
        {
          "@type": "ListItem",
          "position": 3,
          "name": "<?=htmlspecialchars($view['name'], ENT_QUOTES, 'UTF-8')?>"
        }
      ]
    },
    {
      "@type": "VideoObject",
      "name": "<?=htmlspecialchars($view['name'], ENT_QUOTES, 'UTF-8')?>",
      "description": "<?=htmlspecialchars($description, ENT_QUOTES, 'UTF-8')?>",
      "thumbnailUrl": [
        "<?=htmlspecialchars($thumb_full, ENT_QUOTES, 'UTF-8')?>"
      ],
      "uploadDate": "<?=date('c', $view['date'])?>",
      "duration": "PT<?=sec($view['duration'])?>S",
      "contentUrl": "<?=$content_url_ld?>",
      "embedUrl": "<?=$embed_url_ld?>",
      "isFamilyFriendly": false,
      "genre": "<?=htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8')?>",
      "interactionStatistic": [
        {
          "@type": "InteractionCounter",
          "interactionType": { "@type": "https://schema.org/WatchAction" },
          "userInteractionCount": <?=intval($view['view'])?>
        },
        {
          "@type": "InteractionCounter",
          "interactionType": { "@type": "https://schema.org/LikeAction" },
          "userInteractionCount": <?=$likes_count?>
        }
      ]
    }
  ]
}
</script>


<!-- Breadcrumb Title -->
<div class="xxxhd-title-top">
    <p style="font-size:13px; color:#959595; margin-bottom: 6px;">
        <a href="/"><i class="fa fa-home"></i> <?=$lang['home']?></a> &raquo; 
        <a href="/<?=$category['translit']?>/"><?=$category['name']?></a> &raquo; 
        <b style="color:var(--primary-accent, #ff9900);"><?=$view['name']?></b>
    </p>
    <h1><i class="fa fa-play-circle" style="color:var(--primary-accent, #ff9900);"></i> <?=$view['name']?></h1>
</div>

<!-- Responsive Player Container -->
<?php if (function_exists('ads_render_banner')) ads_render_banner('top'); ?>
<div class="video-player-container" style="position:relative;">
    <?php if (function_exists('ads_render_player_overlay')) ads_render_player_overlay(); ?>
    <div class="video-wrapper-responsive">
    <?php
    $cfg_ads = function_exists('ads_get_config') ? ads_get_config() : [];
    $vast_url = (!empty($cfg_ads['ads_enabled']) && !empty($cfg_ads['vast_preroll_enabled']) && !empty($cfg_ads['vast_preroll_url'])) ? $cfg_ads['vast_preroll_url'] : '';

    if (!empty($view['embed']) || strpos($view['address'], '<iframe') !== false) {
        // Iframe embed
        if (strpos($view['address'], '<iframe') !== false) {
            echo $view['address'];
        } else {
            $embed_src = !empty($view['embed']) ? $view['embed'] : $view['address'];
            echo '<iframe src="'.$embed_src.'" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';
        }
    } elseif ($view['server'] == 'vk.com' || $view['server'] == 'drive.google.com') {
        echo '<iframe src="'.$view['address'].'" frameborder="0" allowfullscreen></iframe>';
    } else {
        // Native HTML5 or Playerjs
        $preroll_js = !empty($vast_url) ? ', preroll: "'.$vast_url.'"' : '';
        if ($settings['player'] == 2) {
            echo '<script src="/core/javascript/playerjs.js" type="text/javascript"></script>
            <div id="player"></div>
            <script>
            var player = new Playerjs({
                id: "player",
                file: "/view_'.$view['translit'].'",
                poster: "'.$video_poster.'",
                title: "'.filter($_SERVER['HTTP_HOST']).'"' . $preroll_js . '
            });
            </script>';
        } else {
            echo '<video id="main-video-player" controls preload="metadata" poster="'.$video_poster.'">
                <source src="/view_'.$view['translit'].'" type="video/mp4">
                Sizning brauzeringiz HTML5 videoni qo‘llab-quvvatlamaydi.
            </video>';
        }
    }
    ?>
    </div>
</div>

<!-- Interactive Rating & Action Bar -->
<div class="interaction-bar">
    <div class="votes-group">
        <button class="like_btn <?=($user_vote === 'like' ? 'active' : '')?>" id="btn-like" onclick="voteVideo(<?=$view['id']?>, 'like')" title="Menga yoqdi">
            <i class="fa fa-thumbs-o-up"></i> <span id="like-count"><?=$likes_count?></span>
        </button>
        <span class="rating-badge" id="rate-percent"><i class="fa fa-heart"></i> <?=$rating_percent?>%</span>
        <button class="disLike_btn <?=($user_vote === 'dislike' ? 'active' : '')?>" id="btn-dislike" onclick="voteVideo(<?=$view['id']?>, 'dislike')" title="Menga yoqmadi">
            <i class="fa fa-thumbs-o-down"></i> <span id="dislike-count"><?=$dislikes_count?></span>
        </button>
    </div>

    <div class="video-actions-group">
        <a href="/watch/<?=$view['translit']?>.html?favorites" class="action-btn" title="<?=$lang['to_favorites']?>">
            <i class="fa fa-star" style="color:var(--primary-accent, #ff9900);"></i> <?=$lang['to_favorites']?> (<?=$favorites[0]?>)
        </a>
        <a href="/download/<?=$view['translit']?>.mp4" class="action-btn" title="<?=$lang['download']?>">
            <i class="fa fa-download"></i> <?=$lang['download']?> (<?=$view['downloads']?>)
        </a>
        <?php /* Vaqtinchalik yopildi:
        <a href="https://t.me/share/url?url=<?=$protocol.filter($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'])?>&text=<?=urlencode($view['name'])?>" target="_blank" class="action-btn" style="background:#0088cc;" title="Telegramda ulashish">
            <i class="fa fa-telegram"></i> Telegram
        </a>
        <a href="https://api.whatsapp.com/send?text=<?=urlencode($view['name'].' '.$protocol.filter($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']))?>" target="_blank" class="action-btn" style="background:#25d366;" title="WhatsAppda ulashish">
            <i class="fa fa-whatsapp"></i> WhatsApp
        </a>
        */ ?>
    </div>
</div>

<?php if (function_exists('ads_render_banner')) ads_render_banner('bottom'); ?>

<!-- Video Metadata & Statistics -->
<div class="video-meta-info">
    <span><i class="fa fa-calendar"></i> Sana: <b><?=date('d.m.Y', $view['date'])?></b></span>
    <span><i class="fa fa-eye"></i> Ko‘rishlar: <b><?=$view['view']?></b></span>
    <span><i class="fa fa-clock-o"></i> Davomiyligi: <b><?=$view['duration']?></b></span>
    <span><i class="fa fa-folder-open"></i> Bo‘lim: <a href="/<?=$category['translit']?>/" style="color:var(--primary-accent, #ff9900);"><b><?=$category['name']?></b></a></span>
</div>

<?php if (!empty($view['description'])): ?>
<div class="video-description-box">
    <?=$view['description']?>
</div>
<?php endif; ?>

<?php if (!empty($tags)): ?>
<div class="tags-cloud">
    <i class="fa fa-tags" style="color:var(--primary-accent, #ff9900); margin-right: 6px;"></i> Teglar: 
    <?php
    foreach ($tags as $t) {
        $t = trim($t);
        if (!empty($t)) {
            echo '<a href="/tag/'.urlencode($t).'" class="tag-badge">#'.$t.'</a> ';
        }
    }
    ?>
</div>
<?php endif; ?>

<?php if ($user && $user['access'] == 1): ?>
<div class="functions_data" style="margin-top:10px;">
    <b>Admin boshqaruvi:</b> 
    <a href="/editing_<?=$view['id']?>.html" style="color:#28a745; margin-right:15px;"><i class="fa fa-edit"></i> <?=$lang['edit']?></a>
    <a href="/deletion_<?=$view['id']?>.html" style="color:#dc3545;" onclick="return confirm('Haqiqatdan ham o‘chirmoqchimisiz?');"><i class="fa fa-trash"></i> <?=$lang['remove']?></a>
</div>
<?php endif; ?>

<!-- Comments Section (Izohlar) -->
<?php
$comments_q = $mysqli->query("SELECT * FROM ero_comments WHERE id_video = '{$view['id']}' ORDER BY id DESC LIMIT 50");
$comments_count = $comments_q ? $comments_q->num_rows : 0;
?>
<div class="comments-section">
    <div class="comments-header">
        <i class="fa fa-comments-o" style="color:var(--primary-accent, #ff9900); font-size: 20px;"></i> 
        Izohlar va Fikrlar <span id="comments-count">(<?=$comments_count?>)</span>
    </div>

    <!-- Add Comment Form -->
    <div class="comment-form">
        <form id="form-add-comment">
            <input type="hidden" name="id" value="<?=$view['id']?>" />
            <div class="form-group">
                <input type="text" name="author" class="form-control-custom" placeholder="Ismingiz (ixtiyoriy, standart: Anonim)" maxlength="50" />
            </div>
            <div class="form-group">
                <textarea name="text" id="comment-text" class="form-control-custom" placeholder="Video haqida fikringizni yozing..." required maxlength="1000"></textarea>
            </div>
            <button type="submit" class="btn-submit-comment" id="btn-submit-comm">
                <i class="fa fa-paper-plane"></i> Izoh qoldirish
            </button>
            <span id="comment-status" style="margin-left: 10px; font-size: 13px;"></span>
        </form>
    </div>

    <!-- Comments List -->
    <div class="comment-list" id="comments-list">
        <?php
        if ($comments_count > 0) {
            while ($comm = $comments_q->fetch_assoc()) {
                echo '<div class="comment-item">
                    <div class="comment-meta">
                        <span class="comment-author"><i class="fa fa-user-circle"></i> '.htmlspecialchars($comm['author'], ENT_QUOTES, 'UTF-8').'</span>
                        <span class="comment-date"><i class="fa fa-clock-o"></i> '.time_ago($comm['date']).'</span>
                    </div>
                    <div class="comment-text">'.nl2br(htmlspecialchars($comm['text'], ENT_QUOTES, 'UTF-8')).'</div>
                </div>';
            }
        } else {
            echo '<div id="no-comments-msg" style="color: #666; font-style: italic; padding: 10px 0;">Hozircha izohlar yo‘q. Birinchi bo‘lib fikringizni bildiring!</div>';
        }
        ?>
    </div>
</div>

<!-- Similar Videos Grid (O'xshash videolar) -->
<div class="xxxhd-title-top" style="margin-top: 20px;">
    <h2><i class="fa fa-random"></i> O‘xshash videolar</h2>
</div>

<div class="xxxhd-thumbs-content">
<?php
$similar_q = $mysqli->query("SELECT id, screenshot, name, translit, duration, view, likes, dislikes FROM ero_files WHERE category = '{$category['id']}' AND id != '{$view['id']}' AND date < '".time()."' ORDER BY RAND() LIMIT 8");

while ($sim = $similar_q->fetch_assoc()) {
    $tot_sim = intval($sim['likes']) + intval($sim['dislikes']);
    $rate_sim = $tot_sim > 0 ? round((intval($sim['likes']) / $tot_sim) * 100) . '%' : '98%';

    $sim_img = (!empty($sim['screenshot']) && $sim['screenshot'] != '/designs/water.png') ? $sim['screenshot'] : '/designs/no_poster.jpg';

    echo '<div class="xxxhd-thumb-wr">
        <div class="xxxhd-thumb">
            <a href="/watch/'.$sim['translit'].'.html" title="'.htmlspecialchars($sim['name'], ENT_QUOTES, 'UTF-8').'">
                <div class="thumb-image-wrap">
                    <img src="'.$sim_img.'" alt="'.htmlspecialchars($sim['name'], ENT_QUOTES, 'UTF-8').'" loading="lazy" onerror="this.onerror=null; this.src=\'/designs/no_poster.jpg\';" />
                </div>
                <div class="xxxhd-thumb-name" title="'.htmlspecialchars($sim['name'], ENT_QUOTES, 'UTF-8').'">'.$sim['name'].'</div>
            </a>
            <span class="xxxhd-thumb-top top-left">HD</span>
            <span class="xxxhd-thumb-top top-right"><i class="fa fa-thumbs-o-up"></i> '.$rate_sim.'</span>
            <span class="xxxhd-thumb-bottom bottom-left"><i class="fa fa-eye"></i> '.intval($sim['view']).'</span>
            <span class="xxxhd-thumb-bottom bottom-right"><i class="fa fa-clock-o"></i> '.$sim['duration'].'</span>
        </div>
    </div>';
}
?>
</div>
<?php if (function_exists('ads_render_native_grid')) ads_render_native_grid(); ?>

<!-- AJAX Scripts for Likes & Comments -->
<script>
function voteVideo(videoId, action) {
    $.getJSON('/index.php?func=ajax_rating', { id: videoId, action: action }, function(res) {
        if (res.status === 'success') {
            $('#like-count').text(res.likes);
            $('#dislike-count').text(res.dislikes);
            $('#rate-percent').html('<i class="fa fa-heart"></i> ' + res.percent);

            if (action === 'like') {
                $('#btn-like').addClass('active');
                $('#btn-dislike').removeClass('active');
            } else {
                $('#btn-dislike').addClass('active');
                $('#btn-like').removeClass('active');
            }
        } else {
            alert(res.message || 'Xatolik yuz berdi!');
        }
    }).fail(function() {
        alert('Server bilan aloqa uzildi!');
    });
}

$(document).ready(function() {
    $('#form-add-comment').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $('#btn-submit-comm');
        var $status = $('#comment-status');

        $btn.prop('disabled', true);
        $status.css('color', 'var(--primary-accent, #ff9900)').text('Yuborilmoqda...');

        $.post('/index.php?func=ajax_comment', $form.serialize(), function(res) {
            $btn.prop('disabled', false);
            if (res.status === 'success') {
                $status.css('color', '#28a745').text(res.message);
                $('#no-comments-msg').remove();
                $('#comments-list').prepend(res.html);
                $('#comment-text').val('');
                $('#comments-count').text('(' + res.total + ')');
                setTimeout(function() { $status.text(''); }, 3000);
            } else {
                $status.css('color', '#dc3545').text(res.message);
            }
        }, 'json').fail(function() {
            $btn.prop('disabled', false);
            $status.css('color', '#dc3545').text('Xatolik! Qayta urinib ko‘ring.');
        });
    });
});
</script>
