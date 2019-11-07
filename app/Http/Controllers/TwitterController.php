<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Abraham\TwitterOAuth\TwitterOAuth;
use Illuminate\Support\Facades\DB;

class TwitterController extends Controller
{

    /**
     * Index action
     */
    public function index()
    {
        # First, check if user is authenticated
        if (!Auth::user()) {
            return redirect()->route('home');
        }

        $connection = $this->connect();

        # Check whether the user is authenticated to use twitter
        if (Auth::user()->twitter_authenticated != 1) {
            return redirect()->route('twitter.authenticate');
        }

        return view('twitter.index');
    }

    /**
     * Connect to the Twitter API
     */
    public function connect()
    {
        # Set the default keys
        $consumerKey       = 'No9TPys7j3eFa0BQ69zDytMmI';
        $consumerSecret    = '7yse5pHOk8D1DeBXBxEV6plTmzdK6t03SvNbPvN86XL1unDojM';
        $accessToken       = '1192101975351005187-C1uoEfgpxgxUrKl3bhNcECrv8Snyis';
        $accessTokenSecret = 'XjaOkZBZGVkgmm2TQt4hr3FpvByceOKk4aqClsNvqTFnk';
        define('OAUTH_CALLBACK', getenv('OAUTH_CALLBACK'));

        # Establish connection
        $connection = new TwitterOAuth($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);

        # Check the connection
        $content = $connection->get('account/verify_credentials');
        if ($content) {
            return $connection;
        }

        return false;
    }

    /**
     * Get the authenticate view
     */
    public function getAuthenticate()
    {
        # Update user and set authenticate twitter to 1
        if (!empty(request()->oauth_token) && !empty(request()->oauth_verifier)) {
            DB::table('users')
                ->where('id', Auth::user()->id)
                ->update(['twitter_authenticated' => 1]);
        }

        # Check if user is not authenticated yet
        if (Auth::user()->twitter_authenticated) {
            return redirect()
                ->route('home')
                ->with('status', 'You have already authenticated yourself at Twitter!');
        }

        return view('twitter.authenticate');
    }

    /**
     * Authenticate at twitter
     */
    public function postAuthenticate()
    {
        $connection    = $this->connect();
        $request_token = $connection->oauth('oauth/request_token', ['oauth_callback' => OAUTH_CALLBACK]);
        if (!empty($request_token)) {
            $oauth_token        = $request_token['oauth_token'];
            $oauth_token_secret = $request_token['oauth_token_secret'];

            if (!empty($oauth_token) && !empty($oauth_token_secret)) {
                return redirect($connection->url('oauth/authorize', ['oauth_token' => $oauth_token]));
            }
        }
    }
}