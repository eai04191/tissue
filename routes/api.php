<?php

use App\MetadataResolver\MetadataResolver;
use App\Utilities\Formatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/checkin/card', function (Request $request, MetadataResolver $resolver, Formatter $formatter) {
    $request->validate([
        'url:required|url'
    ]);
    $url = $formatter->normalizeUrl($request->input('url'));

    $metadata = App\Metadata::find($url);
    if ($metadata == null || ($metadata->expires_at !== null && $metadata->expires_at < now())) {
        $resolved = $resolver->resolve($url);
        $metadata = App\Metadata::updateOrCreate(['url' => $url], [
            'title' => $resolved->title,
            'description' => $resolved->description,
            'image' => $resolved->image,
            'expires_at' => $resolved->expires_at
        ]);
    }
    if($metadata->image){
        $url = parse_url($metadata->image);
        $ip = gethostbyname($url["host"]);
        $geoip = geoip($ip);
        Log::debug($url["host"] . " " . $geoip["iso_code"] . " " . $geoip["country"]);
    }

    $response = response()->json($metadata);
    if (!config('app.debug')) {
        $response = $response->setCache(['public' => true, 'max_age' => 86400]);
    }

    return $response;
});
