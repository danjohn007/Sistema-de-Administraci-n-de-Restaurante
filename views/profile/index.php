<?php $title = 'Mi Perfil'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-person-circle"></i> Mi Perfil</h1>
    <a href="<?= BASE_URL ?>/profile/edit" class="btn btn-primary">
        <i class="bi bi-pencil"></i> Editar Perfil
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-person"></i> Información Personal
                </h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">Nombre:</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <?= htmlspecialchars($user['name']) ?>
                    </div>
                </div>
                <hr>
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">Email:</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <?= htmlspecialchars($user['email']) ?>
                    </div>
                </div>
                <hr>
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">Rol:</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <?php
                        $roleNames = [
                            ROLE_ADMIN => 'Administrador',
                            ROLE_WAITER => 'Mesero',
                            ROLE_CASHIER => 'Cajero'
                        ];
                        echo $roleNames[$user['role']] ?? $user['role'];
                        ?>
                    </div>
                </div>
                <hr>
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">Fecha de Registro:</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-gear"></i> Acciones
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= BASE_URL ?>/profile/edit" class="btn btn-outline-primary">
                        <i class="bi bi-pencil"></i> Editar Perfil
                    </a>
                    <a href="<?= BASE_URL ?>/profile/changePassword" class="btn btn-outline-warning">
                        <i class="bi bi-key"></i> Cambiar Contraseña
                    </a>
                    <a href="<?= BASE_URL ?>/dashboard" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Volver al Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>