<?php $title = 'Reservación Exitosa'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-success">
            <div class="card-header bg-success text-white text-center">
                <h4 class="card-title mb-0">
                    <i class="bi bi-check-circle"></i> ¡Reservación Exitosa!
                </h4>
            </div>
            <div class="card-body text-center">
                <div class="mb-4">
                    <i class="bi bi-calendar-check text-success" style="font-size: 4rem;"></i>
                </div>
                
                <h5 class="mb-3">Su reservación ha sido creada correctamente</h5>
                
                <div class="alert alert-light border">
                    <h6 class="mb-3"><strong>Número de Reservación:</strong></h6>
                    <h3 class="text-primary mb-0">#<?= str_pad($reservation_id, 6, '0', STR_PAD_LEFT) ?></h3>
                </div>
                
                <div class="mb-4">
                    <p class="mb-2">
                        <i class="bi bi-info-circle text-info"></i>
                        <strong>Estado:</strong> Pendiente de confirmación
                    </p>
                    <p class="text-muted">
                        Nuestro personal se pondrá en contacto con usted para confirmar los detalles de su reservación.
                    </p>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <a href="<?= BASE_URL ?>/public/menu" class="btn btn-outline-primary w-100">
                            <i class="bi bi-cup-hot"></i> Ver Menú
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="<?= BASE_URL ?>/public/reservations" class="btn btn-success w-100">
                            <i class="bi bi-calendar-plus"></i> Hacer Otra Reservación
                        </a>
                    </div>
                </div>
                
                <hr>
                
                <div class="text-start">
                    <h6 class="mb-3"><i class="bi bi-exclamation-circle text-warning"></i> Información Importante:</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="bi bi-clock text-muted"></i>
                            Mantenemos un margen de tolerancia de <strong>15 minutos</strong>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-telephone text-muted"></i>
                            Si necesita modificar o cancelar, contacte al restaurante
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-gift text-muted"></i>
                            Si indicó su cumpleaños, recibirá ofertas especiales
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-envelope text-muted"></i>
                            Recibirá recordatorios por teléfono
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.border-success {
    border-width: 2px !important;
}
</style>