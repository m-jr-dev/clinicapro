<?php
session_start();
require_once __DIR__ . "/config/db.php";

$msg = "";

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $email = $_POST['email'] ?? '';
    $pass  = $_POST['password'] ?? '';

    $st = $pdo->prepare("SELECT * FROM users WHERE email=? AND password=SHA2(?,256)");
    $st->execute([$email, $pass]);
    $u = $st->fetch();

    if($u){
        $_SESSION['user'] = $u;
        header("Location: dashboard.php");
        exit;
    } else {
        $msg = "Email ou senha inválidos.";
    }
}
?>
<!doctype html>
<html data-theme="dark">
<head>
<meta charset="utf-8">
<title>ClínicaPRO - Acesso ao Sistema</title>

<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/login.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>
<body>

<div class="login-page">

  <div class="login-card">

    <div class="login-brand">
      <i class="fa-solid fa-shield-heart"></i>
      ClínicaPRO
    </div>

    <div class="login-sub">
      Acesso seguro ao sistema da clínica
    </div>

    <form method="post">

      <div class="login-field">
        <label>Email</label>
        <div class="login-input">
          <i class="fa-solid fa-envelope"></i>
          <input name="email" type="email" required placeholder="seu@email.com">
        </div>
      </div>

      <div class="login-field">
        <label>Senha</label>
        <div class="login-input">
          <i class="fa-solid fa-lock"></i>
          <input name="password" type="password" required placeholder="••••••••">
        </div>
      </div>

      <button class="login-btn" type="submit">
        Entrar no sistema
      </button>

      <?php if($msg): ?>
        <div class="login-error"><?php echo htmlspecialchars($msg); ?></div>
      <?php endif; ?>

    </form>

    <div class="login-footer">
      <span>ClínicaPRO © <?php echo date('Y'); ?></span>
      <a href="#">Esqueci a senha</a>
    </div>

  </div>

</div>

</body>
</html>
