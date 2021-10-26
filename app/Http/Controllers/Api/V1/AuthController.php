<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;


class AuthController extends BaseController
{
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
     * @OA\Post (
     *     path="/api/auth/login",
     *     operationId="/api/auth/login",
     *     tags={"Authentication"},
     *     description = "Handle a login request to the application.",
     *     @OA\Parameter(
     *         name="user_name",
     *         in="path",
     *         description="User Name",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="path",
     *         description="User Password",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Return login user token",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
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
        } catch (JWTException $e) {
            $this->response->errorInternal('could_not_create_token');
        }
        // all good so return the token
        return $this->response->array(compact('token'));
    }

}
