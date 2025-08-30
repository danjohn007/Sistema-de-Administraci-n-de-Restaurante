<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Menú Público - ' . APP_NAME ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= BASE_URL ?>/public/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Public Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="<?= BASE_URL ?>/public/menu">
                <i class="bi bi-shop"></i> <?= APP_NAME ?> - Menú
            </a>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php 
    $flashTypes = ['success', 'error', 'warning', 'info'];
    foreach ($flashTypes as $type): 
        $message = isset($_SESSION['flash_' . $type]) ? $_SESSION['flash_' . $type] : null;
        if ($message):
            unset($_SESSION['flash_' . $type]);
    ?>
        <div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php 
        endif;
    endforeach; 
    ?>

    <div class="container mt-4">