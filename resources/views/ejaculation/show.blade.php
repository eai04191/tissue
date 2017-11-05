@extends('layouts.base')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-4">
            @component('components.profile', ['user' => $user])
            @endcomponent
        </div>
        <div class="col-lg-8">
            @if ($user->is_protected && !$user->isMe())
                <div class="card">
                    <div class="card-body">
                        <span class="oi oi-lock-locked"></span> このユーザはチェックイン履歴を公開していません。
                    </div>
                </div>
            @elseif ($ejaculation->is_private && !$user->isMe())
                <div class="card">
                    <div class="card-body">
                        <span class="oi oi-lock-locked"></span> 非公開チェックインのため、表示できません
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body">
                        <!-- span -->
                        <div class="d-flex justify-content-between">
                            <h5>{{ $ejaculatedSpan ?? '精通' }} <small class="text-muted">{{ $ejaculation->before_date }}{{ !empty($ejaculation->before_date) ? ' ～ ' : '' }}{{ $ejaculation->ejaculated_date->format('Y/m/d H:i') }}</small></h5>
                            @if ($user->isMe())
                                <div>
                                    <a class="text-secondary timeline-action-item" href="#"><span class="oi oi-pencil" data-toggle="tooltip" data-placement="bottom" title="修正"></span></a>
                                    <a class="text-secondary timeline-action-item" href="#" data-toggle="modal" data-target="#deleteCheckinModal" data-id="{{ $ejaculation->id }}" data-date="{{ $ejaculation->ejaculated_date }}"><span class="oi oi-trash" data-toggle="tooltip" data-placement="bottom" title="削除"></span></a>
                                </div>
                            @endif
                        </div>
                        <!-- tags -->
                        @if ($ejaculation->is_private) {{-- TODO: タグを付けたら、タグが空じゃないかも判定に加える --}}
                        <p class="mb-2">
                            @if ($ejaculation->is_private)
                                <span class="badge badge-warning"><span class="oi oi-lock-locked"></span> 非公開</span>
                            @endif
                            {{--
                            <span class="badge badge-secondary"><span class="oi oi-tag"></span> 催眠音声</span>
                            <span class="badge badge-secondary"><span class="oi oi-tag"></span> 適当なタグ</span>
                            --}}
                        </p>
                        @endif
                        <!-- okazu link -->
                        {{--
                        <div class="card mb-2 w-50" style="font-size: small;">
                            <a class="text-dark card-link" href="#">
                                <img src="holder.js/320x240" alt="Thumbnail" class="card-img-top">
                                <div class="card-body">
                                    <h6 class="card-title">タイトル</h6>
                                    <p class="card-text">コンテンツの説明文</p>
                                </div>
                            </a>
                        </div>
                        --}}
                        <!-- note -->
                        @if (!empty($ejaculation->note))
                            <p class="mb-0 tis-word-wrap">
                                {!! Formatter::linkify(nl2br(e($ejaculation->note))) !!}
                            </p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@component('components.modal', ['id' => 'deleteCheckinModal'])
    @slot('title')
        削除確認
    @endslot
    <span class="date-label"></span> のチェックインを削除してもよろしいですか？
    <form action="{{ route('checkin.destroy', ['id' => '@']) }}" method="post">
        {{ csrf_field() }}
        {{ method_field('DELETE') }}
    </form>
    @slot('footer')
        <button type="button" class="btn btn-secondary" data-dismiss="modal">キャンセル</button>
        <button type="button" class="btn btn-danger">削除</button>
    @endslot
@endcomponent
@endsection

@push('script')
    <script>
        $('#deleteCheckinModal').on('show.bs.modal', function (event) {
            var target = $(event.relatedTarget);
            var modal = $(this);
            modal.find('.modal-body .date-label').text(target.data('date'));
            modal.data('id', target.data('id'));
        }).find('.btn-danger').on('click', function (event) {
            var modal = $('#deleteCheckinModal');
            var form = modal.find('form');
            form.attr('action', form.attr('action').replace('@', modal.data('id')));
            form.submit();
        });
    </script>
@endpush