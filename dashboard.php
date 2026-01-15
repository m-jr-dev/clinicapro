<?php
require_once __DIR__ . "/includes/auth.php";

/* Usuário Logado */
$userId = $_SESSION['user']['id'];

$stUser = $pdo->prepare("SELECT name FROM users WHERE id=?");
$stUser->execute([$userId]);
$u = $stUser->fetch();

$userName = $u ? $u['name'] : 'Paciente';

/* Dark Mode */

/* Busca preferência de tema */
$stPref = $pdo->prepare("SELECT dark_mode FROM user_preferences WHERE user_id=?");
$stPref->execute([$userId]);
$pref = $stPref->fetch();

$theme = "dark"; // padrão

if($pref){
    $theme = $pref['dark_mode'] ? "dark" : "light";
}

$weights=$pdo->query("SELECT * FROM weight_history WHERE patient_id=1 ORDER BY measured_at ASC")->fetchAll();
$bio=$pdo->query("SELECT * FROM bioimpedance WHERE patient_id=1 ORDER BY measured_at DESC LIMIT 1")->fetch();
$ap=$pdo->query("SELECT a.*,p.name proc FROM appointments a JOIN procedures p ON p.id=a.procedure_id WHERE patient_id=1")->fetchAll();
$doses=$pdo->query("SELECT * FROM monjaro_applications WHERE patient_id=1 ORDER BY applied_at DESC")->fetchAll();
$labels=[];$data=[];
foreach($weights as $w){$labels[]=$w['measured_at'];$data[]=$w['weight_kg'];}
$total_lost=$weights[0]['weight_kg'] - end($weights)['weight_kg'];
?><!doctype html>
<html data-theme="<?php echo $theme; ?>">
<head>
<meta charset="utf-8">
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/dark.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="assets/js/dark.js" defer></script>
</head>
<body>
<div class="app">
<aside class="sidebar">
  <div class="brand"><i class="fa-solid fa-shield-heart"></i> ClínicaPRO</div>

  <div class="nav-title">PRINCIPAL</div>
  <nav class="nav">
    <a class="active"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
    <a href="#"><i class="fa-regular fa-calendar"></i> Agendamentos</a>
    <a href="#"><i class="fa-solid fa-chart-line"></i> Evolução</a>
    <a href="bioimpedancia.php"><i class="fa-solid fa-dna"></i> Bioimpedância</a>
  </nav>

  <div class="nav-title">CONTA</div>
  <nav class="nav">
    <a href="perfil.php"><i class="fa-regular fa-user"></i> Meu Perfil</a>
    <a href="#"><i class="fa-solid fa-gear"></i> Configurações</a>
  </nav>

  <div class="footer">
  <a href="logout.php" class="logout-btn">
    <i class="fa-solid fa-right-from-bracket"></i>
    <span>Sair</span>
  </a>
  </div>
</aside>

<main class="main">
<div class="topbar">
 <div>
  <div style="color:#2dd4bf;font-weight:800">ClínicaPRO</div>
  <div class="muted">Portal do Paciente | Bem-vinda, <strong style="color: #2dd4bf;"><?php echo htmlspecialchars($userName); ?></strong></div>
 </div>
 <div class="topbar-actions">
  <span class="badge">Unidade Jardins</span>

  <button id="toggleTheme" class="theme-toggle" title="Alternar tema">
    <i class="fa-solid fa-moon"></i>
  </button>
</div>

</div>

<div class="grid">
<div class="card">
 <div style="display:flex;justify-content:space-between;align-items:flex-start">
  <div>
    <h3 style="display:flex;align-items:center;gap:8px"><i class="fa-solid fa-syringe" style="color:#2dd4bf"></i> Protocolo de Emagrecimento</h3>
    <div class="muted">Acompanhamento de peso e dosagem</div>
  </div>
  <div style="text-align:right">
    <div class="kpi"><?php echo number_format($total_lost,1); ?>kg</div>
    <div class="muted">PESO ELIMINADO</div>
  </div>
 </div>
 <canvas id="weightChart"></canvas>
</div>

