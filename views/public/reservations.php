<?php $title = 'Hacer Reservación'; ?>

<div class="row">
    <div class="col-12">
        <h1 class="text-center mb-4">
            <i class="bi bi-calendar-check"></i> Hacer una Reservación
        </h1>
        <p class="text-center lead mb-5">Reserve su mesa para una fecha y hora específica</p>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<form method="POST" action="<?= BASE_URL ?>/public/reservation" id="publicReservationForm">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle"></i> Información de la Reservación
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customer_name" class="form-label">Nombre Completo *</label>
                                <input type="text" 
                                       class="form-control <?= isset($errors['customer_name']) ? 'is-invalid' : '' ?>" 
                                       id="customer_name" 
                                       name="customer_name" 
                                       value="<?= htmlspecialchars($old['customer_name'] ?? '') ?>"
                                       required>
                                <?php if (isset($errors['customer_name'])): ?>
                                    <div class="invalid-feedback">
                                        <?= htmlspecialchars($errors['customer_name']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customer_phone" class="form-label">Teléfono *</label>
                                <input type="tel" 
                                       class="form-control <?= isset($errors['customer_phone']) ? 'is-invalid' : '' ?>" 
                                       id="customer_phone" 
                                       name="customer_phone" 
                                       value="<?= htmlspecialchars($old['customer_phone'] ?? '') ?>"
                                       required>
                                <?php if (isset($errors['customer_phone'])): ?>
                                    <div class="invalid-feedback">
                                        <?= htmlspecialchars($errors['customer_phone']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customer_birthday" class="form-label">Fecha de Cumpleaños (Opcional)</label>
                                <input type="date" 
                                       class="form-control <?= isset($errors['customer_birthday']) ? 'is-invalid' : '' ?>" 
                                       id="customer_birthday" 
                                       name="customer_birthday" 
                                       value="<?= htmlspecialchars($old['customer_birthday'] ?? '') ?>">
                                <?php if (isset($errors['customer_birthday'])): ?>
                                    <div class="invalid-feedback">
                                        <?= htmlspecialchars($errors['customer_birthday']) ?>
                                    </div>
                                <?php endif; ?>
                                <div class="form-text">
                                    Ayúdanos a celebrar contigo y obtener ofertas especiales
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="party_size" class="form-label">Número de Personas *</label>
                                <select class="form-select <?= isset($errors['party_size']) ? 'is-invalid' : '' ?>" 
                                        id="party_size" 
                                        name="party_size" 
                                        required>
                                    <option value="">Seleccionar...</option>
                                    <?php for ($i = 1; $i <= 20; $i++): ?>
                                        <option value="<?= $i ?>" 
                                                <?= (($old['party_size'] ?? '') == $i) ? 'selected' : '' ?>>
                                            <?= $i ?> persona<?= $i > 1 ? 's' : '' ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                                <?php if (isset($errors['party_size'])): ?>
                                    <div class="invalid-feedback">
                                        <?= htmlspecialchars($errors['party_size']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="table_id" class="form-label">Mesa Preferida *</label>
                                <select class="form-select <?= isset($errors['table_id']) ? 'is-invalid' : '' ?>" 
                                        id="table_id" 
                                        name="table_id" 
                                        required>
                                    <option value="">Seleccionar mesa...</option>
                                    <?php foreach ($tables as $table): ?>
                                        <option value="<?= $table['id'] ?>" 
                                                <?= (($old['table_id'] ?? '') == $table['id']) ? 'selected' : '' ?>>
                                            Mesa <?= $table['number'] ?> 
                                            (Capacidad: <?= $table['capacity'] ?> personas)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['table_id'])): ?>
                                    <div class="invalid-feedback">
                                        <?= htmlspecialchars($errors['table_id']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="reservation_datetime" class="form-label">Fecha y Hora de Reservación *</label>
                                <input type="datetime-local" 
                                       class="form-control <?= isset($errors['reservation_datetime']) ? 'is-invalid' : '' ?>" 
                                       id="reservation_datetime" 
                                       name="reservation_datetime"
                                       value="<?= htmlspecialchars($old['reservation_datetime'] ?? '') ?>"
                                       required>
                                <?php if (isset($errors['reservation_datetime'])): ?>
                                    <div class="invalid-feedback">
                                        <?= htmlspecialchars($errors['reservation_datetime']) ?>
                                    </div>
                                <?php endif; ?>
                                <div class="form-text">
                                    Seleccione fecha y hora (mínimo 30 minutos de anticipación)
                                    <br><small id="timezone-info" class="text-muted"></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notas Especiales</label>
                        <textarea class="form-control" 
                                  id="notes" 
                                  name="notes" 
                                  rows="3" 
                                  placeholder="Celebración especial, alergias alimentarias, solicitudes especiales..."><?= htmlspecialchars($old['notes'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Información importante:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Su reservación será confirmada por nuestro personal</li>
                            <li>Mantenemos un margen de tolerancia de 15 minutos</li>
                            <li>Para grupos grandes (más de 10 personas), contacte directamente al restaurante</li>
                            <li>Si proporcionó su fecha de cumpleaños, recibirá ofertas especiales</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4 mb-5">
        <div class="col-12 text-center">
            <a href="<?= BASE_URL ?>/public/menu" class="btn btn-outline-secondary me-3">
                <i class="bi bi-arrow-left"></i> Volver al Menú
            </a>
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-calendar-check"></i> Hacer Reservación
            </button>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const reservationDatetime = document.getElementById('reservation_datetime');
    const partySizeSelect = document.getElementById('party_size');
    const tableSelect = document.getElementById('table_id');
    
    // Set minimum datetime to now + 30 minutes
    const now = new Date();
    now.setMinutes(now.getMinutes() + 30);
    reservationDatetime.min = now.toISOString().slice(0, 16);
    
    // Set maximum datetime to 30 days from now
    const maxDate = new Date();
    maxDate.setDate(maxDate.getDate() + 30);
    reservationDatetime.max = maxDate.toISOString().slice(0, 16);
    
    // Show timezone info to user
    updateTimezoneInfo();
    
    // Filter tables based on party size
    partySizeSelect.addEventListener('change', function() {
        const partySize = parseInt(this.value);
        const tableOptions = tableSelect.querySelectorAll('option');
        
        tableOptions.forEach(function(option) {
            if (option.value === '') return; // Skip placeholder
            
            const capacityMatch = option.textContent.match(/Capacidad: (\d+)/);
            if (capacityMatch) {
                const capacity = parseInt(capacityMatch[1]);
                if (partySize > capacity) {
                    option.style.display = 'none';
                    option.disabled = true;
                } else {
                    option.style.display = 'block';
                    option.disabled = false;
                }
            }
        });
        
        // Reset table selection if current selection is invalid
        const selectedOption = tableSelect.options[tableSelect.selectedIndex];
        if (selectedOption && selectedOption.disabled) {
            tableSelect.value = '';
        }
    });
    
    // Add additional validation for reservation datetime
    reservationDatetime.addEventListener('change', function() {
        if (this.value) {
            const selectedTime = new Date(this.value);
            const now = new Date();
            const minTime = new Date(now.getTime() + 30 * 60000); // 30 minutes from now
            const maxTime = new Date(now.getTime() + 30 * 24 * 60 * 60000); // 30 days from now
            
            if (selectedTime < minTime) {
                alert('La fecha y hora de reservación debe ser al menos 30 minutos en adelante.');
                this.value = '';
                return;
            }
            
            if (selectedTime > maxTime) {
                alert('La fecha y hora de reservación no puede ser más de 30 días en adelante.');
                this.value = '';
                return;
            }
        }
    });
    
    function updateTimezoneInfo() {
        const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
        const now = new Date();
        const timeString = now.toLocaleString('es-MX', {
            timeZone: timezone,
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            timeZoneName: 'short'
        });
        
        const timezoneInfo = document.getElementById('timezone-info');
        if (timezoneInfo) {
            timezoneInfo.textContent = `Su zona horaria: ${timezone} | Hora actual: ${timeString}`;
        }
    }
});
</script>

<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.alert-info {
    border-left: 4px solid #0dcaf0;
}

.form-control:focus,
.form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}
</style>