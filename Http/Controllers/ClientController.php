<?php

namespace App\Http\Controllers;

use App\DB\ClientRepo;
use Illuminate\Http\Request;
use Throwable;

class ClientController extends Controller
{

    // public function clientId()
    // {
    //     $clientRepo = resolve(ClientRepo::class);

    //     return $clientRepo->getClientId(auth()->guard('api')->id());
    // }

    public function fetch(ClientRepo $clientRepo)
    {
        return response([
            'client' => $clientRepo->fetch(auth()->guard('api')->user()->client_id),
            'user' => $clientRepo->fetchEmail(auth()->guard('api')->id())
        ], 200);
    }

    public function account(Request $request, ClientRepo $clientRepo)
    {
        $client_id = auth()->guard('api')->user()->client_id;

        if ($request->first_name) {

            $clientRepo->firstName($client_id, $request->first_name);
        }

        if ($request->last_name) {

            $clientRepo->lastName($client_id, $request->last_name);
        }

        if ($request->birthdate) {

            $clientRepo->birthdate($client_id, $request->birthdate);
        }

        if ($request->email) {

            $clientRepo->email(auth()->guard('api')->id(), $request->email);
        }

        if ($request->gender) {

            $clientRepo->gender($client_id, $request->gender);
        }

        if ($request->iban) {

            $clientRepo->iban($client_id, $request->iban);
        }
        return response(['message' => 'اطلاعات کاربر با موفقیت ذخیره شد'], 200);
    }

    public function changeProfilePicture(Request $request, ClientRepo $clientRepo)
    {
        $fileName = time() . '_client' . auth()->guard('api')->user()->client_id . '.' . $request->photo->getClientOriginalExtension();

        $request->photo->move(public_path('/profile_pictures/'), $fileName);

        return response(
            ['profile_picture' => $clientRepo->profilePicture(auth()->guard('api')->user()->client_id, '/profile_pictures/' . $fileName)],
            200
        );
    }

    public function fetchTransactions(ClientRepo $clientRepo)
    {
        $client_id = auth()->guard('api')->user()->client_id;

        return response([
            'transactions' => $clientRepo->transactions($client_id),
            'balance' => $clientRepo->balance($client_id)
        ], 200);
    }

    public function fetchSavedTechnicians(ClientRepo $clientRepo)
    {
        return response(
            ['technicians' => $clientRepo->technicians(auth()->guard('api')->user()->client_id)],
            200
        );
    }

    public function saveTechnician($id, ClientRepo $clientRepo)
    {
        try {

            $clientRepo->saveTechnician(auth()->guard('api')->user()->client_id, $id);

            return response(['message' => 'متخصص با موفقیت ذخیره شد'], 200);
        } catch (Throwable $e) {

            return response(['message' => 'خطایی رخ داد. متخصص ذخیره نشد', 'error' => $e], 500);
        }
    }

    public function unsaveTechnician($id, ClientRepo $clientRepo)
    {
        try {
            $clientRepo->unsaveTechnician(auth()->guard('api')->user()->client_id, $id);

            return response(['message' => 'متخصص با موفقیت حذف شد'], 200);
        } catch (Throwable $e) {

            return response(['message' => 'خطایی رخ داد. متخصص حذف نشد', 'error' => $e], 500);
        }
    }

    public function balance(ClientRepo $clientRepo)
    {
        return response(
            ['balance' => $clientRepo->balance(auth()->guard('api')->user()->client_id)],
            200
        );
    }

    public function deposit(Request $request, ClientRepo $clientRepo)
    {
        return response([
            'message' => $clientRepo->transaction(
                auth()->guard('api')->user()->client_id,
                $request->amount,
                'deposit'
            )
        ], 200);
    }

    public function withdraw(Request $request, ClientRepo $clientRepo)
    {
        $balance = $clientRepo->balance(auth()->guard('api')->user()->client_id);
        if ($balance > $request->amount) {

            if ($request->amount >= 100000) {

                return response([
                    'message' => $clientRepo->withdraw(
                        auth()->guard('api')->user()->client_id,
                        $request->amount
                    )
                ], 200);
            } else {
                return response([
                    'message' => 'حداقل موجودی قابل برداشت ۱۰۰،۰۰ تومان میباشد'

                ], 400);
            }
        } else {
            return response([
                'message' => 'موجودی شما کمتر از مقدار درخواستی است'

            ], 400);
        }
    }

    public function inviteCode(ClientRepo $clientRepo)
    {
        return response(['invite_code' => $clientRepo->getInviteCode(auth()->guard('api')->id())], 200);
    }

    public function fetchTechnician($id, ClientRepo $clientRepo)
    {
        return response(['technician' => $clientRepo->technician($id)], 200);
    }
}
