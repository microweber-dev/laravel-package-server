<div class="p-4 text-center">
    <p>OR</p>
<div class="row justify-content-center">

    @if (JoelButcher\Socialstream\Socialstream::hasFacebookSupport())
        <div class="col-md-2">
            <a href="{{ route('oauth.redirect', ['provider' => JoelButcher\Socialstream\Providers::facebook()]) }}">
                <x-facebook-icon/>
            </a>
        </div>
    @endif

    @if (JoelButcher\Socialstream\Socialstream::hasGoogleSupport())
        <div class="col-md-2">
            <a href="{{ route('oauth.redirect', ['provider' => JoelButcher\Socialstream\Providers::google()]) }}">
                <x-google-icon/>
            </a>
        </div>
    @endif

    @if (JoelButcher\Socialstream\Socialstream::hasTwitterSupport())
        <div class="col-md-2">
            <a href="{{ route('oauth.redirect', ['provider' => JoelButcher\Socialstream\Providers::twitter()]) }}">
                <x-twitter-icon/>
            </a>
        </div>
    @endif

    @if (JoelButcher\Socialstream\Socialstream::hasLinkedInSupport())
        <div class="col-md-2">
            <a href="{{ route('oauth.redirect', ['provider' => JoelButcher\Socialstream\Providers::linkedin()]) }}">
                <x-linked-in-icon/>
            </a>
        </div>
    @endif

    @if (JoelButcher\Socialstream\Socialstream::hasGithubSupport())
        <div class="col-md-2">
            <a href="{{ route('oauth.redirect', ['provider' => JoelButcher\Socialstream\Providers::github()]) }}">
                <x-github-icon/>
            </a>
        </div>
    @endif

    @if (JoelButcher\Socialstream\Socialstream::hasGitlabSupport())
        <div class="col-md-2">
            <a href="{{ route('oauth.redirect', ['provider' => JoelButcher\Socialstream\Providers::gitlab()]) }}">
                <x-gitlab-icon/>
            </a>
        </div>
    @endif

    @if (JoelButcher\Socialstream\Socialstream::hasBitbucketSupport())
        <div class="col-md-2">
            <a href="{{ route('oauth.redirect', ['provider' => JoelButcher\Socialstream\Providers::bitbucket()]) }}">
                <x-bitbucket-icon/>
            </a>
        </div>
    @endif
</div>
</div>
