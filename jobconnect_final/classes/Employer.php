<?php
class Employer {
    private $pdo;
    private $employer_id;
    private $user_id;
    private $company_name;
    private $company_description;
    private $industry;
    private $website;
    private $logo;
    private $location;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Load employer by ID
    public function loadById($employer_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM employers WHERE employer_id = ?");
        $stmt->execute([$employer_id]);
        $employer = $stmt->fetch();

        if ($employer) {
            $this->employer_id = $employer['employer_id'];
            $this->user_id = $employer['user_id'];
            $this->company_name = $employer['company_name'];
            $this->company_description = $employer['company_description'];
            $this->industry = $employer['industry'];
            $this->website = $employer['website'];
            $this->logo = $employer['logo'];
            $this->location = $employer['location'];
            return true;
        }
        return false;
    }

    // Load employer by user ID
    public function loadByUserId($user_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM employers WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $employer = $stmt->fetch();

        if ($employer) {
            $this->employer_id = $employer['employer_id'];
            $this->user_id = $employer['user_id'];
            $this->company_name = $employer['company_name'];
            $this->company_description = $employer['company_description'];
            $this->industry = $employer['industry'];
            $this->website = $employer['website'];
            $this->logo = $employer['logo'];
            $this->location = $employer['location'];
            return true;
        }
        return false;
    }

    // Create new employer
    public function create($user_id, $data) {
        $stmt = $this->pdo->prepare("INSERT INTO employers (user_id, company_name, company_description, industry, website, logo, location) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $success = $stmt->execute([
            $user_id,
            $data['company_name'],
            $data['company_description'] ?? null,
            $data['industry'] ?? null,
            $data['website'] ?? null,
            $data['logo'] ?? null,
            $data['location'] ?? null
        ]);

        if ($success) {
            $this->employer_id = $this->pdo->lastInsertId();
            return $this->loadById($this->employer_id);
        }
        return false;
    }

    // Update employer
    public function update($data) {
        $stmt = $this->pdo->prepare("UPDATE employers SET company_name = ?, company_description = ?, industry = ?, website = ?, logo = ?, location = ? WHERE employer_id = ?");
        return $stmt->execute([
            $data['company_name'] ?? $this->company_name,
            $data['company_description'] ?? $this->company_description,
            $data['industry'] ?? $this->industry,
            $data['website'] ?? $this->website,
            $data['logo'] ?? $this->logo,
            $data['location'] ?? $this->location,
            $this->employer_id
        ]);
    }

    // Get user details
    public function getUser() {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$this->user_id]);
        return $stmt->fetch();
    }

    // Get jobs posted by this employer
    public function getJobs($only_active = false) {
        $sql = "SELECT * FROM jobs WHERE employer_id = ?";
        if ($only_active) {
            $sql .= " AND is_active = TRUE";
        }
        $sql .= " ORDER BY posted_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->employer_id]);
        return $stmt->fetchAll();
    }

    // Get applications for all jobs posted by this employer
    public function getApplications() {
        $stmt = $this->pdo->prepare("SELECT a.*, j.title, u.first_name, u.last_name FROM applications a JOIN jobs j ON a.job_id = j.job_id JOIN users u ON a.user_id = u.user_id WHERE j.employer_id = ? ORDER BY a.applied_at DESC");
        $stmt->execute([$this->employer_id]);
        return $stmt->fetchAll();
    }

    // Getters
    public function getId() { return $this->employer_id; }
    public function getUserId() { return $this->user_id; }
    public function getCompanyName() { return $this->company_name; }
    public function getCompanyDescription() { return $this->company_description; }
    public function getIndustry() { return $this->industry; }
    public function getWebsite() { return $this->website; }
    public function getLogo() { return $this->logo; }
    public function getLocation() { return $this->location; }
}
?>