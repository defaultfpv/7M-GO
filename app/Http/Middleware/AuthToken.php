<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AccessToken;
use App\Models\Account;

class AuthToken 
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'The token is not provided'], 401);
        }

        $accessToken = AccessToken::where('token', $token)->first();
        if (!$accessToken) {
            return response()->json(['message' => 'not authorized'], 401);
        }

        $user = User::find($accessToken->user_id);
        if (!$user) {
            return response()->json(['message' => 'not authorized'], 401);
        }

        if ($request->method() !== 'GET') { // Если закончились средства, то запрещаем все запросы кроме GET
            $accountStatus = $user->workspaces->first()->account->status;
            if ($accountStatus == 'unpaid') {
                return response()->json(['message' => 'Top up your account balance'], 401);
            }
        }

        AccessToken::where('id', $accessToken->id)->update(['last_used_at' => now()]); // правильно я записал, если колонка у меня timestamp

        // Устанавливаем пользователя в запрос
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}