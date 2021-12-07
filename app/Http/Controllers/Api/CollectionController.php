<?php

namespace App\Http\Controllers\Api;

use App\Collection;
use App\CollectionItem;
use App\Http\Controllers\Controller;
use App\Http\Resources\CollectionItemResource;
use App\Http\Resources\CollectionResource;
use App\Tag;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Validator;

class CollectionController extends Controller
{
    public function index(User $user)
    {
        if (!$user->isMe() && $user->is_protected) {
            throw new AccessDeniedHttpException('このユーザはチェックイン履歴を公開していません');
        }

        $collections = $user->collections();
        if (!$user->isMe()) {
            $collections = $collections->where('is_private', false);
        }

        return response()->json($collections->get()->map(fn ($collection) => new CollectionResource($collection)));
    }

    public function show(Collection $collection)
    {
        // FIXME: たぶんPolicyで定義したほうがいい
        if (!$collection->user->isMe()) {
            if ($collection->is_private) {
                throw new NotFoundHttpException();
            }
            if ($collection->user->is_protected) {
                throw new AccessDeniedHttpException('このユーザはチェックイン履歴を公開していません');
            }
        }

        return new CollectionResource($collection);
    }

    public function inbox(Request $request)
    {
        /** @var Collection $collection */
        $collection = Auth::user()->collections()->firstOrCreate(['title' => 'あとで抜く']);

        $validated = Validator::make($request->input(), [
            'link' => [
                'required',
                'url',
                'max:2000',
                Rule::unique('collection_items', 'link')->where(function ($query) use ($collection) {
                    return $query->where('collection_id', $collection->id);
                })
            ],
            'note' => 'nullable|string|max:500',
            'tags' => 'nullable|array|max:40',
            'tags.*' => ['string', 'not_regex:/[\s\r\n]/u', 'max:255'],
        ], [
            'tags.max' => 'タグは最大40個までです。',
            'tags.*.not_regex' => 'The :attribute cannot contain spaces, tabs and newlines.',
        ])->validate();

        $item = DB::transaction(function () use ($collection, $validated) {
            $item = new CollectionItem($validated);
            $collection->items()->save($item);

            $tagIds = [];
            if (!empty($validated['tags'])) {
                foreach ($validated['tags'] as $tag) {
                    $tag = trim($tag);
                    if ($tag === '') {
                        continue;
                    }

                    $tag = Tag::firstOrCreate(['name' => $tag]);
                    $tagIds[] = $tag->id;
                }
            }
            $item->tags()->sync($tagIds);

            return $item;
        });

        if ($request->input('flash') === true) {
            session()->flash('status', '登録しました。');
        }

        return new CollectionItemResource($item);
    }
}
