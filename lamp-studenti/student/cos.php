<?php
$page_title = "Cos de cumparaturi";
require __DIR__ . '/includes/header.php';
?>

<section class="main-content">
  <h1>Coșul tău de cumpărături</h1>

  <div id="cart-items" style="margin-top:12px;">
  </div>

  <div style="margin-top:18px;">
    <p id="cart-total">Total: 0.00 lei</p>
    <div style="display:flex;gap:10px;margin-top:8px;">
      <a href="produse.php" class="btn" style="background:#7f8c8d">Continuă cumpărăturile</a>

      <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id']): ?>
        <button id="checkout-btn" class="btn" type="button">Spre plată</button>

        <form id="checkout-form" action="plata.php" method="post" style="display:none;">
          <input type="hidden" name="cart" id="checkout-cart" value="">
        </form>
      <?php else: ?>
        <a href="login.php?next=plata.php" class="btn" style="background:#2980b9">Autentifică-te pentru plată</a>
      <?php endif; ?>
    </div>
  </div>
</section>

<aside class="sidebar" aria-labelledby="aside-cart">
  <h2 id="aside-cart">Sumă plată</h2>
  <p>Total: 0.00 lei</p>
  <p><a href="produse.php">Continuă cumpărăturile</a></p>
</aside>

<?php require __DIR__ . '/includes/footer.php'; ?>
