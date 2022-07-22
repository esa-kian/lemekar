<?php

namespace App\DB;

use App\Http\Resources\DiscountCodeResource;
use App\Http\Resources\ProductResource;
use App\Models\DiscountCode;
use App\Models\Notification;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Technician;
use Illuminate\Support\Facades\DB;
use Throwable;

class HomeRepo
{
    public function popularCats()
    {
        return Project::with('proficiency:id,title')
            ->having('proficiency_id', '>', 0)
            ->groupBy('proficiency_id')
            ->orderBy(DB::raw('COUNT(proficiency_id)'), 'desc')
            ->take(6)->get('proficiency_id');;
    }

    public function vipTechnicians()
    {
        return Technician::where('vip', 1)
            ->leftJoin('users', 'technicians.id', '=', 'users.technician_id')
            ->leftJoin('rates', 'rates.voted_id', '=', 'users.id')
            ->leftJoin('proficiencies', 'technicians.proficiency_id', '=', 'proficiencies.id')
            ->select('technicians.profile_picture', 'technicians.first_name', 'technicians.last_name', 'technicians.id', 'proficiencies.title as proficiency')
            ->selectRaw('ifnull(AVG(rates.vote),0) as rate')
            ->selectRaw('ifnull(COUNT(rates.voted_id),0) as votes')
            ->groupBy('rates.voted_id', 'technicians.id', 'technicians.first_name', 'technicians.last_name', 'proficiencies.title')
            ->get();
    }

    public function lastOrders()
    {
        return Project::with('proficiency:title,id', 'address:id,city_id,town_id', 'address.city:title,id', 'address.town:title,id')
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->get(['id', 'start_at', 'budget', 'address_id', 'proficiency_id']);
    }

    public function notifications($user_id)
    {
        return Notification::select('id', 'title', 'description', 'created_at', 'unread')
            ->where('user_id', $user_id)
            ->latest()->paginate(10);
    }

    public function seenNotification($notification_id, $user_id)
    {
        try {

            return Notification::where('id', $notification_id)
                ->where('user_id', $user_id)
                ->update(['unread' => 0]);
        } catch (Throwable $e) {
            return $e;
        }
    }

    public function hasUnreadNotification($user_id)
    {
        $notifications = Notification::where('user_id', $user_id)
            ->where('unread', 1)
            ->count();

        if ($notifications > 0) {
            return $notifications;
        } else {
            return null;
        }
    }

    public function topDiscount()
    {
        return DiscountCodeResource::collection(DiscountCode::orderBy('percent', 'desc')->with('proficiency')->take(3)->get());
    }

    public function lastDiscount()
    {
        return DiscountCodeResource::collection(DiscountCode::orderBy('created_at', 'desc')->with('proficiency')->take(4)->get());
    }

    public function settings()
    {
        return Setting::all('id', 'title', 'value', 'type');
    }
}