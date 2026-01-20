<?php
session_start();
require __DIR__ . '/includes/db.php';

/* -------------------------
   VALIDARE ID PRODUS
-------------------------- */
$id_produs = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_produs <= 0) {
    http_response_code(404);
    require __DIR__ . '/includes/header.php';
    echo "<h1>Produs invalid</h1>";
    require __DIR__ . '/includes/footer.php';
    exit;
}

/* -------------------------
   PRODUS
-------------------------- */
$stmt = $pdo->prepare("
    SELECT p.*, c.denumire AS categorie
    FROM produse p
    LEFT JOIN categorii c ON p.id_categorie = c.id
    WHERE p.id = ?
");
$stmt->execute([$id_produs]);
$prod = $stmt->fetch();

if (!$prod) {
    http_response_code(404);
    require __DIR__ . '/includes/header.php';
    echo "<h1>Produs inexistent</h1>";
    require __DIR__ . '/includes/footer.php';
    exit;
}

/* -------------------------
   RECENZII
-------------------------- */
$stmt = $pdo->prepare("
    SELECT r.rating, r.comentariu, u.username
    FROM recenzii r
    JOIN user u ON r.id_utilizator = u.id
    WHERE r.id_produs = ?
    ORDER BY r.id DESC
");
$stmt->execute([$id_produs]);
$reviews = $stmt->fetchAll();

/* -------------------------
   VERIFICARE DREPT RECENZIE
-------------------------- */
$canReview = false;

if (isset($_SESSION['user_id'])) {
    $userId = (int)$_SESSION['user_id'];

    // 1. ia toate comenzile livrate ale userului
    $stmt = $pdo->prepare("
        SELECT id
        FROM comenzi
        WHERE id_utilizator = ?
          AND status = 'livrata'
    ");
    $stmt->execute([$userId]);
    $comenziLivrate = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if ($comenziLivrate) {
        // 2. verifica daca produsul exista in detalii_comenzi
        $in  = implode(',', array_fill(0, count($comenziLivrate), '?'));
        $sql = "
            SELECT 1
            FROM detalii_comenzi
            WHERE id_produs = ?
              AND id_comanda IN ($in)
            LIMIT 1
        ";

        $params = array_merge([$id_produs], $comenziLivrate);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $hasReceived = (bool)$stmt->fetchColumn();

        // 3. verifica daca deja a lasat recenzie
        $stmt = $pdo->prepare("
            SELECT 1
            FROM recenzii
            WHERE id_utilizator = ?
              AND id_produs = ?
            LIMIT 1
        ");
        $stmt->execute([$userId, $id_produs]);
        $alreadyReviewed = (bool)$stmt->fetchColumn();

        $canReview = $hasReceived && !$alreadyReviewed;
    }
}

$page_title = $prod['denumire'];
require __DIR__ . '/includes/header.php';

$img = $prod['img'] ? 'images/' . htmlspecialchars($prod['img']) : 'placeholder.png';
$price = number_format((float)$prod['pret'], 2, ',', '.');
?>

<main class="grid-layout">
<article class="main-content">

<h1><?= htmlspecialchars($prod['denumire']) ?></h1>

<img src="<?= $img ?>" alt="">
<p><strong><?= $price ?> lei</strong></p>
<p><?= nl2br(htmlspecialchars($prod['descriere'])) ?></p>

<hr>

<h2>Recenzii</h2>

<?php if (!$reviews): ?>
    <p>Nu există recenzii.</p>
<?php else: ?>
    <?php foreach ($reviews as $r): ?>
        <div style="margin-bottom:15px;">
            <strong><?= htmlspecialchars($r['username']) ?></strong>
            — <?= (int)$r['rating'] ?>/5
            <p><?= nl2br(htmlspecialchars($r['comentariu'])) ?></p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<hr>

<?php if (!isset($_SESSION['user_id'])): ?>
    <p>Trebuie să fii logat pentru a scrie o recenzie.</p>

<?php elseif ($canReview): ?>
    <h3>Scrie o recenzie</h3>

    <form action="adauga_recenzie.php" method="post">
        <input type="hidden" name="id_produs" value="<?= $id_produs ?>">

        <label>Rating:</label>
        <select name="rating" required>
            <option value="5">5</option>
            <option value="4">4</option>
            <option value="3">3</option>
            <option value="2">2</option>
            <option value="1">1</option>
        </select>

        <br><br>

        <textarea name="comentariu" required rows="4" cols="50"
                  placeholder="Comentariul tău..."></textarea>

        <br><br>

        <button type="submit" class="btn">Trimite recenzia</button>
    </form>

<?php else: ?>
    <p>Poți scrie o recenzie doar dacă ai primit produsul și nu ai mai lăsat una.</p>
<?php endif; ?>

</article>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
