<?php
header('Content-Type: application/json; charset=utf-8');

if (!isset($_POST['id']) || empty($_POST['text'])) {
    echo json_encode(['status' => 'error', 'message' => 'Izoh matni bo‘sh bo‘lishi mumkin emas!']);
    exit;
}

$id = abs(intval($_POST['id']));
$author = trim(filter($_POST['author'] ?? ''));
if (empty($author)) {
    $author = 'Anonim';
}
$author = mb_substr($author, 0, 50, 'UTF-8');

$text = trim(filter($_POST['text'] ?? ''));
if (mb_strlen($text, 'UTF-8') < 2) {
    echo json_encode(['status' => 'error', 'message' => 'Izoh matni juda qisqa!']);
    exit;
}
$text = mb_substr($text, 0, 1000, 'UTF-8');

$ip = mysqli_real_escape_string($mysqli, filter($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'));

// Anti-spam cooldown (10 soniya)
$last_comm = $mysqli->query("SELECT date FROM ero_comments WHERE ip = '$ip' ORDER BY id DESC LIMIT 1")->fetch_assoc();
if ($last_comm && (time() - intval($last_comm['date'])) < 10) {
    echo json_encode(['status' => 'error', 'message' => 'Iltimos, keyingi izohni 10 soniyadan so‘ng qoldiring!']);
    exit;
}

$safe_author = mysqli_real_escape_string($mysqli, $author);
$safe_text = mysqli_real_escape_string($mysqli, $text);
$now = time();

$insert = $mysqli->query("INSERT INTO ero_comments (id_video, author, text, ip, date) VALUES ('$id', '$safe_author', '$safe_text', '$ip', '$now')");

if ($insert) {
    $count_q = $mysqli->query("SELECT COUNT(*) FROM ero_comments WHERE id_video = '$id'")->fetch_row();
    $total_count = intval($count_q[0]);

    $time_str = time_ago($now);
    $html = '<div class="comment-item">
        <div class="comment-meta">
            <span class="comment-author"><i class="fa fa-user-circle"></i> '.htmlspecialchars($author, ENT_QUOTES, 'UTF-8').'</span>
            <span class="comment-date"><i class="fa fa-clock-o"></i> '.$time_str.'</span>
        </div>
        <div class="comment-text">'.nl2br(htmlspecialchars($text, ENT_QUOTES, 'UTF-8')).'</div>
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
