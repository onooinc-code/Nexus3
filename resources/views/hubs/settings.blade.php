@extends('layouts.app')

@section('page_title', 'SettingsHub')

@include('hubs.settings.styles')

@section('content')
    @include('hubs.settings.header')

    <div class="row g-4 animate-fade-in stagger-2 align-items-stretch">
        @include('hubs.settings.sidebar')
        
        @include('hubs.settings.tab-content')
    </div>

    @include('hubs.settings.action-panels')
@endsection

@include('hubs.settings.scripts')