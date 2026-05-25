<?php
include(__DIR__ . '/../config/config.php');

// Iniciais do nome do usuário para o avatar
$nome = $_SESSION['usuario_nome'] ?? 'Usuário';
$partes = explode(' ', trim($nome));
$iniciais = strtoupper(substr($partes[0], 0, 1) . (isset($partes[1]) ? substr($partes[1], 0, 1) : ''));
$primeiro_nome = $partes[0];

$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : '';
$data_fim    = isset($_GET['data_fim'])    ? $_GET['data_fim']    : '';

$registros_por_pagina = 10;
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_atual - 1) * $registros_por_pagina;

$sql = "SELECT e.id_empresa, emp.nome AS empresa, p.nome AS produto, f.nome AS fornecedor,
               e.quantidade, e.valor_unitario, e.frete, e.valor_total, e.data_entrada, e.nf, e.observacao
        FROM entrada_produto e
        JOIN empresas emp ON e.id_empresa = emp.id
        JOIN produtos p   ON e.id_produto  = p.id
        JOIN fornecedores f ON e.id_fornecedor = f.id
        WHERE 1=1";

if (!empty($data_inicio) && !empty($data_fim)) {
    $sql .= " AND e.data_entrada BETWEEN '$data_inicio' AND '$data_fim'";
}

$sql .= " ORDER BY e.data_entrada DESC";
$sql .= " LIMIT $registros_por_pagina OFFSET $offset";

$result = $mysqli->query($sql);
if (!$result) die("Erro na consulta: " . $mysqli->error);

