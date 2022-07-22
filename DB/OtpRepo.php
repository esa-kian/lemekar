<?php

namespace App\DB;

use App\Models\Address;
use App\Models\Client;
use App\Models\InviteCode;
use App\Models\Message;
use App\Models\Otp;
use App\Models\Proficiency;
use App\Models\RegisteredCode;
use App\Models\Technician;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Exception;
use Throwable;

class OtpRepo
{
    public function sentOtp($phone_number)
    {
        return Otp::where('phone_number', $phone_number)
            ->whereNotNull('otp')
            ->whereTime('created_at', '>=', Carbon::now()->subHour(1))
            ->get();
    }

    public function save($code, $phone_number)
    {
        $make_otp = resolve(Otp::class);

        $make_otp->otp = $code;

        $make_otp->phone_number = $phone_number;

        $make_otp->expire_at = Carbon::now()->addMinute(2);

        $make_otp->save();
    }

    public function checkPhoneNumber($phone_number)
    {

        if (Otp::where('phone_number', $phone_number)->first()) {

            return true;
        } else {

            return false;
        }
    }

    public function checkOtp($phone_number, $otp)
    {

        if (Otp::where('phone_number', $phone_number)->where('otp', $otp)->first()) {

            return true;
        } else {

            return false;
        }
    }

    public function checkTime($phone_number, $otp)
    {

        if (Otp::where('phone_number', $phone_number)->where('otp', $otp)->where('expire_at', '>=', Carbon::now())->first()) {

            return true;
        } else {

            return false;
        }
    }

    public function done($phone_number)
    {
        Otp::where('phone_number', $phone_number)->update(['otp' => null]);
    }



    public function checkClientRegistered($phone_number)
    {
        if (Client::where('phone_number', $phone_number)->first()) {

            return true;
        } else {

            return false;
        }
    }

    public function checkTechnicianRegistered($phone_number)
    {
        if (Technician::where('phone_number', $phone_number)->first()) {

            return true;
        } else {

            return false;
        }
    }

    public function fetchUserByPhoneNumber($phone_number)
    {
        $client = $this->fetchClientByPhoneNumber($phone_number);

        $technician = $this->fetchTechnicianByPhoneNumber($phone_number);

        if ($client) {

            return User::where('client_id', $client->id)->first();
        } elseif ($technician) {

            return User::where('technician_id', $technician->id)->first();
        } else {
            return response()->json('کاربری یافت نشد.');
        }
    }

    public function fetchClientByPhoneNumber($phone_number)
    {
        return Client::select('first_name', 'last_name', 'id')->where('phone_number', $phone_number)->first();
    }


    public function fetchTechnicianByPhoneNumber($phone_number)
    {
        return Technician::select('first_name', 'last_name', 'id')->where('phone_number', $phone_number)->first();
    }

    public function clientSignUp($first_name, $last_name, $phone_number)
    {
        $client = $this->createClient($first_name, $last_name, $phone_number);

        return $this->createUser($client, null);
    }

    public function technicianSignUp(
        $first_name,
        $last_name,
        $phone_number,
        $city_id,
        $proficiency_id
    ) {

        $technician = $this->createTechnician($first_name, $last_name, $phone_number, $city_id, $proficiency_id);

        return $this->createUser(null, $technician);
    }

    public function createUser($client, $technician)
    {
        $user = resolve(User::class);

        if ($client != null) {

            $user->client_id  = $client->id;
        } elseif ($technician != null) {

            $user->technician_id  = $technician->id;
        }

        $user->remember_token = Str::random(10);

        // $invite = $this->findInviteCode($invite_code);

        $user->save();

        return $user;
    }

    public function createClient($first_name, $last_name, $phone_number)
    {
        $client = resolve(Client::class);

        $client->first_name = $first_name;

        $client->last_name = $last_name;

        $client->phone_number = $phone_number;

        $client->save();

        return $client;
    }

    public function createTechnician($first_name, $last_name, $phone_number, $city_id, $proficiency_id)
    {
        $technician = resolve(Technician::class);

        $addressRepo = resolve(AddressRepo::class);

        $proficiency = Proficiency::find($proficiency_id);

        $technician->first_name = $first_name;

        $technician->last_name = $last_name;

        $technician->phone_number = $phone_number;

        $technician->city()->associate($addressRepo->getCity($city_id));

        $technician->proficiency()->associate($proficiency);

        $technician->save();

        return $technician;
    }

    public function createInviteCode($user, $type)
    {
        $invite_code = resolve((InviteCode::class));

        $invite_code->user_id = $user->id;

        $invite_code->type = $type;

        $invite_code->code = $this->codeGenerator();

        $invite_code->save();
    }

    public function registerInviteCode($user, $invite_code)
    {
        $register_code = resolve(RegisteredCode::class);

        $register_code->user()->associate($user);

        $register_code->inviteCode()->associate($invite_code);

        $register_code->save();
    }

    public function codeGenerator()
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $charactersLength = strlen($characters);

        $randomString = '';

        for ($i = 0; $i < 6; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    public function findInviteCode($code)
    {
        $invite = InviteCode::where('code', $code)->first();

        InviteCode::find($invite->id);

        return $invite;
    }

    // admin's id = 11
    public function adminConversation($user_id)
    {
        $conversation  = resolve(Message::class);

        $conversation->content = 'سلام به لم کار خوش آمدید. برای ارتباط با پشتیبانی پیام دهید';

        $conversation->conversation_id = $user_id . '_' . 11;

        $conversation->sender_id = 11;

        $conversation->receiver_id  = $user_id;

        $conversation->save();
    }
}
