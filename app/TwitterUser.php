<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TwitterUser extends Model
{
    /**
     * Get a twitter user
     *
     * @param int $user_id
     *
     * @return mixed TwitterUser | false
     */
    public function get($user_id)
    {
        $twitterUser = DB::table('twitter_users')
            ->where('user_id', $user_id)
            ->first();

        if (!empty($twitterUser)) {
            return $twitterUser;
        }

        return false;
    }

    /**
     * Create a new twitter user
     *
     * @param string $oauth_token
     * @param string $oauth_token_secret
     * @param int    $twitter_user_id
     * @param string $screen_name
     */
    public function create($oauth_token, $oauth_verifier, $twitter_user_id, $screen_name)
    {
        $twitter_user = DB::table('twitter_users')
            ->where('user_id', Auth::user()->id)
            ->first();

        if (empty($twitter_user)) {
            if (DB::table('twitter_users')
                ->insert(
                    [
                        'user_id'         => Auth::user()->id,
                        'twitter_user_id' => $twitter_user_id,
                        'screen_name'     => $screen_name,
                        'oauth_token'     => $oauth_token,
                        'oauth_verifier'  => $oauth_verifier,
                    ]
                )) {

                return true;
            }
        }

        return false;
    }

    /**
     * Set the authenticated status
     *
     * @param int $status
     * @param int $user_id
     */
    public function setAuthenticated($status, $user_id)
    {
        DB::table('users')
            ->where('id', $user_id)
            ->update(['twitter_authenticated' => $status]);
    }
}