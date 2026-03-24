@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div id="app">
    <login></login>
</div>
@endsection

@push('scripts')
    @vite(['resources/js/app.js'])
@endpush