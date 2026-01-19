<?php
// pornim sesiunea imediat (fără output înainte)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// includem DB (presupunem că nu produce output)
require __DIR__ . '/includes/db.php';

$page_title = "Inregistrare";

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nume = trim($_POST['nume'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefon = trim($_POST['telefon'] ?? '');
    $parola = $_POST['parola'] ?? '';

    if ($nume === '') $errors[] = 'Completează numele.';
    if ($username === '') $errors[] = 'Alege un username.';
    if ($parola === '' || strlen($parola) < 6) $errors[] = 'Parola trebuie să aibă cel puțin 6 caractere.';
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalid.';

    if (empty($errors)) {
        // make sure username is unique
        $stmt = $pdo->prepare("SELECT id FROM user WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $existing = $stmt->fetch();
        if ($existing) {
            $errors[] = 'Username-ul este deja folosit. Alege altul.';
        } else {
            // hash password with SHA-256 (to match DB's SHA2 expectation)
            $hashed = hash('sha256', $parola);

            $ins = $pdo->prepare("INSERT INTO user (username, parola, nume, email) VALUES (:username, :parola, :nume, :email)");
            try {
                $ins->execute([
                    'username' => $username,
                    'parola' => $hashed,
                    'nume' => $nume,
                    'email' => $email ?: null
                ]);
                // auto-login: set session and redirect to account page
                $userId = $pdo->lastInsertId();
                $_SESSION['user_id'] = (int)$userId;
                $_SESSION['username'] = $username;
                $_SESSION['nume'] = $nume;
                $success = true;

                // redirect către cont.php
                header('Location: cont.php');
                exit;
            } catch (\PDOException $e) {
                $errors[] = 'Eroare la crearea contului. Încearcă din nou.';
            }
        }
    }
}

// acum includem headerul — aici începe outputul HTML
require __DIR__ . '/includes/header.php';
?>
<section class="main-content">
  <h2>Pagina de inregistrare</h2>

  <?php if ($errors): ?>
    <div style="color:#c0392b;margin-bottom:12px;">
      <ul>
        <?php foreach ($errors as $err): ?>
          <li><?php echo htmlspecialchars($err); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form action="inregistrare.php" method="post" novalidate>
    <label for="nume">Nume:</label>
    <input id="nume" name="nume" type="text" value="<?php echo isset($nume) ? htmlspecialchars($nume) : ''; ?>" required>

    <label for="username">Username:</label>
    <input id="username" name="username" type="text" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required>

    <label for="email">Email:</label>
    <input id="email" name="email" type="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">

    <label for="telefon">Telefon:</label>
    <input id="telefon" name="telefon" type="tel" value="<?php echo isset($telefon) ? htmlspecialchars($telefon) : ''; ?>">

    <label for="parola">Parola:</label>
    <input id="parola" name="parola" type="password" required>

    <input type="submit" value="Inregistreaza-te" class="btn" style="margin-top:10px;">
  </form>

  <p>Ai deja cont? <a href="login.php">Autentifica-te</a></p>
</section>

<aside class="sidebar">
  <h2>Ajutor</h2>
  <p>Contact suport: <a href="mailto:caloriferulmeudevis2@hotmail.tr">caloriferulmeudevis2@hotmail.tr</a></p>
</aside>

<?php require __DIR__ . '/includes/footer.php'; ?>
