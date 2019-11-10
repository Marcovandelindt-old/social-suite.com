@extends ('layouts.app')

@section ('content')
    <div class="container">
        <form method="POST" action="{{ route('twitter.schedule.post') }}">
            @csrf

            <div class="form-text">
                What's on your mind today, {{ Auth::user()->name }} ?
            </div>
            <br/>
            <div class="form-group">
                <textarea name="status" class="form-control" placeholder="Schedule your tweet!" rows="5"></textarea>
            </div>
            <div class="form-group">
                <div class="row">
                    <div class="col-md-6">
                        <input type="text" name="date" class="date form-control" value="" placeholder="Date"/>
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="time" class="form-control" value="" placeholder="Time"/>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <div class="col-md-6">
                        <input type="submit" name="schedule" class="btn btn-success" value="Schedule your tweet!" style="width:100%;"/>
                    </div>
                    <div class="col-md-6">
                        <input type="reset" name="reset" class="btn btn-danger" value="Reset fields" style="width: 100%;"/>
                    </div>
                </div>
            </div>
        </form>
        <hr />
        <div class="recent-tweets">

        </div>
    </div>
@endsection