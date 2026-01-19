<?php
$page_title = "Cos de cumparaturi";
require __DIR__ . '/includes/header.php';
?>

<section class="main-content">
  <h1>Coșul tău de cumpărături</h1>

  <div id="cart-items" style="margin-top:12px;">
    <!-- cart.js va popula acest container cu imagini dacă există -->
  </div>

  <div style="margin-top:18px;">
    <p id="cart-total">Total: 0.00 lei</p>
    <div style="display:flex;gap:10px;margin-top:8px;">
      <a href="produse.php" class="btn" style="background:#7f8c8d">Continuă cumpărăturile</a>

      <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id']): ?>
        <!-- logged in: keep the checkout button & hidden POST form (as before) -->
        <button id="checkout-btn" class="btn" type="button">Spre plată</button>

        <!-- optional: if your cart.js uses the checkout-form to POST cart JSON, keep it -->
        <form id="checkout-form" action="plata.php" method="post" style="display:none;">
          <input type="hidden" name="cart" id="checkout-cart" value="">
        </form>
      <?php else: ?>
        <!-- not logged in: send them to login and return to plata.php afterwards -->
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
