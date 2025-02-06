<?php
include('C:/xampp/htdocs/centro_de_custos/protect/protect.php');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Centro de Custos TIC</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .dashboard-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease-in-out;
            background-color: #ffffff;
        }
        .dashboard-card:hover {
            transform: scale(1.05);
        }
        .dashboard-card h5 {
            font-weight: bold;
            color: #343a40;
        }
        .welcome-text {
            font-size: 1.2rem;
            font-weight: bold;
            color: #343a40;
        }
        .sidebar {
            height: 100%;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #343a40;
            padding-top: 20px;
        }
        .sidebar a {
            padding: 10px 15px;
            text-decoration: none;
            font-size: 18px;
            color: #ffffff;
            display: block;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .navbar {
            margin-left: 250px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <a href="/centro_de_custos/dashboard/painel.php">Dashboard</a>
        <a href="/centro_de_custos/product/product_menu.php">Produtos</a>
        <a href="/centro_de_custos/settings/supplier_menu.php">Fornecedores</a>
        <a href="/centro_de_custos/settings/user_menu.php">Usuários</a>
        <a href="/centro_de_custos/settings/enterprise_menu.php">Empresas</a>
        <a href="/centro_de_custos/settings/state_menu.php">Configurações</a>
        <a href="/centro_de_custos/dashboard/logout.php">Sair</a>
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="/centro_de_custos/dashboard/painel.php">Centro de Custos TIC</a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Welcome Message -->
        <div class="container mt-4">
            <div class="row">
                <div class="col-12 text-center">
                    <p class="welcome-text">Bem-vindo ao Centro de Custos TIC, <?php echo $_SESSION['usuario_nome']; ?>!</p>
                </div>
            </div>
        </div>

        <!-- Dashboard Cards -->
        <div class="container mt-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card dashboard-card text-center">
                        <div class="card-body">
                            <i class="fas fa-boxes fa-3x mb-3"></i>
                            <h5 class="card-title">Produtos</h5>
                            <p class="card-text">Gerencie os produtos e suas movimentações.</p>
                            <a href="/centro_de_custos/product/product_menu.php" class="btn btn-primary">Acessar</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card dashboard-card text-center">
                        <div class="card-body">
                            <i class="fas fa-truck fa-3x mb-3"></i>
                            <h5 class="card-title">Fornecedores</h5>
                            <p class="card-text">Cadastre e visualize fornecedores.</p>
                            <a href="/centro_de_custos/settings/supplier_menu.php" class="btn btn-primary">Acessar</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card dashboard-card text-center">
                        <div class="card-body">
                            <i class="fas fa-users fa-3x mb-3"></i>
                            <h5 class="card-title">Usuários</h5>
                            <p class="card-text">Gerencie os usuários do sistema.</p>
                            <a href="/centro_de_custos/settings/user_menu.php" class="btn btn-primary">Acessar</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card dashboard-card text-center">
                        <div class="card-body">
                            <i class="fas fa-building fa-3x mb-3"></i>
                            <h5 class="card-title">Empresas</h5>
                            <p class="card-text">Visualize e gerencie as empresas.</p>
                            <a href="/centro_de_custos/settings/enterprise_menu.php" class="btn btn-primary">Acessar</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card dashboard-card text-center">
                        <div class="card-body">
                            <i class="fas fa-cog fa-3x mb-3"></i>
                            <h5 class="card-title">Configurações</h5>
                            <p class="card-text">Configure estados, cidades e setores.</p>
                            <a href="/centro_de_custos/settings/state_menu.php" class="btn btn-primary">Acessar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>