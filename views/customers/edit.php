<?php $title = 'Editar Cliente - ' . $customer['name']; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-pencil-square"></i> Editar Cliente</h1>
    <div>
        <a href="<?= BASE_URL ?>/customers/show/<?= $customer['id'] ?>" class="btn btn-outline-primary">
            <i class="bi bi-eye"></i> Ver Detalles
        </a>
        <a href="<?= BASE_URL ?>/customers" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver a Clientes
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Información del Cliente</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">
                                Nombre Completo <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="name" 
                                   name="name" 
                                   required
                                   value="<?= htmlspecialchars($_POST['name'] ?? $customer['name']) ?>"
                                   placeholder="Ej: Juan Pérez">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">
                                Teléfono <span class="text-danger">*</span>
                            </label>
                            <input type="tel" 
                                   class="form-control" 
                                   id="phone" 
                                   name="phone" 
                                   required
                                   value="<?= htmlspecialchars($_POST['phone'] ?? $customer['phone']) ?>"
                                   placeholder="Ej: 555-1234">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                                   value="<?= htmlspecialchars($_POST['email'] ?? $customer['email'] ?? '') ?>"
                                   placeholder="Ej: cliente@email.com">
                            <div class="form-text">Campo opcional</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="birthday" class="form-label">Cumpleaños</label>
                            <?php 
                            // Reconstruct birthday from day and month columns
                            $birthdayValue = '';
                            if (isset($_POST['birthday'])) {
                                $birthdayValue = $_POST['birthday'];
                            } elseif (!empty($customer['birthday_day']) && !empty($customer['birthday_month'])) {
                                $birthdayValue = sprintf('%02d/%02d', $customer['birthday_day'], $customer['birthday_month']);
                            } elseif (!empty($customer['birthday'])) {
                                // Fallback to old birthday field if it exists
                                $birthdayValue = $customer['birthday'];
                            }
                            ?>
                            <input type="text" 
                                   class="form-control" 
                                   id="birthday" 
                                   name="birthday" 
                                   value="<?= htmlspecialchars($birthdayValue) ?>"
                                   placeholder="DD/MM (Ej: 15/03)"
                                   pattern="[0-3][0-9]/[0-1][0-9]">
                            <div class="form-text">Formato: DD/MM (Campo opcional)</div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Información:</strong> Los campos marcados con (*) son obligatorios. 
                        Las estadísticas de visitas y consumo no se pueden editar manualmente ya que se actualizan automáticamente.
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Guardar Cambios
                        </button>
                        <a href="<?= BASE_URL ?>/customers/show/<?= $customer['id'] ?>" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Current Stats Card (Read-only) -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-bar-chart"></i> Estadísticas Actuales (Solo Lectura)
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="bg-light p-3 rounded">
                            <h5 class="text-success"><?= $customer['total_visits'] ?></h5>
                            <small class="text-muted">Total Visitas</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="bg-light p-3 rounded">
                            <h5 class="text-primary">$<?= number_format($customer['total_spent'], 2) ?></h5>
                            <small class="text-muted">Total Gastado</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="bg-light p-3 rounded">
                            <h5 class="text-info">$<?= $customer['total_visits'] > 0 ? number_format($customer['total_spent'] / $customer['total_visits'], 2) : '0.00' ?></h5>
                            <small class="text-muted">Promedio por Visita</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="bg-light p-3 rounded">
                            <h5 class="text-warning"><?= date('d/m/Y', strtotime($customer['created_at'])) ?></h5>
                            <small class="text-muted">Cliente desde</small>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="bi bi-lock"></i> 
                        Estas estadísticas se actualizan automáticamente cuando el cliente realiza pedidos y no pueden ser editadas manualmente.
                    </small>
                </div>
            </div>
        </div>
        
        <!-- Help Card -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-question-circle"></i> Ayuda
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Cambio de teléfono</h6>
                        <p class="small text-muted">
                            El número de teléfono debe ser único. Si cambias el teléfono, 
                            asegúrate de que no pertenezca a otro cliente.
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h6>Formato de cumpleaños</h6>
                        <p class="small text-muted">
                            Usa el formato DD/MM (día/mes). Ejemplo: 15/03 para el 15 de marzo.
                            Deja en blanco si no deseas registrar esta información.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-format birthday field
document.getElementById('birthday').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
    
    if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
    }
    
    e.target.value = value;
});

// Validate phone number
document.getElementById('phone').addEventListener('blur', function(e) {
    const phone = e.target.value.trim();
    if (phone && phone.length < 8) {
        alert('El número de teléfono debe tener al menos 8 dígitos');
        e.target.focus();
    }
});
</script>