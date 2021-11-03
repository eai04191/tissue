@extends('user.base')

@section('title', $user->display_name . ' さんのオカズ')

@section('tab-content')
    @if ($user->is_protected && !$user->isMe())
        <p class="mt-4">
            <span class="oi oi-lock-locked"></span> このユーザはチェックイン履歴を公開していません。
        </p>
    @else
        <div class="row okazu-gallary">
            @forelse ($ejaculations as $ejaculation)
                <div class="col-sm-6 col-lg-4 mb-4 link-card-gallary">
                    <div class="card text-white" data-link={{ $ejaculation->link }}>
                        <a href={{ $ejaculation->link }}>
                            <img class="card-img" />
                        </a>
                        {{-- TODO: グラデーションの開始位置を下から何pxになるようにする --}}
                        <div class="card-img-overlay"
                            style="background: linear-gradient(rgb(0 255 179 / 0%) 70%, #555 100%);">
                            <div class="position-absolute" style="bottom:0;">
                                <h5 class="card-title text-"></h5>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <li class="list-group-item border-bottom-only">
                    <p>まだオカズがありません。</p>
                </li>
            @endforelse
        </div>

    @endif
    {{-- 共通で出る項目 --}}
@endsection

@push('script')
    {{-- laravel-mixの使い方がわからなかった --}}
    <script src="https://cdn.jsdelivr.net/npm/masonry-layout@4.2.2/dist/masonry.pkgd.min.js"></script>
@endpush
