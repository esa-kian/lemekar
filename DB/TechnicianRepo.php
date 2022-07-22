<?php

namespace App\DB;

use App\Http\Resources\ClientAvatarResource;
use App\Http\Resources\ClientProfileResource;
use App\Http\Resources\CommentResource;
use App\Http\Resources\NationalCardResource;
use App\Http\Resources\PhotoResource;
use App\Http\Resources\TechnicianProfileResource;
use App\Http\Resources\TechnicianResource;
use App\Models\BankInformation;
use App\Models\Bid;
use App\Models\Certificate;
use App\Models\Comment;
use App\Models\Income;
use App\Models\Project;
use App\Models\Rate;
use App\Models\Technician;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;
use \Morilog\Jalali\Jalalian;

class TechnicianRepo
{
    public function getTechnicianId($user_id)
    {
        return User::find($user_id);
    }

    public function getUserByTechnicianId($technician_id)
    {
        return User::where('technician_id', $technician_id)->get(['id']);
    }

    public function getTechnician($technician_id)
    {
        return Technician::findOrFail($technician_id);
    }

    public function fetch($technician_id)
    {

        return TechnicianProfileResource::collection(DB::table('technicians')
            ->where('technicians.id', $technician_id)
            ->leftJoin('bank_information', 'bank_information.id', '=', 'technicians.bank_information_id')
            ->select(
                'technicians.id',
                'technicians.first_name',
                'technicians.last_name',
                'technicians.phone_number',
                'technicians.birthdate',
                'technicians.national_id',
                'technicians.telephone',
                'technicians.address',
                'technicians.gender',
                'technicians.bank_information_id',
                'bank_information.iban',
                'technicians.city_id',
                'technicians.proficiency_id',
                'technicians.national_card_picture',
                'technicians.profile_picture',
            )->get());
    }

    public function fetchSkills($technician_id)
    {
        return Technician::findOrFail($technician_id)->skills()->select('title', 'skill_id')->get();
    }

    public function fetchEmail($user_id)
    {
        return User::select('email')->findOrFail($user_id);
    }

    public function profilePicture($technician_id, $photo)
    {
        try {
            Technician::where('id', $technician_id)->update([
                'profile_picture' => $photo
            ]);

            return ClientAvatarResource::collection(Technician::where('id', $technician_id)->get());
        } catch (Throwable $e) {

            return response(['message' => 'خطایی رخ داد. تصویر پروفایل ذخیره نشد', 'error' => $e], 500);
        }
    }

    public function nationalCard($technician_id, $card)
    {
        try {
            Technician::where('id', $technician_id)->update([
                'national_card_picture' => $card
            ]);

            return NationalCardResource::collection(Technician::where('id', $technician_id)->get());
        } catch (Throwable $e) {

            return response(['message' => 'خطایی رخ داد. تصویر پروفایل ذخیره نشد', 'error' => $e], 500);
        }
    }

    public function firstName($technician_id, $first_name)
    {
        try {
            Technician::where('id', $technician_id)->update([
                'first_name' => $first_name
            ]);
        } catch (Throwable $e) {

            return response(['message' => 'خطایی رخ داد. نام ذخیره نشد', 'error' => $e], 500);
        }
    }

    public function lastName($technician_id, $last_name)
    {
        try {
            Technician::where('id', $technician_id)->update([
                'last_name' => $last_name
            ]);
        } catch (Throwable $e) {

            return response(['message' => 'خطایی رخ داد. نام خانوادگی ذخیره نشد', 'error' => $e], 500);
        }
    }

