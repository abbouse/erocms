<?php
header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['id']) || !isset($_GET['action'])) {
    echo json_encode(['status' => 'error', 'message' => 'Noto‘g‘ri parametrlar!']);
    exit;
}

$id = abs(intval($_GET['id']));
$action = $_GET['action'] === 'dislike' ? 'dislike' : 'like';
$ip = mysqli_real_escape_string($mysqli, filter($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'));

$check_video = $mysqli->query("SELECT id FROM ero_files WHERE id = '$id' LIMIT 1");
if (!$check_video || $check_video->num_rows == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Video topilmadi!']);
    exit;
}

// Ovoz tekshirish
$check_vote = $mysqli->query("SELECT id, type FROM ero_likes WHERE id_video = '$id' AND ip = '$ip' LIMIT 1");
if ($check_vote && $check_vote->num_rows > 0) {
    $existing = $check_vote->fetch_assoc();
    if ($existing['type'] === $action) {
        $msg = 'Siz allaqachon ovoz bergansiz!';
    } else {
        $mysqli->query("UPDATE ero_likes SET type = '$action', date = '".time()."' WHERE id = '{$existing['id']}'");
        $msg = 'Ovozingiz yangilandi!';
    }
} else {
    $mysqli->query("INSERT INTO ero_likes (id_video, ip, type, date) VALUES ('$id', '$ip', '$action', '".time()."')");
    $msg = 'Ovozingiz qabul qilindi!';
}

// Qayta hisoblash
$likes_q = $mysqli->query("SELECT COUNT(*) FROM ero_likes WHERE id_video = '$id' AND type = 'like'")->fetch_row();
$dislikes_q = $mysqli->query("SELECT COUNT(*) FROM ero_likes WHERE id_video = '$id' AND type = 'dislike'")->fetch_row();

$total_likes = intval($likes_q[0]);
$total_dislikes = intval($dislikes_q[0]);
$total = $total_likes + $total_dislikes;
$percent = $total > 0 ? round(($total_likes / $total) * 100) : 100;

$mysqli->query("UPDATE ero_files SET likes = '$total_likes', dislikes = '$total_dislikes' WHERE id = '$id'");

echo json_encode([
    'status' => 'success',
    'likes' => $total_likes,
    'dislikes' => $total_dislikes,
    'percent' => $percent . '%',
    'message' => $msg
]);
exit;
