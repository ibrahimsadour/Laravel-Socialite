<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Socialite;
use Illuminate\Support\Facades\Hash;
use Auth;
Use App\User;
class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }


    /**
   * Redirect the user to the GitHub authentication page.
   *
   * @return \Illuminate\Http\Response
   */
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

  /**
   * Obtain the user information from Google.
   *
   * @return \Illuminate\Http\Response
   */
  public function handleProviderCallback($provider)

  {
    try {
      if($provider == 'google'){

        $user = Socialite::driver($provider)->stateless()->user();
        // $userEmail  = $user->email;
        // $userName = explode("@", $userEmail);
        // $name=$userName[0];

      }else{
        $user = Socialite::driver($provider)->user();
        // $name = $user->getName();
      }

      /**
       * If a user has registered before using social auth, return the user
       * else, create a new user object.
       * @param  $user Socialite user object
       * @param  $provider Social auth provider
       * @return  User
      */
      $findUser= User::where('provider_id',$user->id)->first();

      if($findUser){
        
        Auth::login($findUser);
        return redirect()->intended('home');

      }else{ //if the user not exist => we need to make new user

        //add new user to database
        $newUser = new User([
          'email' => $user->getEmail(),
          'name' => $user->name,
          'provider'=>$provider,
          'provider_id' => $user->id,
          'password' => Hash::make('12345') //or bcrypt();
        ]);

        if($newUser->save()){ //check if the crated or not
          // login the user
          Auth::login($newUser);
          return redirect()->intended('home');
        }
      }
    } catch (\Exception $ex) {
      DB::rollback();
      return view('login')->with('error', 'Something went wrong, please try again later');

    }

  }
   
}
