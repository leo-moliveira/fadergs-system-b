<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User as ModelUser;
use App\Http\Classes\User;
use Illuminate\Http\Request;
use App\Transformers\UserTransformer;

class UsersController extends BaseController
{
    protected $user;

    /**
     * @param ModelUser $user
     */
    public function __construct(ModelUser $user)
    {
        $this->user = $user;
    }

    /**
     * @OA\Get (
     *     path="/api/user",
     *     tags={"User"},
     *     summary = "Get authenticated user information",
     *     @OA\Response(
     *         response="200",
     *         description="Return user information"
     *     ),
     *     security={{"JWT":{}}}
     * )
     * @return \Dingo\Api\Http\Response
     */
    public function index(Request $request){
        $this->user = $request->user();
        $user = new User($this->user);
        return $this->response->array($user->toArray());
    }
}
