<?php $title = 'Nueva Reservación'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-calendar-plus"></i> Nueva Reservación</h1>
    <a href="<?= BASE_URL ?>/reservations" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver a Reservaciones
    </a>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle"></i>
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<form method="POST" action="<?= BASE_URL ?>/reservations/create">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle"></i> Información de la Reservación
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="customer_name" class="form-label">Nombre del Cliente *</label>
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
                                <label for="customer_birthday" class="form-label">Cumpleaños (Opcional)</label>
                                <input type="text" 
                                       class="form-control <?= isset($errors['customer_birthday']) ? 'is-invalid' : '' ?>" 
                                       id="customer_birthday" 
                                       name="customer_birthday" 
                                       placeholder="DD/MM"
                                       value="<?= htmlspecialchars($old['customer_birthday'] ?? '') ?>">
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
                                <label for="reservation_datetime" class="form-label">Fecha y Hora *</label>
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
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="waiter_id" class="form-label">Mesero Asignado (Opcional)</label>
                                <select class="form-select <?= isset($errors['waiter_id']) ? 'is-invalid' : '' ?>" 
                                        id="waiter_id" 
                                        name="waiter_id">
                                    <option value="">Sin asignación específica</option>
                                    <?php foreach ($waiters as $waiter): ?>
                                        <option value="<?= $waiter['id'] ?>" 
                                                <?= (($old['waiter_id'] ?? '') == $waiter['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($waiter['name']) ?> (<?= htmlspecialchars($waiter['employee_code']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['waiter_id'])): ?>
                                    <div class="invalid-feedback">
                                        <?= htmlspecialchars($errors['waiter_id']) ?>
                                    </div>
                                <?php endif; ?>
                                <div class="form-text">
                                    El mesero puede asignarse durante la confirmación de la reservación
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mesas Preferidas (Opcional)</label>
                        <div class="form-text mb-2">
                            Seleccione una o más mesas para esta reservación. Si no selecciona ninguna, el personal asignará las mejores mesas disponibles.
                        </div>
                        <div class="row" id="tables-selection">
                            <?php foreach ($tables as $table): ?>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input table-checkbox" 
                                               type="checkbox" 
                                               value="<?= $table['id'] ?>" 
                                               name="table_ids[]" 
                                               id="table_<?= $table['id'] ?>"
                                               <?= in_array($table['id'], $old['table_ids'] ?? []) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="table_<?= $table['id'] ?>">
                                            <strong>Mesa <?= $table['number'] ?></strong><br>
                                            <small class="text-muted">Capacidad: <?= $table['capacity'] ?> personas</small>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notas Especiales</label>
                        <textarea class="form-control" 
                                  id="notes" 
                                  name="notes" 
                                  rows="3" 
                                  placeholder="Alguna solicitud especial, dieta, alergias, etc."><?= htmlspecialchars($old['notes'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle"></i> Información Importante
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="bi bi-clock"></i> Horarios de Atención</h6>
                        <p class="mb-1"><strong>Lunes a Viernes:</strong> 11:00 - 22:00</p>
                        <p class="mb-1"><strong>Sábados:</strong> 10:00 - 23:00</p>
                        <p class="mb-0"><strong>Domingos:</strong> 10:00 - 21:00</p>
                    </div>

                    <div class="alert alert-warning">
                        <h6><i class="bi bi-exclamation-triangle"></i> Políticas</h6>
                        <ul class="mb-0 small">
                            <li>La reservación debe hacerse con al menos 30 minutos de anticipación</li>
                            <li>La mesa se mantendrá disponible por 15 minutos después de la hora reservada</li>
                            <li>Para grupos de más de 8 personas, contactar directamente al restaurante</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 mt-3">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-calendar-plus"></i> Crear Reservación
                </button>
                <a href="<?= BASE_URL ?>/reservations" class="btn btn-outline-secondary">
                    Cancelar
                </a>
            </div>
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
    
    // Filter tables based on party size
    partySizeSelect.addEventListener('change', function() {
        const partySize = parseInt(this.value);
        let totalSelectedCapacity = 0;
        
        tableCheckboxes.forEach(function(checkbox) {
            const label = checkbox.nextElementSibling;
            const capacityMatch = label.textContent.match(/Capacidad: (\d+)/);
            
            if (capacityMatch) {
                const capacity = parseInt(capacityMatch[1]);
                const tableContainer = checkbox.closest('.col-md-4');
                
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
    
    // Add date change handler for real-time table availability filtering
    reservationDatetime.addEventListener('change', function() {
        const selectedDate = this.value;
        
        if (selectedDate) {
            // Show loading state
            const tablesContainer = document.getElementById('tables-selection');
            const originalContent = tablesContainer.innerHTML;
            tablesContainer.innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Cargando...</span></div> Actualizando disponibilidad...</div>';
            
            // Make AJAX request to get available tables for the selected date
            fetch('<?= BASE_URL ?>/reservations/getAvailableTablesByDate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    datetime: selectedDate<?php if (isset($reservation['id'])): ?>,
                    exclude_reservation_id: <?= $reservation['id'] ?><?php endif; ?>
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
            html = '<div class="col-12"><div class="alert alert-warning"><i class="bi bi-exclamation-triangle"></i> No hay mesas disponibles para la fecha y hora seleccionada. Por favor, elija otra fecha.</div></div>';
        } else {
            availableTables.forEach(function(table) {
                const isSelected = <?php echo json_encode(array_column($reservationTables ?? [], 'id')); ?>.includes(table.id);
                html += `
                    <div class="col-md-4 mb-2">
                        <div class="form-check">
                            <input class="form-check-input table-checkbox" 
                                   type="checkbox" 
                                   value="${table.id}" 
                                   name="table_ids[]" 
                                   id="table_${table.id}"
                                   ${isSelected ? 'checked' : ''}>
                            <label class="form-check-label" for="table_${table.id}">
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
    
    // Initial capacity update
    updateCapacityCounter();
});
</script>

<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.alert h6 {
    margin-bottom: 0.5rem;
}

.alert ul {
    padding-left: 1rem;
}
</style>