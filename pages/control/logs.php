<?php
/**
 * EroCMS Tizim Harakatlari Jurnali (Audit Logs)
 */

if (!defined('ADMIN_LOADED') && !isset($user)) {
    header('Location: /control.html');
    exit;
}

$filter_user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (isset($_GET['clear'])) {
    if ($filter_user_id > 0) {
        $mysqli->query("DELETE FROM ero_logs WHERE id_user = '$filter_user_id'");
    } else {
        $mysqli->query("TRUNCATE TABLE ero_logs");
    }
    logs($user['id'], 'Loglar tozalandi', 0);
    header('Location: /control.html?func=logs');
    exit;
}

$where_sql = ($filter_user_id > 0) ? "WHERE id_user = '$filter_user_id'" : "";

$per_page = 25;
$page = max(1, (int)($_GET['page'] ?? 1));
$start = ($page - 1) * $per_page;

$total_logs = (int)($mysqli->query("SELECT count(*) FROM ero_logs $where_sql")->fetch_row()[0] ?? 0);
$total_pages = ceil($total_logs / $per_page);

$query = $mysqli->query("
    SELECT l.*, u.disclosed 
    FROM ero_logs l 
    LEFT JOIN ero_users u ON l.id_user = u.id 
    $where_sql 
    ORDER BY l.id DESC 
    LIMIT $start, $per_page
");
?>

<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title"><i class="fa fa-history" style="color: #ff9900;"></i> Harakatlar Jurnali (Audit Logs)</h1>
        <p class="adm-page-subtitle">Adminlar tomonidan amalga oshirilgan barcha tizim harakatlari va o‘zgarishlar tarixi</p>
    </div>
    <div style="display:flex; gap:8px;">
        <?php if ($total_logs > 0): ?>
            <a href="/control.html?func=logs<?=($filter_user_id > 0 ? '&id='.$filter_user_id : '')?>&clear=1" class="adm-btn adm-btn-danger adm-btn-sm" onclick="return confirm('Loglarni tozalashni tasdiqlaysizmi?');">
                <i class="fa fa-trash"></i> Jurnalni tozalash
            </a>
        <?php endif; ?>
        <?php if ($filter_user_id > 0): ?>
            <a href="/control.html?func=logs" class="adm-btn adm-btn-secondary adm-btn-sm">
                <i class="fa fa-filter"></i> Barcha loglar
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="adm-card">
    <div class="adm-card-header">
        <h3 class="adm-card-title">
            <i class="fa fa-list"></i> Yozuvlar Ro‘yxati (Jami: <?=number_format($total_logs)?> ta)
        </h3>
    </div>

    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead>
                <tr>
                    <th width="50">ID</th>
                    <th width="140">Foydalanuvchi</th>
                    <th>Bajarilgan Amal / Harakat</th>
                    <th width="160">Sana & Vaqt</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($query && $query->num_rows > 0): ?>
                <?php while ($row = $query->fetch_assoc()): ?>
                <tr>
                    <td style="color:#64748b; font-weight:700;">#<?=$row['id']?></td>
                    <td>
                        <a href="/control.html?func=logs&id=<?=$row['id_user']?>" style="color:#ff9900; font-weight:600; text-decoration:none;">
                            <i class="fa fa-user"></i> <?=(!empty($row['disclosed']) ? htmlspecialchars($row['disclosed']) : 'User #'.$row['id_user'])?>
                        </a>
                    </td>
                    <td>
                        <span style="color:#e2e8f0; font-size:13px;">
                            <?=htmlspecialchars($row['act'])?>
                        </span>
                        <?php if ($row['id_file'] > 0): ?>
                            &nbsp;<a href="/control.html?func=rewriting&id=<?=$row['id_file']?>" style="color:#60a5fa; font-size:11px; text-decoration:none;">
                                [Video #<?=$row['id_file']?>]
                            </a>
                        <?php endif; ?>
                    </td>
                    <td style="color:#94a3b8; font-size:12px;">
                        <i class="fa fa-clock-o"></i> <?=date('d.m.Y H:i:s', $row['date'])?>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align:center; padding:30px; color:#64748b;">
                        Hozircha hech qanday log yozuvlari mavjud emas.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($total_pages > 1): ?>
<div class="adm-pagination">
    <?php
    for ($p = 1; $p <= $total_pages; $p++) {
        $prefix = '/control.html?func=logs' . ($filter_user_id > 0 ? '&id=' . $filter_user_id : '') . '&page=';
        if ($p == $page) {
            echo '<span class="active">' . $p . '</span>';
        } else {
            echo '<a href="' . $prefix . $p . '">' . $p . '</a>';
        }
    }
    ?>
</div>
<?php endif; ?>