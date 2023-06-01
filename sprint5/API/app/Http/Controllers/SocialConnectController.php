<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SocialConnectController extends Controller
{
    public $service;

    public function __construct()
    {
        $configureProviders = [
//            'redirectUri' => 'https://api.practicesoftwaretesting.com/auth/cb',
            'redirectUri' => 'https://api.practicesoftwaretesting.com/auth/cb/${provider}/',
            'provider' => [
                'google' => [
                    'applicationId' => env('APP_GOOGLE_ID'),
                    'applicationSecret' => env('APP_GOOGLE_SECRET'),
                    'scope' => [
                        'https://www.googleapis.com/auth/userinfo.email',
                        'https://www.googleapis.com/auth/userinfo.profile'
                    ]
                ],
                'github' => [
                    'applicationId' => env('APP_GITHUB_ID'),
                    'applicationSecret' => env('APP_GITHUB_SECRET'),
                    'options' => [
                        'fetch_emails' => true
                    ]
                ]
            ]
        ];

        $collectionFactory = null;
        $httpClient = new \SocialConnect\HttpClient\Curl();

        $httpStack = new \SocialConnect\Common\HttpStack(
        // HTTP-client `Psr\Http\Client\ClientInterface`
            $httpClient,
            // RequestFactory that implements Psr\Http\Message\RequestFactoryInterface
            new \SocialConnect\HttpClient\RequestFactory(),
            // StreamFactoryInterface that implements Psr\Http\Message\StreamFactoryInterface
            new \SocialConnect\HttpClient\StreamFactory()
        );



        $this->service = new \SocialConnect\Auth\Service(
        $httpStack,
        new \SocialConnect\Provider\Session\Session(),
        $configureProviders,
        $collectionFactory
    );


//
//        $this->service = new \SocialConnect\Auth\Service(
//            $httpClient,
//            new \SocialConnect\Provider\Session\Session(),
//            $configureProviders
//        );
//
//        /**
//         * By default collection factory is null, in this case Auth\Service will create
//         * a new instance of \SocialConnect\Auth\CollectionFactory
//         * you can use custom or register another providers by CollectionFactory instance
//         */
//        $collectionFactory = null;
//
//        $this->service = new \SocialConnect\Auth\Service(
//            $httpClient,
//            new \SocialConnect\Provider\Session\Session(),
//            $configureProviders,
//            $collectionFactory
//        );
    }

    public function callbackGoogle()
    {
        $this->callback('google');
    }

    public function callbackGithub()
    {
        $this->callback('github');
    }

    public function getAuthUrl(Request $request)
    {
        $providerName = $request->input('provider');;

        $provider = $this->service->getProvider($providerName);
        return redirect($provider->makeAuthUrl());
    }

    private function callback($provider)
    {
        $providerName = $provider;

        try {
            $provider = $this->service->getProvider($providerName);
            $accessToken = $provider->getAccessTokenByRequestParameters($_GET);
            $oauthUser = $provider->getIdentity($accessToken);
            $user = User::where(['uid' => $oauthUser->id])->where('provider', $providerName)->first(); // , 'provider', $request->provider
            if (!$user) {
                $user = new User;
                $user->provider = $providerName;
                $user->uid = $oauthUser->id;
                $user->email = $oauthUser->email;
                $user->first_name = $oauthUser->firstname;
                $user->last_name = $oauthUser->lastname;
                $user->role = 'user';
                try {
                    $user->save();
                } catch (QueryException $e) {
                    return response()->json(['status' => 'Account with email already exists']);
                }
            }
            $token = Auth::login($user);//(['uid' => '$oauthUser->id']);
            if (isset($oauthUser->id)) {
                ?>
                <script language="javascript">
                    if (window.opener) {
                        window.opener.parent.location.href = "https://practicesoftwaretesting.com/#/auth/login;socialid=<?php echo urlencode($token); ?>";
                    }
                    window.self.close();
                </script>
                <?php
            }
        } catch (\ErrorException $e) {
            ?>
            <script language="javascript">
                if (window.opener) {
                    window.opener.parent.location.href = "https://practicesoftwaretesting.com/#/auth/login";
                }
                window.self.close();
            </script>
            <?php
        }
    }

}
