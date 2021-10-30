<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;


class AuthController extends BaseController
{
    protected $user;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * @OA\Post(
     * path="/api/auth/login",
     * summary="Handle a login request to the application.",
     * description="Login by user_name and password",
     * operationId="login",
     * tags={"Authentication"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"user_name","password"},
     *       @OA\Property(property="user_name", type="string", example="gerente"),
     *       @OA\Property(property="password", type="string", format="password", example="1234")
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Authenticated token",
     *    @OA\JsonContent(
     *       @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0L2FwaS9hdXRoL2xvZ2luIiwiaWF0IjoxNjM1NDU5NzM3LCJleHAiOjE2MzU0NjMzMzcsIm5iZiI6MTYzNTQ1OTczNywianRpIjoiVXozanpUeElNNnJBSDJCSiIsInN1YiI6MiwicHJ2IjoiMjNiZDVjODk0OWY2MDBhZGIzOWU3MDFjNDAwODcyZGI3YTU5NzZmNyJ9.TWsfFNrcU4dFGnbNyNbudzQSwdCknOKGrImic5BROtw")
     *        )
     *     )
     * ),
     * @OA\Response(
     *    response=403,
     *    description="Forbidden",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="")
     *        )
     *     )
     * )
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_name' => 'required|string',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }

        // grab credentials from the request
        $credentials = $request->only('user_name', 'password');
        try {
            // attempt to verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt($credentials)) {
                $this->response->errorForbidden(trans('auth.incorrect'));
            }
            $this->user = $request->user();
            (!$this->validateStatus($this->user)) ? $this->response->errorUnauthorized(trans('auth.unauthorized')): '';
            $this->updateDateLogin($this->user);
        } catch (JWTException $e) {
            $this->response->errorInternal('could_not_create_token');
        }
        // all good so return the token
        return $this->response->array(compact('token'));
    }

    //private
    private function validateStatus(User $user) {
        if($user['status']){
            return true;
        }
        return false;
    }

    private function updateDateLogin(User $user){
        try {
            $user['last_login_at'] = Carbon::now()->toDateTimeString();
            $user->save();
        } catch (\Exception $e){
            return $e->getMessage();
        }
        return true;
    }

}
