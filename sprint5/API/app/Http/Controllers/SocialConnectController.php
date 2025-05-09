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
            $parts = explode(' ', $socialUser->name, 2);
            $firstName = $parts[0];
            $lastName = $parts[1] ?? '';
            $user->first_name = $firstName;
            $user->last_name = $lastName;
            $user->role = 'user';
            $user->save();
        }

        $token = app('auth')->login($user);

        // Now check user role
        if ($token) {
            ?>
            <script language="javascript">
                if (window.opener) {
                    window.opener.parent.location.href = "https://practicesoftwaretesting.com/auth/login;socialid=<?php echo urlencode($token); ?>";
                }
                window.self.close();
            </script>
            <?php
        }
        ?>
        <script language="javascript">
            if (window.opener) {
                window.opener.parent.location.href = "https://practicesoftwaretesting.com/auth/login";
            }
            window.self.close();
        </script>
        <?php
    }

    public function getAuthUrl(Request $request)
    {
        $providerName = $request->input('provider');

        return Socialite::driver($providerName)->stateless()->redirect();
    }

}
