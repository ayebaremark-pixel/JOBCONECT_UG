<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JobConnectUganda - <?php echo $pageTitle ?? 'Find Your Dream Job'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/Assets/CSS/styles.css">
<script src="<?php echo BASE_URL; ?>/Assets/Javascript/main.js" defer></script>
</head>
<body class="bg-gray-100">
    <header class="bg-white border-b border-gray-100">
  <div class="container mx-auto px-4">
    <div class="flex items-center justify-between py-4">
      <div class="flex items-center gap-4">
        <a href="<?php echo BASE_URL; ?>/index.php" class="flex items-center gap-3">
          <svg class="w-10 h-10 text-blue-700" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" role="img" aria-label="JobConnectUganda">
            <path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M9 6V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v1m4 4H5m14 0v8a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-8m14 0H5" />
          </svg>
          <span class="text-xl font-bold text-blue-700">JobConnectUganda</span>
        </a>
      </div>

      <div class="flex-1 mx-6 hidden md:block">
        <form action="<?php echo BASE_URL; ?>/jobs.php" method="GET" class="w-full">
          <div class="flex items-center bg-gray-50 border border-gray-200 rounded-lg overflow-hidden">
            <input name="search" aria-label="Search jobs" type="search" placeholder="Search jobs, companies, keywords..." class="w-full px-4 py-2 text-sm bg-transparent focus:outline-none" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
            <input name="location" type="text" placeholder="Location" class="w-48 px-3 py-2 text-sm border-l border-gray-200 bg-transparent focus:outline-none" value="<?php echo htmlspecialchars($_GET['location'] ?? ''); ?>">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 text-sm font-medium">Search</button>
          </div>
        </form>
      </div>

      <div class="flex items-center gap-4">
        <?php if (isLoggedIn()): ?>
          <!-- <a href="<?php echo BASE_URL; ?>/dashboard.php" class="hidden sm:inline-block text-sm text-gray-700 hover:text-blue-700">Dashboard</a> -->

          <div class="hidden sm:block">
            <a href="<?php echo BASE_URL; ?>/jobs.php" class="text-sm text-gray-700 hover:text-blue-700">Jobs</a>
          </div>

          <div class="relative">
            <button class="flex items-center gap-2 text-sm bg-white border border-gray-200 px-3 py-1 rounded-lg hover:shadow-sm" id="userMenuToggle">
              <span class="text-gray-700"><?php echo htmlspecialchars($_SESSION['first_name'] ?? ($_SESSION['email'] ?? 'User')); ?></span>
              <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div id="userMenu" class="hidden absolute right-0 mt-2 w-44 bg-white border border-gray-100 rounded-md shadow-lg py-2 z-50">
              <?php if (isJobSeeker()): ?>
                <!-- <a href="<?php echo BASE_URL; ?>/job-seeker/dashboard.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">My Profile</a> -->
                <a href="<?php echo BASE_URL; ?>/profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Profile & Stats</a>
                <!-- <a href="<?php echo BASE_URL; ?>/saved.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Saved Jobs</a> -->
              <?php elseif (isEmployer()): ?>
                <a href="<?php echo BASE_URL; ?>/Employer/dashboard.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Employer Dashboard</a>
                <a href="<?php echo BASE_URL; ?>/Employer/jobs.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">My Postings</a>
              <?php endif; ?>
              <a href="<?php echo BASE_URL; ?>/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-50">Logout</a>
            </div>
          </div>

        <?php else: ?>
          <a href="#" onclick="openModal('registerModal')" class="text-sm text-gray-700 hover:text-blue-700">Register</a>
          <a href="#" onclick="openModal('loginModal')" class="bg-blue-600 text-white px-3 py-2 rounded-lg text-sm font-medium hover:bg-blue-700">Login</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</header>

<!-- Login Modal -->
<div id="loginModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-all">
    <div class="flex items-center justify-between p-6 border-b">
      <h2 class="text-2xl font-bold text-gray-800">Login to Your Account</h2>
      <button onclick="closeModal('loginModal')" class="text-gray-400 hover:text-gray-600">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    
    <form id="loginForm" class="p-6">
      <div class="mb-4">
        <label for="login_email" class="block text-gray-700 text-sm font-medium mb-2">Email Address</label>
        <input type="email" id="login_email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      
      <div class="mb-6">
        <label for="login_password" class="block text-gray-700 text-sm font-medium mb-2">Password</label>
        <input type="password" id="login_password" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        <div class="text-right mt-2">
          <a href="#" class="text-sm text-blue-600 hover:underline">Forgot password?</a>
        </div>
      </div>
      
      <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
        Login
      </button>
      
      <div class="mt-4 text-center">
        <p class="text-gray-600">Don't have an account? <a href="#" onclick="switchModal('loginModal', 'registerModal')" class="text-blue-600 hover:underline">Register here</a></p>
      </div>
    </form>
  </div>
</div>

