<?php $title = 'Configuración del Sistema'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-gear"></i> Configuración del Sistema</h1>
    <a href="<?= BASE_URL ?>/inventory" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver al Inventario
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-toggles"></i> Configuración de Módulos
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/inventory/settings">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0">
                                        <i class="bi bi-cash-coin"></i> Módulo de Cobranza
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" 
                                               id="collections_enabled" name="collections_enabled"
                                               <?= $settings['collections_enabled'] === '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="collections_enabled">
                                            Habilitar Cuentas por Cobrar
                                        </label>
                                    </div>
                                    <small class="text-muted">
                                        Permite el uso del método de pago "Pendiente por Cobrar" en los tickets.
                                        Cuando está deshabilitado, este método no aparecerá como opción.
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border">
                                <div class="card-header bg-light">
                                    <h6 class="card-title mb-0">
                                        <i class="bi bi-boxes"></i> Módulo de Inventarios
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" 
                                               id="inventory_enabled" name="inventory_enabled"
                                               <?= $settings['inventory_enabled'] === '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="inventory_enabled">
                                            Habilitar Inventarios
                                        </label>
                                    </div>
                                    
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" 
                                               id="auto_deduct_inventory" name="auto_deduct_inventory"
                                               <?= $settings['auto_deduct_inventory'] === '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="auto_deduct_inventory">
                                            Descuento Automático
                                        </label>
                                    </div>
                                    <small class="text-muted">
                                        Descuenta automáticamente del inventario cuando se genera un ticket.
                                        Requiere que los platillos tengan ingredientes configurados.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check"></i> Guardar Configuración
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            <i class="bi bi-x"></i> Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-info-circle"></i> Información
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="bi bi-lightbulb"></i> Consejos</h6>
                    <ul class="mb-0">
                        <li><strong>Cuentas por Cobrar:</strong> Úselo para clientes de confianza o empresas con crédito.</li>
                        <li><strong>Inventarios:</strong> Mantenga control de sus productos y costos.</li>
                        <li><strong>Descuento Automático:</strong> Configure primero las recetas de los platillos.</li>
                    </ul>
                </div>
                
                <div class="alert alert-warning">
                    <h6><i class="bi bi-exclamation-triangle"></i> Importante</h6>
                    <p class="mb-0">
                        Solo los <strong>Superadministradores</strong> pueden cambiar estas configuraciones.
                        Los cambios afectan a todo el sistema inmediatamente.
                    </p>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-shield-check"></i> Permisos por Rol
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Rol</th>
                            <th>Inventario</th>
                            <th>Configuración</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="badge bg-danger">SuperAdmin</span></td>
                            <td><i class="bi bi-check text-success"></i></td>
                            <td><i class="bi bi-check text-success"></i></td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-primary">Admin</span></td>
                            <td><i class="bi bi-check text-success"></i></td>
                            <td><i class="bi bi-x text-danger"></i></td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-info">Cajero</span></td>
                            <td><i class="bi bi-eye text-warning"></i></td>
                            <td><i class="bi bi-x text-danger"></i></td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-secondary">Mesero</span></td>
                            <td><i class="bi bi-x text-danger"></i></td>
                            <td><i class="bi bi-x text-danger"></i></td>
                        </tr>
                    </tbody>
                </table>
                <small class="text-muted">
                    <i class="bi bi-check text-success"></i> Acceso completo &nbsp;
                    <i class="bi bi-eye text-warning"></i> Solo lectura &nbsp;
                    <i class="bi bi-x text-danger"></i> Sin acceso
                </small>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Habilitar/deshabilitar el descuento automático basado en si el inventario está habilitado
    const inventoryEnabled = document.getElementById('inventory_enabled');
    const autoDeduct = document.getElementById('auto_deduct_inventory');
    
    function toggleAutoDeduct() {
        autoDeduct.disabled = !inventoryEnabled.checked;
        if (!inventoryEnabled.checked) {
            autoDeduct.checked = false;
        }
    }
    
    inventoryEnabled.addEventListener('change', toggleAutoDeduct);
    toggleAutoDeduct(); // Ejecutar al cargar la página
});
</script>

<style>
.form-switch .form-check-input {
    width: 2.5em;
    height: 1.25em;
}

.card .card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.alert {
    border: none;
    border-radius: 0.5rem;
}

.table-sm td, .table-sm th {
    padding: 0.5rem;
}

.badge {
    font-size: 0.75em;
}
</style>