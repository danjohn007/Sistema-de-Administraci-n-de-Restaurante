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
                    
                    <?php if (in_array($_SESSION['user_role'], [ROLE_ADMIN, ROLE_CASHIER])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/tickets">
                            <i class="bi bi-receipt"></i> Tickets
                        </a>
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
            $message = $this->getFlashMessage($type);
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