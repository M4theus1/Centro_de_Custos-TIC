<?php
$nome     ??= $_SESSION['usuario_nome'] ?? 'Usuário';
$partes     = explode(' ', trim($nome));
$iniciais ??= strtoupper(substr($partes[0], 0, 1) . (isset($partes[1]) ? substr($partes[1], 0, 1) : ''));
?>
<nav class="sidebar" id="sidebar">
  <!-- Logo / Header -->
  <div class="sidebar-logo">
    <div class="sidebar-logo-eyebrow">Sistema</div>
    <a href="/centro_de_custos/dashboard/painel.php" class="sidebar-logo-link">
    <div class="sidebar-logo-name">Centro de Custos<br>TIC</div>
    </a>
  </div>

  <!-- Principal -->
  <div class="sidebar-section">
    <div class="sidebar-section-label">Principal</div>
    <a href="/centro_de_custos/dashboard/painel.php">
      <i class="fas fa-th-large"></i> Dashboard
    </a>
  </div>

  <!-- Produtos -->
  <div class="sidebar-section">
    <div class="sidebar-section-label">Produtos</div>

    <button class="dropdown-btn">
      <i class="fas fa-box"></i> Gestão de Produtos
      <i class="fas fa-caret-down"></i>
    </button>

    <div class="dropdown-container">
      <a href="/centro_de_custos/product/product_stock.php">Estoque</a>
      <a href="/centro_de_custos/product/product_menu.php">Produto</a>
      <a href="/centro_de_custos/product/product_entry.php">Entrada</a>
      <a href="/centro_de_custos/product/product_departure.php">Saída</a>
      <a href="/centro_de_custos/product/product_rel_entry.php">Rel. Entradas</a>
      <a href="/centro_de_custos/product/product_rel_departure.php">Rel. Saídas</a>
    </div>
  </div>

  <!-- Manutenção -->
  <div class="sidebar-section">
    <div class="sidebar-section-label">Manutenção</div>

    <button class="dropdown-btn">
      <i class="fas fa-mobile-alt"></i> Smartphones
      <i class="fas fa-caret-down"></i>
    </button>

    <div class="dropdown-container">
      <a href="/centro_de_custos/smartphone_maintenance/maintenance_menu.php">Menu</a>
      <a href="/centro_de_custos/smartphone_maintenance/maintenance_entry.php">Entrada</a>
    </div>
  </div>

  <!-- Cadastros -->
  <div class="sidebar-section">
    <div class="sidebar-section-label">Cadastros</div>
    <a href="/centro_de_custos/settings/supplier_menu.php">
      <i class="fas fa-truck"></i> Fornecedores
    </a>
    <a href="/centro_de_custos/settings/user_menu.php">
      <i class="fas fa-users"></i> Usuários
    </a>
  </div>

  <!-- Configurações -->
  <div class="sidebar-section">
    <div class="sidebar-section-label">Configurações</div>

    <button class="dropdown-btn">
      <i class="fas fa-cogs"></i> Sistema
      <i class="fas fa-caret-down"></i>
    </button>

    <div class="dropdown-container">
      <a href="/centro_de_custos/settings/enterprise_menu.php">Empresas</a>
      <a href="/centro_de_custos/settings/state_menu.php">Estados</a>
      <a href="/centro_de_custos/settings/city_menu.php">Cidades</a>
      <a href="/centro_de_custos/settings/sector_menu.php">Setores</a>
    </div>
  </div>

   <!-- Footer -->
  <div class="sidebar-footer">
    <div class="sidebar-footer-avatar"><?php echo $iniciais; ?></div>
    <div class="sidebar-footer-info">
      <div class="sidebar-footer-name"><?php echo htmlspecialchars($nome); ?></div>
      <div class="sidebar-footer-role">Administrador</div>
    </div>
    <a href="/centro_de_custos/dashboard/logout.php" class="sidebar-footer-exit">
      <i class="fas fa-sign-out-alt"></i>
    </a>
  </div>

</nav>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".dropdown-btn").forEach(btn => {
            btn.addEventListener("click", function () {
                const content = this.nextElementSibling;
                const isOpen = content.style.display === "block";
                content.style.display = isOpen ? "none" : "block";
                this.classList.toggle("open", !isOpen);
            });
        });
    });
</script>
