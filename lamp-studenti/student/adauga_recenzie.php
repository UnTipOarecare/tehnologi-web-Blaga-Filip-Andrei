<?php
session_start();
require __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    die("Neautorizat");
}

$userId = (int)$_SESSION['user_id'];
$id_produs = (int)($_POST['id_produs'] ?? 0);
$rating = (int)($_POST['rating'] ?? 0);
$comentariu = trim($_POST['comentariu'] ?? '');

if ($id_produs <= 0 || $rating < 1 || $rating > 5 || $comentariu === '') {
    die("Date invalide");
}

/* -------------------------
   IA COMENZI LIVRATE
-------------------------- */
$stmt = $pdo->prepare("
    SELECT id
    FROM comenzi
    WHERE id_utilizator = ?
      AND status = 'livrata'
");
$stmt->execute([$userId]);
$comenziLivrate = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (!$comenziLivrate) {
    die("Nu ai comenzi livrate");
}

/* -------------------------
   VERIFICĂ PRODUS ÎN COMANDĂ
-------------------------- */
$in = implode(',', array_fill(0, count($comenziLivrate), '?'));

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

if (!$stmt->fetchColumn()) {
    die("Nu ai primit acest produs");
}

/* -------------------------
   BLOC DUPLICAT
-------------------------- */
$stmt = $pdo->prepare("
    SELECT 1
    FROM recenzii
    WHERE id_utilizator = ?
      AND id_produs = ?
");
$stmt->execute([$userId, $id_produs]);

if ($stmt->fetchColumn()) {
    die("Recenzie deja existentă");
}

/* -------------------------
   INSERARE
-------------------------- */
$stmt = $pdo->prepare("
    INSERT INTO recenzii (id_produs, id_utilizator, rating, comentariu)
    VALUES (?, ?, ?, ?)
");
$stmt->execute([$id_produs, $userId, $rating, $comentariu]);

header("Location: product.php?id=" . $id_produs);
exit;
