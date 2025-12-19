<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BroMan Social</title>
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
                        brand: { 50: '#f0f9ff', 100: '#e0f2fe', 500: '#0ea5e9', 600: '#0284c7', 700: '#0369a1' }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
    
    <div class="bg-white shadow-sm rounded-2xl p-8 w-full max-w-md border border-slate-200">
        <div class="text-center mb-8">
            <img src="images/Final_Logo.png" alt="BroMan Social" class="h-14 mx-auto mb-4">
            <h1 class="text-xl font-semibold text-slate-800">Welcome back</h1>
            <p class="text-slate-500 mt-1 text-sm">Sign in to continue</p>
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
            
            <button 
                type="submit" 
                id="loginBtn"
                class="w-full bg-brand-500 text-white font-medium py-2.5 rounded-lg hover:bg-brand-600 transition duration-150 mt-2"
            >
                Sign In
            </button>
        </form>
        

    </div>

    <script>
        // Check if already logged in
        fetch('api.php?action=get_user')
            .then(res => res.json())
            .then(data => {
                if (data.success) window.location.href = 'index.php';
            });

        // Handle login
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const errorMessage = document.getElementById('errorMessage');
            const loginBtn = document.getElementById('loginBtn');
            
            errorMessage.classList.add('hidden');
            loginBtn.disabled = true;
            loginBtn.textContent = 'Signing in...';
            
            try {
                const response = await fetch('api.php?action=login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username, password })
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
    </script>
</body>
</html>
