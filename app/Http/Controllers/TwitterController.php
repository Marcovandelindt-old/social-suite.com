<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;

use Abraham\TwitterOAuth\TwitterOAuth;

use App\TwitterUser;
use App\Tweet;

class TwitterController extends Controller
{
    public $callback = null;

    /**
     * @inheritdoc
     */
    public function __construct()
    {
        $this->callback = URL::current();
    }

    /**
     * Index action
     *
     * @param Request $request
     *
     * @return \Illuminate\Support\Facades\Redirect | \Illuminate\View\View
     */
    public function index(Request $request)
    {
        if (Auth::user()->twitter_authenticated != 1) {
            return redirect()->route('twitter.authenticate');
        }

        return view('twitter.index');
    }

    /**
     * Connect to the twitter API
     *
     * @return mixed TwitterOAuth $connection | false
     */
    public function connect($oauth_token = null, $oauth_verifier = null)
    {
        # Default values
        $consumer_key    = env('TWITTER_CONSUMER_KEY');
        $consumer_secret = env('TWITTER_CONSUMER_SECRET_KEY');

        if (!empty($oauth_token) && !empty($oauth_verifier)) {
            $connection = new TwitterOAuth($consumer_key, $consumer_secret, $oauth_token, $oauth_verifier);
        } else {
            $connection = new TwitterOAuth($consumer_key, $consumer_secret);
        }

        if ($connection) {
            return $connection;
        }

        return false;
    }

    /**
     * Get the authentication view
     *
     * @param Request $request
     *
     * @return \Illuminate\Support\Facades\Redirect | \Illuminate\View\View
     */
    public function getAuthenticate(Request $request)
    {
        if (Auth::user()->twitter_authenticated == 1) {
            return redirect()
                ->route('twitter.index')
                ->with('status', 'You have already authenticated yourself at Twitter');
        }

        # Check for oauth data
        if (!empty(request()->oauth_token) && !empty(request()->oauth_verifier)) {
            $this->verifyAndSave();
        }

        return view('twitter.authenticate');
    }

    /**
     * Authenticate at Twitter
     *
     * @param Request $request
     *
     * @return \Illuminate\Support\Facades\Redirect
     */
    public function postAuthenticate(Request $request)
    {
        $connection = $this->connect();
        if ($connection) {
            $request_token = $connection->oauth('oauth/request_token', ['oauth_callback' => $this->callback]);

            if (!empty($request_token)) {
                $oauth_token  = $request_token['oauth_token'];
                $oauth_secret = $request_token['oauth_token_secret'];

                if (!empty($oauth_token) && !empty($oauth_secret)) {
                    $url = $connection->url('oauth/authorize', ['oauth_token' => $oauth_token]);

                    if (!empty($url)) {
                        return redirect($url);
                    }
                }
            }
        }
    }

    /**
     * Verify the oauth data
     *
     * @return \Illuminate\Support\Facades\Redirect
     */
    public function verifyAndSave()
    {
        $oauth_token    = request()->oauth_token;
        $oauth_verifier = request()->oauth_verifier;
        $connection     = $this->connect($oauth_token, $oauth_verifier);

        if ($connection) {
            $oauth_data = $connection->oauth('oauth/access_token', ['oauth_token' => $oauth_token, 'oauth_consumer_key' => env('TWITTER_CONSUMER_KEY'), 'oauth_verifier' => $oauth_verifier]);
            if (!empty($oauth_data)) {
                $oauth_token        = $oauth_data['oauth_token'];
                $oauth_token_secret = $oauth_data['oauth_token_secret'];
                $twitter_user_id    = $oauth_data['user_id'];
                $screen_name        = $oauth_data['screen_name'];

                # Create new TwitterUser
                $twitter_user = new TwitterUser();
                if ($twitter_user->create($oauth_token, $oauth_token_secret, $twitter_user_id, $screen_name)) {
                    $twitter_user->setAuthenticated(1, Auth::user()->id);

                    return redirect()
                        ->route('twitter.index')
                        ->with('status', 'Successfully authenticated at Twitter!');
                } else {
                    $twitter_user->setAuthenticated(0, Auth::user()->id);

                    return redirect()
                        ->route('twitter.authenticate')
                        ->with('status', 'Something went wrong while trying to authenticate you at Twitter. Please try again later.');
                }
            }
        }
    }

    /**
     * Get the schedule view
     */
    public function getSchedule()
    {
        if (Auth::user()->twitter_authenticated == 1) {
            return view('twitter.schedule');
        }

        return redirect()
            ->route('twitter.authenticate')
            ->with('status', 'You must be authenticated at Twitter.');
    }

    /**
     * Post a scheduled tweet
     *
     * @param Request $request
     */
    public function postSchedule(Request $request)
    {
        $errors = [];

        if (!$request->filled('status')) {
            $errors[] = 'Please fill in a tweet.';
        }

        if (!$request->filled('date')) {
            $errors[] = 'Please fill in a date';
        }

        if (!$request->filled('time')) {
            $errors[] = 'Please fill in a time';
        }

        if (empty($errors)) {
            $formatted_date     = date('Y-m-d', strtotime($request->date));
            $formatted_time     = date('H:i:s', strtotime($request->time));
            $formatted_datetime = $formatted_date . ' ' . $formatted_time;

            if (!empty($formatted_datetime)) {
                $tweet            = new Tweet();
                $tweet->user_id   = Auth::user()->id;
                $tweet->tweet     = $request->status;
                $tweet->scheduled = $formatted_datetime;

                if ($tweet->save()) {
                    return redirect()
                        ->route('twitter.schedule')
                        ->with('status', 'Tweet was successfully scheduled.');
                } else {
                    return redirect()
                        ->route('twitter.schedule')
                        ->with('status', 'Tweet could not be scheduled. Please try again.');
                }
            }
        } else {
            return redirect()
                ->route('twitter.schedule')
                ->with('errors', $errors);
        }
    }
}