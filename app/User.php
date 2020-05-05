<?php

namespace App;

use App\Services\Client;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;
    public $_client;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'google_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function hasGoogleToken()
    {
        return !is_null($this->google_token);
    }

    public function setClient()
    {
        $this->_client = new Client($this);
    }
    public function setAccessToken()
    {
        $this->_client->getClient()->setAccessToken($this->google_token);
    }

    public function getGoogleTokenAttribute($value)
    {
        return !is_null($value) ? json_decode($value, true) : $value;
    }

}
