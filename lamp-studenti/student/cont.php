<?php
session_start();

require __DIR__ . '/includes/db.php';

// redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$page_title = "Detalii Cont";

// fetch user info
$userId = $_SESSION['user_id'];
$stmtUser = $pdo->prepare("SELECT nume, email FROM user WHERE id = :id LIMIT 1");
$stmtUser->execute(['id' => $userId]);
$user = $stmtUser->fetch();

// fetch orders
$stmtOrders = $pdo->prepare("
    SELECT c.id AS comanda_id, c.data_comenzii, c.status, 
           p.id AS produs_id, p.denumire, dc.cantitate
    FROM comenzi c
    LEFT JOIN detalii_comenzi dc ON c.id = dc.id_comanda
    LEFT JOIN produse p ON dc.id_produs = p.id
    WHERE c.id_utilizator = :user_id
    ORDER BY c.data_comenzii DESC, c.id ASC
");
$stmtOrders->execute(['user_id' => $userId]);
$ordersRaw = $stmtOrders->fetchAll();

// organize orders
$orders = [];
foreach ($ordersRaw as $row) {
    $oid = $row['comanda_id'];
    if (!isset($orders[$oid])) {
        $orders[$oid] = [
            'date' => $row['data_comenzii'],
            'status' => $row['status'],
            'products' => []
        ];
    }
    if ($row['produs_id']) {
        $orders[$oid]['products'][] = [
            'name' => $row['denumire'],
            'qty' => $row['cantitate']
        ];
    }
}

require __DIR__ . '/includes/header.php';
?>

<section class="main-content">
  <h1>Contul meu</h1>
  <div class="account-info" style="margin-top:12px;">
    <p><strong>Nume:</strong> <?php echo htmlspecialchars($user['nume'] ?? $_SESSION['username']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email'] ?? '—'); ?></p>

    <h2>Istoric comenzi</h2>
    <?php if (empty($orders)): ?>
        <p>Nu exista comenzi.</p>
    <?php else: ?>
        <?php foreach ($orders as $orderId => $order): ?>
            <div style="border:1px solid #ddd; padding:10px; margin-bottom:10px;">
                <p><strong>Comanda #<?php echo $orderId; ?></strong></p>
                <p><strong>Data:</strong> <?php echo htmlspecialchars($order['date']); ?></p>
                <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
                <?php if (!empty($order['products'])): ?>
                    <ul>
                        <?php foreach ($order['products'] as $prod): ?>
                            <li><?php echo htmlspecialchars($prod['name']); ?> x <?php echo (int)$prod['qty']; ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>(Fara produse listate)</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>

<aside class="sidebar" aria-labelledby="aside-account">
  <h2 id="aside-account">Setari cont</h2>
  <p>Bun venit, <strong><?php echo htmlspecialchars($user['nume'] ?? $_SESSION['username']); ?></strong></p>
  <p><a href="logout.php">Logout</a></p>
</aside>

<?php require __DIR__ . '/includes/footer.php'; ?>
