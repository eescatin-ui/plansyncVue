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

        <form method="POST" action="{{ route('register') }}">
            @csrf
            
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" 
                       value="{{ old('name') }}" required placeholder="John Smith">
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" 
                       value="{{ old('email') }}" required placeholder="you@example.com">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" 
                       required placeholder="min 6 chars">
            </div>
            
            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" class="form-control" id="password_confirmation" 
                       name="password_confirmation" required placeholder="Confirm password">
            </div>
            
            <button type="submit" class="btn" style="width: 100%;">
                Create Account
            </button>
            
            <div style="margin-top: 1rem; text-align: center;">
                <a href="{{ route('login') }}" style="color: var(--primary); text-decoration: underline;">
                    Already have an account? <strong>Log In</strong>
                </a>
            </div>
        </form>
    </div>
</div>
@endsection