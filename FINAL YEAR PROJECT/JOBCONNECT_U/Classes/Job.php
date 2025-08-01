<?php
class Job {
    private $pdo;
    private $job_id;
    private $employer_id;
    private $title;
    private $description;
    private $requirements;
    private $location;
    private $job_type;
    private $salary_range;
    private $experience_level;
    private $posted_at;
    private $deadline;
    private $is_active;
    private $views;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Load job by ID
    public function loadById($job_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM jobs WHERE job_id = ?");
        $stmt->execute([$job_id]);
        $job = $stmt->fetch();

        if ($job) {
            $this->job_id = $job['job_id'];
            $this->employer_id = $job['employer_id'];
            $this->title = $job['title'];
            $this->description = $job['description'];
            $this->requirements = $job['requirements'];
            $this->location = $job['location'];
            $this->job_type = $job['job_type'];
            $this->salary_range = $job['salary_range'];
            $this->experience_level = $job['experience_level'];
            $this->posted_at = $job['posted_at'];
            $this->deadline = $job['deadline'];
            $this->is_active = $job['is_active'];
            $this->views = $job['views'];
            return true;
        }
        return false;
    }

    // Create new job
    public function create($employer_id, $data) {
        $stmt = $this->pdo->prepare("INSERT INTO jobs (employer_id, title, description, requirements, location, job_type, salary_range, experience_level, deadline) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $success = $stmt->execute([
            $employer_id,
            $data['title'],
            $data['description'],
            $data['requirements'],
            $data['location'],
            $data['job_type'],
            $data['salary_range'],
            $data['experience_level'],
            $data['deadline']
        ]);

        if ($success) {
            $this->job_id = $this->pdo->lastInsertId();
            return $this->loadById($this->job_id);
        }
        return false;
    }

    // Update job
    public function update($data) {
        $stmt = $this->pdo->prepare("UPDATE jobs SET title = ?, description = ?, requirements = ?, location = ?, job_type = ?, salary_range = ?, experience_level = ?, deadline = ?, is_active = ? WHERE job_id = ?");
        return $stmt->execute([
            $data['title'],
            $data['description'],
            $data['requirements'],
            $data['location'],
            $data['job_type'],
            $data['salary_range'],
            $data['experience_level'],
            $data['deadline'],
            $data['is_active'] ?? $this->is_active,
            $this->job_id
        ]);
    }

    // Delete job
    public function delete() {
        $stmt = $this->pdo->prepare("DELETE FROM jobs WHERE job_id = ?");
        return $stmt->execute([$this->job_id]);
    }

    // Increment views
    public function incrementViews() {
        $stmt = $this->pdo->prepare("UPDATE jobs SET views = views + 1 WHERE job_id = ?");
        return $stmt->execute([$this->job_id]);
    }

    // Get applications for this job
    public function getApplications() {
        $stmt = $this->pdo->prepare("SELECT a.*, u.first_name, u.last_name FROM applications a JOIN users u ON a.user_id = u.user_id WHERE a.job_id = ? ORDER BY a.applied_at DESC");
        $stmt->execute([$this->job_id]);
        return $stmt->fetchAll();
    }

    // Get employer details
    public function getEmployer() {
        $stmt = $this->pdo->prepare("SELECT e.*, u.email FROM employers e JOIN users u ON e.user_id = u.user_id WHERE e.employer_id = ?");
        $stmt->execute([$this->employer_id]);
        return $stmt->fetch();
    }

    // Getters
    public function getId() { return $this->job_id; }
    public function getEmployerId() { return $this->employer_id; }
    public function getTitle() { return $this->title; }
    public function getDescription() { return $this->description; }
    public function getRequirements() { return $this->requirements; }
    public function getLocation() { return $this->location; }
    public function getJobType() { return $this->job_type; }
    public function getSalaryRange() { return $this->salary_range; }
    public function getExperienceLevel() { return $this->experience_level; }
    public function getPostedAt() { return $this->posted_at; }
    public function getDeadline() { return $this->deadline; }
    public function isActive() { return $this->is_active; }
    public function getViews() { return $this->views; }
}
?>