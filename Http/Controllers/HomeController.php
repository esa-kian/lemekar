<?php

namespace App\Http\Controllers;

use App\DB\AddressRepo;
use App\DB\HomeRepo;
use App\DB\ProjectRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function isAuth()
    {
        if (auth()->guard('api')->user()->client_id) {

            return response(['type' => 'client'], 200);
        } elseif (auth()->guard('api')->user()->technician_id) {
            
            return response(['type' => 'technician'], 200);
        }
    }
    public function fetchPopularCats(HomeRepo $homeRepo)
    {
        return response(['cats' => $homeRepo->popularCats()], 200);
    }

    public function fetchLastOrders(HomeRepo $homeRepo)
    {
        return response(['orders' => $homeRepo->lastOrders()], 200);
    }

    public function fetchVipTechnicians(HomeRepo $homeRepo)
    {
        return response(['technicians' => $homeRepo->vipTechnicians()], 200);
    }

    public function getNotifications(HomeRepo $homeRepo)
    {
        return response(
            ['notifications' => $homeRepo->notifications(auth()->guard('api')->id())],
            200
        );
    }

    public function seenNotification(Request $request, HomeRepo $homeRepo)
    {
        return response(
            ['message' => $homeRepo->seenNotification($request->notification_id, auth()->guard('api')->id())],
            200
        );
    }

    public function hasUnreadNotification(HomeRepo $homeRepo)
    {
        return response(
            ['new_notifications' => $homeRepo->hasUnreadNotification(auth()->guard('api')->id())],
            200
        );
    }

    public function mobileHome(HomeRepo $homeRepo, ProjectRepo $projectRepo, AddressRepo $addressRepo)
    {
        $top_discounts = $homeRepo->topDiscount();

        $last_discounts = $homeRepo->lastDiscount();

        $proficiencies = $projectRepo->proficiencies();

        $cities = $addressRepo->cities();

        return response([
            'top_discounts' => $top_discounts,
            'last_discounts' => $last_discounts,
            'proficiencies' => $proficiencies,
            'cities' => $cities
        ], 200);
    }

    public function fetchSettings(HomeRepo $homeRepo)
    {
        return response(['settings'=> $homeRepo->settings()], 200);
    }
}
