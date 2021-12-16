@props(['provider', 'createdAt' => null])

<div>
    <div class="mt-4">
        <div class="row">
            @switch($provider)
                @case(JoelButcher\Socialstream\Providers::facebook())
                    <div class="col-md-2">
                    <x-facebook-icon  />
                    </div>
                    @break
                @case(JoelButcher\Socialstream\Providers::google())
                <div class="col-md-2">
                    <x-google-icon  />
                </div>
                    @break
                @case(JoelButcher\Socialstream\Providers::twitter())
                <div class="col-md-2">
                    <x-twitter-icon  />
                </div>
                    @break
                @case(JoelButcher\Socialstream\Providers::linkedin())
                <div class="col-md-2">
                    <x-linked-in-icon  />
                </div>
                    @break
                @case(JoelButcher\Socialstream\Providers::github())
                <div class="col-md-2">
                    <x-github-icon  />
                </div>
                    @break
                @case(JoelButcher\Socialstream\Providers::gitlab())
                <div class="col-md-2">
                    <x-gitlab-icon  />
                </div>
                    @break
                @case(JoelButcher\Socialstream\Providers::bitbucket())
                <div class="col-md-2">
                    <x-bitbucket-icon  />
                </div>
                    @break
                @default
            @endswitch

            <div>
                <div class="text-sm font-semibold text-gray-600">
                    {{ __(ucfirst($provider)) }}
                </div>

                @if (! empty($createdAt))
                    <div class="text-xs text-gray-500">
                        Connected {{ $createdAt }}
                    </div>
                @else
                    <div class="text-xs text-gray-500">
                        {{ __('Not connected.') }}
                    </div>
                @endif
            </div>
        </div>

        <div>
            {{ $action }}
        </div>
    </div>

    @error($provider.'_connect_error')
        <div class="text-sm font-semibold text-red-500 px-3 mt-2">
            {{ $message }}
        </div>
    @enderror
</div>
