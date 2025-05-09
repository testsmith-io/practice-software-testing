<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialConnectController extends Controller
{
    public function callbackGoogle()
    {
        $socialUser = Socialite::driver("google")->stateless()->user();
        $user = User::where(['uid' => $socialUser->id])->where('provider', 'google')->first();
        if (!$user) {
            $user = new User;
            $user->provider = 'google';
            $user->uid = $socialUser->id;
            $user->email = $socialUser->email;
            $user->first_name = $socialUser->firstname;
            $user->last_name = $socialUser->lastname;
            $user->role = 'user';
            $user->save();
        }

        $token = Auth::login($user);;

        // Now check user role
        if ($token) {
            return redirect('https://practicesoftwaretesting.com/auth/login;socialid='. $token);
        }
        return redirect('https://practicesoftwaretesting.com/auth/login');
    }

    public function getAuthUrl(Request $request)
    {
        $providerName = $request->input('provider');

        return Socialite::driver($providerName)->stateless()->redirect();
    }

}
