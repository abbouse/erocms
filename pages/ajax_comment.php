<?php
header('Content-Type: application/json; charset=utf-8');

if (!isset($_POST['id']) || empty($_POST['text'])) {
    echo json_encode(['status' => 'error', 'message' => ($lang['comment_empty'] ?? 'Izoh matni bo‘sh bo‘lishi mumkin emas!')]);
    exit;
}

$id = abs(intval($_POST['id']));

// Muallifni aniqlash
if ($member) {
    $author = $member['username'];
} else {
    $author = trim(filter($_POST['author'] ?? ''));
    if (empty($author)) {
        $author = $lang['anonymous'] ?? 'Anonim';
    }
}
$author = mb_substr($author, 0, 50, 'UTF-8');

$text = trim(filter($_POST['text'] ?? ''));
if (mb_strlen($text, 'UTF-8') < 2) {
    echo json_encode(['status' => 'error', 'message' => ($lang['comment_too_short'] ?? 'Izoh matni juda qisqa!')]);
    exit;
}
$text = mb_substr($text, 0, 1000, 'UTF-8');

$ip = mysqli_real_escape_string($mysqli, filter($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'));

// Anti-spam cooldown (10 soniya)
$last_comm = $mysqli->query("SELECT date FROM ero_comments WHERE ip = '$ip' ORDER BY id DESC LIMIT 1")->fetch_assoc();
if ($last_comm && (time() - intval($last_comm['date'])) < 10) {
    echo json_encode(['status' => 'error', 'message' => ($lang['comment_flood_wait'] ?? 'Iltimos, keyingi izohni 10 soniyadan so‘ng qoldiring!')]);
    exit;
}

$safe_author = mysqli_real_escape_string($mysqli, $author);
$safe_text = mysqli_real_escape_string($mysqli, $text);
$now = time();

$insert = $mysqli->query("INSERT INTO ero_comments (id_video, author, text, ip, date) VALUES ('$id', '$safe_author', '$safe_text', '$ip', '$now')");

if ($insert) {
    $new_comm_id = $mysqli->insert_id;
    @$mysqli->query("UPDATE ero_files SET comments_count = comments_count + 1, score = score + 20 WHERE id = '$id'");

    // Javob (Reply / Mention) berilgan foydalanuvchini aniqlash
    // Masalan: "@jasur_77," yoki "jasur_77," yoki "@jasur_77"
    if (preg_match_all('/(?:@([a-zA-Z0-9_\-\.]{3,30})|^([a-zA-Z0-9_\-\.]{3,30})[,:])/u', $text, $matches, PREG_SET_ORDER)) {
        $mentioned_usernames = [];
        foreach ($matches as $m) {
            $cand = !empty($m[1]) ? $m[1] : (!empty($m[2]) ? $m[2] : '');
            if (!empty($cand)) $mentioned_usernames[] = $cand;
        }
        $mentioned_usernames = array_unique($mentioned_usernames);

        foreach ($mentioned_usernames as $m_user) {
            $safe_mu = mysqli_real_escape_string($mysqli, $m_user);
            $target_q = $mysqli->query("SELECT id, username FROM ero_members WHERE username = '$safe_mu' AND status = 1 LIMIT 1");
            if ($target_q && $target_q->num_rows > 0) {
                $target_row = $target_q->fetch_assoc();
                $target_id = intval($target_row['id']);

                // O'ziga o'zi bildirishnoma yubormaslik
                if ($member && intval($member['id']) === $target_id) {
                    continue;
                }

                // Xabarnoma yozish
                $mysqli->query("
                    INSERT INTO ero_notifications 
                    (member_id, from_author, id_video, comment_id, text, is_read, date) 
                    VALUES 
                    ('$target_id', '$safe_author', '$id', '$new_comm_id', '$safe_text', 0, '$now')
                ");
            }
        }
    }

    $count_q = $mysqli->query("SELECT COUNT(*) FROM ero_comments WHERE id_video = '$id'")->fetch_row();
    $total_count = intval($count_q[0]);

    $time_str = time_ago($now);
    $html = '<div class="comment-item">
        <div class="comment-meta">
            <span class="comment-author"><i class="fa fa-user-circle"></i> '.htmlspecialchars($author, ENT_QUOTES, 'UTF-8').'</span>
            <span class="comment-date"><i class="fa fa-clock-o"></i> '.$time_str.'</span>
        </div>
        <div class="comment-text">'.nl2br(htmlspecialchars($text, ENT_QUOTES, 'UTF-8')).'</div>
        <div class="comment-actions">
            <button type="button" class="btn-comment-reply" onclick="replyComment(\''.htmlspecialchars($author, ENT_QUOTES, 'UTF-8').'\')">
                <i class="fa fa-reply"></i> Javob berish
            </button>
        </div>
    </div>';

    echo json_encode([
        'status' => 'success',
        'message' => 'Izohingiz muvaffaqiyatli qo‘shildi!',
        'total' => $total_count,
        'html' => $html
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Baza xatosi!']);
}
exit;