<!-- Register Modal -->
<div id="registerModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 transform transition-all max-h-screen overflow-y-auto">
    <div class="flex items-center justify-between p-6 border-b">
      <h2 class="text-2xl font-bold text-gray-800">Create Your Account</h2>
      <button onclick="closeModal('registerModal')" class="text-gray-400 hover:text-gray-600">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    
    <div class="p-6">
      <div class="flex border-b mb-6">
        <button type="button" onclick="switchUserType('job_seeker')" id="jobSeekerTab" class="flex-1 text-center py-2 border-b-2 border-blue-600 text-blue-600 font-medium">
          I'm a Job Seeker
        </button>
        <button type="button" onclick="switchUserType('employer')" id="employerTab" class="flex-1 text-center py-2 text-gray-600">
          I'm an Employer
        </button>
      </div>
      
      <form id="registerForm">
        <input type="hidden" name="user_type" id="user_type" value="job_seeker">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
          <div>
            <label for="reg_first_name" class="block text-gray-700 text-sm font-medium mb-2">First Name*</label>
            <input type="text" id="reg_first_name" name="first_name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          
          <div>
            <label for="reg_last_name" class="block text-gray-700 text-sm font-medium mb-2">Last Name*</label>
            <input type="text" id="reg_last_name" name="last_name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
        </div>
        
        <div class="mb-4">
          <label for="reg_email" class="block text-gray-700 text-sm font-medium mb-2">Email Address*</label>
          <input type="email" id="reg_email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
          <div>
            <label for="reg_password" class="block text-gray-700 text-sm font-medium mb-2">Password*</label>
            <input type="password" id="reg_password" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          
          <div>
            <label for="reg_confirm_password" class="block text-gray-700 text-sm font-medium mb-2">Confirm Password*</label>
            <input type="password" id="reg_confirm_password" name="confirm_password" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
        </div>
        
        <div class="mb-4">
          <label for="reg_phone" class="block text-gray-700 text-sm font-medium mb-2">Phone Number</label>
          <input type="tel" id="reg_phone" name="phone" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        
        <div id="employerFields" class="hidden mb-4">
          <label for="reg_company_name" class="block text-gray-700 text-sm font-medium mb-2">Company Name*</label>
          <input type="text" id="reg_company_name" name="company_name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        
        <div class="mb-6">
          <label for="reg_location" class="block text-gray-700 text-sm font-medium mb-2">Location</label>
          <input type="text" id="reg_location" name="location" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        
        <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
          Register
        </button>
        
        <div class="mt-4 text-center">
          <p class="text-gray-600">Already have an account? <a href="#" onclick="switchModal('registerModal', 'loginModal')" class="text-blue-600 hover:underline">Login here</a></p>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Modal functionality
function openModal(modalId) {
  document.getElementById(modalId).classList.remove('hidden');
  document.getElementById(modalId).classList.add('flex');
  document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
  document.getElementById(modalId).classList.add('hidden');
  document.getElementById(modalId).classList.remove('flex');
  document.body.style.overflow = 'auto';
}

function switchModal(fromModalId, toModalId) {
  closeModal(fromModalId);
  openModal(toModalId);
}

function switchUserType(type) {
  document.getElementById('user_type').value = type;
  
  // Update tabs
  if (type === 'job_seeker') {
    document.getElementById('jobSeekerTab').className = 'flex-1 text-center py-2 border-b-2 border-blue-600 text-blue-600 font-medium';
    document.getElementById('employerTab').className = 'flex-1 text-center py-2 text-gray-600';
    document.getElementById('employerFields').classList.add('hidden');
    document.getElementById('reg_company_name').required = false;
  } else {
    document.getElementById('employerTab').className = 'flex-1 text-center py-2 border-b-2 border-blue-600 text-blue-600 font-medium';
    document.getElementById('jobSeekerTab').className = 'flex-1 text-center py-2 text-gray-600';
    document.getElementById('employerFields').classList.remove('hidden');
    document.getElementById('reg_company_name').required = true;
  }
}

// Close modals when clicking outside
window.onclick = function(event) {
  const loginModal = document.getElementById('loginModal');
  const registerModal = document.getElementById('registerModal');
  
  if (event.target === loginModal) {
    closeModal('loginModal');
  }
  if (event.target === registerModal) {
    closeModal('registerModal');
  }
}

// Handle login form submission
document.getElementById('loginForm').addEventListener('submit', function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  
  fetch('<?php echo BASE_URL; ?>/ajax/login.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      location.reload(); // Refresh page to update header
    } else {
      // Show error message
      alert(data.message || 'Login failed. Please try again.');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('An error occurred. Please try again.');
  });
});

// Handle register form submission
document.getElementById('registerForm').addEventListener('submit', function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  
  fetch('<?php echo BASE_URL; ?>/ajax/register.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      location.reload(); // Refresh page to update header
    } else {
      // Show error messages
      if (data.errors) {
        let errorMsg = 'Please fix the following errors:\n';
        for (let field in data.errors) {
          errorMsg += '- ' + data.errors[field] + '\n';
        }
        alert(errorMsg);
      } else {
        alert(data.message || 'Registration failed. Please try again.');
      }
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('An error occurred. Please try again.');
  });
});
</script>

<script>
  // User menu toggle functionality
  document.addEventListener('click', function(e){
    var toggle = document.getElementById('userMenuToggle');
    var menu = document.getElementById('userMenu');
    if (!toggle || !menu) return;
    if (toggle.contains(e.target)) {
      menu.classList.toggle('hidden');
    } else if (!menu.contains(e.target)) {
      menu.classList.add('hidden');
    }
  });
</script>

    <main class="container mx-auto px-4 py-8">
        <?php displayMessage(); ?>