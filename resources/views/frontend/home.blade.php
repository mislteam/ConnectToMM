@extends('frontend.layouts.index')
@section('title', 'Connect To Myanmar')
@section('content')
    @include('components.alert')
    <!--Banner-->
    <x-frontend.simple-section :section="$simple_section" />
    <!--What-we-do-->
    <x-frontend.our-action :section="$action_section" />
    <!--About Repay-->
    <x-frontend.about-repay :section="$about_repay" />
    <!--Services section-->
    <x-frontend.our-service :section="$service_section" />
    <!-- manage -->
    <x-frontend.manage-section :section="$manage_section" />
    <!-- need more help -->
    <x-frontend.section-item :section="$help_section" />
@endsection
