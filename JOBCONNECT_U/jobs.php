<?php
require_once 'Includes/config.php';
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


$industries = $pdo->query("SELECT DISTINCT industry FROM employers WHERE industry IS NOT NULL AND industry != ''")->fetchAll(PDO::FETCH_COLUMN);

require_once 'Includes/header.php';
?>

<style>
/* Job card hover effects */
.job-card {
  transition: all 0.3s ease;
  cursor: pointer;
  position: relative;
  overflow: hidden;
}

.job-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.job-card .job-details {
  max-height: 0;
  opacity: 0;
  overflow: hidden;
  transition: all 0.4s ease;
  margin-top: 0;
}

.job-card:hover .job-details {
  max-height: 200px;
  opacity: 1;
  margin-top: 16px;
}
</style>

<section class="py-10 bg-gray-50" style="border-radius: 12px;">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
            <h1 class="text-3xl font-extrabold text-gray-900">Browse Jobs</h1>
            <div class="mt-4 md:mt-0 flex items-center gap-6">
                <div class="text-sm text-gray-600"><span class="font-medium"><?php echo count($jobs); ?></span> Jobs Found</div>
                <div class="hidden sm:block text-sm text-gray-600">Sorted by: <span class="font-medium">Newest First</span></div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5 mb-8">
            <form method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                    <div class="md:col-span-2">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Keywords</label>
                        <div class="relative">
                            <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($filters['search']); ?>" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Job title, company">
                        </div>
                    </div>

                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                        <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($filters['location']); ?>" class="w-full px-3 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Any location">
                    </div>

                    <div>
                        <label for="job_type" class="block text-sm font-medium text-gray-700 mb-1">Job Type</label>
                        <select id="job_type" name="job_type" class="w-full px-3 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                        <select id="experience_level" name="experience_level" class="w-full px-3 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Any Level</option>
                            <option value="Entry level" <?php echo $filters['experience_level'] === 'Entry level' ? 'selected' : ''; ?>>Entry Level</option>
                            <option value="1-2 years" <?php echo $filters['experience_level'] === '1-2 years' ? 'selected' : ''; ?>>1-2 Years</option>
                            <option value="3-5 years" <?php echo $filters['experience_level'] === '3-5 years' ? 'selected' : ''; ?>>3-5 Years</option>
                            <option value="5+ years" <?php echo $filters['experience_level'] === '5+ years' ? 'selected' : ''; ?>>5+ Years</option>
                        </select>
                    </div>

                    <div>
                        <label for="industry" class="block text-sm font-medium text-gray-700 mb-1">Industry</label>
                        <select id="industry" name="industry" class="w-full px-3 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Any Industry</option>
                            <?php foreach ($industries as $industry): ?>
                                <option value="<?php echo htmlspecialchars($industry); ?>" <?php echo $filters['industry'] === $industry ? 'selected' : ''; ?>><?php echo htmlspecialchars($industry); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200">Search Jobs</button>
                        <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="text-gray-600 hover:text-blue-600 text-sm">Clear Filters</a>
                    </div>
                    <div class="text-xs text-gray-500 mt-3 sm:mt-0">Tip: Use multiple filters to narrow results</div>
                </div>
            </form>
        </div>

        <?php if (empty($jobs)): ?>
            <div class="bg-white rounded-xl shadow-md p-10 text-center">
                <svg class="w-20 h-20 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No jobs found</h3>
                <p class="text-gray-600">Try adjusting your search filters or check back later for new postings.</p>
            </div>
        <?php else: ?>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($jobs as $job): ?>
                    <article class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition p-5 flex flex-col job-card">
                        <div class="flex items-start gap-4">
                            <?php if ($job['logo']): ?>
                                <img src="<?php echo BASE_URL; ?>/Assets/uploads/logos/<?php echo $job['logo']; ?>" alt="<?php echo $job['company_name']; ?>" class="w-14 h-14 rounded-md object-contain">
                            <?php else: ?>
                                <div class="w-14 h-14 bg-blue-50 text-blue-700 rounded-md flex items-center justify-center font-semibold mr-2">
                                    <?php echo substr($job['company_name'], 0, 1); ?>
                                </div>
                            <?php endif; ?>

                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900"><?php echo $job['title']; ?></h3>
                                <p class="text-sm text-gray-600 mt-1"><?php echo $job['company_name']; ?> • <?php echo $job['location']; ?></p>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full"><?php echo ucfirst(str_replace('_', ' ', $job['job_type'])); ?></span>
                                    <?php if ($job['salary_range']): ?>
                                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full"><?php echo $job['salary_range']; ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($job['experience_level'])): ?>
                                        <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded-full"><?php echo $job['experience_level']; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="text-right ml-3">
                                <span class="text-sm text-gray-500">Posted <?php echo date('M j', strtotime($job['posted_at'])); ?></span><br>
                                <a href="<?php echo BASE_URL; ?>/job-details.php?id=<?php echo $job['job_id']; ?>" class="mt-3 inline-block text-blue-600 font-medium hover:underline">View Details</a>
                            </div>
                        </div>

                        <!-- Summary description (always visible) -->
                        <div class="mt-4">
                            <p class="text-gray-700 text-sm line-clamp-2"><?php echo htmlspecialchars(substr($job['description'], 0, 120)) . '...'; ?></p>
                        </div>

                        <!-- Expanded details on hover -->
                        <div class="job-details">
                            <div class="border-t border-gray-100 pt-4">
                                <h4 class="font-semibold text-gray-900 mb-2 text-sm">Full Description</h4>
                                <p class="text-gray-700 text-sm mb-3"><?php echo htmlspecialchars($job['description']); ?></p>
                                
                                <?php if (!empty($job['requirements'])): ?>
                                    <h4 class="font-semibold text-gray-900 mb-2 text-sm">Requirements</h4>
                                    <p class="text-gray-700 text-sm mb-3"><?php echo htmlspecialchars(substr($job['requirements'], 0, 150)) . '...'; ?></p>
                                <?php endif; ?>
                                
                                <div class="flex flex-wrap gap-2">
                                    <?php if (!empty($job['deadline'])): ?>
                                        <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">Deadline: <?php echo date('M j, Y', strtotime($job['deadline'])); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($job['views'])): ?>
                                        <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded-full"><?php echo $job['views']; ?> views</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php require_once 'Includes/footer.php'; ?>