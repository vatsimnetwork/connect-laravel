<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use League\OAuth2\Client\Token;
use App\Http\Controllers\Controller;
use App\Http\Controllers\VatsimOAuthController;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

/**
 * This controller handles authenticating users for the application and
 * redirecting them to your home screen. The controller uses a trait
 * to conveniently provide its functionality to your applications.
 */
class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $provider;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->provider = new VatsimOAuthController();
    }

    public function login(Request $request)
    {
        if (! $request->has('code') || ! $request->has('state')) {
            $authorizationUrl = $this->provider->getAuthorizationUrl(); // Generates state
            $request->session()->put('vatsimauthstate', $this->provider->getState());
			return redirect()->away($authorizationUrl);
        } else if ($request->input('state') !== session()->pull('vatsimauthstate')) {
            return redirect()->route('home')->withError("Something went wrong, please try again (state mismatch).");
        } else {
            return $this->verifyLogin($request);
        }
    }

    protected function verifyLogin(Request $request)
    {
        try {
            $accessToken = $this->provider->getAccessToken('authorization_code', [
                'code' => $request->input('code')
            ]);
        } catch (IdentityProviderException $e) {
            return redirect()->route('home')->withError("Something went wrong, please try again later.");
        }
        $resourceOwner = json_decode(json_encode($this->provider->getResourceOwner($accessToken)->toArray()));

        if (!
            (isset($resourceOwner->data) &&
	        isset($resourceOwner->data->cid) &&
            isset($resourceOwner->data->personal->name_first) &&
            isset($resourceOwner->data->personal->name_last) &&
            isset($resourceOwner->data->personal->email) &&
            $resourceOwner->data->oauth->token_valid === "true")
        ) {
            return redirect()->route('home')->withError("We need you to grant us all marked permissions");
        }

        $account = $this->completeLogin($resourceOwner, $accessToken);

        auth()->login($account, true);

        return redirect()->intended(route('home'))->withSuccess('Login Successful');
    }

    protected function completeLogin($resourceOwner, $token)
    {
        $account = User::firstOrNew(['id' => $resourceOwner->data->cid]);
        $account->fname = $resourceOwner->data->personal->name_first;
        $account->lname = $resourceOwner->data->personal->name_last;
        $account->email = $resourceOwner->data->personal->email;
        if ($resourceOwner->data->oauth->token_valid) { // User has given us permanent access to updated data
            $account->access_token = $token->getToken();
            $account->refresh_token = $token->getRefreshToken();
            $account->token_expires = $token->getExpires();
        }

        $account->save();

        return $account;
    }

    public function logout()
    {
        auth()->logout();

        return redirect(route('home'))->withSuccess('You have been successfully logged out');
    }
}