    public function birthdate($technician_id, $birthdate)
    {
        try {
            Technician::where('id', $technician_id)->update([
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

    // public function phoneNumber($technician_id, $phone_number)
    // {
    //     try {

    //         Technician::where('id', $technician_id)->update([
    //             'phone_number' => $phone_number
    //         ]);
    //     } catch (Throwable $e) {

    //         return response(['message' => 'خطایی رخ داد. شماره موبایل ذخیره نشد', 'error' => $e], 500);
    //     }
    // }

    public function nationalId($technician_id, $national_id)
    {
        try {

            Technician::where('id', $technician_id)->update([
                'national_id' => $national_id
            ]);
        } catch (Throwable $e) {

            return response(['message' => 'خطایی رخ داد. کد ملی ذخیره نشد', 'error' => $e], 500);
        }
    }

    public function telephone($technician_id, $telephone)
    {
        try {

            Technician::where('id', $technician_id)->update([
                'telephone' => $telephone
            ]);
        } catch (Throwable $e) {

            return response(['message' => 'خطایی رخ داد. شماره ثابت ذخیره نشد', 'error' => $e], 500);
        }
    }

    public function address($technician_id, $address)
    {
        try {

            Technician::where('id', $technician_id)->update([
                'address' => $address
            ]);
        } catch (Throwable $e) {

            return response(['message' => 'خطایی رخ داد. آدرس ذخیره نشد', 'error' => $e], 500);
        }
    }

    public function gender($technician_id, $gender)
    {
        try {
            Technician::where('id', $technician_id)->update([
                'gender' => $gender
            ]);
        } catch (Throwable $e) {

            return response(['message' => 'خطایی رخ داد. جنیست ذخیره نشد', 'error' => $e], 500);
        }
    }

    public function nationalCardPicture($technician_id, $image)
    {
        try {
            Technician::where('id', $technician_id)->update([
                'national_card_picture' => $image
            ]);
        } catch (Throwable $e) {

            return response(['message' => 'خطایی رخ داد. تصویر کارت ملی ذخیره نشد', 'error' => $e], 500);
        }
    }

    public function iban($technician_id, $iban)
    {
        try {
            $technician = Technician::find($technician_id);

            $bank_information = resolve(BankInformation::class);

            $bank_information->iban = $iban;

            $bank_information->save();

            $technician->bank_information_id = $bank_information->id;

            $technician->save();
        } catch (Throwable $e) {

            return response(['message' => 'خطایی رخ داد.شماره شبا ذخیره نشد', 'error' => $e], 500);
        }
    }

    public function setSkills($technician_id, $skills)
    {
        $technician = Technician::find($technician_id);

        try {
            $technician->skills()->detach();

            if ($skills) {

                foreach ($skills as $skill) {

                    $technician->skills()->attach($skill);
                }
            }
        } catch (Exception $e) {

            if ($skills) {

                foreach ($skills as $skill) {

                    $technician->skills()->attach($skill);
                }
            }
        }
    }

    public function weeklyIncome($technician_id)
    {
        $report = [];

        $date = Carbon::now();

        $count = 0;

        for ($i = 0; $i <= $date->dayOfWeek + 2; $i++) {

            $report[$i] =  Income::where('technician_id', $technician_id)
                ->whereDate('created_at', '=', Carbon::now()->subDays($i))
                ->sum('amount');

            $count += Project::where('technician_id', $technician_id)
                ->where('technician_status', 'done')
                ->whereDate('updated_at', '=', Carbon::now()->subDays($i))
                ->get()
                ->count();
        }

        $sum = 0;

        foreach ($report as $r) {
            $sum += $r;
        }

        return ['report' => $report, 'count' => $count, 'total' => $sum];
    }

    public function monthlyIncome($technician_id)
    {
        $report = [];

        $count = 0;

        $date = \Morilog\Jalali\Jalalian::now();

        for ($i = 0; $i <= $date->getDay() - 1; $i++) {

            $report[$i] =  Income::where('technician_id', $technician_id)
                ->whereDate('created_at', '=', Carbon::now()->subDays($i))
                ->sum('amount');

            $count += Project::where('technician_id', $technician_id)
                ->where('technician_status', 'done')
                ->whereDate('updated_at', '=', Carbon::now()->subDays($i))
                ->get()
                ->count();
        }

        $sum = 0;

        foreach ($report as $r) {
            $sum += $r;
        }

        return ['report' => $report, 'count' => $count, 'total' => $sum];
    }

    public function yearlyIncome($technician_id)
    {
        $report = [];

        $count = 0;

        $date = \Morilog\Jalali\Jalalian::now();

        for ($i  = 0; $i <= $date->getMonth() - 2; $i++) {

            $report[$i] =  Income::where('technician_id', $technician_id)
                ->whereBetween('created_at', [Carbon::now()->subMonth($i)->firstOfMonth(), Carbon::now()->subMonth($i)->lastOfMonth()])
                ->sum('amount');

            $count += Project::where('technician_id', $technician_id)
                ->where('technician_status', 'done')
                ->whereBetween('created_at', [Carbon::now()->subMonth($i)->firstOfMonth(), Carbon::now()->subMonth($i)->lastOfMonth()])
                ->get()
                ->count();
        }

        $sum = 0;

        foreach ($report as $r) {
            $sum += $r;
        }

        return ['report' => $report, 'count' => $count, 'total' => $sum];
    }

    public function resume($technician_id)
    {
        $technician = $this->detail($technician_id);

        $projects = $this->projectCount($technician_id);

        $technician_skill = $this->skills($technician_id);

        $comments = $this->comments($technician_id);

        $certificates = $this->certificates($technician_id);

        $photos = $this->photos($technician_id);

        return [
            'technician' => $technician,
            'projects' => $projects,
            'skills' => $technician_skill,
            'comments' => $comments,
            'certificates' => $certificates,
            'photos' => $photos
        ];
    }

    public function detail($technician_id)
    {
        return TechnicianResource::collection(DB::table('technicians')
            ->leftJoin('projects', 'technicians.id', '=', 'projects.technician_id')
            ->leftJoin('users', 'technicians.id', '=', 'users.technician_id')
            ->leftJoin('proficiencies', 'technicians.proficiency_id', '=', 'proficiencies.id')
            ->leftJoin('rates', 'rates.voted_id', '=', 'users.id')
            ->select('technicians.id', 'technicians.profile_picture',  'technicians.first_name', 'technicians.last_name', 'technicians.created_at', 'proficiencies.title as proficiency')
            ->selectRaw('ifnull(AVG(rates.vote),0) as rate')
            ->selectRaw('COUNT(rates.voted_id) as votes')
            ->where('technicians.id', $technician_id)
            ->groupBy('rates.voted_id', 'technicians.id', 'technicians.first_name', 'technicians.last_name', 'technicians.created_at', 'proficiencies.title')
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

    public function photos($technician_id)
    {
        return PhotoResource::collection(DB::table('technicians')
            ->leftJoin('projects', 'technicians.id', '=', 'projects.technician_id')
            ->leftJoin('photos', 'projects.id', '=', 'photos.project_id')
            ->select('photos.id', 'photos.url')
            ->where('technicians.id', $technician_id)
            ->take(12)->get());
    }

    public function client($client_id)
    {

        $detail = ClientProfileResource::collection(DB::table('clients')
            ->where('clients.id', $client_id)
            ->leftJoin('users', 'users.client_id', '=', 'clients.id')
            ->leftJoin('rates', 'rates.voted_id', '=', 'users.id')
            ->leftJoin('projects', 'projects.client_id', '=', 'clients.id')
            ->select('clients.id', 'clients.profile_picture', 'clients.first_name', 'clients.last_name', 'clients.created_at')
            ->selectRaw('ifnull(AVG(rates.vote), 0) as rate')
            ->selectRaw('count(rates.voted_id) as votes')
            ->get());

        $comments = CommentResource::collection(Comment::select('comments.content', 'comments.created_at', 'comments.id', 'technicians.profile_picture', 'technicians.first_name', 'technicians.last_name')
            ->leftJoin('clients', 'clients.id', '=', 'comments.client_id')
            ->leftJoin('users', 'users.id', '=', 'comments.user_id')
            ->leftJoin('technicians', 'technicians.id', '=', 'users.technician_id')
            ->where('comments.client_id', $client_id)
            ->get());

        $user = User::where('client_id', $client_id)->get(['id']);

        foreach ($user as $u) {

            $votes = Rate::where('voted_id', $u->id)->count();
        }
        $projects = Project::where('client_id', $client_id)->count();

        return [
            'detail' => $detail,
            'projects' => $projects,
            'comments' => $comments,
            'votes' => $votes
        ];
    }

    public function comment($user_id, $project_id, $content)
    {
        try {

            $comment = resolve(Comment::class);

            $comment->user_id = $user_id;

            $comment->project_id = $project_id;

            $comment->content = $content;

            $comment->save();

            return response(['message' => 'نظر شما ثبت شد'], 200);
        } catch (Throwable $e) {

            return response(['message' => 'متن نظر نمیتوان خالی باشد'], 400);
        }
    }

    public function bid($technician_id, $project_id, $description, $amount)
    {

        if ($this->checkHasBid($technician_id, $project_id)) {

            $bid = resolve(Bid::class);

            $bid->technician_id = $technician_id;

            $bid->project_id = $project_id;

            $bid->description = $description;

            $bid->amount = $amount;

            $bid->status = 'pending';

            $bid->save();

            return 'پیشنهاد شما ثبت شد';
        } else {

            return 'شما قبلا پیشنهاد داده اید';
        }
    }

    public function checkHasBid($technician_id, $project_id)
    {
        $bid = Bid::where('technician_id', $technician_id)
            ->where('project_id', $project_id)
            ->get();

        if (count($bid) > 0) {

            return false;
        } else {

            return true;
        }
    }

    public function income($technician_id, $project_id, $amount, $type)
    {
        try {

            $income  = resolve(Income::class);

            $income->technician_id = $technician_id;

            $income->project_id = $project_id;

            $income->amount = $amount;

            $income->type = $type;

            $income->save();

            return 'تراکنش با موفقیت انجام شد';
        } catch (Throwable $e) {

            return ['error' => 'تراکنش ناموفق' . $e];
        }
    }
}
