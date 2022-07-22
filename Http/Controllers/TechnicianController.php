<?php

namespace App\Http\Controllers;

use App\DB\TechnicianRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class TechnicianController extends Controller
{

    // public function technicianId()
    // {
    //     $technicianRepo = resolve(TechnicianRepo::class);

    //     return $technicianRepo->getTechnicianId(auth()->guard('api')->id());
    // }

    public function fetch(TechnicianRepo $technicianRepo)
    {
        return response([
            'technician' => $technicianRepo->fetch(auth()->guard('api')->user()->technician_id),
            'skills' => $technicianRepo->fetchSkills(auth()->guard('api')->user()->technician_id),
            'user' => $technicianRepo->fetchEmail(auth()->guard('api')->id())
        ], 200);
    }

    public function account(Request $request, TechnicianRepo $technicianRepo)
    {
        $technician_id = auth()->guard('api')->user()->technician_id;

        if ($request->first_name) {

            $technicianRepo->firstName($technician_id, $request->first_name);
        }

        if ($request->last_name) {

            $technicianRepo->lastName($technician_id, $request->last_name);
        }

        if ($request->birthdate) {

            $technicianRepo->birthdate($technician_id, $request->birthdate);
        }

        if ($request->email) {

            $technicianRepo->email(auth()->guard('api')->id(), $request->email);
        }

        if ($request->gender) {

            $technicianRepo->gender($technician_id, $request->gender);
        }

        // if ($request->phone_number) {

        //     $technicianRepo->phoneNumber($technician_id, $request->phone_number);
        // }

        if ($request->national_id) {

            $technicianRepo->nationalId($technician_id, $request->national_id);
        }

        if ($request->telephone) {

            $technicianRepo->telephone($technician_id, $request->telephone);
        }

        if ($request->address) {

            $technicianRepo->address($technician_id, $request->address);
        }

        if ($request->iban) {

            $technicianRepo->iban($technician_id, $request->iban);
        }


        if ($request->file('photo')) {

            $image = $request->file('photo');

            $imageName = time() . $image->getClientOriginalExtension();

            $image->move(public_path('profile_pictures'), $imageName);

            $technicianRepo->profilePicture($technician_id, '/profile_pictures/' . $imageName);
        }

        $technicianRepo->setSkills($technician_id,   $this->removeBracket($request->skills));

        return response(['message' => 'اطلاعات کاربر با موفقیت ذخیره شد'], 200);
    }

    public function removeBracket($array_with_bracket)
    {
        if ($array_with_bracket != "[]") {

            $array_without_bracket = substr_replace($array_with_bracket, "", -1);

            $array_removed_bracket = substr($array_without_bracket, 1);

            $array_clean = explode(',', $array_removed_bracket);

            return $array_clean;
        }
    }

    public function changeProfilePicture(Request $request, TechnicianRepo $technicianRepo)
    {
        $fileName = time() . '_technician' . auth()->guard('api')->user()->technician_id . '.' . $request->photo->getClientOriginalExtension();

        $request->photo->move(public_path('/profile_pictures/'), $fileName);

        return response(
            ['message' => $technicianRepo->profilePicture(auth()->guard('api')->user()->technician_id, '/profile_pictures/' . $fileName)],
            200
        );
    }

    public function uploadNationalCard(Request $request, TechnicianRepo $technicianRepo)
    {
        $fileName = time() . '_technician' . auth()->guard('api')->user()->technician_id . '.' . $request->national_card->getClientOriginalExtension();

        $request->national_card->move(public_path('/national_cards/'), $fileName);

        return response(
            ['message' => $technicianRepo->nationalCard(auth()->guard('api')->user()->technician_id, '/national_cards/' . $fileName)],
            200
        );
    }

    public function fetchTotalIncome(TechnicianRepo $technicianRepo)
    {
        $weekly = $technicianRepo->weeklyIncome(auth()->guard('api')->user()->technician_id);

        $monthly = $technicianRepo->monthlyIncome(auth()->guard('api')->user()->technician_id);

        $yearly = $technicianRepo->yearlyIncome(auth()->guard('api')->user()->technician_id);

        return ['weekly' => $weekly, 'monthly' => $monthly, 'yearly' => $yearly];
    }

    public function weekly(TechnicianRepo $technicianRepo)
    {
        return $technicianRepo->weeklyIncome(auth()->guard('api')->user()->technician_id);
    }

    public function monthly(TechnicianRepo $technicianRepo)
    {
        return $technicianRepo->monthlyIncome(auth()->guard('api')->user()->technician_id);
    }

    public function yearly(TechnicianRepo $technicianRepo)
    {
        return $technicianRepo->yearlyIncome(auth()->guard('api')->user()->technician_id);
    }

    public function resume(TechnicianRepo $technicianRepo)
    {
        return response(['resume' => $technicianRepo->resume(auth()->guard('api')->user()->technician_id)], 200);
    }

    public function fetchClient($id, TechnicianRepo $technicianRepo)
    {
        return response(['client' => $technicianRepo->client($id)], 200);
    }

    public function commentToProject(Request $request, TechnicianRepo $technicianRepo)
    {

        return  $technicianRepo->comment(
            auth()->guard('api')->id(),
            $request->project_id,
            $request->content
        );
    }

    public function makeBid(Request $request, TechnicianRepo $technicianRepo)
    {
        return response([
            'message' => $technicianRepo->bid(
                auth()->guard('api')->user()->technician_id,
                $request->project_id,
                $request->description,
                $request->amount
            )
        ], 200);
    }
}
