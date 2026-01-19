<?php
require __DIR__ . '/includes/db.php';
session_start();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        $errors[] = 'Trebuie să fii logat pentru a plasa comanda.';
    }

    $tipPlata = $_POST['tip-plata'] ?? '';
    $numeLivrare = trim($_POST['nume-livrare'] ?? '');
    $adresa = trim($_POST['adresa-livrare'] ?? '');
    $oras = trim($_POST['oras-livrare'] ?? '');
    $codPostal = trim($_POST['cod-postal'] ?? '');
    $telefon = trim($_POST['telefon-livrare'] ?? '');
    $cartJson = $_POST['cart_data'] ?? '[]';

    if (!$numeLivrare || !$adresa || !$oras || !$codPostal || !$telefon) {
        $errors[] = 'Completează toate câmpurile de livrare.';
    }

    $cartItems = json_decode($cartJson, true);
    if (empty($cartItems)) {
        $errors[] = 'Coșul este gol.';
    }

    // card validation for card payment
    if ($tipPlata === 'card') {
        $card = trim($_POST['card'] ?? '');
        $expirare = trim($_POST['expirare'] ?? '');
        $cvv = trim($_POST['cvv'] ?? '');
        if (!$card || !$expirare || !$cvv) {
            $errors[] = 'Completează toate câmpurile cardului.';
        } elseif (!preg_match('/^\d{16}$/', $card)) {
            $errors[] = 'Număr card invalid.';
        } elseif (!preg_match('/^\d{3}$/', $cvv)) {
            $errors[] = 'CVV invalid.';
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            // insert order
            $stmt = $pdo->prepare("INSERT INTO comenzi (id_utilizator) VALUES (:id_utilizator)");
            $stmt->execute(['id_utilizator' => $_SESSION['user_id']]);
            $orderId = $pdo->lastInsertId();

            // insert each item into detalii_comenzi
            $stmtItem = $pdo->prepare("INSERT INTO detalii_comenzi (id_comanda, id_produs, cantitate) VALUES (:id_comanda, :id_produs, :cantitate)");
            foreach ($cartItems as $item) {
                $stmtItem->execute([
                    'id_comanda' => $orderId,
                    'id_produs' => $item['id'],
                    'cantitate' => $item['qty'] ?? 1
                ]);
            }

            $pdo->commit();
            $success = true;

            header('Location: comanda plasata.php?id=' . $orderId);
            exit;

        } catch (\PDOException $e) {
            $pdo->rollBack();
            $errors[] = 'Eroare la procesarea comenzii. Încearcă din nou.';
        }
    }
}

$page_title = "Plata";
require __DIR__ . '/includes/header.php';
?>

<section class="main-content">
  <h2>Pagina de Plata</h2>

  <?php if ($errors): ?>
    <div style="color:#c0392b;margin-bottom:12px;">
      <ul>
        <?php foreach ($errors as $err): ?>
          <li><?php echo htmlspecialchars($err); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form id="plata-form" action="plata.php" method="post" autocomplete="off">
    <h3>Date de livrare</h3>

    <label for="nume-livrare">Nume complet:</label>
    <input id="nume-livrare" name="nume-livrare" type="text" required>

    <label for="adresa-livrare">Adresa:</label>
    <input id="adresa-livrare" name="adresa-livrare" type="text" required>

    <label for="oras-livrare">Oras:</label>
    <input id="oras-livrare" name="oras-livrare" type="text" required>

    <label for="cod-postal">Cod postal:</label>
    <input id="cod-postal" name="cod-postal" type="text" required>

    <label for="telefon-livrare">Telefon:</label>
    <input id="telefon-livrare" name="telefon-livrare" type="tel" required>

    <h3>Date de plata</h3>

    <label for="tip-plata">Tip plata:</label>
    <select id="tip-plata" name="tip-plata" required>
      <option value="card">Card bancar</option>
      <option value="ramburs">Ramburs</option>
    </select>

    <div id="card-details" aria-hidden="false" style="margin-top:12px;">
      <label for="card">Numar card:</label>
      <input id="card" name="card" type="text" pattern="\d{16}" inputmode="numeric" maxlength="16">

      <label for="expirare">Data expirarii:</label>
      <input id="expirare" name="expirare" type="month">

      <label for="cvv">CVV:</label>
      <input id="cvv" name="cvv" type="text" pattern="\d{3}" inputmode="numeric" maxlength="3">
    </div>

    <input type="hidden" id="cart-data" name="cart_data">

    <input type="submit" value="Finalizeaza comanda" class="btn" style="margin-top:12px;">
  </form>

  <p style="margin-top:10px;"><a href="cos.php">Revizuieste cosul</a></p>
</section>

<aside class="sidebar">
  <h2>Siguranta</h2>
  <p>Nu stocam datele de plata precum card. Sau cel putin asta va spunem 😨</p>
</aside>

<?php require __DIR__ . '/includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const tipSelect = document.getElementById('tip-plata');
  const cardDetails = document.getElementById('card-details');
  const cartDataInput = document.getElementById('cart-data');

  const cardInputs = Array.from(cardDetails.querySelectorAll('input'));

  function setCardVisibility(show) {
    cardDetails.style.display = show ? '' : 'none';
    cardDetails.setAttribute('aria-hidden', show ? 'false' : 'true');
    cardInputs.forEach(input => {
      if (show) {
        input.removeAttribute('disabled');
        input.setAttribute('required', 'required');
      } else {
        input.removeAttribute('required');
        input.setAttribute('disabled', 'disabled');
        input.value = '';
      }
    });
  }

  tipSelect.addEventListener('change', function () {
    setCardVisibility(tipSelect.value === 'card');
  });

  setCardVisibility(tipSelect.value === 'card');

  const form = document.getElementById('plata-form');
  form.addEventListener('submit', function () {
    const cart = JSON.parse(localStorage.getItem('calorifere_cart') || '[]');
    cartDataInput.value = JSON.stringify(cart);

    // Clear cart
    localStorage.removeItem('calorifere_cart');
  });
});
</script>
