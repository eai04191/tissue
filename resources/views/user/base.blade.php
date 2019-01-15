@extends('layouts.base')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-lg-4">
                @component('components.profile', ['user' => $user])
                @endcomponent
                @section('sidebar')
                @show
            </div>
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs">
                            <li class="nav-item">
                                <a class="nav-link {{ Route::currentRouteName() === 'user.profile' ? 'active' : '' }}" href="{{ route('user.profile', ['name' => $user->name]) }}">タイムライン</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ Route::currentRouteName() === 'user.stats' ? 'active' : '' }}" href="{{ route('user.stats', ['name' => $user->name]) }}">グラフ</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ Route::currentRouteName() === 'user.okazu' ? 'active' : '' }}" href="{{ route('user.okazu', ['name' => $user->name]) }}">オカズ</a>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-content">
                        @yield('tab-content')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
