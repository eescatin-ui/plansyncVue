<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PlanSync - Academic Organizer')</title>
    <!-- Add CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Vite CSS -->
    @vite(['resources/css/app.css'])
    
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3a0ca3;
            --accent: #7209b7;
            --success: #4cc9f0;
            --warning: #f72585;
            --danger: #e63946;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --due-today: #ff0000;
            --due-tomorrow: #ffa500;
            --sidebar-width: 250px;
            --header-height: 70px;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background-color: var(--light);
            color: var(--dark);
            line-height: 1.6;
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 1.5rem 0;
            height: 100vh;
            position: fixed;
            overflow-y: auto;
        }
        .app-title {
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0 1.5rem;
            margin-bottom: 2rem;
            color: white;
        }
        .sidebar-menu {
            list-style: none;
        }
        .sidebar-menu li {
            margin-bottom: 0.5rem;
        }
        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0.8rem 1.5rem;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 4px solid white;
        }
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
        }
        .header {
            height: var(--header-height);
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .search-box {
            display: flex;
            align-items: center;
            background-color: var(--light-gray);
            border-radius: 20px;
            padding: 0.5rem 1rem;
            width: 300px;
        }
        .search-box input {
            background: transparent;
            border: none;
            outline: none;
            width: 100%;
            margin-left: 0.5rem;
            color: var(--dark);
        }
        .user-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        .content {
            padding: 2rem;
            min-height: calc(100vh - var(--header-height));
        }
        .btn {
            padding: 0.6rem 1.2rem;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
        }
        .btn:hover {
            background-color: var(--secondary);
            color: white;
        }
        .btn-danger {
            background-color: var(--danger);
        }
        .btn-danger:hover {
            background-color: #d32f2f;
        }
        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .module-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .module-title {
            font-size: 1.8rem;
            color: #4361ee;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        @media (max-width: 900px) {
            .sidebar {
                width: 70px;
            }
            .sidebar .app-title span,
            .sidebar-menu span {
                display: none;
            }
            .main-content {
                margin-left: 70px;
                width: calc(100% - 70px);
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    @include('layouts.partials.sidebar')
    
    <div class="main-content">
        @include('layouts.partials.header')
        
        <div class="content">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-error">
                    {{ session('error') }}
                </div>
            @endif
            
            <!-- This is where page content goes - Vue will mount here -->
            @yield('content')
        </div>
    </div>
    
    @stack('modals')
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Vite JS -->
    @vite(['resources/js/app.js'])
    
    @stack('scripts')
</body>
</html>