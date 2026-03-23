@extends('layouts.main')

@section('title', 'Edit Class')

@section('content')
<div class="schedule">
    <div class="module-header">
        <h2 class="module-title"><i class="fas fa-calendar-week"></i> Edit Class</h2>
        <a href="{{ route('schedule.index') }}" class="btn">
            <i class="fas fa-arrow-left"></i> Back to Schedule
        </a>
    </div>
    
    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <div class="card-header">
            <h3>Edit Class</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('schedule.update', $class) }}">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label for="name">Class Name</label>
                    <input type="text" class="form-control" id="name" name="name" 
                           value="{{ old('name', $class->name) }}" required>
                </div>
                
                <div class="form-group">
                    <label for="time">Time</label>
                    <input type="text" class="form-control" id="time" name="time" 
                           value="{{ old('time', $class->time) }}" required>
                </div>
                
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" class="form-control" id="location" name="location" 
                           value="{{ old('location', $class->location) }}" required>
                </div>
                
                <div class="form-group">
                    <label for="day">Day</label>
                    <select class="form-control" id="day" name="day" required>
                        <option value="Monday" {{ $class->day == 'Monday' ? 'selected' : '' }}>Monday</option>
                        <option value="Tuesday" {{ $class->day == 'Tuesday' ? 'selected' : '' }}>Tuesday</option>
                        <option value="Wednesday" {{ $class->day == 'Wednesday' ? 'selected' : '' }}>Wednesday</option>
                        <option value="Thursday" {{ $class->day == 'Thursday' ? 'selected' : '' }}>Thursday</option>
                        <option value="Friday" {{ $class->day == 'Friday' ? 'selected' : '' }}>Friday</option>
                        <option value="Saturday" {{ $class->day == 'Saturday' ? 'selected' : '' }}>Saturday</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i> Update Class
                    </button>
                    
                    <button type="button" class="btn btn-danger" 
                            onclick="if(confirm('Are you sure?')) { document.getElementById('deleteForm').submit(); }">
                        <i class="fas fa-trash"></i> Delete Class
                    </button>
                    
                    <a href="{{ route('schedule.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
            
            <form id="deleteForm" action="{{ route('schedule.destroy', $class) }}" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>

<style>
    .card {
        background: white;
        border-radius: 10px;
        padding: 2rem;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .card-header {
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--light-gray);
    }
    .form-group {
        margin-bottom: 1.5rem;
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
    }
    .form-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: 2rem;
        padding-top: 1rem;
        border-top: 1px solid var(--light-gray);
    }
</style>
@endsection