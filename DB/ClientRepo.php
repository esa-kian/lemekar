<?php

namespace App\DB;

use App\Http\Resources\ClientAvatarResource;
use App\Http\Resources\ClientDataResource;
use App\Http\Resources\CommentResource;
use App\Http\Resources\TechnicianResource;
use App\Models\BankInformation;
use App\Models\Certificate;
use App\Models\Client;
use App\Models\Comment;
use App\Models\InviteCode;
use App\Models\Project;
use App\Models\Technician;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WithdrawRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ClientRepo
{
    public function getClientId($user_id)
    {
        return User::find($user_id);
    }

    public function fetch($client_id)
    {
        return ClientDataResource::collection(Client::with('bankInformation')->where('id', $client_id)->get());
    }

    public function getClient($client_id)
    {
        return Client::findOrFail($client_id);
    }

    public function fetchEmail($user_id)
    {
        return User::select('email')->findOrFail($user_id);
    }

    public function profilePicture($client_id, $photo)
    {
        try {
            Client::where('id', $client_id)->update([
                'profile_picture' => $photo
            ]);

            return ClientAvatarResource::collection(Client::where('id', $client_id)->get());
        } catch (Throwable $e) {

            return response(['message' => 'خطایی رخ داد. تصویر پروفایل ذخیره نشد', 'error' => $e], 500);
        }
    }

    public function firstName($client_id, $first_name)
    {
        try {
            Client::where('id', $client_id)->update([
                'first_name' => $first_name
            ]);
        } catch (Throwable $e) {

            return response(['message' => 'خطایی رخ داد. نام ذخیره نشد', 'error' => $e], 500);
        }
    }

    public function lastName($client_id, $last_name)
    {
        try {
            Client::where('id', $client_id)->update([
                'last_name' => $last_name
            ]);
        } catch (Throwable $e) {

            return response(['message' => 'خطایی رخ داد. نام خانوادگی ذخیره نشد', 'error' => $e], 500);
        }
    }

    public function birthdate($client_id, $birthdate)
    {
        try {
            Client::where('id', $client_id)->update([
                'birthdate' => $birthdate
            ]);
        } catch (Throwable $e) {

            return response(['message' => 'خطایی رخ داد. تاریخ تولد ذخیره نشد', 'error' => $e], 500);
        }
    }

    public function email($user_id, $email)
    {
        try {
            User::where('id',  $user_id)->update([
                'email' => $email
            ]);
        } catch (Throwable $e) {

            return response(['message' => 'خطایی رخ داد. ایمیل ذخیره نشد', 'error' => $e], 500);
        }
    }

    // public function phoneNumber($client_id, $phone_number)
    // {
    //     try {

    //         Client::where('id', $client_id)->update([
    //             'phone_number' => $phone_number
    //         ]);
    //     } catch (Throwable $e) {

    //         return response(['message' => 'خطایی رخ داد. شماره موبایل ذخیره نشد', 'error' => $e], 500);
    //     }
    // }

    public function iban($client_id, $iban)
    {
        try {
            $client = Client::find($client_id);

            $bank_information = resolve(BankInformation::class);

            $bank_information->iban = $iban;

            $bank_information->save();

            $client->bank_information_id = $bank_information->id;

            $client->save();
        } catch (Throwable $e) {

            return response(['message' => 'خطایی رخ داد.شماره شبا ذخیره نشد', 'error' => $e], 500);
        }
    }

    public function gender($client_id, $gender)
    {
        try {
            Client::where('id', $client_id)->update([
                'gender' => $gender
            ]);
        } catch (Throwable $e) {

            return response(['message' => 'خطایی رخ داد. جنیست ذخیره نشد', 'error' => $e], 500);
        }
    }

    public function transactions($client_id)
    {
        try {
            return Transaction::where('client_id', $client_id)
                ->orderBy('created_at', 'desc')
                ->get(['id', 'amount', 'type', 'created_at']);
        } catch (Throwable $e) {

            return response(['message' => 'خطایی رخ داد. تراکنشی یافت نشد', 'error' => $e], 500);
        }
    }

    public function withdraw($client_id, $amount)
    {
        if ($this->getClient($client_id)->bank_information_id) {

            try {
                $withdraw = resolve(WithdrawRequest::class);

                $withdraw->client_id = $client_id;

                $withdraw->amount  = $amount;

                $withdraw->type = 'pending';

                $withdraw->save();

                return 'درخواست برداشت ثبت شد';
            } catch (Throwable $e) {

                return ['error' => 'درخواست ثبت نشد' . $e];
            }
        } else {
            return 'لطفا شماره شبا خود را ثبت کنید';
        }
    }
    public function transaction($client_id, $amount, $type)
    {
        try {

            $transaction  = resolve(Transaction::class);

            $transaction->client_id = $client_id;

            $transaction->amount = $amount;

            $transaction->type = $type;

            $transaction->save();

            return 'تراکنش با موفقیت انجام شد';
        } catch (Throwable $e) {

            return ['error' => 'تراکنش ناموفق' . $e];
        }
    }

    public function balance($client_id)
    {
        $deposit = $this->sumOfTransactionByType($client_id, 'deposit');

        $tip = $this->sumOfTransactionByType($client_id, 'tip');

        $payment = $this->sumOfTransactionByType($client_id, 'payment');

        $credit = $this->sumOfTransactionByType($client_id, 'credit');

        $withdraw = $this->sumOfTransactionByType($client_id, 'withdraw');

        return $deposit - $tip - $payment + $credit - $withdraw;
    }

    public function sumOfTransactionByType($client_id, $type)
    {
        return Transaction::where('client_id', $client_id)->where('type', $type)->sum('amount');
    }

    public function saveTechnician($client_id, $technician_id)
    {
        $technician = Technician::findOrFail($technician_id);
        $client = Client::findOrFail($client_id);

        $client->technicians()->attach($technician);
    }

    public function unsaveTechnician($client_id, $technician_id)
    {
        $technician = Technician::findOrFail($technician_id);
        $client = Client::findOrFail($client_id);

        $client->technicians()->detach($technician);
    }

    public function technicians($client_id)
    {
        try {
            return TechnicianResource::collection(DB::table('clients')
                ->leftJoin('client_technician', 'clients.id', '=', 'client_technician.client_id')
                ->leftJoin('technicians', 'technicians.id', '=', 'client_technician.technician_id')
                ->leftJoin('users', 'technicians.id', '=', 'users.technician_id')
                ->leftJoin('proficiencies', 'technicians.proficiency_id', '=', 'proficiencies.id')
                ->leftJoin('rates', 'rates.voted_id', '=', 'users.id')
                ->select('technicians.id', 'technicians.profile_picture', 'technicians.first_name', 'technicians.last_name', 'technicians.created_at', 'proficiencies.title as proficiency')
                ->selectRaw('AVG(rates.vote) as rate')
                ->selectRaw('COUNT(rates.voted_id) as votes')
                ->where('clients.id', $client_id)
                ->groupBy('rates.voted_id', 'technicians.id', 'technicians.first_name', 'technicians.last_name', 'proficiencies.title')
                ->get());
        } catch (Throwable $e) {
            return response(['message' => 'خطایی رخ داد. متخصصی یافت نشد', 'error' => $e], 500);
        }
    }

    public function getInviteCode($user_id)
    {
        return InviteCode::select('code')->where('user_id', $user_id)->get();
    }

    public function getUserByClientId($client_id)
    {
        return User::where('client_id', $client_id)->get(['id']);
    }

    public function technician($technician_id)
    {
        $technicianRepo = resolve(TechnicianRepo::class);

        $technician = $this->detail($technician_id);

        $photos = $technicianRepo->photos($technician_id);

        $projects = $this->projectCount($technician_id);

        $technician_skill = $this->skills($technician_id);

        $comments = $this->comments($technician_id);

        $certificates = $this->certificates($technician_id);

        return [
            'detail' => $technician,
            'photos' => $photos,
            'projects' => $projects,
            'skills' => $technician_skill,
            'comments' => $comments,
            'certificates' => $certificates
        ];
    }

    public function detail($technician_id)
    {
        return TechnicianResource::collection(DB::table('technicians')
            ->leftJoin('projects', 'technicians.id', '=', 'projects.technician_id')
            ->leftJoin('users', 'technicians.id', '=', 'users.technician_id')
            ->leftJoin('proficiencies', 'technicians.proficiency_id', '=', 'proficiencies.id')
            ->leftJoin('rates', 'rates.voted_id', '=', 'users.id')
            ->select('technicians.id', 'technicians.profile_picture', 'technicians.first_name', 'technicians.last_name', 'technicians.created_at', 'proficiencies.title as proficiency')
            ->selectRaw('ifnull(AVG(rates.vote),0) as rate')
            ->selectRaw('COUNT(rates.voted_id) as votes')
            ->where('technicians.id', $technician_id)
            ->get());
    }

    public function projectCount($technician_id)
    {
        return Project::where('technician_id', $technician_id)
            ->where('technician_status', 'done')
            ->count();
    }

    public function skills($technician_id)
    {
        return Technician::find($technician_id)
            ->skills()
            ->select('title', 'skill_id')
            ->get();
    }

    public function comments($technician_id)
    {
        return CommentResource::collection(Comment::select('comments.content', 'comments.created_at', 'comments.id', 'clients.profile_picture', 'clients.first_name', 'clients.last_name')
            ->leftJoin('technicians', 'technicians.id', '=', 'comments.technician_id')
            ->leftJoin('users', 'users.id', '=', 'comments.user_id')
            ->leftJoin('clients', 'clients.id', '=', 'users.client_id')
            ->where('comments.technician_id', $technician_id)
            ->get());
    }

    public function certificates($technician_id)
    {
        return  Certificate::where('technician_id', $technician_id)->get(['title', 'id']);
    }
}
