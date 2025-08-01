<?php
require_once 'includes/config.php';
$pageTitle = 'Browse Jobs';

// Get filters from query parameters
$filters = [
    'search' => $_GET['search'] ?? '',
    'location' => $_GET['location'] ?? '',
    'job_type' => $_GET['job_type'] ?? '',
    'experience_level' => $_GET['experience_level'] ?? '',
    'industry' => $_GET['industry'] ?? ''
];

// Get jobs with filters
$jobs = getJobs($pdo, $filters);

// Get unique industries for filter dropdown
$industries = $pdo->query("SELECT DISTINCT industry FROM employers WHERE industry IS NOT NULL AND industry != ''")->fetchAll(PDO::FETCH_COLUMN);

require_once 'includes/header.php';
?>

<section class="py-8">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold mb-8">Browse Jobs</h1>
        
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <form method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Keywords</label>
                        <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($filters['search']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Job title, company">
                    </div>
                    
                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                        <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($filters['location']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Any location">
                    </div>
                    
                    <div>
                        <label for="job_type" class="block text-sm font-medium text-gray-700 mb-1">Job Type</label>
                        <select id="job_type" name="job_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Any Type</option>
                            <option value="full_time" <?php echo $filters['job_type'] === 'full_time' ? 'selected' : ''; ?>>Full Time</option>
                            <option value="part_time" <?php echo $filters['job_type'] === 'part_time' ? 'selected' : ''; ?>>Part Time</option>
                            <option value="contract" <?php echo $filters['job_type'] === 'contract' ? 'selected' : ''; ?>>Contract</option>
                            <option value="internship" <?php echo $filters['job_type'] === 'internship' ? 'selected' : ''; ?>>Internship</option>
                            <option value="temporary" <?php echo $filters['job_type'] === 'temporary' ? 'selected' : ''; ?>>Temporary</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="experience_level" class="block text-sm font-medium text-gray-700 mb-1">Experience</label>
                        <select id="experience_level" name="experience_level" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Any Level</option>
                            <option value="Entry level" <?php echo $filters['experience_level'] === 'Entry level' ? 'selected' : ''; ?>>Entry Level</option>
                            <option value="1-2 years" <?php echo $filters['experience_level'] === '1-2 years' ? 'selected' : ''; ?>>1-2 Years</option>
                            <option value="3-5 years" <?php echo $filters['experience_level'] === '3-5 years' ? 'selected' : ''; ?>>3-5 Years</option>
                            <option value="5+ years" <?php echo $filters['experience_level'] === '5+ years' ? 'selected' : ''; ?>>5+ Years</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="industry" class="block text-sm font-medium text-gray-700 mb-1">Industry</label>
                        <select id="industry" name="industry" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Any Industry</option>
                            <?php foreach ($industries as $industry): ?>
                                <option value="<?php echo htmlspecialchars($industry); ?>" <?php echo $filters['industry'] === $industry ? 'selected' : ''; ?>><?php echo htmlspecialchars($industry); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="mt-4 flex justify-between">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-300">Search Jobs</button>
                    <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="text-gray-600 hover:text-blue-600 flex items-center">Clear Filters</a>
                </div>
            </form>
        </div>
        
        <div class="mb-4 flex justify-between items-center">
            <h2 class="text-xl font-semibold"><?php echo count($jobs); ?> Jobs Found</h2>
            <div class="text-sm text-gray-600">
                Sorted by: <span class="font-medium">Newest First</span>
            </div>
        </div>
        
        <div class="space-y-4">
            <?php if (empty($jobs)): ?>
                <div class="bg-white rounded-lg shadow-md p-8 text-center">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No jobs found</h3>
                    <p class="text-gray-600">Try adjusting your search filters or check back later for new postings.</p>
                </div>
            <?php else: ?>
                <?php foreach ($jobs as $job): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-300">
                        <div class="p-6">
                            <div class="flex flex-col md:flex-row md:items-center justify-between">
                                <div class="flex items-start mb-4 md:mb-0">
                                    <?php if ($job['logo']): ?>
                                        <img src="<?php echo BASE_URL; ?>/assets/uploads/logos/<?php echo $job['logo']; ?>" alt="<?php echo $job['company_name']; ?>" class="w-12 h-12 object-contain mr-4">
                                    <?php else: ?>
                                        <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 font-bold mr-4">
                                            <?php echo substr($job['company_name'], 0, 1); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <h3 class="font-bold text-lg"><?php echo $job['title']; ?></h3>
                                        <p class="text-gray-600"><?php echo $job['company_name']; ?></p>
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-md"><?php echo ucfirst(str_replace('_', ' ', $job['job_type'])); ?></span>
                                            <span class="inline-block bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-md"><?php echo $job['location']; ?></span>
                                            <?php if ($job['salary_range']): ?>
                                                <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded-md"><?php echo $job['salary_range']; ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end">
                                    <span class="text-sm text-gray-500 mb-2">Posted <?php echo date('M j, Y', strtotime($job['posted_at'])); ?></span>
                                    <a href="<?php echo BASE_URL; ?>/job-details.php?id=<?php echo $job['job_id']; ?>" class="text-blue-600 hover:text-blue-800 font-medium">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>