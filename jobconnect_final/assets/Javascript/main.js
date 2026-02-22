// Document ready function
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }

    // File input display
    document.querySelectorAll('input[type="file"]').forEach(function(input) {
        input.addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : 'No file chosen';
            const fileDisplay = document.getElementById('file-name');
            if (fileDisplay) {
                fileDisplay.textContent = fileName;
            }
        });
    });

    // Form validation
    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            let valid = true;
            const requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    valid = false;
                    field.classList.add('border-red-500');
                    
                    // Create error message if it doesn't exist
                    if (!field.nextElementSibling || !field.nextElementSibling.classList.contains('error-message')) {
                        const errorMessage = document.createElement('p');
                        errorMessage.className = 'mt-1 text-sm text-red-600 error-message';
                        errorMessage.textContent = 'This field is required';
                        field.parentNode.insertBefore(errorMessage, field.nextSibling);
                    }
                } else {
                    field.classList.remove('border-red-500');
                    const errorMessage = field.nextElementSibling;
                    if (errorMessage && errorMessage.classList.contains('error-message')) {
                        errorMessage.remove();
                    }
                }
            });
            
            if (!valid) {
                e.preventDefault();
                
                // Scroll to first error
                const firstError = form.querySelector('.border-red-500');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            }
        });
    });

    // Password toggle visibility
    document.querySelectorAll('.toggle-password').forEach(function(button) {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            if (input.type === 'password') {
                input.type = 'text';
                this.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>';
            } else {
                input.type = 'password';
                this.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>';
            }
        });
    });

    // Auto-dismiss alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 300);
        }, 5000);
    });

    // Tab functionality
    document.querySelectorAll('.tab-button').forEach(function(button) {
        button.addEventListener('click', function() {
            const tabGroup = this.closest('.tab-group');
            const tabName = this.getAttribute('data-tab');
            
            // Hide all tab contents
            tabGroup.querySelectorAll('.tab-content').forEach(function(content) {
                content.classList.add('hidden');
            });
            
            // Show selected tab content
            document.getElementById(tabName).classList.remove('hidden');
            
            // Update active tab button
            tabGroup.querySelectorAll('.tab-button').forEach(function(btn) {
                btn.classList.remove('active');
            });
            this.classList.add('active');
        });
    });

    // Modal functionality
    document.querySelectorAll('[data-modal-toggle]').forEach(function(button) {
        button.addEventListener('click', function() {
            const modalId = this.getAttribute('data-modal-toggle');
            const modal = document.getElementById(modalId);
            
            if (modal) {
                modal.classList.toggle('hidden');
                document.body.classList.toggle('overflow-hidden');
            }
        });
    });

    // Close modal when clicking outside
    document.querySelectorAll('.modal').forEach(function(modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        });
    });

    // Close modal with button
    document.querySelectorAll('[data-modal-hide]').forEach(function(button) {
        button.addEventListener('click', function() {
            const modalId = this.getAttribute('data-modal-hide');
            const modal = document.getElementById(modalId);
            
            if (modal) {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        });
    });

    // Dropdown functionality
    document.querySelectorAll('.dropdown-toggle').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            const dropdown = this.nextElementSibling;
            dropdown.classList.toggle('hidden');
        });
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-menu').forEach(function(menu) {
            menu.classList.add('hidden');
        });
    });

    // Prevent dropdown from closing when clicking inside
    document.querySelectorAll('.dropdown-menu').forEach(function(menu) {
        menu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });

    // Job search filters - show/hide advanced filters
    const toggleAdvancedFilters = document.getElementById('toggle-advanced-filters');
    if (toggleAdvancedFilters) {
        toggleAdvancedFilters.addEventListener('click', function() {
            const advancedFilters = document.getElementById('advanced-filters');
            if (advancedFilters) {
                advancedFilters.classList.toggle('hidden');
                this.textContent = advancedFilters.classList.contains('hidden') ? 
                    'Show Advanced Filters' : 'Hide Advanced Filters';
            }
        });
    }

    document.querySelectorAll('[data-max-length]').forEach(function(textarea) {
        const maxLength = parseInt(textarea.getAttribute('data-max-length'));
        const counterId = textarea.getAttribute('data-counter-id');
        const counter = document.getElementById(counterId);
        
        if (counter) {
            const updateCounter = function() {
                const remaining = maxLength - textarea.value.length;
                counter.textContent = remaining + ' characters remaining';
                counter.className = 'text-sm ' + (remaining < 0 ? 'text-red-600' : 'text-gray-500');
            };
            
            textarea.addEventListener('input', updateCounter);
            updateCounter();
        }
    });

    // AJAX form submissions
    document.querySelectorAll('[data-ajax-form]').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitButton = form.querySelector('[type="submit"]');
            const originalText = submitButton.innerHTML;
            const loadingText = submitButton.getAttribute('data-loading-text') || 'Processing...';
            
            // Show loading state
            submitButton.disabled = true;
            submitButton.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>' + loadingText;
            
            const formData = new FormData(form);
            const action = form.getAttribute('action');
            const method = form.getAttribute('method') || 'POST';
            
            fetch(action, {
                method: method,
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    if (data.message) {
                        showAlert(data.message, 'success');
                    }
                    
                    // Redirect if needed
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    }
                    
                    // Reset form if needed
                    if (data.reset) {
                        form.reset();
                    }
                    
                    // Reload page if needed
                    if (data.reload) {
                        window.location.reload();
                    }
                } else {
                    // Show error message
                    if (data.message) {
                        showAlert(data.message, 'error');
                    }
                    
                    // Show field errors
                    if (data.errors) {
                        Object.keys(data.errors).forEach(function(field) {
                            const input = form.querySelector('[name="' + field + '"]');
                            if (input) {
                                input.classList.add('border-red-500');
                                
                                const errorContainer = input.nextElementSibling;
                                if (errorContainer && errorContainer.classList.contains('error-message')) {
                                    errorContainer.textContent = data.errors[field][0];
                                } else {
                                    const errorMessage = document.createElement('p');
                                    errorMessage.className = 'mt-1 text-sm text-red-600 error-message';
                                    errorMessage.textContent = data.errors[field][0];
                                    input.parentNode.insertBefore(errorMessage, input.nextSibling);
                                }
                            }
                        });
                    }
                }
            })
            .catch(error => {
                showAlert('An error occurred. Please try again.', 'error');
                console.error('Error:', error);
            })
            .finally(() => {
                // Reset button state
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            });
        });
    });

    // Function to show alert messages
    function showAlert(message, type) {
        const alertContainer = document.getElementById('alert-container');
        if (!alertContainer) return;
        
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} p-4 mb-4 rounded-md border border-${type}-200 bg-${type}-50 text-${type}-800`;
        alert.innerHTML = `
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${type === 'success' ? 'M5 13l4 4L19 7' : 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'}"></path>
                </svg>
                ${message}
            </div>
        `;
        
        alertContainer.appendChild(alert);
        
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    }

    // Initialize tooltips
    tippy('[data-tippy-content]', {
        arrow: true,
        animation: 'shift-away',
        duration: 200,
        theme: 'light'
    });

    // Initialize date pickers
    flatpickr('[data-datepicker]', {
        dateFormat: 'Y-m-d',
        minDate: 'today'
    });

    // Initialize select2 for enhanced select elements
    $('[data-select2]').select2({
        width: '100%',
        theme: 'bootstrap4'
    });

    // Initialize rich text editors
    tinymce.init({
        selector: '[data-tinymce]',
        plugins: 'link lists table code',
        toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link table | code',
        menubar: false,
        statusbar: false
    });
});