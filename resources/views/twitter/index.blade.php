@extends('layouts.app')

@section('content')

    @if (Auth::user()->twitter_authenticated == 1)
        @include('twitter.partials.tabs')
    @endif

    <div class="container">
        <div class="twitter-intro-text" style="background-color: #fff; border-radius: 5px; margin-top: 50px;padding: 20px;">
            <h1>Welcome to your twitter page, {{ Auth::user()->name }}</h1>
            <p>How are you doing today? Ready to schedule some tweets, view some insights on your recent activity or maybe start on that bot you have been talking about? Get started by one of the buttons above.</p>
        </div>
    </div>



@endsection