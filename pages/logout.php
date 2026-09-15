<?php
/**
 * EroCMS - Foydalanuvchi Chiqish (Logout)
 */

if ($member) {
    $m_id = intval($member['id']);
    @$mysqli->query("UPDATE ero_members SET token = '' WHERE id = '$m_id'");
}

unset($_SESSION['member_token']);
if (isset($_COOKIE['member_token'])) {
    setcookie('member_token', '', time() - 3600, '/');
}

header('Location: /');
exit;
