<div class="sidebar">
    <a href="/centro_de_custos/dashboard/painel.php">Dashboard</a>

    <!-- Produtos (Dropdown) -->
    <button class="dropdown-btn">Produtos 
        <i class="fas fa-caret-down"></i>
    </button>
    <div class="dropdown-container">
        <a href="/centro_de_custos/product/product_stock.php">Estoque</a>
        <a href="/centro_de_custos/product/product_menu.php">Produto</a>
        <a href="/centro_de_custos/product/product_entry.php">Entrada</a>
        <a href="/centro_de_custos/product/product_departure.php">Saída</a>
        <a href="/centro_de_custos/product/product_rel_entry.php">Rel. Entradas</a>
    </div>

    <a href="/centro_de_custos/settings/supplier_menu.php">Fornecedores</a>
    <a href="/centro_de_custos/settings/user_menu.php">Usuários</a>

    <!-- Configurações (Dropdown) -->
    <button class="dropdown-btn">Configurações
        <i class="fas fa-caret-down"></i>
    </button>
    <div class="dropdown-container">
        <a href="/centro_de_custos/settings/enterprise_menu.php">Empresas</a>
        <a href="/centro_de_custos/settings/state_menu.php">Estados</a>
        <a href="/centro_de_custos/settings/city_menu.php">Cidades</a>
        <a href="/centro_de_custos/settings/sector_menu.php">Setores</a>
    </div>

    <a href="/centro_de_custos/dashboard/logout.php">Sair</a>
</div>

<!-- Estilos -->
<style>
    .sidebar {
        height: 100%;
        width: 250px;
        position: fixed;
        top: 0;
        left: 0;
        background-color: #343a40;
        padding-top: 20px;
    }
    .sidebar a, .dropdown-btn {
        padding: 10px 15px;
        text-decoration: none;
        font-size: 18px;
        color: #ffffff;
        display: block;
        border: none;
        background: none;
        width: 100%;
        text-align: left;
        cursor: pointer;
        outline: none;
    }
    .sidebar a:hover, .dropdown-btn:hover {
        background-color: #495057;
    }
    .dropdown-container {
        display: none;
        background-color: #6c757d;
        padding-left: 15px;
    }
    .dropdown-container a {
        font-size: 16px;
    }
    .fa-caret-down {
        float: right;
        padding-right: 8px;
    }
</style>

<!-- Script para controlar os dropdowns -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        let dropdowns = document.querySelectorAll(".dropdown-btn");
        dropdowns.forEach(function (btn) {
            btn.addEventListener("click", function () {
                this.classList.toggle("active");
                let dropdownContent = this.nextElementSibling;
                if (dropdownContent.style.display === "block") {
                    dropdownContent.style.display = "none";
                } else {
                    dropdownContent.style.display = "block";
                }
            });
        });
    });
</script>