$sql_total = "SELECT COUNT(*) AS total FROM entrada_produto e WHERE 1=1";
if (!empty($data_inicio) && !empty($data_fim)) {
    $sql_total .= " AND e.data_entrada BETWEEN '$data_inicio' AND '$data_fim'";
}
$result_total    = $mysqli->query($sql_total);
$total_registros = $result_total->fetch_assoc()['total'];
$total_paginas   = ceil($total_registros / $registros_por_pagina);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Relatório de Entradas</title>
  <link rel="stylesheet" href="/centro_de_custos/assets/sistema.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    .main { padding: 40px 44px 60px; }

    .btn-export {
      background: var(--success-bg); color: var(--success);
      border: 1px solid var(--success-border);
    }
    .btn-export:hover { background: #d5eddf; transform: translateY(-1px); opacity: 1; }

    .stats-row {
      display: grid; grid-template-columns: repeat(3,1fr); gap: 16px;
      margin-bottom: 32px;
    }
    .stats-row .stat-card {
      background: var(--paper); border: 1px solid var(--border);
      border-radius: var(--radius-lg); padding: 22px 24px;
      position: relative; overflow: hidden;
      transition: box-shadow .2s, transform .2s;
      display: block;
    }
    .stats-row .stat-card:hover { box-shadow: 0 4px 20px rgba(15,14,13,.07); transform: translateY(-2px); }
    .stats-row .stat-card::before {
      content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
      background: var(--accent); opacity: 0; transition: opacity .2s;
    }
    .stats-row .stat-card:hover::before { opacity: 1; }
    .stats-row .stat-label {
      font-size: 11px; letter-spacing: .12em; text-transform: uppercase;
      color: var(--ink-3); font-weight: 500; font-family: var(--font-mono);
      margin-bottom: 10px; display: block;
    }
    .stats-row .stat-value {
      font-size: 28px; font-weight: 300; color: var(--ink);
      letter-spacing: -.04em; line-height: 1; display: block;
    }
    .stats-row .stat-value small { font-size: 14px; color: var(--ink-3); font-weight: 400; margin-left: 4px; }
    .stat-icon {
      position: absolute; right: 22px; top: 20px;
      font-size: 22px; color: var(--paper-3);
    }

    .filter-card {
      background: var(--paper-2); border: 1px solid var(--border);
      border-radius: var(--radius-lg); padding: 22px 24px;
      margin-bottom: 28px;
    }
    .filter-label {
      font-size: 11px; letter-spacing: .12em; text-transform: uppercase;
      color: var(--ink-3); font-weight: 500; font-family: var(--font-mono);
      margin-bottom: 16px;
    }
    .filter-row { display: flex; align-items: flex-end; gap: 16px; flex-wrap: wrap; }
    .filter-field { display: flex; flex-direction: column; gap: 6px; flex: 1; min-width: 160px; }
    .filter-field label { font-size: 12px; color: var(--ink-3); font-weight: 500; }
    .filter-field input[type="date"] {
      padding: 9px 12px; border: 1px solid var(--border-strong);
      border-radius: var(--radius-sm); font-family: var(--font-mono); font-size: 13px;
      color: var(--ink); background: var(--paper); outline: none;
      transition: border-color .15s, box-shadow .15s;
    }
    .filter-field input[type="date"]:focus {
      border-color: var(--accent);
      box-shadow: 0 0 0 3px rgba(50,104,228,.12);
    }
    .filter-actions { display: flex; gap: 8px; }

    .table-card {
      background: var(--paper); border: 1px solid var(--border);
      border-radius: var(--radius-lg); overflow: hidden;
      margin-bottom: 32px;
    }
    .table-card-header {
      display: flex; align-items: center; justify-content: space-between;
      padding: 18px 24px; border-bottom: 1px solid var(--border);
    }
    .table-card-title { font-size: 13px; font-weight: 500; color: var(--ink); }
    .table-card-meta { font-size: 12px; color: var(--ink-4); font-family: var(--font-mono); }

    .table-card table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    .table-card thead th {
      padding: 11px 16px; text-align: left;
      font-size: 10.5px; letter-spacing: .10em; text-transform: uppercase;
      font-weight: 500; color: var(--ink-3); font-family: var(--font-mono);
      background: var(--paper-2); border-bottom: 1px solid var(--border);
      white-space: nowrap;
    }
    .table-card thead th:first-child { padding-left: 24px; }
    .table-card thead th:last-child  { padding-right: 24px; }

    .table-card tbody tr { border-bottom: 1px solid var(--border); transition: background .12s; }
    .table-card tbody tr:last-child  { border-bottom: none; }
    .table-card tbody tr:hover       { background: var(--paper-2); }

    .table-card tbody td { padding: 13px 16px; color: var(--ink-2); vertical-align: middle; }
    .table-card tbody td:first-child { padding-left: 24px; color: var(--ink); font-weight: 500; }
    .table-card tbody td:last-child  { padding-right: 24px; }

    .td-mono  { font-family: var(--font-mono); font-size: 12.5px; }
    .td-right { text-align: right; }
    .td-total { color: var(--accent); font-family: var(--font-mono); font-weight: 500; text-align: right; }

    .nf-tag {
      display: inline-block; padding: 3px 8px; border-radius: 5px;
      font-size: 11.5px; font-weight: 500; background: var(--paper-2);
      color: var(--ink-3); font-family: var(--font-mono);
      border: 1px solid var(--border);
    }
    .obs-empty { color: var(--ink-4); font-size: 12px; }

    .pagination-wrap {
      display: flex; justify-content: center; align-items: center; gap: 5px;
    }
    .page-link {
      display: inline-flex; align-items: center; justify-content: center;
      width: 36px; height: 36px; border-radius: var(--radius-sm);
      border: 1px solid var(--border); background: var(--paper);
      color: var(--ink-2); font-size: 13px; font-weight: 500;
      text-decoration: none; transition: all .12s; font-family: var(--font-mono);
    }
    .page-link:hover:not(.disabled):not(.active) {
      background: var(--paper-2); border-color: var(--border-strong);
    }
    .page-link.active   { background: var(--ink); color: #fff; border-color: var(--ink); }
    .page-link.disabled { opacity: .35; pointer-events: none; }
    .page-dots { color: var(--ink-4); font-size: 14px; padding: 0 4px; }

    @media (max-width: 900px) {
      .main { padding: 72px 20px 40px; }
      .stats-row { grid-template-columns: 1fr 1fr; }
      .page-header { flex-wrap: wrap; gap: 12px; }
      .btn-export { width: 100%; justify-content: center; }
      .filter-row { flex-direction: column; }
      .filter-field { min-width: 0; width: 100%; }
      .filter-actions { width: 100%; }
      .filter-actions .btn { flex: 1; justify-content: center; }
    }
    @media (max-width: 600px) {
      .stats-row { grid-template-columns: 1fr; }
      .table-card-header { flex-direction: column; align-items: flex-start; gap: 4px; }
      .pagination-wrap { flex-wrap: wrap; justify-content: center; }
    }
  </style>
</head>
<body>

<!-- Mobile Bar -->
<div class="mobile-bar">
  <button class="hamburger" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
  </button>
  <span style="font-size:14px;font-weight:500">Entradas</span>
  <span></span>
</div>
<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

<!-- Sidebar -->
<?php include(__DIR__ . '/../sidebar.php'); ?>

<!-- Main Content -->
<main class="main">

  <!-- Page Header -->
  <div class="page-header">
    <div>
      <div class="page-eyebrow">Relatório</div>
      <h1 class="page-title">Entradas de Produtos</h1>
    </div>
    <a id="exportLink" href="export_entry.php<?php
      if (!empty($data_inicio) && !empty($data_fim))
        echo '?data_inicio=' . urlencode($data_inicio) . '&data_fim=' . urlencode($data_fim);
    ?>" class="btn btn-export">
      <i class="fas fa-file-excel"></i> Exportar Excel
    </a>
  </div>

  <!-- Stats -->
  <div class="stats-row">
    <div class="stat-card">
      <div class="stat-label">Total de Registros</div>
      <div class="stat-value">
        <?php echo number_format($total_registros, 0, ',', '.'); ?>
        <small>registros</small>
      </div>
      <i class="fas fa-layer-group stat-icon"></i>
    </div>
    <div class="stat-card">
      <div class="stat-label">Período Selecionado</div>
      <div class="stat-value" style="font-size:16px;font-weight:400;padding-top:6px;letter-spacing:-.02em">
        <?php
          if (!empty($data_inicio) && !empty($data_fim))
            echo date('d/m/Y', strtotime($data_inicio)) . ' — ' . date('d/m/Y', strtotime($data_fim));
          else
            echo 'Todos os períodos';
        ?>
      </div>
      <i class="fas fa-calendar-alt stat-icon"></i>
    </div>
    <div class="stat-card">
      <div class="stat-label">Página Atual</div>
      <div class="stat-value">
        <?php echo $pagina_atual; ?>
        <small>de <?php echo $total_paginas; ?></small>
      </div>
      <i class="fas fa-file-alt stat-icon"></i>
    </div>
  </div>

  <!-- Filters -->
  <div class="filter-card">
    <div class="filter-label">Filtros de período</div>
    <form method="GET" action="">
      <div class="filter-row">
        <div class="filter-field">
          <label for="data_inicio">Data início</label>
          <input type="date" id="data_inicio" name="data_inicio"
                 value="<?php echo htmlspecialchars($data_inicio); ?>">
        </div>
        <div class="filter-field">
          <label for="data_fim">Data fim</label>
          <input type="date" id="data_fim" name="data_fim"
                 value="<?php echo htmlspecialchars($data_fim); ?>">
        </div>
        <div class="filter-actions" style="align-self:flex-end">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-filter"></i> Filtrar
          </button>
          <?php if (!empty($data_inicio) || !empty($data_fim)): ?>
          <a href="?" class="btn btn-ghost">
            <i class="fas fa-times"></i> Limpar
          </a>
          <?php endif; ?>
        </div>
      </div>
    </form>
  </div>

  <!-- Table -->
  <div class="table-card">
    <div class="table-card-header">
      <span class="table-card-title">Entradas registradas</span>
      <span class="table-card-meta">
        mostrando <?php echo $offset + 1; ?>–<?php echo min($offset + $registros_por_pagina, $total_registros); ?>
        de <?php echo number_format($total_registros, 0, ',', '.'); ?>
      </span>
    </div>
    <div style="overflow-x:auto">
      <table id="dataTable">
        <thead>
          <tr>
            <th>Empresa</th>
            <th>Produto</th>
            <th>Fornecedor</th>
            <th style="text-align:right">Qtd</th>
            <th style="text-align:right">Vl. Unit.</th>
            <th style="text-align:right">Frete</th>
            <th style="text-align:right">Total</th>
            <th>Data</th>
            <th>NF</th>
            <th>Observação</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?php echo htmlspecialchars($row['empresa']); ?></td>
            <td style="color:var(--ink-2)"><?php echo htmlspecialchars($row['produto']); ?></td>
            <td style="color:var(--ink-3)"><?php echo htmlspecialchars($row['fornecedor']); ?></td>
            <td class="td-mono td-right"><?php echo number_format($row['quantidade'], 0, ',', '.'); ?></td>
            <td class="td-mono td-right">R$&nbsp;<?php echo number_format($row['valor_unitario'], 2, ',', '.'); ?></td>
            <td class="td-mono td-right">R$&nbsp;<?php echo number_format($row['frete'], 2, ',', '.'); ?></td>
            <td class="td-total">R$&nbsp;<?php echo number_format($row['valor_total'], 2, ',', '.'); ?></td>
            <td class="td-mono"><?php echo date('d/m/Y', strtotime($row['data_entrada'])); ?></td>
            <td><span class="nf-tag"><?php echo htmlspecialchars($row['nf']); ?></span></td>
            <td>
              <?php if (!empty($row['observacao'])): ?>
                <?php echo nl2br(htmlspecialchars($row['observacao'])); ?>
              <?php else: ?>
                <span class="obs-empty">—</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Pagination -->
  <nav class="pagination-wrap" aria-label="Paginação">

    <?php $qs = ($data_inicio || $data_fim) ? "&data_inicio={$data_inicio}&data_fim={$data_fim}" : ''; ?>

    <a href="?pagina=1<?php echo $qs; ?>"
       class="page-link <?php echo $pagina_atual == 1 ? 'disabled' : ''; ?>"
       title="Primeira">«</a>

    <a href="?pagina=<?php echo max(1, $pagina_atual - 1); ?><?php echo $qs; ?>"
       class="page-link <?php echo $pagina_atual == 1 ? 'disabled' : ''; ?>"
       title="Anterior">‹</a>

    <?php
    $start = max(1, $pagina_atual - 2);
    $end   = min($total_paginas, $pagina_atual + 2);
    if ($start > 1) echo '<span class="page-dots">···</span>';
    for ($i = $start; $i <= $end; $i++):
    ?>
      <a href="?pagina=<?php echo $i; ?><?php echo $qs; ?>"
         class="page-link <?php echo $i == $pagina_atual ? 'active' : ''; ?>">
        <?php echo $i; ?>
      </a>
    <?php endfor;
    if ($end < $total_paginas) echo '<span class="page-dots">···</span>';
    ?>

    <a href="?pagina=<?php echo min($total_paginas, $pagina_atual + 1); ?><?php echo $qs; ?>"
       class="page-link <?php echo $pagina_atual == $total_paginas ? 'disabled' : ''; ?>"
       title="Próxima">›</a>

    <a href="?pagina=<?php echo $total_paginas; ?><?php echo $qs; ?>"
       class="page-link <?php echo $pagina_atual == $total_paginas ? 'disabled' : ''; ?>"
       title="Última">»</a>

  </nav>

</main>

<script>
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('open');
  document.getElementById('overlay').classList.toggle('open');
}

function atualizarLinkExport() {
  const inicio = document.getElementById('data_inicio').value;
  const fim    = document.getElementById('data_fim').value;
  const link   = document.getElementById('exportLink');
  if (inicio && fim) {
    link.href = 'export_entry.php?data_inicio=' + encodeURIComponent(inicio) + '&data_fim=' + encodeURIComponent(fim);
  } else {
    link.href = 'export_entry.php';
  }
}

document.addEventListener('DOMContentLoaded', function () {
  document.getElementById('data_inicio').addEventListener('change', atualizarLinkExport);
  document.getElementById('data_fim').addEventListener('change', atualizarLinkExport);
});
</script>

</body>
</html>