<?php
$page_title = "Produse — Magazin Calorifere";
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/db.php';

$catStmt = $pdo->query("SELECT id, denumire FROM categorii ORDER BY denumire ASC");
$categorii = $catStmt->fetchAll();

$selectedCats = [];
if (isset($_GET['cat']) && is_array($_GET['cat'])) {
    $selectedCats = array_values(array_unique(array_map('intval', $_GET['cat'])));
}

if (count($selectedCats) > 0) {
    $placeholders = implode(',', array_fill(0, count($selectedCats), '?'));
    $sql = "SELECT id, denumire, descriere, pret, img, id_categorie FROM produse
            WHERE id_categorie IN ($placeholders)
            ORDER BY denumire ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($selectedCats);
} else {
    $stmt = $pdo->query("SELECT id, denumire, descriere, pret, img, id_categorie FROM produse ORDER BY denumire ASC");
}
$produse = $stmt->fetchAll();

$clearUrl = strtok($_SERVER["REQUEST_URI"], '?'); 
?>
<main class="grid-layout">
  <section class="main-content">
    <h1>Produse</h1>

    <section class="products-section" aria-labelledby="produse-heading">
      <h2 id="produse-heading">Catalog</h2>

      <p style="margin-top:8px;">
        <?php if (count($selectedCats) > 0): ?>
          Filtrate după <?php echo count($selectedCats); ?> categorie<?php echo (count($selectedCats) > 1 ? 'i' : ''); ?>.
          <a href="<?php echo $clearUrl; ?>" style="margin-left:8px;">Șterge filtre</a>
        <?php else: ?>
          Afisează toate produsele (<?php echo count($produse); ?>).
        <?php endif; ?>
      </p>

      <div class="product-list" style="margin-top:12px;">
        <?php if (!$produse): ?>
          <p>Nu există produse pentru criteriile selectate.</p>
        <?php endif; ?>

        <?php foreach ($produse as $prod):
          $id = (int)$prod['id'];
          $title = htmlspecialchars($prod['denumire']);
          $price = number_format((float)$prod['pret'], 2, ',', '.');
          $img = $prod['img'] ? 'images/' . htmlspecialchars($prod['img']) : 'placeholder.png';
        ?>
        <article class="product-card" data-id="<?php echo $id; ?>">
          <a class="product-thumb" href="product.php?id=<?php echo $id; ?>">
            <img src="<?php echo $img; ?>" alt="<?php echo $title; ?>">
          </a>
          <h3 class="product-title"><a href="product.php?id=<?php echo $id; ?>"><?php echo $title; ?></a></h3>
          <p class="product-price"><?php echo $price; ?> lei</p>
          <div class="add-to-cart-wrap">
            <button type="button" class="btn go-to-product" data-id="<?php echo $id; ?>">Vezi detalii</button>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    </section>
  </section>

  <!-- Sidebar -->
  <aside class="sidebar" aria-labelledby="aside-products">
    <h2 id="aside-products">Filtre</h2>

    <?php if (!$categorii): ?>
      <p>Nu există categorii definite.</p>
    <?php else: ?>
      <form id="filter-form" method="get" action="<?php echo htmlspecialchars($clearUrl); ?>">
        <fieldset>
          <legend>Filtrează după categorie</legend>

          <?php foreach ($categorii as $cat):
            $cid = (int)$cat['id'];
            $label = htmlspecialchars($cat['denumire']);
            $checked = in_array($cid, $selectedCats, true);
          ?>
            <div class="checkbox" style="display:flex; align-items:center; gap:6px; margin-bottom:6px;">
              <input type="checkbox"
                     name="cat[]"
                     id="cat-<?php echo $cid; ?>"
                     value="<?php echo $cid; ?>"
                     <?php echo $checked ? 'checked' : ''; ?>
                     style="margin:0; flex-shrink:0;"
                     onchange="document.getElementById('filter-form').submit();">
              <label for="cat-<?php echo $cid; ?>" style="display:inline; margin:0; cursor:pointer;">
                <?php echo $label; ?>
              </label>
            </div>
          <?php endforeach; ?>
        </fieldset>

        <div style="margin-top:12px;">
          <button type="submit" class="btn">Aplică filtre</button>
          <a href="<?php echo $clearUrl; ?>" class="btn" style="margin-left:8px;">Șterge filtre</a>
        </div>
      </form>
    <?php endif; ?>
  </aside>

  <?php require __DIR__ . '/includes/footer.php'; ?>
</main>

<script>
document.querySelectorAll('#filter-form input[type="checkbox"]').forEach(cb => {
  cb.addEventListener('keydown', e => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      cb.checked = !cb.checked;
      document.getElementById('filter-form').submit();
    }
  });
});

document.querySelectorAll('.go-to-product').forEach(btn => {
  btn.addEventListener('click', () => {
    const id = btn.dataset.id;
    window.location.href = 'product.php?id=' + encodeURIComponent(id);
  });
});
</script>
