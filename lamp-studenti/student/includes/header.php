<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!doctype html>
<html lang="ro">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Magazin de Calorifere'; ?></title>
  <link rel="stylesheet" href="styles.css">
  <script src="cart.js?v=<?= time() ?>" defer></script>
  <script src="background-geom.js" defer></script>
</head>
<body id="top">
  <div class="bg-geom" data-theme="vibrant" aria-hidden="true"></div>

  <header class="site-header">
    <a class="logo" href="pagina principala.php">CALORIFERE</a>
    <div class="header-right">
      <a href="cont.php" class="account-icon"><img src="cont.png" alt="Cont"></a>
      <a href="cos.php" class="cart-icon" aria-label="Cos">
        <img src="cos.png" alt="Cos">
        <span id="cart-count" aria-hidden="true" style="display:none"></span>
      </a>
    </div>
  </header>

  <nav class="site-nav" aria-label="Meniu produse">
    <ul>
      <li><a href="index.php">Acasa</a></li>
      <li><a href="produse.php">Produse</a></li>
      <li><a href="informatii.php">Informatii</a></li>
    </ul>
  </nav>

  <main class="grid-layout">