<div class="card">
 <h3>Composição Corporal</h3>
 <div style="display:flex;justify-content:center;margin:14px 0">
   <div class="fat-progress"style="--p: <?php echo floatval($bio['fat_percent']); ?>;">
     <div class="fat-progress-inner">
       <?php echo number_format($bio['fat_percent'],1); ?>%
     </div>
   </div>
 </div>

 <div class="stat-grid">
  <div class="stat-box"><div class="stat-label">Massa Muscular</div><div class="stat-value" style="color:#38bdf8"><?php echo $bio['muscle_kg']; ?>kg</div></div>
  <div class="stat-box"><div class="stat-label">Gordura Visceral</div><div class="stat-value" style="color:#facc15">Nível <?php echo $bio['visceral_level']; ?></div></div>
  <div class="stat-box"><div class="stat-label">Água Corporal</div><div class="stat-value" style="color:#22d3ee"><?php echo $bio['water_percent']; ?>%</div></div>
  <div class="stat-box"><div class="stat-label">Taxa Metabólica</div><div class="stat-value" style="color:#c084fc"><?php echo $bio['bmr_kcal']; ?> kcal</div></div>
 </div>
</div>
</div>

<div class="grid" style="margin-top:20px">

<!-- MEUS AGENDAMENTOS -->
<div class="card">
  <h3 class="section-title">Meus Agendamentos</h3>

  <div class="appointments-grid">

    <?php foreach($ap as $a): ?>
    <div class="appointment-card">

      <div class="appointment-header">
        <div class="appointment-icon">
          <i class="fa-regular fa-calendar"></i>
        </div>

        <div class="appointment-status <?php echo $a['status']=='CONFIRMADO'?'ok':'soon'; ?>">
          <?php echo $a['status']; ?>
        </div>
      </div>

      <div class="appointment-body">
        <div class="appointment-title"><?php echo $a['proc']; ?></div>

        <div class="appointment-line">
          <i class="fa-regular fa-clock"></i>
          <?php echo date('d/m/Y H:i', strtotime($a['scheduled_at'])); ?>
        </div>

        <div class="appointment-line">
          <?php if(($a['payment_method'] ?? '') === 'PIX'): ?>
            <i class="fa-brands fa-pix"></i>
            PIX
          <?php elseif(($a['payment_method'] ?? '') === 'CARTAO'): ?>
            <i class="fa-solid fa-credit-card"></i>
            Cartão de Crédito
          <?php else: ?>
            <i class="fa-solid fa-credit-card"></i>
            Cartão
          <?php endif; ?>
        </div>
      </div>

      <div class="appointment-footer">
        <div class="appointment-reminder">
          LEMBRETE: 2 DIAS
        </div>
        <a href="#" class="appointment-action">Alterar</a>
      </div>

    </div>
    <?php endforeach; ?>

    <!-- NOVO AGENDAMENTO -->
    <a href="novo_agendamento.php" class="appointment-new">
      <div class="appointment-new-plus">+</div>
      <div class="appointment-new-text">Novo Agendamento</div>
    </a>

  </div>
</div>

<!-- HISTÓRICO DE DOSES -->
<div class="card">
  <h3 class="section-title">Histórico de Doses</h3>

  <div class="dose-list">

    <?php foreach($doses as $d): ?>
    <div class="dose-item">
      <div class="dose-left">
        <div class="dose-date">
          <?php echo date('d/m/Y', strtotime($d['applied_at'])); ?>
        </div>
        <div class="dose-sub">
          DOSE: <?php echo $d['dose_mg']; ?>MG
        </div>
      </div>

      <div class="dose-right">
        <?php echo number_format($d['weight_kg'] ?? 0,1); ?> kg
      </div>
    </div>
    <?php endforeach; ?>

  </div>
</div>

</div>

</main>
</div>

<script>
const ctx=document.getElementById('weightChart');
new Chart(ctx,{
 type:'line',
 data:{
  labels: <?php echo json_encode($labels); ?>,
  datasets:[{
    data: <?php echo json_encode($data); ?>,
    borderColor:'#38bdf8',
    backgroundColor:'rgba(56,189,248,.25)',
    tension:.4,
    fill:true,
    pointRadius:3,
    pointBackgroundColor:'#2dd4bf'
  }]
 },
 options:{
  plugins:{legend:{display:false}},
  scales:{
   x:{grid:{color:'rgba(255,255,255,.05)'},ticks:{color:'#6b7aa8'}},
   y:{grid:{color:'rgba(255,255,255,.05)'},ticks:{color:'#6b7aa8'}}
  }
 }
});
</script>

</body>
</html>
