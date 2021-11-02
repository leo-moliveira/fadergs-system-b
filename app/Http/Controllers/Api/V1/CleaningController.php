<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Classes\Helpers;
use App\Models\Cleaning;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CleaningController extends BaseController
{
    protected $cleaning;

    public function __construct(Cleaning $cleaning)
    {
        $this->cleaning = $cleaning;
    }

    public function list()
    {

    }

    public function show()
    {

    }

    public function store(Request $request)
    {
        //Check permissions
        if (!Helpers::validateUserRole($request->user(), ['admin', 'manager'])){
            return $this->response->errorUnauthorized(trans('unauthorized'));
        }

        //Validate request
        $validator = \Validator::make($request->input(), [
            'room_number' => 'required|integer',
            'manager_id' => 'required|integer',
            'employee_id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }

        //Insert room
        $atributes = [
            'rooms_id'       => $request->get('room_number'),
            'manager_id'        => $request->get('manager_id'),
            'employee_id'       => $request->get('employee_id'),
            'cleaning_date'     => null,
            'status'            => 'to-clean',
            'created_at'        => Carbon::now()->toDateTimeString()
        ];

        if(!$this->cleaning->create($atributes)){
            dd($this->cleaning);die();
            return $this->response->error();
        }

        return $this->response->created(trans('cleaning.sucess'));
    }

    public function update()
    {

    }

    public function delete()
    {

    }

    public function start()
    {

    }

    public function completed()
    {

    }
}
