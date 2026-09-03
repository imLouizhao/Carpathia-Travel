<?php
session_start();

require 'config.php';
require 'functions.php';

requireRole(['agent', 'admin'], 'index.php');

refreshUserRoleFromDb(); 

$csrf_token = csrfEnsureToken();

$role = currentUserRole();
$tab = $_GET['tab'] ?? 'orders';
if (!in_array($tab, ['orders','users','products'], true)) {
    $tab = 'orders';
}

$success_message = $_SESSION['success_message'] ?? null;
$error_message   = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

function badgeStatus(string $s): string {
    $map = [
        'in_asteptare' => 'În așteptare',
        'confirmata'   => 'Confirmată',
        'anulata'      => 'Anulată'
    ];
    return $map[$s] ?? $s;
}

$pageTitle = 'Panou administrare - Carpathia Travel';
$pageDescription = 'Panou intern de administrare Carpathia Travel.';
$noIndex = true;
?>

<?php require 'header.php'; ?>

<main>
    <div class="container" style="margin-top:30px;">
        <div class="content-section">
            <h2 style="margin-bottom:10px;">Panou <?php echo esc(ucfirst($role)); ?></h2>

            <?php if ($success_message): ?>
                <div class="highlight" style="border-left-color:#28a745;">
                    <?php echo esc($success_message); ?>
                </div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="highlight" style="border-left-color:#dc3545;">
                    <?php echo esc($error_message); ?>
                </div>
            <?php endif; ?>

            <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:25px;">
                <a class="btn" href="admin_panel.php?tab=orders" style="padding:10px 18px; <?php echo $tab==='orders'?'opacity:1':'opacity:0.8'; ?>">
                    <i class="fas fa-receipt"></i> Comenzi
                </a>
                <?php if (hasRole('admin')): ?>
                    <a class="btn" href="admin_panel.php?tab=products" style="padding:10px 18px; <?php echo $tab==='products'?'opacity:1':'opacity:0.8'; ?>">
                        <i class="fas fa-suitcase-rolling"></i> Produse
                    </a>
                    <a class="btn" href="admin_panel.php?tab=users" style="padding:10px 18px; <?php echo $tab==='users'?'opacity:1':'opacity:0.8'; ?>">
                        <i class="fas fa-users"></i> Utilizatori
                    </a>
                <?php endif; ?>
            </div>

            <?php if ($tab === 'orders'): ?>
                <?php
                $orders = $conn->query(
                    "SELECT c.id, c.data_comanda, c.total, c.status, u.nume, u.email\n".
                    "FROM comenzi c\n".
                    "JOIN utilizatori u ON u.id = c.id_utilizator\n".
                    "ORDER BY c.data_comanda DESC, c.id DESC"
                );
                ?>

                <h3 style="margin-bottom:15px;">Gestionare comenzi</h3>

                <div style="display:flex; gap:10px; margin-bottom:15px; flex-wrap:wrap;">
                    <span style="align-self:center; color:#666;">Exportă:</span>
                    <a class="btn" style="padding:8px 14px;" href="export.php?entity=orders&amp;format=csv">
                        <i class="fas fa-file-csv"></i> CSV / Excel
                    </a>
                    <a class="btn" style="padding:8px 14px;" href="export.php?entity=orders&amp;format=doc">
                        <i class="fas fa-file-word"></i> Word
                    </a>
                    <a class="btn" style="padding:8px 14px;" href="export.php?entity=orders&amp;format=pdf">
                        <i class="fas fa-file-pdf"></i> PDF
                    </a>
                </div>

                <div style="overflow:auto;">
                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr style="text-align:left; border-bottom:2px solid #eee;">
                                <th style="padding:10px;">ID</th>
                                <th style="padding:10px;">Client</th>
                                <th style="padding:10px;">Email</th>
                                <th style="padding:10px;">Data</th>
                                <th style="padding:10px;">Total</th>
                                <th style="padding:10px;">Status</th>
                                <th style="padding:10px;">Acțiuni</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($orders && $orders->num_rows): ?>
                            <?php while ($o = $orders->fetch_assoc()): ?>
                                <tr style="border-bottom:1px solid #f0f0f0;">
                                    <td style="padding:10px;">#<?php echo (int)$o['id']; ?></td>
                                    <td style="padding:10px;"><?php echo esc($o['nume']); ?></td>
                                    <td style="padding:10px;"><?php echo esc($o['email']); ?></td>
                                    <td style="padding:10px; white-space:nowrap;"><?php echo esc($o['data_comanda']); ?></td>
                                    <td style="padding:10px; white-space:nowrap;"><?php echo number_format((float)$o['total'], 2, '.', ''); ?> lei</td>
                                    <td style="padding:10px;"><?php echo esc(badgeStatus((string)$o['status'])); ?></td>
                                    <td style="padding:10px;">
                                        <form method="POST" action="admin_actions.php" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                                            <input type="hidden" name="csrf_token" value="<?php echo esc($csrf_token); ?>">
                                            <input type="hidden" name="action" value="update_order_status">
                                            <input type="hidden" name="id_comanda" value="<?php echo (int)$o['id']; ?>">
                                            <select name="status" required>
                                                <option value="in_asteptare" <?php echo ($o['status']==='in_asteptare')?'selected':''; ?>>În așteptare</option>
                                                <option value="confirmata" <?php echo ($o['status']==='confirmata')?'selected':''; ?>>Confirmată</option>
                                                <option value="anulata" <?php echo ($o['status']==='anulata')?'selected':''; ?>>Anulată</option>
                                            </select>
                                            <button class="btn" type="submit" style="padding:8px 14px;">Salvează</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" style="padding:15px; color:#666;">Nu există comenzi.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($tab === 'users' && hasRole('admin')): ?>
                <?php
                $users = $conn->query("SELECT id, nume, email, COALESCE(rol,'client') AS rol, data_inregistrare FROM utilizatori ORDER BY id DESC");
                ?>
                <h3 style="margin-bottom:15px;">Gestionare utilizatori</h3>
                <div style="overflow:auto;">
                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr style="text-align:left; border-bottom:2px solid #eee;">
                                <th style="padding:10px;">ID</th>
                                <th style="padding:10px;">Nume</th>
                                <th style="padding:10px;">Email</th>
                                <th style="padding:10px;">Rol</th>
                                <th style="padding:10px;">Înregistrare</th>
                                <th style="padding:10px;">Acțiuni</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($users && $users->num_rows): ?>
                            <?php while ($u = $users->fetch_assoc()): ?>
                                <tr style="border-bottom:1px solid #f0f0f0;">
                                    <td style="padding:10px;">#<?php echo (int)$u['id']; ?></td>
                                    <td style="padding:10px;"><?php echo esc($u['nume']); ?></td>
                                    <td style="padding:10px;"><?php echo esc($u['email']); ?></td>
                                    <td style="padding:10px;"><strong><?php echo esc($u['rol']); ?></strong></td>
                                    <td style="padding:10px; white-space:nowrap;"><?php echo esc($u['data_inregistrare']); ?></td>
                                    <td style="padding:10px;">
                                        <form method="POST" action="admin_actions.php" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                                            <input type="hidden" name="csrf_token" value="<?php echo esc($csrf_token); ?>">
                                            <input type="hidden" name="action" value="update_user_role">
                                            <input type="hidden" name="id_utilizator" value="<?php echo (int)$u['id']; ?>">
                                            <select name="rol" required>
                                                <option value="client" <?php echo ($u['rol']==='client')?'selected':''; ?>>client</option>
                                                <option value="agent" <?php echo ($u['rol']==='agent')?'selected':''; ?>>agent</option>
                                                <option value="admin" <?php echo ($u['rol']==='admin')?'selected':''; ?>>admin</option>
                                            </select>
                                            <button class="btn" type="submit" style="padding:8px 14px;">Schimbă rol</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="padding:15px; color:#666;">Nu există utilizatori.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($tab === 'products' && hasRole('admin')): ?>
                <?php
                $products = $conn->query("SELECT id, tip_pachet, plecare, destinatie, pret, durata, locuri_disponibile, data_plecare FROM produse ORDER BY id DESC");
                ?>
                <h3 style="margin-bottom:15px;">Gestionare produse</h3>

                <div style="display:flex; gap:10px; margin-bottom:15px; flex-wrap:wrap;">
                    <span style="align-self:center; color:#666;">Exportă:</span>
                    <a class="btn" style="padding:8px 14px;" href="export.php?entity=products&amp;format=csv">
                        <i class="fas fa-file-csv"></i> CSV / Excel
                    </a>
                    <a class="btn" style="padding:8px 14px;" href="export.php?entity=products&amp;format=doc">
                        <i class="fas fa-file-word"></i> Word
                    </a>
                    <a class="btn" style="padding:8px 14px;" href="export.php?entity=products&amp;format=pdf">
                        <i class="fas fa-file-pdf"></i> PDF
                    </a>
                </div>

                <div class="highlight" style="margin-bottom:25px;">
                    <strong>Adaugă produs nou</strong>
                    <form method="POST" action="admin_actions.php" style="margin-top:15px; display:grid; gap:10px; grid-template-columns: 1fr 1fr;">
                        <input type="hidden" name="csrf_token" value="<?php echo esc($csrf_token); ?>">
                        <input type="hidden" name="action" value="create_product">

                        <div>
                            <label>Tip pachet</label>
                            <input name="tip_pachet" required>
                        </div>
                        <div>
                            <label>Preț (lei)</label>
                            <input name="pret" type="number" step="0.01" min="0" required>
                        </div>
                        <div>
                            <label>Plecare</label>
                            <input name="plecare" required>
                        </div>
                        <div>
                            <label>Destinație</label>
                            <input name="destinatie" required>
                        </div>
                        <div>
                            <label>Durată (zile)</label>
                            <input name="durata" type="number" min="1" required>
                        </div>
                        <div>
                            <label>Locuri disponibile</label>
                            <input name="locuri_disponibile" type="number" min="0" required value="20">
                        </div>
                        <div>
                            <label>Data plecare</label>
                            <input name="data_plecare" type="date">
                        </div>
                        <div style="grid-column:1 / -1;">
                            <label>Descriere</label>
                            <textarea name="descriere" required style="width:100%; height:90px;"></textarea>
                        </div>
                        <div style="grid-column:1 / -1;">
                            <button class="btn" type="submit">Adaugă produs</button>
                        </div>
                    </form>
                </div>

                <div style="overflow:auto;">
                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr style="text-align:left; border-bottom:2px solid #eee;">
                                <th style="padding:10px;">ID</th>
                                <th style="padding:10px;">Tip</th>
                                <th style="padding:10px;">Destinație</th>
                                <th style="padding:10px;">Preț</th>
                                <th style="padding:10px;">Durată</th>
                                <th style="padding:10px;">Locuri</th>
                                <th style="padding:10px;">Data</th>
                                <th style="padding:10px;">Acțiuni</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($products && $products->num_rows): ?>
                            <?php while ($p = $products->fetch_assoc()): ?>
                                <tr style="border-bottom:1px solid #f0f0f0;">
                                    <td style="padding:10px;">#<?php echo (int)$p['id']; ?></td>
                                    <td style="padding:10px;"><?php echo esc($p['tip_pachet']); ?></td>
                                    <td style="padding:10px;"><?php echo esc($p['destinatie']); ?></td>
                                    <td style="padding:10px;"><?php echo number_format((float)$p['pret'], 2, '.', ''); ?> lei</td>
                                    <td style="padding:10px;"><?php echo (int)$p['durata']; ?> zile</td>
                                    <td style="padding:10px;"><?php echo (int)$p['locuri_disponibile']; ?></td>
                                    <td style="padding:10px; white-space:nowrap;"><?php echo esc($p['data_plecare']); ?></td>
                                    <td style="padding:10px;">
                                        <form method="POST" action="admin_actions.php" onsubmit="return confirm('Sigur ștergi produsul?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo esc($csrf_token); ?>">
                                            <input type="hidden" name="action" value="delete_product">
                                            <input type="hidden" name="id_produs" value="<?php echo (int)$p['id']; ?>">
                                            <button class="btn" type="submit" style="padding:8px 14px; background:#dc3545;">Șterge</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="8" style="padding:15px; color:#666;">Nu există produse.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="color:#666;">Tab indisponibil pentru rolul tău.</p>
            <?php endif; ?>

        </div>
    </div>
</main>

<?php require 'footer.php'; ?>
