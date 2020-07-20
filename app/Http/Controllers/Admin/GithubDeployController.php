<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

class GithubDeployController extends Controller
{
    public function deploy(Request $request)
    {
        $githubPayload = $request->getContent();
        $githubHash = $request->header('X-Hub-Signature');

        $localToken = config('app.deploy_secret');
        $localHash = 'sha1=' . hash_hmac('sha1', $githubPayload, $localToken, false);

        if (hash_equals($githubHash, $localHash)) {
            Artisan::call("diploy:github");

            app('log')->debug('githubHash: '. $githubHash);
            app('log')->debug('localHash: '. $localHash);
            return response()->json(['success' => true], 200);
        }


        app('log')->debug('githubHash: '. $githubHash);
        app('log')->debug('localHash: '. $localHash);

    }
}
