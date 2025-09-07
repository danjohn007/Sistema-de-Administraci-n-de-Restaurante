<?php $title = 'Crear Cliente'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-person-plus"></i> Crear Cliente</h1>
    <div>
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
                                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
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
                                   value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
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
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                   placeholder="Ej: cliente@email.com">
                            <div class="form-text">Campo opcional</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="birthday" class="form-label">Cumpleaños</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="birthday" 
                                   name="birthday" 
                                   value="<?= htmlspecialchars($_POST['birthday'] ?? '') ?>"
                                   placeholder="DD/MM (Ej: 15/03)"
                                   pattern="[0-3][0-9]/[0-1][0-9]">
                            <div class="form-text">Formato: DD/MM (Campo opcional)</div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Información:</strong> Los campos marcados con (*) son obligatorios. 
                        Las estadísticas de visitas y consumo se actualizarán automáticamente cuando el cliente realice pedidos.
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Crear Cliente
                        </button>
                        <a href="<?= BASE_URL ?>/customers" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                    </div>
                </form>
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
                        <h6>Información del teléfono</h6>
                        <p class="small text-muted">
                            El número de teléfono debe ser único para cada cliente. 
                            Se usa para identificar al cliente en pedidos futuros.
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h6>Formato de cumpleaños</h6>
                        <p class="small text-muted">
                            Usa el formato DD/MM (día/mes). Ejemplo: 15/03 para el 15 de marzo.
                            Esto ayuda a identificar promociones especiales.
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