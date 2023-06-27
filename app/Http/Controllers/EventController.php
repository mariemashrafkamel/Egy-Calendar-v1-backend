<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{

    public function index()
    {
        // show all Events
        $event = Event::orderBy('created_at', 'DESC')
        ->get();
        return $event;
    }

    public function create(Request $request)
    {
        $price = $request->price ;
        $price_cent = $price * 100;
        $newEvent = Event::create([
            'name' => $request->name,
            'name_local' => $request->name_local,
            'description' => $request->description,
            'author'=> $request -> author,
            'price' => $price_cent,

        ]);
       return response()->json(['data' => $newEvent,'status'=>'success','message' => 'Successfully'],200);
    }


    public function store(Request $request)
    {

    }

    public function show(Event $event)
    {
        //$t = DB::table('events')->where('id', $event)->first();
        return response()->json(['event' => $event ],200);
         // return $event;
    }


    public function edit(Event $event)
    {

    }


    public function update(Request $request, Event $event)
    {

    }


    public function destroy(Event $event)
    {

    }
}
