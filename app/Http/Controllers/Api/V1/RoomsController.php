<?php

namespace App\Http\Controllers\Api\V1;


use App\Models\Rooms;
use Illuminate\Http\Request;
use App\Transformers\RoomTransformer;
use PhpParser\Node\Stmt\TryCatch;

class RoomsController extends BaseController
{
    protected $rooms;

    /**
     * @param $rooms
     */
    public function __construct(Rooms $rooms)
    {
        $this->rooms = $rooms;
    }

    public function index(){
        $rooms = $this->rooms->paginate(25);
        return $this->response->paginator($rooms, new RoomTransformer());
    }

    public function listBusyRooms(){
        $rooms = $this->rooms->where('status', '=', 1)->paginate(25);
        return $this->response->paginator($rooms, new RoomTransformer());
    }

    public function listFreeRooms(){
        $rooms = $this->rooms->where('status', '=', 0)->paginate(25);
        return $this->response->paginator($rooms, new RoomTransformer());
    }

    public function show($id){
        try {
            $rooms = $this->rooms->findOrFail($id);
            return $this->response->item($rooms, new RoomTransformer());
        }catch (\Exception $exception){
            return $this->response->errorNotFound();
        }
    }

    public function store(Request $request){
        //TODO validate permition
        //TODO store room
    }


    //private
    private function validateRole(){
        //TODO: employee validade role
    }
}
