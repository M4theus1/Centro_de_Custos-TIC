<?php
session_start();
require(__DIR__ . '/../config/config.php');

include('C:/xampp/htdocs/centro_de_custos/protect/protect.php');

// Iniciais do nome do usuário para o avatar
$nome = $_SESSION['usuario_nome'] ?? 'Usuário';
$partes = explode(' ', trim($nome));
$iniciais = strtoupper(substr($partes[0], 0, 1) . (isset($partes[1]) ? substr($partes[1], 0, 1) : ''));
$primeiro_nome = $partes[0];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — Centro de Custos TIC</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="/centro_de_custos/assets/sistema.css">

  <style>
    :root {
      --ink:           #0f0e0d;
      --ink-2:         #3a3834;
      --ink-3:         #7a7670;
      --ink-4:         #b8b4ac;
      --paper:         #faf9f7;
      --paper-2:       #f2f0ec;
      --paper-3:       #e8e5df;
      --accent:        #3268E4;
      --accent-2:      #fdf0ea;
      --border:        rgba(15,14,13,.10);
      --border-strong: rgba(15,14,13,.18);
      --radius:        12px;
      --radius-lg:     18px;
      --font:          'DM Sans', sans-serif;
      --mono:          'DM Mono', monospace;
      --sidebar-w:     252px;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      background: var(--paper);
      color: var(--ink);
      font-family: var(--font);
      font-size: 15px;
      line-height: 1.6;
      -webkit-font-smoothing: antialiased;
    }

    /* ─── LAYOUT ─── */
    .main-content {
      margin-left: var(--sidebar-w);
      padding: 44px 48px 60px;
      min-height: 100vh;
    }

    /* ─── TOP BAR ─── */
    .topbar {
      display: flex; align-items: flex-end; justify-content: space-between;
      margin-bottom: 44px;
    }
    .topbar-eyebrow {
      font-family: var(--mono); font-size: 11px; letter-spacing: .13em;
      text-transform: uppercase; color: var(--accent); font-weight: 500;
      margin-bottom: 6px;
    }
    .topbar-title {
      font-size: 28px; font-weight: 400; color: var(--ink);
      letter-spacing: -.03em; line-height: 1.1;
    }
    .topbar-title strong { font-weight: 500; }
    .topbar-date {
      font-family: var(--mono); font-size: 12px; color: var(--ink-4); margin-top: 6px;
    }

    /* ─── SECTION DIVIDER ─── */
    .section-divider {
      display: flex; align-items: center; gap: 14px; margin-bottom: 20px;
    }
    .section-divider-label {
      font-size: 11px; letter-spacing: .13em; text-transform: uppercase;
      color: var(--ink-4); font-weight: 500; font-family: var(--mono);
      white-space: nowrap;
    }
    .section-divider-line { flex: 1; height: 1px; background: var(--border); }

    /* ─── MODULE GRID ─── */
    .modules-grid {
      display: grid; grid-template-columns: repeat(3, 1fr);
      gap: 18px; margin-bottom: 40px;
    }
    .module-card {
      background: var(--paper); border: 1px solid var(--border);
      border-radius: var(--radius-lg); padding: 28px 26px 24px;
      text-decoration: none; color: inherit;
      display: flex; flex-direction: column;
      position: relative; overflow: hidden;
      transition: box-shadow .2s, transform .2s, border-color .2s;
    }
    .module-card::after {
      content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 3px;
      background: var(--accent); transform: scaleX(0); transform-origin: left;
      transition: transform .25s ease;
    }
    .module-card:hover {
      box-shadow: 0 6px 28px rgba(15,14,13,.08);
      transform: translateY(-3px); border-color: var(--border-strong);
    }
    .module-card:hover::after { transform: scaleX(1); }

    .module-icon-wrap {
      width: 44px; height: 44px; border-radius: 10px;
      background: var(--paper-2); border: 1px solid var(--border);
      display: flex; align-items: center; justify-content: center;
      margin-bottom: 18px; transition: background .2s, border-color .2s;
    }
    .module-card:hover .module-icon-wrap {
      background: var(--accent-2); border-color: rgba(212,82,10,.2);
    }
    .module-icon-wrap i {
      font-size: 17px; color: var(--ink-3); transition: color .2s;
    }
    .module-card:hover .module-icon-wrap i { color: var(--accent); }

    .module-title {
      font-size: 15px; font-weight: 500; color: var(--ink);
      margin-bottom: 6px; letter-spacing: -.01em;
    }
    .module-desc {
      font-size: 13px; color: var(--ink-3); line-height: 1.55; flex: 1;
    }
    .module-link {
      margin-top: 20px; font-size: 12px; font-weight: 500; color: var(--accent);
      display: flex; align-items: center; gap: 5px;
      font-family: var(--mono); letter-spacing: .01em;
    }
    .module-link i { font-size: 11px; transition: transform .15s; }
    .module-card:hover .module-link i { transform: translateX(3px); }

    /* ─── QUICK STATS ─── */
    .quick-stats {
      display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px;
    }
    .qs-card {
      background: var(--paper-2); border: 1px solid var(--border);
      border-radius: var(--radius); padding: 16px 18px;
      display: flex; align-items: center; gap: 14px;
    }
    .qs-dot {
      width: 8px; height: 8px; border-radius: 50%;
      background: var(--accent); flex-shrink: 0;
    }
    .qs-dot.green  { background: #1a6b45; }
    .qs-dot.amber  { background: #b8860b; }
    .qs-dot.purple { background: #5b4db5; }
    .qs-label {
      font-size: 10.5px; color: var(--ink-3); font-family: var(--mono);
      text-transform: uppercase; letter-spacing: .10em; margin-bottom: 3px;
    }
    .qs-value {
      font-size: 18px; font-weight: 400; color: var(--ink); letter-spacing: -.03em;
      font-family: var(--mono);
    }

    /* ─── RESPONSIVE ─── */
    .mobile-bar {
      display: none; position: fixed; top: 0; left: 0; right: 0;
      height: 54px; background: var(--paper); border-bottom: 1px solid var(--border);
      align-items: center; justify-content: space-between; padding: 0 20px; z-index: 99;
    }
    .hamburger { background: none; border: none; cursor: pointer; color: var(--ink); font-size: 18px; }
    .overlay { display: none; position: fixed; inset: 0; background: rgba(15,14,13,.4); z-index: 98; }
    .overlay.open { display: block; }

    @media (max-width: 1024px) {
      .modules-grid { grid-template-columns: repeat(2, 1fr); }
      .quick-stats  { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 768px) {
      .sidebar { transform: translateX(-100%); }
      .sidebar.open { transform: translateX(0); z-index: 200; }
      .mobile-bar { display: flex; }
      .main-content { margin-left: 0; padding: 70px 20px 40px; }
      .modules-grid { grid-template-columns: 1fr; }
      .quick-stats  { grid-template-columns: 1fr 1fr; }
      .topbar { flex-direction: column; align-items: flex-start; gap: 0; }
    }
  </style>
</head>
<body>

<!-- Mobile Bar -->
<div class="mobile-bar">
  <button class="hamburger" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
  </button>
  <span style="font-size:14px;font-weight:500">Centro de Custos TIC</span>
  <span></span>
</div>
<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

<!-- Sidebar -->
<?php include('C:/xampp/htdocs/centro_de_custos/sidebar.php'); ?>
<!--
  NOTE: Para aproveitar o novo design, adicione ao elemento raiz da sidebar.php:
    - class="sidebar" id="sidebar"
  E adapte os links com as classes abaixo.
  Alternativamente, substitua o include pelo bloco a seguir:

<nav class="sidebar" id="sidebar">
  <div class="sidebar-logo">
    <div class="sidebar-logo-eyebrow">Sistema</div>
    <div class="sidebar-logo-name">Centro de Custos<br>TIC</div>
  </div>

  <div class="sidebar-section">
    <div class="sidebar-section-label">Principal</div>
    <a href="/centro_de_custos/dashboard/painel.php" class="active">
      <i class="fas fa-th-large"></i> Dashboard
    </a>
  </div>

  <div class="sidebar-section">
    <div class="sidebar-section-label">Movimentações</div>
    <a href="/centro_de_custos/product/entrada.php"><i class="fas fa-arrow-down"></i> Entradas</a>
    <a href="/centro_de_custos/product/saida.php"><i class="fas fa-arrow-up"></i> Saídas</a>
  </div>

  <div class="sidebar-section">
    <div class="sidebar-section-label">Cadastros</div>
    <a href="/centro_de_custos/product/product_menu.php"><i class="fas fa-box"></i> Produtos</a>
    <a href="/centro_de_custos/settings/supplier_menu.php"><i class="fas fa-truck"></i> Fornecedores</a>
    <a href="/centro_de_custos/settings/enterprise_menu.php"><i class="fas fa-building"></i> Empresas</a>
    <a href="/centro_de_custos/settings/user_menu.php"><i class="fas fa-users"></i> Usuários</a>
  </div>

  <div class="sidebar-section">
    <div class="sidebar-section-label">Relatórios</div>
    <a href="/centro_de_custos/reports/entrada.php"><i class="fas fa-chart-bar"></i> Rel. Entradas</a>
    <a href="/centro_de_custos/reports/saida.php"><i class="fas fa-chart-line"></i> Rel. Saídas</a>
  </div>

  <div class="sidebar-footer">
    <div class="sidebar-footer-avatar"><?php echo $iniciais; ?></div>
    <div class="sidebar-footer-info">
      <div class="sidebar-footer-name"><?php echo htmlspecialchars($nome); ?></div>
      <div class="sidebar-footer-role">Administrador</div>
    </div>
    <a href="/centro_de_custos/logout.php" class="sidebar-footer-exit" title="Sair">
      <i class="fas fa-sign-out-alt"></i>
    </a>
  </div>
</nav>
-->

<!-- Main Content -->
<main class="main-content">

  <!-- Top Bar -->
  <div class="topbar">
    <div>
      <div class="topbar-eyebrow">Painel</div>
      <h1 class="topbar-title">
        Bem-vindo, <strong><?php echo htmlspecialchars($primeiro_nome); ?></strong>
      </h1>
      <div class="topbar-date" id="current-date"></div>
    </div>
  </div>

  <!-- Módulos -->
  <div class="section-divider">
    <span class="section-divider-label">Módulos</span>
    <div class="section-divider-line"></div>
  </div>

  <div class="modules-grid">

    <a href="/centro_de_custos/product/product_menu.php" class="module-card">
      <div class="module-icon-wrap"><i class="fas fa-boxes"></i></div>
      <div class="module-title">Produtos</div>
      <div class="module-desc">Gerencie o catálogo de produtos e suas movimentações de estoque.</div>
      <div class="module-link">Acessar <i class="fas fa-arrow-right"></i></div>
    </a>

    <a href="/centro_de_custos/settings/supplier_menu.php" class="module-card">
      <div class="module-icon-wrap"><i class="fas fa-truck"></i></div>
      <div class="module-title">Fornecedores</div>
      <div class="module-desc">Cadastre e visualize os fornecedores parceiros da organização.</div>
      <div class="module-link">Acessar <i class="fas fa-arrow-right"></i></div>
    </a>

    <a href="/centro_de_custos/settings/user_menu.php" class="module-card">
      <div class="module-icon-wrap"><i class="fas fa-users"></i></div>
      <div class="module-title">Usuários</div>
      <div class="module-desc">Gerencie permissões e contas de acesso ao sistema.</div>
      <div class="module-link">Acessar <i class="fas fa-arrow-right"></i></div>
    </a>

    <a href="/centro_de_custos/settings/enterprise_menu.php" class="module-card">
      <div class="module-icon-wrap"><i class="fas fa-building"></i></div>
      <div class="module-title">Empresas</div>
      <div class="module-desc">Visualize e gerencie as unidades e filiais cadastradas.</div>
      <div class="module-link">Acessar <i class="fas fa-arrow-right"></i></div>
    </a>

    <a href="/centro_de_custos/settings/state_menu.php" class="module-card">
      <div class="module-icon-wrap"><i class="fas fa-cog"></i></div>
      <div class="module-title">Configurações</div>
      <div class="module-desc">Configure estados, cidades, setores e parâmetros do sistema.</div>
      <div class="module-link">Acessar <i class="fas fa-arrow-right"></i></div>
    </a>

  </div>

  <!-- Visão Geral -->
  <div class="section-divider" style="margin-top:40px">
    <span class="section-divider-label">Visão geral</span>
    <div class="section-divider-line"></div>
  </div>

  <div class="quick-stats">
    <?php
    $total_entradas = $mysqli->query("
        SELECT COUNT(*) 
        FROM entrada_produto 
        WHERE DATE(data_entrada) = CURDATE()
    ")->fetch_row()[0];

    $total_produtos = $mysqli->query("
        SELECT COUNT(*) 
        FROM produtos 
        WHERE ativo = 1
    ")->fetch_row()[0];

    $total_fornec = $mysqli->query("
        SELECT COUNT(*) 
        FROM fornecedores
    ")->fetch_row()[0];

    $total_usuarios = $mysqli->query("
        SELECT COUNT(*) 
        FROM usuarios
    ")->fetch_row()[0];
    ?>
    <div class="qs-card">
      <div class="qs-dot"></div>
      <div>
        <div class="qs-label">Entradas hoje</div>
        <div class="qs-value"><?php echo $total_entradas; ?></div>
      </div>
    </div>
    <div class="qs-card">
      <div class="qs-dot green"></div>
      <div>
        <div class="qs-label">Produtos ativos</div>
        <div class="qs-value"><?php echo $total_produtos; ?></div>
      </div>
    </div>
    <div class="qs-card">
      <div class="qs-dot amber"></div>
      <div>
        <div class="qs-label">Fornecedores</div>
        <div class="qs-value"><?php echo $total_fornec; ?></div>
      </div>
    </div>
    <div class="qs-card">
      <div class="qs-dot purple"></div>
      <div>
        <div class="qs-label">Usuários</div>
        <div class="qs-value"><?php echo $total_usuarios; ?></div>
      </div>
    </div>
  </div>

</main>

<script>
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('open');
  document.getElementById('overlay').classList.toggle('open');
}

const d = new Date();
document.getElementById('current-date').textContent =
  d.toLocaleDateString('pt-BR', { weekday:'long', year:'numeric', month:'long', day:'numeric' })
   .replace(/^./, c => c.toUpperCase());
</script>

</body>
</html>