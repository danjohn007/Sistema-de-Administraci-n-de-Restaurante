// Global JavaScript functions for the restaurant system

// Show loading spinner
function showLoading() {
    if (!document.querySelector('.spinner-overlay')) {
        const spinner = document.createElement('div');
        spinner.className = 'spinner-overlay';
        spinner.innerHTML = `
            <div class="spinner-border spinner-border-lg text-light" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        `;
        document.body.appendChild(spinner);
    }
}

// Hide loading spinner
function hideLoading() {
    const spinner = document.querySelector('.spinner-overlay');
    if (spinner) {
        spinner.remove();
    }
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(amount);
}

// Confirm delete action
function confirmDelete(message = '¿Estás seguro de que deseas eliminar este elemento?') {
    return confirm(message);
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            if (alert.classList.contains('show')) {
                alert.classList.remove('show');
                setTimeout(function() {
                    alert.remove();
                }, 150);
            }
        }, 5000);
    });
});

// Bootstrap form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Initialize popovers
document.addEventListener('DOMContentLoaded', function() {
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});

// AJAX helper function
function makeRequest(url, options = {}) {
    const defaultOptions = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    };
    
    const config = { ...defaultOptions, ...options };
    
    return fetch(url, config)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .catch(error => {
            console.error('Request failed:', error);
            throw error;
        });
}

// Table helpers
function getStatusBadgeClass(status) {
    const statusClasses = {
        'disponible': 'bg-success',
        'ocupada': 'bg-danger',
        'cuenta_solicitada': 'bg-warning text-dark',
        'cerrada': 'bg-secondary',
        'pendiente': 'bg-secondary',
        'en_preparacion': 'bg-warning text-dark',
        'listo': 'bg-info',
        'entregado': 'bg-success'
    };
    
    return statusClasses[status] || 'bg-secondary';
}

function getStatusText(status) {
    const statusTexts = {
        'disponible': 'Disponible',
        'ocupada': 'Ocupada',
        'cuenta_solicitada': 'Cuenta Solicitada',
        'cerrada': 'Cerrada',
        'pendiente': 'Pendiente',
        'en_preparacion': 'En Preparación',
        'listo': 'Listo',
        'entregado': 'Entregado'
    };
    
    return statusTexts[status] || status;
}

// Print functionality
function printElement(elementId) {
    const element = document.getElementById(elementId);
    if (!element) {
        console.error('Element not found:', elementId);
        return;
    }
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Imprimir</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body { font-size: 12px; }
                @media print {
                    .no-print { display: none !important; }
                }
            </style>
        </head>
        <body onload="window.print(); window.close();">
            ${element.innerHTML}
        </body>
        </html>
    `);
    printWindow.document.close();
}

// Export to PDF (basic implementation using browser print)
function exportToPDF(elementId, filename = 'documento.pdf') {
    printElement(elementId);
}

// Real-time clock
function updateClock() {
    const now = new Date();
    const clockElement = document.getElementById('current-time');
    if (clockElement) {
        clockElement.textContent = now.toLocaleString('es-MX', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
    }
}

// Update clock every second
setInterval(updateClock, 1000);
updateClock(); // Initial call