<?php
session_start();
$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$title = 'Dashboard';

// Definiciones de roles
if (!defined('ROLE_ADMIN')) define('ROLE_ADMIN', 'admin');
if (!defined('ROLE_WAITER')) define('ROLE_WAITER', 'waiter');
if (!defined('ROLE_CASHIER')) define('ROLE_CASHIER', 'cashier');
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="h3 mb-0">
            <i class="bi bi-speedometer2"></i> Dashboard
        </h1>
        <p class="text-muted mb-0">
            Bienvenido, <?= $user && isset($user['name']) ? htmlspecialchars($user['name']) : 'Invitado' ?>
        </p>
    </div>
    <div class="col-md-4 text-end">
        <div class="text-muted">
            <i class="bi bi-clock"></i> <span id="current-time"><?= date('d/m/Y H:i:s') ?></span>
        </div>
    </div>
</div>

<?php if ($user && isset($user['role']) && $user['role'] === ROLE_ADMIN): ?>
    <!-- Admin Dashboard -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted mb-1">Total Mesas</h6>
                            <h3 class="mb-0"><?= isset($total_tables) ? $total_tables : 0 ?></h3>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-grid-3x3-gap" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- ... resto del dashboard de admin ... -->
<?php elseif ($user && isset($user['role']) && $user['role'] === ROLE_WAITER): ?>
    <!-- Waiter Dashboard -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted mb-1">Mesas Asignadas</h6>
                            <h3 class="mb-0"><?= isset($assigned_tables) ? count($assigned_tables) : 0 ?></h3>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-grid-3x3-gap" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- ... resto del dashboard de mesero ... -->
<?php elseif ($user && isset($user['role']) && $user['role'] === ROLE_CASHIER): ?>
    <!-- Cashier Dashboard -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title text-muted mb-1">Ventas Hoy</h6>
                            <h3 class="mb-0"><?= isset($sales_today) ? $sales_today : 0 ?></h3>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-cash-coin" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- ... resto del dashboard de cajero ... -->
<?php else: ?>
    <!-- Dashboard para invitados o sin rol definido -->
    <div class="alert alert-warning">
        No tienes acceso a este dashboard.
    </div>
<?php endif; ?>
