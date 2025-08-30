<?php $title = 'Pedido Realizado'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="mb-4">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                </div>
                
                <h2 class="text-success mb-4">¡Pedido Realizado Exitosamente!</h2>
                
                <div class="alert alert-success" role="alert">
                    <h5 class="alert-heading">Número de Pedido: #<?= $order_id ?></h5>
                    <hr>
                    <?php if ($is_pickup): ?>
                        <p class="mb-0">
                            <i class="bi bi-bag-check"></i> 
                            Su pedido para <strong>recoger (pickup)</strong> ha sido registrado. 
                            Espere la confirmación de nuestro personal antes de venir a recogerlo.
                        </p>
                    <?php else: ?>
                        <p class="mb-0">
                            <i class="bi bi-table"></i> 
                            Su pedido para <strong>mesa</strong> ha sido registrado. 
                            Espere la confirmación de nuestro personal.
                        </p>
                    <?php endif; ?>
                </div>
                
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-title">Estado del Pedido</h6>
                        <span class="badge bg-warning fs-6">Pendiente de Confirmación</span>
                        <p class="mt-2 mb-0 text-muted">
                            Un miembro de nuestro equipo revisará y confirmará su pedido en breve.
                        </p>
                    </div>
                </div>
                
                <div class="mt-4">
                    <p class="text-muted">
                        <i class="bi bi-clock"></i> 
                        Tiempo estimado de confirmación: 5-10 minutos
                    </p>
                </div>
                
                <div class="mt-4">
                    <a href="<?= BASE_URL ?>/public/menu" class="btn btn-success">
                        <i class="bi bi-arrow-left"></i> Realizar Otro Pedido
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row justify-content-center mt-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle"></i> Información Importante
                </h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="bi bi-check2 text-success"></i> 
                        Guarde el número de pedido para cualquier consulta
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check2 text-success"></i> 
                        <?php if ($is_pickup): ?>
                            No se acerque a recoger hasta recibir la confirmación
                        <?php else: ?>
                            Diríjase a la mesa asignada después de la confirmación
                        <?php endif; ?>
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check2 text-success"></i> 
                        El pago se realizará al recibir su pedido
                    </li>
                    <li>
                        <i class="bi bi-check2 text-success"></i> 
                        En caso de problemas, muestre este número: #<?= $order_id ?>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>