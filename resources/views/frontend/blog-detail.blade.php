@extends('frontend.layouts.index')
@section('title', 'Blog Detail')
@section('content')
    <div class="sub-banner">
    </div>
    <section class="refunds-policy-section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="subheading">
                        <h3 class="text-center">{{ $blog->title }}</h3>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="content-description">
                    {!! $blog->desc !!}
                </div>
            </div>
    </section>
@endsection
