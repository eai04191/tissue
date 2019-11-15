@extends('layouts.base')

@section('title', 'チェックインの修正')

@section('content')
<div id="app" class="container">
    <h2>チェックインの修正</h2>
    <hr>
    <div class="row justify-content-center mt-5">
        <div class="col-lg-6">
            <form method="post" action="{{ route('checkin.update', ['id' => $ejaculation->id]) }}">
                {{ method_field('PUT') }}
                {{ csrf_field() }}

                <div class="form-row">
                    <div class="form-group col-sm-6">
                        <label for="date"><span class="oi oi-calendar"></span> 日付</label>
                        <input id="date" name="date" type="text" class="form-control {{ $errors->has('date') || $errors->has('datetime') ? ' is-invalid' : '' }}"
                               pattern="^20[0-9]{2}/(0[1-9]|1[0-2])/(0[1-9]|[12][0-9]|3[01])$" value="{{ old('date') ?? $ejaculation->ejaculated_date->format('Y/m/d') }}" required>

                        @if ($errors->has('date'))
                            <div class="invalid-feedback">{{ $errors->first('date') }}</div>
                        @endif
                    </div>
                    <div class="form-group col-sm-6">
                        <label for="time"><span class="oi oi-clock"></span> 時刻</label>
                        <input id="time" name="time" type="text" class="form-control {{ $errors->has('time') || $errors->has('datetime') ? ' is-invalid' : '' }}"
                               pattern="^([01][0-9]|2[0-3]):[0-5][0-9]$" value="{{ old('time') ?? $ejaculation->ejaculated_date->format('H:i') }}" required>

                        @if ($errors->has('time'))
                            <div class="invalid-feedback">{{ $errors->first('time') }}</div>
                        @endif
                    </div>
                    @if ($errors->has('datetime'))
                        <div class="form-group col-sm-12" style="margin-top: -1rem;">
                            <small class="text-danger">{{ $errors->first('datetime') }}</small>
                        </div>
                    @endif
                </div>
                <div class="form-row">
                    <div class="form-group col-sm-12">
                        <label for="tagInput"><span class="oi oi-tags"></span> タグ</label>
                        <tag-input id="tagInput" name="tags" value="{{ old('tags') ?? $ejaculation->textTags() }}" :is-invalid="{{ $errors->has('tags') ? 'true' : 'false' }}"></tag-input>
                        <small class="form-text text-muted">
                            Tab, Enter, 半角スペースのいずれかで入力確定します。
                        </small>

                        @if ($errors->has('tags'))
                            <div class="invalid-feedback">{{ $errors->first('tags') }}</div>
                        @endif
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-sm-12">
                        <label for="link"><span class="oi oi-link-intact"></span> オカズリンク</label>
                        <input id="link" name="link" type="text" autocomplete="off" class="form-control {{ $errors->has('link') ? ' is-invalid' : '' }}"
                               placeholder="http://..." value="{{ old('link') ?? $ejaculation->link }}"
                               @change="onChangeLink">
                        <small class="form-text text-muted">
                            オカズのURLを貼り付けて登録することができます。
                        </small>
                        @if ($errors->has('link'))
                            <div class="invalid-feedback">{{ $errors->first('link') }}</div>
                        @endif
                    </div>
                </div>
                <metadata-preview :metadata="metadata" :state="metadataLoadState"></metadata-preview>
                <div class="form-row">
                    <div class="form-group col-sm-12">
                        <label for="note"><span class="oi oi-comment-square"></span> ノート</label>
                        <textarea id="note" name="note" class="form-control {{ $errors->has('note') ? ' is-invalid' : '' }}" rows="4" v-on:input="onChangeNote">{{ old('note') ?? $ejaculation->note }}</textarea>
                        <small id="note-character-counter" class="form-text text-muted">
                            最大 500 文字
                        </small>
                        @if ($errors->has('note'))
                            <div class="invalid-feedback">{{ $errors->first('note') }}</div>
                        @endif
                    </div>
                </div>
                <div class="form-row mt-4">
                    <p>オプション</p>
                    <div class="form-group col-sm-12">
                        <div class="custom-control custom-checkbox mb-3">
                            <input id="isPrivate" name="is_private" type="checkbox" class="custom-control-input" {{ (is_bool(old('is_private')) ? old('is_private') : $ejaculation->is_private) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="isPrivate">
                            <span class="oi oi-lock-locked"></span> このチェックインを非公開にする
                            </label>
                        </div>
                        <div class="custom-control custom-checkbox mb-3">
                            <input id="isTooSensitive" name="is_too_sensitive" type="checkbox" class="custom-control-input" {{ (is_bool(old('is_too_sensitive')) ? old('is_too_sensitive') : $ejaculation->is_too_sensitive) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="isTooSensitive">
                            <span class="oi oi-warning"></span> チェックイン対象のオカズをより過激なオカズとして設定する
                            </label>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <button class="btn btn-primary" type="submit">チェックイン</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
    <script src="{{ mix('js/checkin.js') }}"></script>
@endpush
