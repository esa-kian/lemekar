<?php

namespace App\Http\Controllers;

use App\DB\OtpRepo;
use App\DB\ReferralRepo;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class OtpController extends Controller
{
    public function makeOtp()
    {
        return rand(100000, 999999);
    }

    public function sendOtp(Request $request, OtpRepo $otpRepo)
    {
        $sent_otp  = $otpRepo->sentOtp($request->phone_number);

        if (count($sent_otp) < 5) {

            $code = $this->makeOtp();

            // kavenegar sms panel 
            // $sender = "1000596446";
            // $receptor = $request->phone_number;
            // $message = 'کد تایید شما برای ورود به لم کار: ' . $code;
            // $api = new \Kavenegar\KavenegarApi(env('KAVENEGAR'));
            // $api->Send($sender, $receptor, $message);
            // end of kavenegar sms panel 

            $otpRepo->save($code, $request->phone_number);

            return response(['message' => 'کد یکبار مصرف ارسال شد' . $code], 200);
        } else {

            return response(['message' => 'تا یک ساعت آینده امکان ارسال کد برای شما نیست'], 403);
        }
    }

    public function checkOtp(Request $request, OtpRepo $otpRepo)
    {


        $checkPhoneNumber = $otpRepo->checkPhoneNumber($request->phone_number);

        if ($checkPhoneNumber) {

            $checkOtp = $otpRepo->checkOtp($request->phone_number, $request->otp);

            if ($checkOtp) {

                $checkTime = $otpRepo->checkTime($request->phone_number, $request->otp);
                if ($checkTime) {

                    $otpRepo->done($request->phone_number);

                    if ($this->registeredUser($request->phone_number, $request->type)) {

                        return $this->login($request->phone_number, $request->type);
                    }

                    return response(['isAuth' => false, 'message' => 'کد تایید شد'], 200);
                } else {

                    return response(['message' => 'کد منقضی شده لطفا دوباره تلاش کنید'], 403);
                }
            } else {

                return response(['message' => 'کد وارد شده اشتباه است'], 404);
            }
        } else {

            return response(['message' => 'با این شماره درخواستی ارسال نشده'], 404);
        }
    }

    public function login($phone_number, $type)
    {
        $otpRepo = resolve(OtpRepo::class);

        $user = $otpRepo->fetchUserByPhoneNumber($phone_number);

        $resp = $this->setToken($user);

        if ($type == 1) {

            $name = $otpRepo->fetchClientByPhoneNumber($phone_number);
        } elseif ($type == 2) {

            $name = $otpRepo->fetchTechnicianByPhoneNumber($phone_number);
        }

        Auth::login($user, true);

        return response(['isAuth' => true, 'token' => $resp, 'name' => $name], 200);
    }

    public function logout(Request $request)
    {
        $token = $request->user()->token();

        $token->revoke();

        return response(['message' => 'شما با موفقت خارج شدید'], 200);
    }

    public function register(Request $request, OtpRepo $otpRepo, ReferralRepo $referralRepo)
    {
        if ($request->invite_code == null) {

            $invite_code = null;
        } else {

            try {

                $invite_code = $otpRepo->findInviteCode($request->invite_code);
            } catch (Throwable $e) {

                return response(['message' => 'کد دعوت نامعتبر می باشد'], 404);
            }
        }

        // type == 1 is client , type == 2 is technician 
        if ($request->type == 1) {

            $user = $otpRepo->clientSignUp(
                $request->first_name,
                $request->last_name,
                $request->phone_number
            );

            $type = 'client';

            $otpRepo->createInviteCode($user, $type);

            $name = $otpRepo->fetchClientByPhoneNumber($request->phone_number);
        } elseif ($request->type == 2) {

            $user = $otpRepo->technicianSignUp(
                $request->first_name,
                $request->last_name,
                $request->phone_number,
                $request->city_id,
                $request->proficiency_id
            );

            $type = 'technician';

            $otpRepo->createInviteCode($user, $type);

            $name = $otpRepo->fetchTechnicianByPhoneNumber($request->phone_number);
        }

        $otpRepo->registerInviteCode($user, $invite_code);

        $referralRepo->technicianRegistered($invite_code, $user);

        $resp = $this->setToken($user);

        Auth::login($user, true);

        $otpRepo->adminConversation($user->id);

        return response(['isAuth' => true, 'token' => $resp, 'name' => $name], 200);
    }



    // if user registered before return true
    public function registeredUser($phone_number, $type)
    {
        $otpRepo = resolve(OtpRepo::class);

        // type == 1 is client , type == 2 is technician

        if ($type == 1) {
            return $otpRepo->checkClientRegistered($phone_number);
        }
        if ($type == 2) {
            return $otpRepo->checkTechnicianRegistered($phone_number);
        }
    }


    public function setToken($user)
    {
        $token = $user->createToken('LEMEKAR')->accessToken;

        // $user->api_token = $token;

        $user->save();

        return $token;
    }
}
