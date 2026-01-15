<?php
require_once __DIR__ . "/includes/auth.php";

$user = $_SESSION['user'];
$userId = $user['id'];

$msgOk = $_SESSION['flash_ok'] ?? "";
$msgErr = $_SESSION['flash_err'] ?? "";

unset($_SESSION['flash_ok'], $_SESSION['flash_err']);

/* Busca dados do usuário */
$st = $pdo->prepare("SELECT * FROM users WHERE id=?");
$st->execute([$userId]);
$u = $st->fetch();

/* Busca dados do paciente se existir */
$st = $pdo->prepare("SELECT * FROM patients WHERE user_id=?");
$st->execute([$userId]);
$patient = $st->fetch();

/* Atualizar perfil */
if(isset($_POST['update_profile'])){
    $name  = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $birth = $_POST['birthdate'] ?? null;
    $sex   = $_POST['sex'] ?? null;
    $height= $_POST['height_cm'] ?? null;

    if(!$name || !$email){
        $msgErr = "Nome e email são obrigatórios.";
    } else {
        $st = $pdo->prepare("UPDATE users SET name=?, email=? WHERE id=?");
        $st->execute([$name, $email, $userId]);

        if($patient){
            $st = $pdo->prepare("UPDATE patients SET birthdate=?, sex=?, height_cm=? WHERE user_id=?");
            $st->execute([$birth, $sex, $height, $userId]);
        } else {
            $st = $pdo->prepare("INSERT INTO patients (user_id,birthdate,sex,height_cm) VALUES (?,?,?,?)");
            $st->execute([$userId,$birth,$sex,$height]);
        }

        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['email'] = $email;

       $_SESSION['flash_ok'] = "Perfil atualizado com sucesso.";
        header("Location: perfil.php");
        exit;
    }
}

/* Alterar senha */
if(isset($_POST['update_password'])){
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if(!$current || !$new || !$confirm){
        $msgErr = "Preencha todos os campos de senha.";
    } else if($new !== $confirm){
        $msgErr = "A nova senha e a confirmação não coincidem.";
    } else {
        $st = $pdo->prepare("SELECT * FROM users WHERE id=? AND password=SHA2(?,256)");
        $st->execute([$userId, $current]);
        if(!$st->fetch()){
            $msgErr = "Senha atual incorreta.";
        } else {
            $st = $pdo->prepare("UPDATE users SET password=SHA2(?,256) WHERE id=?");
            $st->execute([$new, $userId]);
            
            $_SESSION['flash_ok'] = "Senha alterada com sucesso.";
            header("Location: perfil.php");
            exit;
        }
    }
}

/* Dark Mode */

/* Busca preferência de tema */
$stPref = $pdo->prepare("SELECT dark_mode FROM user_preferences WHERE user_id=?");
$stPref->execute([$userId]);
$pref = $stPref->fetch();

$theme = "dark"; // padrão

if($pref){
    $theme = $pref['dark_mode'] ? "dark" : "light";
}

?>
<!doctype html>
<html data-theme="<?php echo $theme; ?>">
<head>
<meta charset="utf-8">
<title>ClínicaPRO - Meu Perfil</title>

<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/perfil.css">
<link rel="stylesheet" href="assets/css/dark.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>
<body>

<div class="app">

<aside class="sidebar">
  <div class="brand"><i class="fa-solid fa-shield-heart"></i> ClínicaPRO</div>

  <div class="nav-title">PRINCIPAL</div>
  <nav class="nav">
    <a href="dashboard.php"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
    <a href="#"><i class="fa-regular fa-calendar"></i> Agendamentos</a>
    <a href="#"><i class="fa-solid fa-chart-line"></i> Evolução</a>
    <a href="bioimpedancia.php"><i class="fa-solid fa-dna"></i> Bioimpedância</a>
  </nav>

  <div class="nav-title">CONTA</div>
  <nav class="nav">
    <a class="active"><i class="fa-regular fa-user"></i> Meu Perfil</a>
    <a href="#"><i class="fa-solid fa-gear"></i> Configurações</a>
  </nav>
</aside>

<main class="main">

<div class="topbar">
 <div>
  <div style="color:#2dd4bf;font-weight:800">ClínicaPRO</div>
  <div class="muted">Meu Perfil</div>
 </div>
</div>

<?php if($msgOk): ?>
<div class="perfil-ok"><?php echo $msgOk; ?></div>
<?php endif; ?>

<?php if($msgErr): ?>
<div class="perfil-err"><?php echo $msgErr; ?></div>
<?php endif; ?>

<div class="perfil-grid">

<div class="card">

<h3 class="section-title">Dados do Perfil</h3>

<form method="post">

<div class="perfil-grid-form">

<div class="perfil-field">
<label>Nome</label>
<input name="name" value="<?php echo htmlspecialchars($u['name']); ?>">
</div>

<div class="perfil-field">
<label>Email</label>
<input name="email" value="<?php echo htmlspecialchars($u['email']); ?>">
</div>

<div class="perfil-field">
<label>Data de nascimento</label>
<input type="date" name="birthdate" value="<?php echo $patient['birthdate'] ?? ''; ?>">
</div>

<div class="perfil-field">
<label>Sexo</label>
<select name="sex">
 <option value="">Selecione</option>
 <option value="F" <?php if(($patient['sex'] ?? '')=='F') echo 'selected'; ?>>Feminino</option>
 <option value="M" <?php if(($patient['sex'] ?? '')=='M') echo 'selected'; ?>>Masculino</option>
</select>
</div>

<div class="perfil-field">
<label>Altura (cm)</label>
<input name="height_cm" value="<?php echo $patient['height_cm'] ?? ''; ?>">
</div>

</div>

<button class="perfil-btn" name="update_profile">Salvar dados</button>

</form>

</div>

<div class="card">

<h3 class="section-title">Alterar Senha</h3>

<form method="post">

<div class="perfil-grid-form">

<div class="perfil-field">
<label>Senha atual</label>
<input type="password" name="current_password">
</div>

<div class="perfil-field">
<label>Nova senha</label>
<input type="password" name="new_password">
</div>

<div class="perfil-field">
<label>Confirmar nova senha</label>
<input type="password" name="confirm_password">
</div>

</div>

<button class="perfil-btn danger" name="update_password">Alterar senha</button>

</form>

</div>

</div>

</main>
</div>

</body>
</html>
