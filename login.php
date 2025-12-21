<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Social Management</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 50: '#f0f9ff', 100: '#e0f2fe', 500: '#0ea5e9', 600: '#0284c7', 700: '#0369a1' },
                        navy: { 900: '#0f172a', 800: '#1e293b' }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }
        .company-card {
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .company-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .company-card.selected {
            ring: 3px;
            ring-color: #0ea5e9;
            border-color: #0ea5e9;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-100 to-slate-200 min-h-screen flex items-center justify-center p-4">
    
    <div class="bg-white shadow-xl rounded-2xl p-8 w-full max-w-lg border border-slate-200">
        <!-- Step 1: Company Selection -->
        <div id="companyStep">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-slate-800">Choose Your Workspace</h1>
                <p class="text-slate-500 mt-1">Select the company you want to work with</p>
            </div>
            
            <div id="companiesGrid" class="grid grid-cols-2 gap-4 mb-6">
                <!-- Companies loaded dynamically -->
                <div class="company-card bg-slate-50 rounded-xl p-6 text-center animate-pulse">
                    <div class="h-12 bg-slate-200 rounded mb-3"></div>
                    <div class="h-4 bg-slate-200 rounded w-2/3 mx-auto"></div>
                </div>
                <div class="company-card bg-slate-50 rounded-xl p-6 text-center animate-pulse">
                    <div class="h-12 bg-slate-200 rounded mb-3"></div>
                    <div class="h-4 bg-slate-200 rounded w-2/3 mx-auto"></div>
                </div>
            </div>
            
            <button 
                id="continueBtn" 
                disabled
                class="w-full bg-slate-300 text-slate-500 font-medium py-3 rounded-xl cursor-not-allowed transition"
            >
                Select a company to continue
            </button>
        </div>
        
        <!-- Step 2: Login Form (hidden initially) -->
        <div id="loginStep" class="hidden">
            <div class="text-center mb-6">
                <img id="selectedCompanyLogo" src="" alt="" class="h-12 mx-auto mb-3">
                <h1 class="text-xl font-semibold text-slate-800">Sign in to <span id="selectedCompanyName"></span></h1>
                <button onclick="goBackToCompanies()" class="text-brand-500 text-sm hover:underline mt-1">← Choose different company</button>
            </div>
            
            <form id="loginForm" class="space-y-4">
                <div id="errorMessage" class="hidden bg-red-50 border border-red-100 text-red-600 px-4 py-3 rounded-lg text-sm"></div>
                
                <div>
                    <label for="username" class="block text-sm font-medium text-slate-700 mb-1.5">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        required
                        class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent outline-none transition"
                        placeholder="Enter your username"
                    >
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        class="w-full px-4 py-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent outline-none transition"
                        placeholder="Enter your password"
                    >
                </div>
                
                <input type="hidden" id="companyId" name="company_id" value="">
                
                <button 
                    type="submit" 
                    id="loginBtn"
                    class="w-full bg-brand-500 text-white font-medium py-3 rounded-xl hover:bg-brand-600 transition duration-150"
                >
                    Sign In
                </button>
            </form>
        </div>
    </div>

    <script>
        let selectedCompany = null;
        let companies = [];
        
        // Check if already logged in
        fetch('api.php?action=get_user')
            .then(res => res.json())
            .then(data => {
                if (data.success) window.location.href = 'index.php';
            });
        
        // Load companies
        async function loadCompanies() {
            try {
                const res = await fetch('api.php?action=get_companies');
                const data = await res.json();
                
                if (data.success && data.data.length > 0) {
                    companies = data.data;
                    renderCompanies();
                } else {
                    // Fallback if companies not set up yet
                    companies = [
                        { id: 1, name: 'BroMan', logo_url: 'images/Final_Logo.png', primary_color: '#1e3a5f' },
                        { id: 2, name: 'Cible', logo_url: 'images/Logo_Cible.png', primary_color: '#2563eb' }
                    ];
                    renderCompanies();
                }
            } catch (e) {
                console.error('Failed to load companies:', e);
            }
        }
        
        function renderCompanies() {
            const grid = document.getElementById('companiesGrid');
            grid.innerHTML = companies.map(c => `
                <div class="company-card bg-slate-50 border-2 border-slate-200 rounded-xl p-6 text-center" 
                     data-id="${c.id}" 
                     data-name="${c.name}" 
                     data-logo="${c.logo_url}"
                     onclick="selectCompany(${c.id})">
                    <img src="${c.logo_url}" alt="${c.name}" class="h-12 mx-auto mb-3 object-contain" onerror="this.src='images/placeholder.png'">
                    <h3 class="font-semibold text-slate-800">${c.name}</h3>
                </div>
            `).join('');
        }
        
        function selectCompany(id) {
            selectedCompany = companies.find(c => c.id === id);
            
            // Update UI
            document.querySelectorAll('.company-card').forEach(card => {
                card.classList.remove('selected', 'border-brand-500', 'bg-brand-50');
                card.classList.add('border-slate-200');
            });
            
            const selectedCard = document.querySelector(`.company-card[data-id="${id}"]`);
            selectedCard.classList.add('selected', 'border-brand-500', 'bg-brand-50');
            selectedCard.classList.remove('border-slate-200');
            
            // Enable continue button
            const btn = document.getElementById('continueBtn');
            btn.disabled = false;
            btn.className = 'w-full bg-brand-500 text-white font-medium py-3 rounded-xl hover:bg-brand-600 transition cursor-pointer';
            btn.textContent = `Continue with ${selectedCompany.name}`;
        }
        
        document.getElementById('continueBtn').addEventListener('click', () => {
            if (!selectedCompany) return;
            
            // Show login step
            document.getElementById('companyStep').classList.add('hidden');
            document.getElementById('loginStep').classList.remove('hidden');
            
            // Update branding
            document.getElementById('selectedCompanyLogo').src = selectedCompany.logo_url;
            document.getElementById('selectedCompanyName').textContent = selectedCompany.name;
            document.getElementById('companyId').value = selectedCompany.id;
            
            // Focus username
            document.getElementById('username').focus();
        });
        
        function goBackToCompanies() {
            document.getElementById('loginStep').classList.add('hidden');
            document.getElementById('companyStep').classList.remove('hidden');
            document.getElementById('loginForm').reset();
            document.getElementById('errorMessage').classList.add('hidden');
        }

        // Handle login
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const companyId = document.getElementById('companyId').value;
            const errorMessage = document.getElementById('errorMessage');
            const loginBtn = document.getElementById('loginBtn');
            
            errorMessage.classList.add('hidden');
            loginBtn.disabled = true;
            loginBtn.textContent = 'Signing in...';
            
            try {
                const response = await fetch('api.php?action=login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username, password, company_id: parseInt(companyId) })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    window.location.href = 'index.php';
                } else {
                    errorMessage.textContent = data.message || 'Login failed';
                    errorMessage.classList.remove('hidden');
                    loginBtn.disabled = false;
                    loginBtn.textContent = 'Sign In';
                }
            } catch (error) {
                errorMessage.textContent = 'Connection error. Please try again.';
                errorMessage.classList.remove('hidden');
                loginBtn.disabled = false;
                loginBtn.textContent = 'Sign In';
            }
        });
        
        // Initialize
        loadCompanies();
    </script>
</body>
</html>
