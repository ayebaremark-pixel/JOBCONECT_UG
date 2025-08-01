document.addEventListener('DOMContentLoaded', function() {
    // User management
    document.querySelectorAll('.user-status-toggle').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const userId = this.getAttribute('data-user-id');
            const isActive = this.checked;
            
            fetch(`/admin/users/${userId}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ is_active: isActive })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    showAlert(data.message, 'error');
                    this.checked = !isActive;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred. Please try again.', 'error');
                this.checked = !isActive;
            });
        });
    });

    // Job approval
    document.querySelectorAll('.job-approval-toggle').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const jobId = this.getAttribute('data-job-id');
            const isApproved = this.checked;
            
            fetch(`/admin/jobs/${jobId}/approval`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ is_approved: isApproved })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    showAlert(data.message, 'error');
                    this.checked = !isApproved;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred. Please try again.', 'error');
                this.checked = !isApproved;
            });
        });
    });

    // Data export
    document.querySelectorAll('.export-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            const type = this.getAttribute('data-type');
            const format = this.getAttribute('data-format');
            
            fetch(`/admin/export/${type}?format=${format}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                if (response.ok) {
                    return response.blob();
                }
                throw new Error('Export failed');
            })
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `${type}_export.${format}`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Export failed. Please try again.', 'error');
            });
        });
    });

    // System backup
    const backupBtn = document.getElementById('backup-btn');
    if (backupBtn) {
        backupBtn.addEventListener('click', function() {
            this.disabled = true;
            this.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Creating Backup...';
            
            fetch('/admin/backup', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Backup created successfully!', 'success');
                    
                    // Add to backup list
                    const backupList = document.getElementById('backup-list');
                    if (backupList) {
                        const newItem = document.createElement('li');
                        newItem.className = 'flex justify-between items-center py-2 border-b border-gray-200';
                        newItem.innerHTML = `
                            <span>${data.filename}</span>
                            <div>
                                <a href="/admin/backup/download/${data.filename}" class="text-blue-600 hover:text-blue-800 mr-3">Download</a>
                                <button class="text-red-600 hover:text-red-800 delete-backup" data-filename="${data.filename}">Delete</button>
                            </div>
                        `;
                        backupList.prepend(newItem);
                    }
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Backup failed. Please try again.', 'error');
            })
            .finally(() => {
                this.disabled = false;
                this.textContent = 'Create Backup';
            });
        });
    }

    // Delete backup
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-backup')) {
            e.preventDefault();
            
            const filename = e.target.getAttribute('data-filename');
            if (confirm('Are you sure you want to delete this backup?')) {
                fetch(`/admin/backup/delete/${filename}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Backup deleted successfully!', 'success');
                        e.target.closest('li').remove();
                    } else {
                        showAlert(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Delete failed. Please try again.', 'error');
                });
            }
        }
    });

    // Logs filter
    const logsFilter = document.getElementById('logs-filter');
    if (logsFilter) {
        logsFilter.addEventListener('change', function() {
            const action = this.value;
            window.location.href = `/admin/logs?action=${action}`;
        });
    }
});