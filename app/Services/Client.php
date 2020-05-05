<?php


namespace App\Services;


use App\User;
use \Google_Service_Classroom as GoogleClassRoom;

class Client
{
    public $_authToken;
    public $_client;
    public $_user;
    public $_success_redirect_url = 'classes';

    public function __construct(User $user)
    {
        $this->_client = new \Google_Client();
        $this->_client->setApplicationName('Google Classroom API PHP Quickstart');
        $this->_client->setScopes([
            GoogleClassRoom::CLASSROOM_COURSES,
            GoogleClassRoom::CLASSROOM_PROFILE_EMAILS,
            GoogleClassRoom::CLASSROOM_PROFILE_PHOTOS,
            GoogleClassRoom::CLASSROOM_TOPICS,
            GoogleClassRoom::CLASSROOM_COURSEWORK_STUDENTS,
            GoogleClassRoom::CLASSROOM_ROSTERS,
        ]);
        $this->_client->setAuthConfig('credentials.json');
        $this->_client->setAccessType('offline');
        $this->_user = $user;
        if($this->_user->hasGoogleToken()) {
            $this->_client->setAccessToken($this->_user->google_token);
        }
    }

    function getClient(){
       return $this->_client;
    }

    /**
     * @return mixed
     */
    public function promptForToken()
    {

        $this->_client->setPrompt('select_account consent');
        // Load previously authorized token from a file, if it exists.
        // The file token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first
        // time.

        // If there is no previous token or it's expired.
        if ($this->_client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($this->_client->getRefreshToken()) {
                $value =  $this->_client->fetchAccessTokenWithRefreshToken($this->_client->getRefreshToken());
                $this->_user->google_token = $value;
                $this->_user->save();
            } else {
                // Request authorization from the user.
                $authUrl = $this->_client->createAuthUrl();
                return ['status' => 'expired', 'url' => $authUrl];
            }
        }
        return ['status' => 'success', 'url' => $this->_success_redirect_url];
    }

    public function setAuthToken($authCode)
    {
        // Exchange authorization code for an access token.
        $accessToken = $this->_client->fetchAccessTokenWithAuthCode($authCode);

        $this->_client->setAccessToken($accessToken);

        // Check to see if there was an error.
        if (array_key_exists('error', $accessToken)) {
            throw new Exception(join(', ', $accessToken));
        }
        $this->_authToken = $accessToken;
        $this->_user->google_token = $accessToken;
        $this->_user->save();
        return ['status' => 'success', 'url' => $this->_success_redirect_url];
    }

}
