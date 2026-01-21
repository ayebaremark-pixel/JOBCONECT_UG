document.addEventListener('DOMContentLoaded', function() {
    // Save/unsave job functionality
    document.querySelectorAll('.save-job-btn').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const jobId = this.getAttribute('data-job-id');
            const isSaved = this.getAttribute('data-is-saved') === 'true';
            const action = isSaved ? 'unsave' : 'save';
            
            fetch(`/api/jobs/${jobId}/${action}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update button state
                    this.setAttribute('data-is-saved', !isSaved);
                    this.innerHTML = isSaved ? 
                        '<svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path></svg> Save Job' : 
                        '<svg class="w-5 h-5 mr-1" fill="currentColor" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path></svg> Saved';
                    
                    // Show success message
                    showAlert(data.message, 'success');
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred. Please try again.', 'error');
            });
        });
    });

    // Application status filter
    const statusFilter = document.getElementById('application-status-filter');
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            const status = this.value;
            const rows = document.querySelectorAll('#applications-table tbody tr');
            
            rows.forEach(function(row) {
                const rowStatus = row.getAttribute('data-status');
                if (status === 'all' || rowStatus === status) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
        });
    }

    // Resume upload progress
    const resumeUpload = document.getElementById('resume-upload');
    if (resumeUpload) {
        resumeUpload.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const progressBar = document.getElementById('upload-progress');
                const progressText = document.getElementById('upload-progress-text');
                
                // Simulate upload progress (in a real app, you'd use XMLHttpRequest or Fetch with progress events)
                let progress = 0;
                const interval = setInterval(() => {
                    progress += 10;
                    progressBar.style.width = progress + '%';
                    progressText.textContent = progress + '%';
                    
                    if (progress >= 100) {
                        clearInterval(interval);
                        progressText.textContent = 'Upload complete!';
                    }
                }, 200);
            }
        });
    }
});