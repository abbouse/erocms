<?php

/*
 * DMCA (Digital Millennium Copyright Act) va Mualliflik Huquqlari Sahifasi
 * sekschi.online
 */

$mysqli->query("CREATE TABLE IF NOT EXISTS `ero_dmca` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `video_url` text NOT NULL,
  `message` text NOT NULL,
  `date` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `ip` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$title = 'DMCA / Mualliflik huquqi (Copyright Policy) - ' . filter($_SERVER['HTTP_HOST']);
$description = 'DMCA / Mualliflik huquqi bo‘yicha murojaat qilish sahifasi. Digital Millennium Copyright Act talablari bo‘yicha mualliflik huquqini himoya qilish.';
$keywords = 'dmca, copyright, mualliflik huquqi, shikoyat, murojaat, video ochirish';

head();

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_dmca'])) {
    $name = trim(filter($_POST['name'] ?? ''));
    $email = trim(filter($_POST['email'] ?? ''));
    $video_url = trim(filter($_POST['video_url'] ?? ''));
    $message = trim(filter($_POST['message'] ?? ''));
    $code = trim($_POST['code'] ?? '');

    $sess_code = $_SESSION['dmca_code'] ?? '';

    if (empty($name) || mb_strlen($name) < 2 || mb_strlen($name) > 100) {
        $error_msg = 'Iltimos, ismingiz yoki tashkilot nomini to‘g‘ri kiriting (2-100 belgi).';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = 'Iltimos, haqiqiy elektron pochta (Email) manzilini kiriting.';
    } elseif (empty($video_url) || mb_strlen($video_url) < 8) {
        $error_msg = 'Iltimos, saytimizdagi buzilgan video havolasini (URL) to‘liq kiriting.';
    } elseif (empty($message) || mb_strlen($message) < 10) {
        $error_msg = 'Iltimos, shikoyat mazmuni va mualliflik dalillarini batafsilroq yozing (kamida 10 belgi).';
    } elseif (empty($code) || $code != $sess_code) {
        $error_msg = 'Xavfsizlik kodi noto‘g‘ri kiritildi. Qayta urinib ko‘ring.';
    } else {
        $safe_name = mysqli_real_escape_string($mysqli, $name);
        $safe_email = mysqli_real_escape_string($mysqli, $email);
        $safe_url = mysqli_real_escape_string($mysqli, $video_url);
        $safe_msg = mysqli_real_escape_string($mysqli, $message);
        $now = time();
        $ip = mysqli_real_escape_string($mysqli, filter($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'));

        $ins = $mysqli->query("INSERT INTO ero_dmca (name, email, video_url, message, date, status, ip) 
            VALUES ('$safe_name', '$safe_email', '$safe_url', '$safe_msg', '$now', 0, '$ip')");

        if ($ins) {
            $success_msg = 'Murojaatingiz muvaffaqiyatli qabul qilindi! Sayt ma’muriyati 24 soat ichida materialni tekshirib, qonuniy chorani ko‘radi.';
            unset($_SESSION['dmca_code']);
        } else {
            $error_msg = 'Xatolik yuz berdi. Iltimos keyinroq qayta urinib ko‘ring.';
        }
    }
}

$_SESSION['dmca_code'] = rand(1000, 9999);
?>

<div class="xxxhd-title-top">
    <h2><i class="fa fa-shield" style="color:#ff9900;"></i> DMCA & Mualliflik huquqi (Copyright Policy)</h2>
</div>

<div class="functions_data" style="background:#181615; border:1px solid #333; padding:18px; margin-bottom:20px; line-height:1.6; color:#ddd; font-size:14px; border-radius:4px;">
    <h3 style="color:#ff9900; margin-top:0;"><i class="fa fa-info-circle"></i> Huquq egalari diqqatiga (Notice to Copyright Holders)</h3>
    <p>
        <b><?=filter($_SERVER['HTTP_HOST'])?></b> veb-sayti boshqa ochiq internet manbalaridagi video oqimlarini (embed / iframe) o‘z ichiga olgan axborot vositachisi hisoblanadi. Biz mualliflik huquqlari to‘g‘risidagi qonunchilikni va <b>Digital Millennium Copyright Act (DMCA)</b> talablarini to‘liq hurmat qilamiz hamda qo‘llab-quvvatlaymiz.
    </p>
    <p>
        Agar siz saytimizda joylashtirilgan biror kontentning qonuniy mualliflik huquqi egasi bo‘lsangiz va ushbu video sizning ruxsatingizsiz joylashtirilgan deb hisoblasangiz, iltimos, quyidagi forma orqali bizga xabar bering. Murojaat olingandan so‘ng shikoyat qilingan material <b>24 soat ichida</b> saytimizdan butunlay olib tashlanadi yoki bloklanadi.
    </p>
</div>

<?php if (!empty($success_msg)): ?>
<div class="functions_data" style="background:#142918; border:1px solid #28a745; color:#7de89a; padding:16px; margin-bottom:20px; border-radius:4px; font-size:14px;">
    <i class="fa fa-check-circle" style="font-size:18px;"></i> <?=$success_msg?>
</div>
<?php endif; ?>

<?php if (!empty($error_msg)): ?>
<div class="functions_data" style="background:#2b1517; border:1px solid #dc3545; color:#f87171; padding:14px; margin-bottom:20px; border-radius:4px; font-size:14px;">
    <i class="fa fa-exclamation-triangle" style="font-size:16px;"></i> <?=$error_msg?>
</div>
<?php endif; ?>

<div class="functions_data" style="background:#1e1c1a; border:1px solid #383634; padding:20px; border-radius:4px;">
    <h3 style="color:#ff9900; margin:0 0 16px 0;"><i class="fa fa-envelope-o"></i> Videoni o‘chirish bo‘yicha murojaat shakli</h3>

    <form method="post" action="/dmca.html">
        <input type="hidden" name="send_dmca" value="1" />

        <div style="margin-bottom:14px;">
            <label style="display:block; font-weight:bold; margin-bottom:5px; color:#eee;">Ismingiz yoki tashkilotingiz / muallif nomi: <span style="color:red;">*</span></label>
            <input type="text" name="name" class="injected" style="width:100%; box-sizing:border-box; padding:8px 10px; background:#121110; color:#fff; border:1px solid #555; border-radius:4px;" placeholder="Masalan: Studio LLC yoki Ism Familiya" required value="<?=htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8')?>" />
        </div>

        <div style="margin-bottom:14px;">
            <label style="display:block; font-weight:bold; margin-bottom:5px; color:#eee;">Bog‘lanish uchun Email manzilingiz: <span style="color:red;">*</span></label>
            <input type="email" name="email" class="injected" style="width:100%; box-sizing:border-box; padding:8px 10px; background:#121110; color:#fff; border:1px solid #555; border-radius:4px;" placeholder="admin@domain.com" required value="<?=htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8')?>" />
        </div>

        <div style="margin-bottom:14px;">
            <label style="display:block; font-weight:bold; margin-bottom:5px; color:#eee;">Saytimizdagi shikoyat qilinayotgan video havolasi (URL): <span style="color:red;">*</span></label>
            <input type="url" name="video_url" class="injected" style="width:100%; box-sizing:border-box; padding:8px 10px; background:#121110; color:#fff; border:1px solid #555; border-radius:4px;" placeholder="https://sekschi.online/watch/video_nomi_123.html" required value="<?=htmlspecialchars($_POST['video_url'] ?? '', ENT_QUOTES, 'UTF-8')?>" />
        </div>

        <div style="margin-bottom:14px;">
            <label style="display:block; font-weight:bold; margin-bottom:5px; color:#eee;">Mualliflik isboti va shikoyat tafsilotlari: <span style="color:red;">*</span></label>
            <textarea name="message" class="injected" rows="5" style="width:100%; box-sizing:border-box; padding:8px 10px; background:#121110; color:#fff; border:1px solid #555; border-radius:4px;" placeholder="Asl nusxaga havola yoki mualliflik huquqini tasdiqlovchi ma'lumotlar..." required><?=htmlspecialchars($_POST['message'] ?? '', ENT_QUOTES, 'UTF-8')?></textarea>
        </div>

        <div style="margin-bottom:18px; display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
            <div>
                <label style="display:block; font-weight:bold; margin-bottom:5px; color:#eee;">Xavfsizlik kodi: <span style="color:red;">*</span></label>
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="background:#ff9900; color:#000; font-weight:bold; font-size:18px; padding:6px 14px; border-radius:4px; letter-spacing:2px;"><?=$_SESSION['dmca_code']?></span>
                    <input type="number" name="code" class="injected" style="width:110px; padding:8px 10px; background:#121110; color:#fff; border:1px solid #555; border-radius:4px; font-size:16px; font-weight:bold;" placeholder="Kodni yozing" required />
                </div>
            </div>
        </div>

        <button type="submit" class="action-btn" style="background:#ff9900; color:#000; font-weight:bold; font-size:14px; padding:10px 22px; border:none; border-radius:4px; cursor:pointer;">
            <i class="fa fa-paper-plane"></i> Murojaatni yuborish
        </button>
    </form>
</div>

<?php
foot();
