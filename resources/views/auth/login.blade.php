@extends('layouts.app')

@section('content')
<div class="auth-page">
    <div class="auth-card">
        <h1><i class="fas fa-calendar-alt"></i> PlanSync</h1>

        @if($errors->any())
            <div class="alert alert-error">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" 
                       value="{{ old('email') }}" required placeholder="you@example.com">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" 
                       required placeholder="••••••">
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="remember"> Remember me
                </label>
            </div>
            
            <button type="submit" class="btn" style="width: 100%;">
                Log In
            </button>
            
            <div style="margin-top: 1rem; text-align: center;">
                <a href="{{ route('register') }}" style="color: var(--primary); text-decoration: underline;">
                    No account? <strong>Register</strong>
                </a>
            </div>

            <div class="mt-4 text-center">
    <p class="text-muted">Administrator access</p>
    <a href="{{ route('admin.login') }}" class="btn btn-outline-primary btn-sm">
        <i class="fas fa-shield-alt"></i> Admin Login
    </a>
</div>
        </form>
    </div>
</div>
@endsection