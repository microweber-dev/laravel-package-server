<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credential extends Model
{
    use HasFactory;

    public const TYPE_HTTP_BASIC = 'http-basic';
    public const TYPE_GITHUB_OAUTH = 'github-oauth';
    public const TYPE_GITLAB_TOKEN = 'gitlab-token';
    public const TYPE_BITBUCKET_APP_PW = 'bitbucket-app-pw';
    public const TYPE_BITBUCKET_API = 'bitbucket-api';
    public const TYPE_BEARER_TOKEN = 'bearer-token';
    public const TYPE_SSH_KEY = 'ssh-key';

    public $casts = [
        'authentication_data'=>'json'
    ];

    public function owner()
    {
        return $this->hasOne(User::class);
    }
}
