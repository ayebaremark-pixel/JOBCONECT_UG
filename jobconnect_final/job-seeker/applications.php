<?php
class Application {
    private $pdo;
    private $application_id;
    private $job_id;
    private $user_id;
    private $cover_letter;
    private $resume_file;
    private $applied_at;
    private $status;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Load application by ID
    public function loadById($application_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM applications WHERE application_id = ?");
        $stmt->execute([$application_id]);
        $application = $stmt->fetch();

        if ($application) {
            $this->application_id = $application['application_id'];
            $this->job_id = $application['job_id'];
            $this->user_id = $application['user_id'];
            $this->cover_letter = $application['cover_letter'];
            $this->resume_file = $application['resume_file'];
            $this->applied_at = $application['applied_at'];
            $this->status = $application['status'];
            return true;
        }
        return false;
    }

    // Create new application
    public function create($job_id, $user_id, $data) {
        $stmt = $this->pdo->prepare("INSERT INTO applications (job_id, user_id, cover_letter, resume_file) VALUES (?, ?, ?, ?)");
        $success = $stmt->execute([
            $job_id,
            $user_id,
            $data['cover_letter'],
            $data['resume_file']
        ]);

        if ($success) {
            $this->application_id = $this->pdo->lastInsertId();
            return $this->loadById($this->application_id);
        }
        return false;
    }

    // Update application
    public function update($data) {
        $stmt = $this->pdo->prepare("UPDATE applications SET cover_letter = ?, resume_file = ?, status = ? WHERE application_id = ?");
        return $stmt->execute([
            $data['cover_letter'] ?? $this->cover_letter,
            $data['resume_file'] ?? $this->resume_file,
            $data['status'] ?? $this->status,
            $this->application_id
        ]);
    }

    // Delete application
    public function delete() {
        $stmt = $this->pdo->prepare("DELETE FROM applications WHERE application_id = ?");
        return $stmt->execute([$this->application_id]);
    }

    // Get job details
    public function getJob() {
        $stmt = $this->pdo->prepare("SELECT j.*, e.company_name FROM jobs j JOIN employers e ON j.employer_id = e.employer_id WHERE j.job_id = ?");
        $stmt->execute([$this->job_id]);
        return $stmt->fetch();
    }

    // Get user details
    public function getUser() {
        $stmt = $this->pdo->prepare("SELECT u.*, p.headline, p.skills FROM users u LEFT JOIN job_seeker_profiles p ON u.user_id = p.user_id WHERE u.user_id = ?");
        $stmt->execute([$this->user_id]);
        return $stmt->fetch();
    }

    // Getters
    public function getId() { return $this->application_id; }
    public function getJobId() { return $this->job_id; }
    public function getUserId() { return $this->user_id; }
    public function getCoverLetter() { return $this->cover_letter; }
    public function getResumeFile() { return $this->resume_file; }
    public function getAppliedAt() { return $this->applied_at; }
    public function getStatus() { return $this->status; }
}
?>