<?php

/*
Автор скрипта https://3020.ru
Скрипты, программы на заказ.
Быстро, качественно, недорого.
*/

    if (!isset($user) || $user['access'] < 1) {
        header('Location: /'); 
        exit;
    }
    
    $del_id = abs(intval($_GET['id'] ?? 0));
    $view = $mysqli->query("SELECT id, screenshot, recoil, address, name, category FROM ero_files WHERE id = '$del_id'")->fetch_assoc();
     
    if (!$view) {
        header('Location: /');
        exit;
    }
    
    $category = $mysqli->query("SELECT id, translit FROM ero_categories WHERE id = '{$view['category']}'")->fetch_assoc();
    $cat_slug = !empty($category['translit']) ? $category['translit'] : '';
    
    // Fayllarni serverdan o'chirish
    if (!empty($view['screenshot']) && file_exists($_SERVER['DOCUMENT_ROOT'] . $view['screenshot']) && strpos($view['screenshot'], 'no_poster') === false) {
        @unlink($_SERVER['DOCUMENT_ROOT'] . $view['screenshot']);
    }
    if (!empty($view['recoil']) && file_exists($_SERVER['DOCUMENT_ROOT'] . $view['recoil'])) {
        @unlink($_SERVER['DOCUMENT_ROOT'] . $view['recoil']);
    }
    if (!empty($view['address']) && strpos($view['address'], '/content/') === 0 && file_exists($_SERVER['DOCUMENT_ROOT'] . $view['address'])) {
        @unlink($_SERVER['DOCUMENT_ROOT'] . $view['address']);
    }
    
    // Bog'liq jadvallarni tozalash
    $mysqli->query("DELETE FROM ero_files WHERE id = '$del_id'");
    $mysqli->query("DELETE FROM ero_likes WHERE id_video = '$del_id'");
    $mysqli->query("DELETE FROM ero_comments WHERE id_video = '$del_id'");
    $mysqli->query("DELETE FROM ero_favorites WHERE id_video = '$del_id'");
    
    logs($user['id'], "Videoni o‘chirdi: " . $view['name'], 0);
    
    // Keshni tozalash
    @array_map('unlink', glob($_SERVER['DOCUMENT_ROOT'] . '/content/cache/*.html'));
 
    if (!empty($cat_slug)) {
        header('Location: /' . $cat_slug . '/');
    } else {
        header('Location: /');
    }
    exit;