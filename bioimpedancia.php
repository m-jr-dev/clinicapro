<?php
require_once __DIR__ . "/includes/auth.php";

$userId = $_SESSION['user']['id'];

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
<title>ClínicaPRO - Bioimpedância</title>

<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/dark.css">
<link rel="stylesheet" href="assets/css/bioimpedancia.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<script src="assets/js/bioimpedancia.js" defer></script>
<script src="assets/js/dark.js" defer></script>
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
    <a class="active"><i class="fa-solid fa-dna"></i> Bioimpedância</a>
  </nav>

  <div class="nav-title">CONTA</div>
  <nav class="nav">
    <a href="perfil.php"><i class="fa-regular fa-user"></i> Meu Perfil</a>
    <a href="#"><i class="fa-solid fa-gear"></i> Configurações</a>
  </nav>
</aside>

<main class="main">

<div class="bio-header">
  <div class="bio-header-left">
    <div class="bio-badge">METABOLISMO · BIOIMPEDÂNCIA · MONJARO</div>
    <h1>Calculadora de Déficit Calórico Diário</h1>
    <p>
      Estime gasto calórico, IMC e déficit do dia com base nos dados corporais e nível de atividade.
      Útil como apoio em estratégias com ou sem medicação e acompanhamento de bioimpedância.
    </p>
  </div>
</div>

<div class="bio-layout">

<!-- COLUNA ESQUERDA -->
<div class="bio-left">

<div class="bio-box">

<div class="bio-box-header">
  <div class="bio-box-title">
    <i class="fa-solid fa-fire-flame-curved"></i>
    Dados para cálculo
  </div>
  <div class="bio-box-tag">EXEMPLO EDUCATIVO</div>
</div>

<form id="form-deficit" class="bio-form">

<div class="bio-toggle">
  <label class="switch">
    <input type="checkbox" id="usa-monjarocheck">
    <span class="slider"></span>
  </label>
  <div>
    <i class="fa-solid fa-syringe"></i>
    <strong>Uso de Monjaro / outro GLP-1</strong>
    <div class="bio-muted">Opcional · ativa estimativa de perda e ajuste de meta calórica</div>
  </div>
</div>

<div id="monjaro-panel" class="monjaro-panel">

  <div class="bio-grid-2">

    <div class="bio-field">
      <label>Dose atual (mg)</label>
      <select id="monjaro-dose">
        <option value="">Selecione</option>
        <option value="2.5">2.5</option>
        <option value="5">5</option>
        <option value="7.5">7.5</option>
        <option value="10">10</option>
      </select>
    </div>

    <div class="bio-field">
      <label>Frequência de aplicação</label>
      <select id="monjaro-frequencia">
        <option value="1x_semana">1x por semana (padrão)</option>
        <option value="1x_10dias">1x a cada 10 dias</option>
        <option value="1x_14dias">1x a cada 14 dias</option>
      </select>
    </div>

    <div class="bio-field">
      <label>Data da última aplicação</label>
      <input type="date" id="monjaro-ultima-data">
    </div>

    <div class="bio-field">
      <label>Como usar o Monjaro no cálculo</label>
      <select id="monjaro-modo">
        <option value="A">A) Apenas mostrar perda média estimada</option>
        <option value="B">B) Referência</option>
        <option value="C">C) Ajuste automático da meta</option>
      </select>
    </div>

  </div>

</div>

<div class="bio-grid-2">

  <div class="bio-field">
    <label><i class="fa-solid fa-weight-scale"></i> Peso <span class="required">*</span></label>
    <input id="peso" placeholder="Ex.: 82.5 (kg)">
  </div>

  <div class="bio-field">
    <label><i class="fa-solid fa-ruler-vertical"></i> Altura <span class="required">*</span></label>
    <input id="altura" placeholder="Ex.: 175 (cm)">
  </div>

  <div class="bio-field">
    <label><i class="fa-solid fa-calendar-day"></i> Idade <span class="required">*</span></label>
    <input id="idade" placeholder="Ex.: 31 (anos)">
  </div>

  <div class="bio-field">
    <label><i class="fa-solid fa-venus-mars"></i> Sexo biológico <span class="required">*</span></label>
    <select id="sexo">
      <option value="">Selecione</option>
      <option value="F">Feminino</option>
      <option value="M">Masculino</option>
    </select>
  </div>

  <div class="bio-field">
    <label><i class="fa-solid fa-person-running"></i> Nível de atividade</label>
    <select id="atividade">
      <option value="1.2">Sedentário</option>
      <option value="1.375">Leve</option>
      <option value="1.55">Moderado</option>
      <option value="1.725">Intenso</option>
    </select>
  </div>

  <div class="bio-field">
    <label><i class="fa-solid fa-bowl-food"></i> Calorias consumidas hoje (opcional)</label>
    <input id="calorias-consumidas" placeholder="Ex.: 1850 (kcal)">
  </div>

</div>

<button type="submit" class="bio-btn">
  <i class="fa-solid fa-calculator"></i>
  CALCULAR DÉFICIT DO DIA
</button>

</form>

<div id="erro" class="bio-error"></div>

</div>

<div id="resultados" class="bio-results">

<div class="bio-cards-grid">

  <div class="bio-card">
    <div id="res-basal"></div>
    <div class="bio-pill" id="res-basal-pill"></div>
  </div>

  <div class="bio-card">
    <div id="res-tdee"></div>
    <div class="bio-pill" id="res-tdee-pill"></div>
  </div>

  <div class="bio-card">
    <div id="res-imc"></div>
    <div class="bio-small" id="res-imc-extra"></div>
    <div class="bio-pill" id="res-imc-pill"></div>
  </div>

  <div class="bio-card">
    <div id="res-deficit-dia"></div>
    <div class="bio-small" id="res-deficit-extra"></div>
    <div id="res-deficit-pills"></div>
  </div>

  <div class="bio-card">
    <div id="res-deficit-sug"></div>
    <div class="bio-small" id="res-deficit-sug-extra"></div>
  </div>

  <div class="bio-card" id="card-monjaro-resumo" style="display:none">
    <div id="res-monjaro-main"></div>
    <div class="bio-small" id="res-monjaro-extra"></div>
    <div id="res-monjaro-pills"></div>
  </div>

</div>

</div>

</div>

<!-- COLUNA DIREITA -->
<div class="bio-right">

<div class="bio-info-card">
  <div class="bio-info-tags">
    <span>Monjaro / GLP-1</span>
    <span>Composição corporal</span>
    <span>Monitoramento diário</span>
  </div>

  <h3>Combine medicação, alimentação e monitorização inteligente.</h3>
  <p>
    A base é sempre o balanço calórico: TDEE menos ingestão diária.
    GLP-1 pode ajudar na fome e saciedade, mas o cálculo energético permanece fundamental.
  </p>

  <div class="bio-metric-boxes">
    <div class="bio-metric">
	<div class="metric-label"><i class="fa-solid fa-fire"></i> BMR típico</div>
      <div id="metric-bmr">—</div>
      <span>Gasto em repouso</span>
    </div>
    <div class="bio-metric">
	<div class="metric-label"><i class="fa-solid fa-walking"></i> TDEE típico</div>
      <div id="metric-tdee">—</div>
      <span>Gasto total diário</span>
    </div>
  </div>

  <div class="bio-alert">
    <i class="fa-solid fa-circle-exclamation"></i>
    Bioimpedância, exames e acompanhamento são essenciais. Os valores aqui são estimativas.
  </div>

</div>

</div>

</div>

</main>
</div>

</body>
</html>
