<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PlanSync - Academic Organizer')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        }
        .auth-page {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .auth-card {
            background: white;
            padding: 2.5rem;
            border-radius: 12px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 8px 30px rgba(0,0,0,.2);
        }
        .auth-card h1 {
            margin-bottom: 1.5rem;
            color: var(--primary);
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--light-gray);
            border-radius: 6px;
            font-size: 1rem;
            transition: all 0.2s;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }
        .btn {
            padding: 0.75rem 1.2rem;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            width: 100%;
        }
        .btn:hover {
            background-color: var(--secondary);
            transform: translateY(-2px);
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
        .auth-links {
            margin-top: 1rem;
            text-align: center;
        }
        .auth-links a {
            color: var(--primary);
            text-decoration: none;
        }
        .auth-links a:hover {
            text-decoration: underline;
        }
        .admin-section {
            margin-top: 1.5rem;
            text-align: center;
            padding-top: 1rem;
            border-top: 1px solid var(--light-gray);
        }
        .text-muted {
            color: var(--gray);
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }
        .btn-outline-primary {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
            width: auto;
            display: inline-flex;
        }
        .btn-outline-primary:hover {
            background: var(--primary);
            color: white;
        }
        .btn-sm {
            padding: 0.4rem 1rem;
            font-size: 0.85rem;
        }
        .error-message {
            color: var(--danger);
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }
        .password-wrapper {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--gray);
        }
        .password-toggle:hover {
            color: var(--primary);
        }
    </style>
    @stack('styles')
</head>
<body>
    @yield('content')
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @vite(['resources/js/app.js'])
    @stack('scripts')
</body>
</html>