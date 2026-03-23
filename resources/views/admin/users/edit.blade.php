@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')
<div class="users">
    <div class="module-header">
        <h2 class="module-title"><i class="fas fa-user-edit"></i> Edit User</h2>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Users
        </a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Full Name *</label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $user->name) }}" 
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', $user->email) }}" 
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password">New Password (leave blank to keep current)</label>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password_confirmation">Confirm New Password</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password_confirmation" 
                                   name="password_confirmation">
                        </div>
                    </div>
                </div>
                
                <div class="form-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update User
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .card {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        margin-top: 20px;
    }
    
    .card-body {
        padding: 30px;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #495057;
    }
    
    .form-control {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ced4da;
        border-radius: 4px;
        font-size: 1rem;
        transition: border-color 0.15s ease-in-out;
    }
    
    .form-control:focus {
        border-color: #4361ee;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
    }
    
    .is-invalid {
        border-color: #dc3545;
    }
    
    .invalid-feedback {
        display: block;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875rem;
        color: #dc3545;
    }
    
    .form-footer {
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e9ecef;
        display: flex;
        gap: 10px;
    }
    
    .row {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -15px;
    }
    
    .col-md-6 {
        flex: 0 0 50%;
        max-width: 50%;
        padding: 0 15px;
    }
</style>
@endsection