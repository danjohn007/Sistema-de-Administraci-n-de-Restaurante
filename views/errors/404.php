<?php $title = 'Página No Encontrada - Error 404'; ?>

<div class="error-container d-flex align-items-center justify-content-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card error-card">
                    <div class="card-body p-5 text-center">
                        <div class="mb-4">
                            <i class="bi bi-exclamation-triangle text-warning" style="font-size: 5rem;"></i>
                        </div>
                        
                        <h1 class="display-4 fw-bold text-primary mb-3">404</h1>
                        <h3 class="mb-3">Página No Encontrada</h3>
                        <p class="text-muted mb-4">
                            Lo sentimos, la página que estás buscando no existe o ha sido movida.
                            Verifica la URL o regresa al inicio para continuar navegando.
                        </p>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <a href="<?= BASE_URL ?>/dashboard" class="btn btn-primary btn-lg me-md-2">
                                <i class="bi bi-house"></i> Ir al Dashboard
                            </a>
                            <button onclick="history.back()" class="btn btn-outline-secondary btn-lg">
                                <i class="bi bi-arrow-left"></i> Regresar
                            </button>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <small class="text-muted">
                                Si continúas teniendo problemas, contacta al administrador del sistema.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.error-container {
    min-height: 60vh;
}

.error-card {
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    border-radius: 1rem;
}

.error-card .card-body {
    border-radius: 1rem;
}

@media (max-width: 768px) {
    .error-container {
        min-height: 50vh;
    }
    
    .display-4 {
        font-size: 3rem;
    }
}
</style>