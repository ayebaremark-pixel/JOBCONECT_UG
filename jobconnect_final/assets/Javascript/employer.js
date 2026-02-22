document.addEventListener('DOMContentLoaded', function() {
    // Job application status change
    document.querySelectorAll('.application-status-select').forEach(function(select) {
        select.addEventListener('change', function() {
            const applicationId = this.getAttribute('data-application-id');
            const status = this.value;
            
            fetch(`/api/applications/${applicationId}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ status: status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    
                    // Update status badge
                    const badge = document.querySelector(`.status-badge-${applicationId}`);
                    if (badge) {
                        badge.className = `status-badge-${applicationId} px-2 py-1 rounded-full text-xs font-medium ${
                            status === 'accepted' ? 'bg-green-100 text-green-800' : 
                            status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'
                        }`;
                        badge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                    }
                } else {
                    showAlert(data.message, 'error');
                    // Reset to previous value
                    this.value = this.getAttribute('data-previous-value');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred. Please try again.', 'error');
                this.value = this.getAttribute('data-previous-value');
            });
        });
    });

    // Job posting status toggle
    document.querySelectorAll('.job-status-toggle').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const jobId = this.getAttribute('data-job-id');
            const isActive = this.checked;
            
            fetch(`/api/jobs/${jobId}/status`, {
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

    // Job applications filter
    const jobFilter = document.getElementById('job-filter');
    if (jobFilter) {
        jobFilter.addEventListener('change', function() {
            const jobId = this.value;
            window.location.href = `/employer/applications?job_id=${jobId}`;
        });
    }

    // Application search
    const applicationSearch = document.getElementById('application-search');
    if (applicationSearch) {
        applicationSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#applications-table tbody tr');
            
            rows.forEach(function(row) {
                const candidateName = row.querySelector('.candidate-name').textContent.toLowerCase();
                const jobTitle = row.querySelector('.job-title').textContent.toLowerCase();
                
                if (candidateName.includes(searchTerm) || jobTitle.includes(searchTerm)) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
        });
    }
});