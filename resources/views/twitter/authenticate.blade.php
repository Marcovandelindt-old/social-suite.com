@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-body">
                        <h3>Authenticate with Twitter</h3>
                        <p>Hey {{ Auth::user()->name }}, it appears you're not authenticated to Twitter yet. In order to use these functionalities, it is required to authenticate yourself first. Please click the button below to start the
                            authentication!</p>
                        <form method="POST" action="{{ route('twitter.authenticate.post') }}">
                            @csrf
                            <input type="submit" class="btn btn-primary" value="Sign in!"/>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection