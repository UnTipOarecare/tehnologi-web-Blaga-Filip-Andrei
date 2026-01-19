<?php
// pornim sesiunea imediat (fără output înainte)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// includem DB (presupunem că nu produce output)
require __DIR__ . '/includes/db.php';

$page_title = "Login";

$errors = [];
$next = $_GET['next'] ?? ($_POST['next'] ?? null);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $errors[] = 'Completează username și parola.';
    } else {
        $stmt = $pdo->prepare("SELECT id, username, parola, nume, rol FROM user WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        if ($user) {
            $hashedInput = hash('sha256', $password);
            if (hash_equals($user['parola'], $hashedInput)) {
                // success: setează sesiunea
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nume'] = $user['nume'] ?? '';
                $_SESSION['rol'] = $user['rol'] ?? 'client';

                // redirect către cont.php
                header('Location: cont.php');
                exit;
            } else {
                $errors[] = 'Parola incorecta.';
            }
        } else {
            $errors[] = 'Utilizator inexistent.';
        }
    }
}

// acum includem headerul — aici începe outputul HTML
require __DIR__ . '/includes/header.php';
?>
<section class="main-content">
  <h2>Login</h2>

  <?php if ($errors): ?>
    <div style="color:#c0392b;margin-bottom:12px;">
      <ul>
        <?php foreach ($errors as $err): ?>
          <li><?php echo htmlspecialchars($err); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form action="login.php" method="post">
    <input type="hidden" name="next" value="<?php echo htmlspecialchars($next ?? ''); ?>">
    <label for="login-username">Username:</label>
    <input id="login-username" name="username" type="text" required>

    <label for="login-pass">Parola:</label>
    <input id="login-pass" name="password" type="password" required>

    <input type="submit" value="Login" class="btn" style="margin-top:10px;">
  </form>

  <p>Nu ai cont? <a href="inregistrare.php">inregistreaza-te</a></p>
</section>

<aside class="sidebar">
  <h2>Ajutor</h2>
  <p>Contact suport: <a href="mailto:caloriferulmeudevis2@hotmail.tr">caloriferulmeudevis2@hotmail.tr</a></p>
</aside>

<?php require __DIR__ . '/includes/footer.php'; ?>
