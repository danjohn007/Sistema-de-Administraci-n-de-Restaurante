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
                                <input type="text" 
                                       class="form-control <?= isset($errors['customer_birthday']) ? 'is-invalid' : '' ?>" 
                                       id="customer_birthday" 
                                       name="customer_birthday" 
                                       value="<?= htmlspecialchars($old['customer_birthday'] ?? '') ?>"
                                       placeholder="DD/MM (ej: 15/03)"
                                       pattern="^(0[1-9]|[12][0-9]|3[01])/(0[1-9]|1[0-2])$">
                                <?php if (isset($errors['customer_birthday'])): ?>
                                    <div class="invalid-feedback">
                                        <?= htmlspecialchars($errors['customer_birthday']) ?>
                                    </div>
                                <?php endif; ?>
                                <div class="form-text">
                                    Solo día y mes (DD/MM). Ayúdanos a celebrar contigo y obtener ofertas especiales
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
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Mesas Preferidas (Opcional)</label>
                                <div class="form-text mb-2">
                                    Seleccione una o más mesas de su preferencia. Si no selecciona ninguna, nuestro personal le asignará las mejores mesas disponibles.
                                </div>
                                <div class="row" id="tables-selection">
                                    <?php foreach ($tables as $table): ?>
                                        <div class="col-6 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input table-checkbox" 
                                                       type="checkbox" 
                                                       value="<?= $table['id'] ?>" 
                                                       name="table_ids[]" 
                                                       id="public_table_<?= $table['id'] ?>"
                                                       <?= in_array($table['id'], $old['table_ids'] ?? []) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="public_table_<?= $table['id'] ?>">
                                                    <strong>Mesa <?= $table['number'] ?></strong><br>
                                                    <small class="text-muted">Capacidad: <?= $table['capacity'] ?> personas</small>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
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
    const tableCheckboxes = document.querySelectorAll('.table-checkbox');
    
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
        
        tableCheckboxes.forEach(function(checkbox) {
            const label = checkbox.nextElementSibling;
            const capacityMatch = label.textContent.match(/Capacidad: (\d+)/);
            
            if (capacityMatch) {
                const capacity = parseInt(capacityMatch[1]);
                const tableContainer = checkbox.closest('.col-6');
                
                if (partySize > 0) {
                    // Show/hide tables based on individual capacity
                    if (capacity < 2 && partySize > capacity) {
                        tableContainer.style.opacity = '0.5';
                        label.style.textDecoration = 'line-through';
                    } else {
                        tableContainer.style.opacity = '1';
                        label.style.textDecoration = 'none';
                    }
                } else {
                    // Reset all if no party size selected
                    tableContainer.style.opacity = '1';
                    label.style.textDecoration = 'none';
                }
            }
        });
        
        // Update capacity counter
        updateCapacityCounter();
    });
    
    // Monitor table selection
    tableCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', updateCapacityCounter);
    });
    
    function updateCapacityCounter() {
        const partySize = parseInt(partySizeSelect.value) || 0;
        let totalCapacity = 0;
        let selectedTables = [];
        
        tableCheckboxes.forEach(function(checkbox) {
            if (checkbox.checked) {
                const label = checkbox.nextElementSibling;
                const tableMatch = label.textContent.match(/Mesa (\d+)/);
                const capacityMatch = label.textContent.match(/Capacidad: (\d+)/);
                
                if (tableMatch && capacityMatch) {
                    totalCapacity += parseInt(capacityMatch[1]);
                    selectedTables.push(tableMatch[1]);
                }
            }
        });
        
        // Show/hide capacity info
        let capacityInfo = document.getElementById('capacity-info');
        if (!capacityInfo) {
            capacityInfo = document.createElement('div');
            capacityInfo.id = 'capacity-info';
            capacityInfo.className = 'alert mt-2';
            document.getElementById('tables-selection').appendChild(capacityInfo);
        }
        
        if (selectedTables.length > 0) {
            const sufficient = totalCapacity >= partySize;
            capacityInfo.className = `alert mt-2 ${sufficient ? 'alert-success' : 'alert-warning'}`;
            capacityInfo.innerHTML = `
                <strong>Mesas seleccionadas:</strong> ${selectedTables.join(', ')}<br>
                <strong>Capacidad total:</strong> ${totalCapacity} personas
                ${partySize > 0 ? `<br><strong>Requerido:</strong> ${partySize} personas` : ''}
                ${partySize > 0 && !sufficient ? '<br><em>⚠️ Capacidad insuficiente</em>' : ''}
                ${partySize > 0 && sufficient ? '<br><em>✅ Capacidad suficiente</em>' : ''}
            `;
            capacityInfo.style.display = 'block';
        } else {
            capacityInfo.style.display = 'none';
        }
    }
    
    // Add additional validation for reservation datetime and table availability filtering
    reservationDatetime.addEventListener('change', function() {
        const selectedDate = this.value;
        
        if (selectedDate) {
            const selectedTime = new Date(selectedDate);
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
            
            // Show loading state for table availability
            const tablesContainer = document.getElementById('tables-selection');
            const originalContent = tablesContainer.innerHTML;
            tablesContainer.innerHTML = '<div class="col-12"><div class="text-center"><div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Cargando...</span></div> Actualizando disponibilidad de mesas...</div></div>';
            
            // Make AJAX request to get available tables for the selected date
            fetch('<?= BASE_URL ?>/public/getAvailableTablesByDate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    datetime: selectedDate
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateTablesDisplay(data.tables);
                } else {
                    // Restore original content on error
                    tablesContainer.innerHTML = originalContent;
                    alert('Error al cargar las mesas disponibles: ' + (data.error || 'Error desconocido'));
                }
            })
            .catch(error => {
                // Restore original content on error
                tablesContainer.innerHTML = originalContent;
                console.error('Error:', error);
                alert('Error de conexión al cargar las mesas disponibles');
            });
        }
    });
    
    function updateTablesDisplay(availableTables) {
        const tablesContainer = document.getElementById('tables-selection');
        let html = '';
        
        if (availableTables.length === 0) {
            html = '<div class="col-12"><div class="alert alert-warning"><i class="bi bi-exclamation-triangle"></i> No hay mesas disponibles para la fecha y hora seleccionada. La reservación se procesará sin mesa específica y nuestro personal le asignará las mejores mesas disponibles.</div></div>';
        } else {
            availableTables.forEach(function(table) {
                html += `
                    <div class="col-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input table-checkbox" 
                                   type="checkbox" 
                                   value="${table.id}" 
                                   name="table_ids[]" 
                                   id="public_table_${table.id}">
                            <label class="form-check-label" for="public_table_${table.id}">
                                <strong>Mesa ${table.number}</strong><br>
                                <small class="text-muted">Capacidad: ${table.capacity} personas</small>
                            </label>
                        </div>
                    </div>
                `;
            });
        }
        
        tablesContainer.innerHTML = html;
        
        // Re-attach event listeners to new checkboxes
        const newTableCheckboxes = document.querySelectorAll('.table-checkbox');
        newTableCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', updateCapacityCounter);
        });
        
        // Update capacity counter with new selection
        updateCapacityCounter();
    }
    
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
    
    // Add birthday format validation
    const birthdayInput = document.getElementById('customer_birthday');
    if (birthdayInput) {
        birthdayInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^\d]/g, ''); // Remove non-digits
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value;
        });
        
        birthdayInput.addEventListener('blur', function(e) {
            const value = e.target.value;
            if (value && !value.match(/^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])$/)) {
                e.target.setCustomValidity('Use el formato DD/MM (ej: 15/03)');
            } else {
                e.target.setCustomValidity('');
            }
        });
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