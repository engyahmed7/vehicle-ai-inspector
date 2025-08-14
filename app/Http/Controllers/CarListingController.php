<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CarListingController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'car_owner') {
            $cars = Car::where('user_id', $user->id)->with('user')->get();
        } else {
            $cars = Car::where('user_id', '!=', $user->id)->with('user')->get();
        }

        return view('cars.index', compact('cars'));
    }

    public function show($id)
    {
        $car = Car::with('user')->findOrFail($id);
        $user = Auth::user();

        $existingConversation = null;
        if ($user->role === 'customer') {
            $existingConversation = Conversation::where('car_id', $id)
                ->where('customer_id', $user->id)
                ->where('car_owner_id', $car->user_id)
                ->first();
        }

        return view('cars.show', compact('car', 'existingConversation'));
    }
}
