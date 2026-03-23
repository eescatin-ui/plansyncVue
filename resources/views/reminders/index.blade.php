@extends('layouts.main')

@section('title', 'Reminders')

@section('content')
<div id="app">
    <reminder-manager 
        :initial-reminders='@json($allReminders)'
    ></reminder-manager>
</div>
@endsection