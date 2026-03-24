@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div id="app">
    <register></register>
</div>
@endsection

@push('scripts')
    @vite(['resources/js/app.js'])
@endpush