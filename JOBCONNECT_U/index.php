<?php
require_once 'Includes/config.php'; // $pdo is now available
$pageTitle = 'Home';

// Get featured jobs
$stmt = $pdo->query("SELECT j.*, e.company_name, e.logo 
                     FROM jobs j 
                     JOIN employers e ON j.employer_id = e.employer_id 
                     WHERE j.is_active = TRUE 
                     ORDER BY j.posted_at DESC 
                     LIMIT 6");
$featuredJobs = $stmt->fetchAll();

require_once 'Includes/header.php';
?>

<div>

<!-- Add CSS and JS links here if not already in header.php -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/Assets/CSS/styles.css">
<script src="<?php echo BASE_URL; ?>/Assets/Javascript/main.js"></script>

<style>
/* Scroll reveal animations */
.fade-up {
  opacity: 0;
  transform: translateY(30px);
  transition: opacity 0.6s ease-out, transform 0.6s ease-out;
}

.fade-up.revealed {
  opacity: 1;
  transform: translateY(0);
}

.fade-in {
  opacity: 0;
  transition: opacity 0.8s ease-out;
}

.fade-in.revealed {
  opacity: 1;
}

.slide-left {
  opacity: 0;
  transform: translateX(-50px);
  transition: opacity 0.7s ease-out, transform 0.7s ease-out;
}

.slide-left.revealed {
  opacity: 1;
  transform: translateX(0);
}

.slide-right {
  opacity: 0;
  transform: translateX(50px);
  transition: opacity 0.7s ease-out, transform 0.7s ease-out;
}

.slide-right.revealed {
  opacity: 1;
  transform: translateX(0);
}

.scale-up {
  opacity: 0;
  transform: scale(0.8);
  transition: opacity 0.5s ease-out, transform 0.5s ease-out;
}

.scale-up.revealed {
  opacity: 1;
  transform: scale(1);
}

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

.job-card .apply-btn {
  opacity: 0;
  transform: translateY(10px);
  transition: all 0.3s ease;
}

.job-card:hover .apply-btn {
  opacity: 1;
  transform: translateY(0);
}

.job-card .view-details-btn {
  transition: all 0.3s ease;
}

.job-card:hover .view-details-btn {
  opacity: 0;
  transform: translateY(-10px);
}
</style>

<script>
// Scroll reveal functionality
document.addEventListener('DOMContentLoaded', function() {
  const revealElements = document.querySelectorAll('.fade-up, .fade-in, .slide-left, .slide-right, .scale-up');
  
  function reveal() {
    revealElements.forEach(element => {
      const elementTop = element.getBoundingClientRect().top;
      const elementVisible = 150;
      
      if (elementTop < window.innerHeight - elementVisible) {
        element.classList.add('revealed');
      }
    });
  }
  
  window.addEventListener('scroll', reveal);
  reveal(); // Initial check
});
</script>

<!-- Main Hero Section -->
<section class="hero-main py-16 bg-gradient-to-br from-blue-900 via-blue-700 to-blue-600 text-white relative overflow-hidden">
    <div class="absolute inset-0 bg-black opacity-20"></div>
    <div class="container mx-auto px-4 relative z-10">
        <div class="text-center mb-12 fade-in">
            <h1 class="text-5xl md:text-6xl font-extrabold mb-4">Your Career Journey Starts Here</h1>
            <p class="text-xl md:text-2xl opacity-90 max-w-3xl mx-auto">Join thousands of professionals who found their dream careers through JobConnectUganda</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <!-- Success Story Card 1 -->
            <div class="card bg-white text-gray-900 p-6 scale-up" style="transition-delay: 0.1s">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-green-600">Sarah M.</h3>
                        <p class="text-sm text-gray-600">Software Developer</p>
                    </div>
                </div>
                <p class="text-sm italic">"Found my dream tech job within 2 weeks. Amazing platform!"</p>
                <div class="mt-3 text-xs text-gray-500">MTN Uganda</div>
            </div>

            <!-- Success Story Card 2 -->
            <div class="card bg-white text-gray-900 p-6 scale-up" style="transition-delay: 0.2s">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H8a2 2 0 01-2-2V8a2 2 0 012-2h8z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-blue-600">James K.</h3>
                        <p class="text-sm text-gray-600">Marketing Manager</p>
                    </div>
                </div>
                <p class="text-sm italic">"Connected with top employers instantly. Great experience!"</p>
                <div class="mt-3 text-xs text-gray-500">Stanbic Bank</div>
            </div>

            <!-- Stats Card 1 -->
            <div role="article" aria-label="Jobs Posted stats" class="card p-6 scale-up transform transition duration-300 hover:-translate-y-1 hover:shadow-xl rounded-lg text-white" style="background: linear-gradient(90deg,#10b981 0%,#059669 100%);">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 flex items-center justify-center rounded-md bg-white bg-opacity-10">
                        <!-- Briefcase icon -->
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12v.01M9 16h6M5 7h14v10a2 2 0 01-2 2H7a2 2 0 01-2-2V7zM9 7V5a3 3 0 116 0v2"></path>
                        </svg>
                    </div>

                    <div class="flex-1 text-center sm:text-left">
                        <div class="text-3xl sm:text-4xl font-extrabold leading-tight" data-value="5000" data-suffix="+">0</div>
                        <div class="text-sm opacity-90 mt-1">Jobs Posted</div>
                        <div class="text-xs opacity-75">This Month</div>
                    </div>
                </div>
            </div>

            <!-- Stats Card 2 -->
            <div role="article" aria-label="Active Users stats" class="card p-6 scale-up transform transition duration-300 hover:-translate-y-1 hover:shadow-xl rounded-lg text-white" style="background: linear-gradient(90deg,#7c3aed 0%,#6d28d9 100%);">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 flex items-center justify-center rounded-md bg-white bg-opacity-10">
                        <!-- Users icon -->
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m0-4a3 3 0 100-6 3 3 0 000 6zm6 0a3 3 0 100-6 3 3 0 000 6z"></path>
                        </svg>
                    </div>

                    <div class="flex-1 text-center sm:text-left">
                        <div class="text-3xl sm:text-4xl font-extrabold leading-tight" data-value="15000" data-suffix="+">0</div>
                        <div class="text-sm opacity-90 mt-1">Active Users</div>
                        <div class="text-xs opacity-75">And Growing</div>
                    </div>
                </div>
            </div>

            <!-- optional: Stats Card 3 (companies) -->
            <div role="article" aria-label="Companies on platform" class="card p-6 scale-up transform transition duration-300 hover:-translate-y-1 hover:shadow-xl rounded-lg text-blue-900" style="background: linear-gradient(90deg,#fef3c7 0%,#fde68a 100%);">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 flex items-center justify-center rounded-md bg-white">
                        <!-- Building icon -->
                        <svg class="w-6 h-6 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7a2 2 0 012-2h10a2 2 0 012 2v14M9 10h.01M15 10h.01M9 16h6"></path>
                        </svg>
                    </div>

                    <div class="flex-1 text-center sm:text-left">
                        <div class="text-3xl sm:text-4xl font-extrabold leading-tight" data-value="320" data-suffix="+">0</div>
                        <div class="text-sm opacity-90 mt-1">Companies</div>
                        <div class="text-xs opacity-75">Hiring Now</div>
                    </div>
                </div>
            </div>

            <!-- Stats Card 4: Remote Opportunities -->
            <div role="article" aria-label="Remote opportunities stats" class="card p-6 scale-up transform transition duration-300 hover:-translate-y-1 hover:shadow-xl rounded-lg text-white" style="background: linear-gradient(90deg,#0ea5a4 0%,#0891b2 100%);">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 flex items-center justify-center rounded-md bg-white bg-opacity-10">
                        <!-- Globe icon -->
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0zM2 12h20M12 2v20"></path>
                        </svg>
                    </div>

                    <div class="flex-1 text-center sm:text-left">
                        <div class="text-3xl sm:text-4xl font-extrabold leading-tight" data-value="4200" data-suffix="+">0</div>
                        <div class="text-sm opacity-90 mt-1">Remote Jobs</div>
                        <div class="text-xs opacity-75">Available</div>
                    </div>
                </div>
            </div>

            <!-- Stats Card 5: Successful Hires -->
            <div role="article" aria-label="Successful hires stats" class="card p-6 scale-up transform transition duration-300 hover:-translate-y-1 hover:shadow-xl rounded-lg text-white" style="background: linear-gradient(90deg,#ef4444 0%,#dc2626 100%);">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 flex items-center justify-center rounded-md bg-white bg-opacity-10">
                        <!-- Check badge icon -->
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M5 13v6h14v-6"></path>
                        </svg>
                    </div>

                    <div class="flex-1 text-center sm:text-left">
                        <div class="text-3xl sm:text-4xl font-extrabold leading-tight" data-value="9800" data-suffix="+">0</div>
                        <div class="text-sm opacity-90 mt-1">Hires</div>
                        <div class="text-xs opacity-75">Since Launch</div>
                    </div>
                </div>
            </div>

            <!-- Stats Card 6: Applications Processed -->
            <div role="article" aria-label="Applications processed stats" class="card p-6 scale-up transform transition duration-300 hover:-translate-y-1 hover:shadow-xl rounded-lg text-white" style="background: linear-gradient(90deg,#f97316 0%,#fb923c 100%);">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 flex items-center justify-center rounded-md bg-white bg-opacity-10">
                        <!-- Document icon -->
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6M9 16h6M13 4H6a2 2 0 00-2 2v14l4-2 4 2 4-2 4 2V8a2 2 0 00-2-2h-3"></path>
                        </svg>
                    </div>

                    <div class="flex-1 text-center sm:text-left">
                        <div class="text-3xl sm:text-4xl font-extrabold leading-tight" data-value="250000" data-suffix="+">0</div>
                        <div class="text-sm opacity-90 mt-1">Applications</div>
                        <div class="text-xs opacity-75">Processed</div>
                    </div>
                </div>
            </div>

            <script>
            // Small count-up animation triggered when cards enter viewport
            (function() {
              const observers = [];
              const options = { threshold: 0.35 };
              const animate = el => {
                const target = parseInt(el.getAttribute('data-value') || '0', 10);
                const suffix = el.getAttribute('data-suffix') || '';
                const duration = 900;
                let start = null;
                const step = timestamp => {
                  if (!start) start = timestamp;
                  const progress = Math.min((timestamp - start) / duration, 1);
                  const current = Math.floor(progress * target);
                  el.textContent = current.toLocaleString() + (progress === 1 ? suffix : '');
                  if (progress < 1) {
                    window.requestAnimationFrame(step);
                  } else {
                    // ensure final value with suffix
                    el.textContent = target.toLocaleString() + suffix;
                  }
                };
                window.requestAnimationFrame(step);
              };

              document.querySelectorAll('[data-value]').forEach(elem => {
                const io = new IntersectionObserver((entries, obs) => {
                  entries.forEach(entry => {
                    if (entry.isIntersecting) {
                      animate(entry.target);
                      obs.unobserve(entry.target);
                    }
                  });
                }, options);
                io.observe(elem);
                observers.push(io);
              });
            })();
            </script>
        </div>

        <!-- Featured Companies -->
        <div class="text-center fade-up">
            <p class="text-lg opacity-80 mb-6">Trusted by Uganda's leading companies</p>
            <div class="flex flex-wrap justify-center items-center gap-8 opacity-70">
                <div class="bg-white bg-opacity-20 px-4 py-2 rounded-lg">
                    <span class="font-semibold">MTN Uganda</span>
                </div>
                <div class="bg-white bg-opacity-20 px-4 py-2 rounded-lg">
                    <span class="font-semibold">Stanbic Bank</span>
                </div>
                <div class="bg-white bg-opacity-20 px-4 py-2 rounded-lg">
                    <span class="font-semibold">Airtel Uganda</span>
                </div>
                <div class="bg-white bg-opacity-20 px-4 py-2 rounded-lg">
                    <span class="font-semibold">DFCU Bank</span>
                </div>
                <div class="bg-white bg-opacity-20 px-4 py-2 rounded-lg">
                    <span class="font-semibold">Kampala Capital</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="hero py-20 fade-in" role="banner" aria-label="Homepage Hero">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
            <div class="slide-left">
                <div class="card p-8" style="background:linear-gradient(90deg,#1e3a8a 0%,#2563eb 100%); color:#fff; border-radius:14px;">
                    <h1 class="text-4xl font-extrabold mb-4">Find Your Dream Job in Uganda</h1>
                    <p class="text-lg mb-6 opacity-90">Discover a wide range of curated job opportunities from Uganda's top employers — matched to your skills, experience, and ambitions. Create personalized job alerts, compare roles and companies, and apply with a single click to fast‑track your career growth.</p>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="<?php echo BASE_URL; ?>/register.php?type=job_seeker" class="btn btn-ghost" style="background:rgba(255,255,255,0.12); color:#fff; border:1px solid rgba(255,255,255,0.12)">I'm Looking for a Job</a>
                        <a href="<?php echo BASE_URL; ?>/register.php?type=employer" class="btn" style="background:#0b3b84;color:#fff">I'm Hiring Talent</a>
                    </div>
                </div>
            </div>

            <div class="slide-right">
                <div class="grid grid-cols-1 gap-4">
                    <?php if (!empty($featuredJobs)): ?>
                        <?php foreach ($featuredJobs as $index => $job): ?>
                            <article class="card p-4 flex items-start gap-4 fade-up" style="transition-delay: <?php echo $index * 0.1; ?>s" role="listitem" aria-label="<?php echo htmlspecialchars($job['title'] . ' at ' . $job['company_name']); ?>">
                                <?php if (!empty($job['logo'])): ?>
                                    <img src="<?php echo BASE_URL; ?>/Assets/uploads/logos/<?php echo htmlspecialchars($job['logo']); ?>" alt="<?php echo htmlspecialchars($job['company_name']); ?>" class="w-12 h-12 object-contain rounded-md">
                                <?php else: ?>
                                    <div class="w-12 h-12 bg-blue-100 rounded-md flex items-center justify-center text-blue-700 font-bold">
                                        <?php echo htmlspecialchars(substr($job['company_name'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>

                                <div class="flex-1">
                                    <h3 class="job-title"><?php echo htmlspecialchars($job['title']); ?></h3>
                                    <p class="job-company mt-1"><?php echo htmlspecialchars($job['company_name']); ?> • <span class="text-muted"><?php echo htmlspecialchars($job['location']); ?></span></p>
                                    <div class="job-meta mt-2">
                                        <span class="badge" style="background:#e6f0ff;color:#0b3b84"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $job['job_type']))); ?></span>
                                        <span class="text-sm text-muted">Posted <?php echo date('M j', strtotime($job['posted_at'])); ?></span>
                                    </div>
                                </div>

                                <div class="self-center">
                                    <a href="<?php echo BASE_URL; ?>/job-details.php?id=<?php echo urlencode($job['job_id']); ?>" class="text-blue-600 hover:text-blue-800 font-medium">View</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center text-gray-500">No featured jobs available at the moment.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="text-center mt-10 fade-up">
            <a href="<?php echo BASE_URL; ?>/jobs.php" class="btn btn-primary">Browse All Jobs</a>
        </div>
    </div>
</section>

<section class="py-12 bg-transparent" role="region" aria-labelledby="featured-jobs-heading">
    <div class="container mx-auto px-4">
        <h2 id="featured-jobs-heading" class="text-3xl font-bold text-center mb-12 text-blue-700 fade-up">Featured Jobs</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" role="list">
            <?php if (!empty($featuredJobs)): ?>
                <?php foreach ($featuredJobs as $index => $job): ?>
                    <article class="card p-6 scale-up job-card" style="transition-delay: <?php echo $index * 0.15; ?>s" role="listitem" aria-label="<?php echo htmlspecialchars($job['title'] . ' at ' . $job['company_name']); ?>">
                        <div class="flex items-center mb-4">
                            <?php if (!empty($job['logo'])): ?>
                                <img src="<?php echo BASE_URL; ?>/Assets/uploads/logos/<?php echo htmlspecialchars($job['logo']); ?>" alt="<?php echo htmlspecialchars($job['company_name']); ?>" class="w-12 h-12 object-contain mr-4 border border-blue-200 rounded-full bg-white">
                            <?php else: ?>
                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-blue-700 font-bold mr-4 border border-blue-200">
                                    <?php echo htmlspecialchars(substr($job['company_name'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            <div>
                                <h3 class="job-title"><?php echo htmlspecialchars($job['title']); ?></h3>
                                <p class="job-company"><?php echo htmlspecialchars($job['company_name']); ?></p>
                            </div>
                        </div>
                        <div class="mb-4">
                            <span class="badge" style="background:#e6f0ff;color:#0b3b84"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $job['job_type']))); ?></span>
                            <span class="badge" style="background:#f3f4f6;color:#374151"><?php echo htmlspecialchars($job['location']); ?></span>
                            <?php if ($job['salary_range']): ?>
                                <span class="badge" style="background:#dcfce7;color:#16a34a"><?php echo htmlspecialchars($job['salary_range']); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Always visible summary -->
                        <p class="text-gray-700 mb-4 line-clamp-2"><?php echo htmlspecialchars(substr($job['description'], 0, 100)) . '...'; ?></p>
                        
                        <!-- Expanded details on hover -->
                        <div class="job-details">
                            <div class="border-t border-gray-100 pt-4">
                                <h4 class="font-semibold text-gray-900 mb-2">Full Description</h4>
                                <p class="text-gray-700 text-sm mb-3"><?php echo htmlspecialchars($job['description']); ?></p>
                                
                                <?php if (!empty($job['requirements'])): ?>
                                    <h4 class="font-semibold text-gray-900 mb-2">Requirements</h4>
                                    <p class="text-gray-700 text-sm mb-3"><?php echo htmlspecialchars(substr($job['requirements'], 0, 150)) . '...'; ?></p>
                                <?php endif; ?>
                                
                                <div class="flex flex-wrap gap-2 mb-3">
                                    <?php if (!empty($job['experience_level'])): ?>
                                        <span class="badge" style="background:#fef3c7;color:#92400e">Experience: <?php echo htmlspecialchars($job['experience_level']); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($job['deadline'])): ?>
                                        <span class="badge" style="background:#fce7f3;color:#be185d">Deadline: <?php echo date('M j, Y', strtotime($job['deadline'])); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">Posted <?php echo date('M j, Y', strtotime($job['posted_at'])); ?></span>
                            
                            <!-- Default view details button -->
                            <a href="<?php echo BASE_URL; ?>/job-details.php?id=<?php echo urlencode($job['job_id']); ?>" class="text-blue-600 hover:text-blue-800 font-medium view-details-btn">View Details</a>
                            
                            <!-- Apply button (shows on hover) -->
                            <div class="apply-btn">
                                <?php if (isLoggedIn() && isJobSeeker()): ?>
                                    <a href="<?php echo BASE_URL; ?>/job-details.php?id=<?php echo urlencode($job['job_id']); ?>#apply" class="bg-blue-600 text-white px-4 py-2 rounded-md font-medium hover:bg-blue-700 transition duration-200">Apply Now</a>
                                <?php else: ?>
                                    <a href="<?php echo BASE_URL; ?>/login.php?redirect=<?php echo urlencode(BASE_URL . '/job-details.php?id=' . $job['job_id']); ?>" class="bg-blue-600 text-white px-4 py-2 rounded-md font-medium hover:bg-blue-700 transition duration-200">Apply Now</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center text-gray-500">No featured jobs available at the moment.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="py-12 bg-transparent" role="region" aria-labelledby="how-it-works-heading">
    <div class="container mx-auto px-4">
        <h2 id="how-it-works-heading" class="text-3xl font-bold text-center mb-12 text-blue-700 fade-up">How It Works</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8" role="list">
            <div class="card p-6 text-center fade-up" style="transition-delay: 0.1s" role="listitem" aria-label="Create Your Profile">
                <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-2 text-blue-700">Create Your Profile</h3>
                <p class="text-gray-600">Register as a job seeker and build your professional profile to showcase your skills and experience.</p>
            </div>

            <div class="card p-6 text-center fade-up" style="transition-delay: 0.2s" role="listitem" aria-label="Find Jobs">
                <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-2 text-blue-700">Find Jobs</h3>
                <p class="text-gray-600">Search and apply for jobs that match your qualifications and career goals.</p>
            </div>

            <div class="card p-6 text-center fade-up" style="transition-delay: 0.3s" role="listitem" aria-label="Get Hired">
                <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-2 text-blue-700">Get Hired</h3>
                <p class="text-gray-600">Connect with employers and land your dream job in Uganda's growing job market.</p>
            </div>
        </div>
    </div>
</section>

<?php require_once 'Includes/footer.php'; ?>

</div>