/**
 * Common JavaScript Functions
 * Shared functionality used across the entire application
 */

// Global AJAX setup
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// Show success message using SweetAlert2
function showSuccess(message) {
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: message,
        timer: 2000,
        showConfirmButton: false
    });
}

// Show error message using SweetAlert2
function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: message
    });
}

// Show warning message using SweetAlert2
function showWarning(message) {
    Swal.fire({
        icon: 'warning',
        title: 'Warning!',
        text: message
    });
}

// Show info message using SweetAlert2
function showInfo(message) {
    Swal.fire({
        icon: 'info',
        title: 'Information',
        text: message
    });
}

// Confirm delete with SweetAlert2
function confirmDelete(url, message = 'Are you sure you want to delete this item?') {
    Swal.fire({
        title: 'Are you sure?',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: url,
                type: 'DELETE',
                success: function(response) {
                    showSuccess(response.message);
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                },
                error: function(xhr) {
                    showError('An error occurred while deleting the item.');
                }
            });
        }
    });
}

// Confirm action with SweetAlert2
function confirmAction(url, method = 'POST', message = 'Are you sure you want to perform this action?') {
    Swal.fire({
        title: 'Are you sure?',
        text: message,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, proceed!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: url,
                type: method,
                success: function(response) {
                    showSuccess(response.message);
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                },
                error: function(xhr) {
                    showError('An error occurred while performing the action.');
                }
            });
        }
    });
}

// Initialize Fancybox for image galleries
function initFancybox() {
    if (typeof Fancybox !== 'undefined') {
        Fancybox.bind("[data-fancybox]", {
            loop: true,
            buttons: [
                "zoom",
                "slideShow",
                "fullScreen",
                "thumbs",
                "close"
            ],
            animationEffect: "fade",
            transitionEffect: "slide",
            thumbs: {
                autoStart: false
            }
        });
        console.log('Fancybox initialized successfully');
    } else {
        console.error('Fancybox not loaded');
    }
}

// Test Fancybox function
function testFancybox() {
    console.log('Testing Fancybox...');
    if (typeof Fancybox !== 'undefined') {
        Fancybox.show([
            {
                src: "https://picsum.photos/800/600",
                type: "image",
                caption: "Test Image"
            }
        ]);
        console.log('Fancybox test successful');
    } else {
        console.error('Fancybox is not available');
        alert('Fancybox is not loaded. Please check the console for errors.');
    }
}

// Utility function to format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Utility function to format datetime
function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Utility function to validate email
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Utility function to validate phone number
function isValidPhone(phone) {
    const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
    return phoneRegex.test(phone.replace(/[\s\-\(\)]/g, ''));
}

// Utility function to show loading spinner
function showLoading() {
    Swal.fire({
        title: 'Loading...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
}

// Utility function to hide loading spinner
function hideLoading() {
    Swal.close();
}

// Utility function to copy text to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        showSuccess('Copied to clipboard!');
    }, function(err) {
        showError('Failed to copy text');
    });
}

// Utility function to debounce function calls
function debounce(func, wait, immediate) {
    var timeout;
    return function executedFunction() {
        var context = this;
        var args = arguments;
        var later = function() {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        var callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
}

// Utility function to throttle function calls
function throttle(func, limit) {
    var inThrottle;
    return function() {
        var args = arguments;
        var context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    }
}

// Initialize common functionality when document is ready
$(document).ready(function() {
    // Initialize Fancybox
    initFancybox();
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Enable tooltips if Bootstrap is available
    if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // Enable popovers if Bootstrap is available
    if (typeof bootstrap !== 'undefined') {
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    }
}); 