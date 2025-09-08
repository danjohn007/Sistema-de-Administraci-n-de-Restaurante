<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? APP_NAME ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= BASE_URL ?>/public/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?= BASE_URL ?>/dashboard">
                <i class="bi bi-shop"></i> <?= APP_NAME ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/dashboard">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    
                    <?php if ($_SESSION['user_role'] === ROLE_ADMIN): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-gear"></i> Administración
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/users">
                                <i class="bi bi-people"></i> Usuarios
                            </a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/waiters">
                                <i class="bi bi-person-badge"></i> Meseros
                            </a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/tables">
                                <i class="bi bi-grid-3x3-gap"></i> Mesas
                            </a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/dishes">
                                <i class="bi bi-cup-hot"></i> Menú
                            </a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (in_array($_SESSION['user_role'], [ROLE_ADMIN, ROLE_WAITER])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/orders">
                            <i class="bi bi-clipboard-check"></i> Pedidos
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/reservations">
                            <i class="bi bi-calendar-check"></i> Reservaciones
                        </a>
                    </li>
                    
                    <?php if (in_array($_SESSION['user_role'], [ROLE_ADMIN, ROLE_CASHIER])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/tickets">
                            <i class="bi bi-receipt"></i> Tickets
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-calculator"></i> Financiero
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/financial">
                                <i class="bi bi-graph-up"></i> Dashboard
                            </a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/financial/expenses">
                                <i class="bi bi-credit-card"></i> Gastos
                            </a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/financial/withdrawals">
                                <i class="bi bi-cash-coin"></i> Retiros
                            </a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/financial/closures">
                                <i class="bi bi-journal-check"></i> Corte de Caja
                            </a></li>
                            <?php if ($_SESSION['user_role'] === ROLE_ADMIN): ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/financial/categories">
                                <i class="bi bi-tags"></i> Categorías
                            </a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/financial/branches">
                                <i class="bi bi-building"></i> Sucursales
                            </a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    
                    <?php if (in_array($_SESSION['user_role'], [ROLE_ADMIN, ROLE_SUPERADMIN, ROLE_CASHIER])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-boxes"></i> Inventario
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/inventory">
                                <i class="bi bi-list"></i> Productos
                            </a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/inventory/movements">
                                <i class="bi bi-arrow-up-down"></i> Movimientos
                            </a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/inventory/report">
                                <i class="bi bi-graph-up"></i> Reportes
                            </a></li>
                            <?php if ($_SESSION['user_role'] === ROLE_SUPERADMIN): ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/inventory/settings">
                                <i class="bi bi-gear"></i> Configuración
                            </a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-star-fill"></i> Clientes
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/customers">
                                <i class="bi bi-people"></i> Gestión de Clientes
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/best_diners">
                                <i class="bi bi-trophy"></i> Mejores Comensales
                            </a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/best_diners/bySpending">
                                <i class="bi bi-currency-dollar"></i> Top por Consumo
                            </a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/best_diners/byVisits">
                                <i class="bi bi-people-fill"></i> Top por Visitas
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/best_diners/report">
                                <i class="bi bi-bar-chart"></i> Reporte Completo
                            </a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['user_name']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/profile">
                                <i class="bi bi-person"></i> Mi Perfil
                            </a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/auth/changePassword">
                                <i class="bi bi-key"></i> Cambiar Contraseña
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/auth/logout">
                                <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>
    
    <!-- Main Content -->
    <main class="<?= isset($_SESSION['user_id']) ? 'container mt-4' : '' ?>">
        <!-- Flash Messages -->
        <?php 
        $flashTypes = ['success', 'error', 'warning', 'info'];
        foreach ($flashTypes as $type): 
            $key = 'flash_' . $type;
            $message = null;
            if (isset($_SESSION[$key])) {
                $message = $_SESSION[$key];
                unset($_SESSION[$key]); // Remove message after displaying it
            }
            if ($message):
                $alertClass = $type === 'error' ? 'danger' : $type;
        ?>
        <div class="alert alert-<?= $alertClass ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php 
            endif;
        endforeach; 
        ?>