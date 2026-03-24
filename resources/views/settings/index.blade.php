@extends('layouts.main')

@section('title', 'Settings')

@section('content')
<div id="app">
    <settings-manager 
        :initial-user='@json($user)'
        :initial-preferences='@json($preferences)'
    ></settings-manager>
</div>
@endsection

@push('scripts')
    @vite(['resources/js/app.js'])
@endpush