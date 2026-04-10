<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PlanSync - Academic Organizer</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body>
    <div id="app">
        <router-view></router-view>
    </div>
    
    <script>
        // Set CSRF token for Axios
        window.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // =============================================
        // USER AUTHENTICATION DATA
        // =============================================
        @auth
            window.user = {
                id: {{ auth()->id() }},
                name: "{{ auth()->user()->name }}",
                email: "{{ auth()->user()->email }}",
                avatarColor: "{{ auth()->user()->avatar_color ?? '#4361ee' }}",
                isAuthenticated: true
            };
            localStorage.setItem('auth_token', 'authenticated');
            localStorage.setItem('user', JSON.stringify(window.user));
        @else
            window.user = {
                isAuthenticated: false
            };
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
        @endauth
        
        // =============================================
        // ADMIN AUTHENTICATION DATA
        // =============================================
        @auth('admin')
            window.admin = {
                id: {{ auth('admin')->id() }},
                name: "{{ auth('admin')->user()->name }}",
                email: "{{ auth('admin')->user()->email }}",
                isSuperAdmin: {{ auth('admin')->user()->is_super_admin ? 'true' : 'false' }},
                isAuthenticated: true
            };
            localStorage.setItem('admin_token', 'authenticated');
            localStorage.setItem('admin_user', JSON.stringify(window.admin));
        @else
            window.admin = {
                isAuthenticated: false
            };
            // Don't remove admin_token if it exists from API login
            // localStorage.removeItem('admin_token');
        @endauth
        
        // =============================================
        // APPLICATION SETTINGS
        // =============================================
        
        // Apply saved theme
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.body.classList.add('dark-mode');
        }
        
        // Set default axios headers
        if (window.axios) {
            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = window.csrfToken;
            window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
            window.axios.defaults.withCredentials = true;
        }
        
        // Log authentication status (for debugging)
        console.log('App loaded:', {
            userAuthenticated: window.user?.isAuthenticated || false,
            adminAuthenticated: window.admin?.isAuthenticated || false,
            csrfToken: window.csrfToken ? 'present' : 'missing'
        });
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @vite(['resources/js/app.js'])
</body>
</html>