<?php

namespace App\DB;

use App\Http\Resources\NotificationResource;
use App\Http\Resources\UserResource;
use App\Models\Bid;
use App\Models\Certificate;
use App\Models\City;
use App\Models\Client;
use App\Models\Comment;
use App\Models\DiscountCode;
use App\Models\Income;
use App\Models\Product;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Skill;
use App\Models\Technician;
use App\Models\Town;
use App\Models\Notification;
use App\Models\User;
use App\Models\WithdrawRequest;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AdminRepo
{
    public function incomesMonthly()
    {
        $report = [];

        $date = \Morilog\Jalali\Jalalian::now();

        for ($i = 0; $i <= $date->getDay() - 1; $i++) {

            $report[$i] =  Income::whereDate('created_at', '=', Carbon::now()->subDays($i))
                ->sum('amount');
        }
        $sum = 0;

        foreach ($report as $r) {
            $sum += $r;
        }

        return ['report' => array_reverse($report), 'total' => $sum];
    }

    public function incomesYearly()
    {
        $report = [];

        $date = \Morilog\Jalali\Jalalian::now();

        for ($i  = 0; $i <= $date->getMonth() - 1; $i++) {

            $report[$i] =  Income::whereBetween('created_at', [Carbon::now()->subMonth($i)->firstOfMonth(), Carbon::now()->subMonth($i)->lastOfMonth()])
                ->sum('amount');
        }

        $sum = 0;

        foreach ($report as $r) {
            $sum += $r;
        }

        return ['report' => array_reverse($report),  'total' => $sum];
    }

    public function incomesTotal()
    {
        $report = [];

        $last = Income::latest('id')->first();

        for ($i  = 1; $i <= $last->id; $i++) {
            $report[$i - 1] =  Income::where('id', $i)->sum('amount');
        }

        $sum = 0;

        foreach ($report as $r) {
            $sum += $r;
        }

        return ['report' => $report,  'total' => $sum];
    }

    public function paymentsMonthly()
    {
        $report = [];

        $date = \Morilog\Jalali\Jalalian::now();

        for ($i = 0; $i <= $date->getDay() - 1; $i++) {

            $report[$i] =  WithdrawRequest::where('type', 'done')->whereDate('created_at', '=', Carbon::now()->subDays($i))
                ->sum('amount');
        }
        $sum = 0;

        foreach ($report as $r) {
            $sum += $r;
        }

        return ['report' => array_reverse($report), 'total' => $sum];
    }

    public function paymentsYearly()
    {
        $report = [];

        $date = \Morilog\Jalali\Jalalian::now();

        for ($i  = 0; $i <= $date->getMonth() - 1; $i++) {

            $report[$i] =  WithdrawRequest::where('type', 'done')->whereBetween('created_at', [Carbon::now()->subMonth($i)->firstOfMonth(), Carbon::now()->subMonth($i)->lastOfMonth()])
                ->sum('amount');
        }

        $sum = 0;

        foreach ($report as $r) {
            $sum += $r;
        }

        return ['report' => array_reverse($report),  'total' => $sum];
    }

    public function paymentsTotal()
    {
        $report = [];

        $last = WithdrawRequest::latest('id')->first();

        for ($i  = 1; $i <= $last->id; $i++) {
            $report[$i - 1] =  WithdrawRequest::where('type', 'done')->where('id', $i)->sum('amount');
        }

        $sum = 0;

        foreach ($report as $r) {
            $sum += $r;
        }

        return ['report' => $report,  'total' => $sum];
    }

    public function notVerifiedTechnicians()
    {
        return Technician::where('verified', 0)
            ->with('proficiency:title,id', 'city:title,id')->get();
    }

    public function verifiedTechnicians()
    {
        return Technician::where('verified', 1)
            ->with('proficiency:title,id', 'city:title,id')->get();
    }

    public function technician($technician_id)
    {
        return [
            'details' => Technician::where('id', $technician_id)->with('bankInformation:iban,id', 'proficiency:title,id', 'city:title,id')->get(),
            'skills' => $this->skillsOfTechnician($technician_id)
        ];
    }

    public function skillsOfTechnician($technician_id)
    {
        return Technician::findOrFail($technician_id)->skills;
    }

    public function verifyTechnician($technician_id, $verify)
    {
        Technician::where('id', $technician_id)->update(['verified' => $verify]);

        if ($verify == 1) {
            return 'متخصص با موفقیت فعال شد';
        } else {
            return 'متخصص با موفقیت غیرفعال شد';
        }
    }

    public function vipTechnician($technician_id, $vip)
    {
        Technician::where('id', $technician_id)->update(['vip' => $vip]);

        $technician = Technician::select('first_name', 'last_name')->where('id', $technician_id)->get();

        if ($vip == 1) {
            foreach ($technician as $t) {
                return $t->first_name . ' ' . $t->last_name . ' با موفقیت vip شد';
            }
        } else {
            foreach ($technician as $t) {
                return $t->first_name . ' ' . $t->last_name .  ' با موفقیت عادی شد';
            }
        }
    }

    public function certificates()
    {
        return DB::table('certificates')
            ->leftJoin('technicians', 'certificates.technician_id', '=', 'technicians.id')
            ->select('technicians.first_name', 'technicians.last_name', 'certificates.id', 'certificates.title', 'certificates.attachment', 'certificates.created_at')
            ->orderBy('certificates.created_at')
            ->get();
    }

    public function newCertificate($attachment, $title, $technician_id)
    {
        $certificate = resolve(Certificate::class);

        $certificate->attachment = $attachment;

        $certificate->title = $title;

        $certificate->technician_id = $technician_id;

        $certificate->save();

        return $certificate->title;
    }

    public function deleteCertificate($certificate_id)
    {
        $certificate = Certificate::find($certificate_id);

        $message = $certificate->title;

        $certificate->delete();

        return ' مدرک ' . $message . ' حذف شد';
    }

    public function incomes()
    {
        return DB::table('technicians')
            ->leftJoin('incomes', 'incomes.technician_id', '=', 'technicians.id')
            ->select('technicians.first_name', 'technicians.last_name', 'technicians.id')
            ->selectRaw('SUM(incomes.amount) as income')
            ->groupBy('technicians.id')
            ->orderBy('income', 'desc')
            ->get();
    }

    public function incomesDetail($technician_id)
    {
        return Income::where('technician_id', $technician_id)->with('project', 'project.client')->get();
    }

    public function paidIncome($income_id)
    {
        Income::where('id', $income_id)->update(['paid' => 1]);

        $income = Income::find($income_id);

        return ' درآمد با شناسه ' . $income->id . ' پرداخت شد ';
    }

    public function allTechnicians()
    {
        return Technician::with('proficiency:title,id', 'city:title,id')->get();
    }

    public function allClients()
    {
        return Client::all();
    }

    public function client($client_id)
    {
        return  Client::where('id', $client_id)
            ->with('bankInformation:iban,id', 'addresses', 'addresses.city:id,title', 'addresses.town:id,title')
            ->get();
    }

    public function balances()
    {
        $clientRepo = resolve(ClientRepo::class);

        $clients = $this->allClients();

        $clients_balance = [];

        foreach ($clients as $client) {

            $clients_balance[] = ['detail' => $client, 'balance' => $clientRepo->balance($client->id)];
        }

        return $clients_balance;
    }

    public function withdrawRequests()
    {
        return WithdrawRequest::with('client', 'client.bankInformation')->orderBy('type')->get();
    }

    public function withdrawStatus($withdraw_id, $status)
    {
        WithdrawRequest::findOrFail($withdraw_id)->update(['type' => $status]);
    }

    public function projects($type, $status)
    {
        return Project::with('proficiency', 'address', 'address.city', 'address.town')
            ->where('client_status', $status)
            ->where('vip', $type)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function project($project_id)
    {
        return Project::where('id', $project_id)
            ->with(
                'proficiency',
                'skills',
                'address',
                'address.city',
                'address.town',
                'client',
                'technician',
                'products',
                'bids.technician'
            )->get();
    }

    public function photos($project_id)
    {
        return DB::table('projects')
            ->leftJoin('photos', 'photos.project_id', '=', 'projects.id')
            ->select('photos.id', 'photos.url', 'photos.uploaded_by')
            ->where('projects.id', $project_id)
            ->get();
    }

    public function vipTechnicians($proficiency_id)
    {
        return Technician::where('proficiency_id', $proficiency_id)->where('vip', 1)->get();
    }

    public function deleteBid($bid_id)
    {
        $bid = Bid::find($bid_id);

        $bid->delete();

        return 'پیشنهاد مورد نظر حذف شد';
    }

    public function verifyProject($project_id, $verify)
    {
        Project::where('id', $project_id)->update(['verified' => $verify]);

        $project = Project::select('id')->where('id', $project_id)->get();

        if ($verify == 1) {
            foreach ($project as $p) {
                return 'پروژه با شناسه ی ' . $p->id  . ' با موفقیت تایید شد';
            }
        } else {
            foreach ($project as $p) {
                return 'پروژه با شناسه ی ' . $p->id  .  ' با موفقیت غیرفعال شد ';
            }
        }
    }

    public function comments($type)
    {
        if ($type == 'project') {

            return Comment::with('user', 'project')->whereNotNull('project_id')->get();
        }
        if ($type == 'technician') {

            return Comment::with('user', 'technician')->whereNotNull('technician_id')->get();
        }
        if ($type == 'client') {

            return Comment::with('user', 'client')->whereNotNull('client_id')->get();
        }
    }

    public function deleteComment($comment_id)
    {
        $comment = Comment::find($comment_id);

        $comment->delete();

        return 'کامنت مورد نظر حذف شد';
    }

    public function newCity($title)
    {
        $city = resolve(City::class);

        $city->title = $title;

        $city->save();

        return $city->title;
    }

    public function deleteCity($city_id)
    {
        $city = City::find($city_id);

        $message = $city->title;

        $city->delete();

        return 'شهر ' . $message . ' حذف شد';
    }

    public function towns()
    {
        return Town::with('city')->get();
    }

    public function newTown($title, $city_id)
    {
        $town = resolve(Town::class);

        $town->title = $title;

        $town->city_id = $city_id;

        $town->save();

        return $town->title;
    }

    public function deleteTown($town_id)
    {
        $town = Town::find($town_id);

        $message = $town->title;

        $town->delete();

        return 'محله ' . $message . ' حذف شد';
    }

    public function skills()
    {
        return Skill::with('proficiency')->get();
    }

    public function newSkill($title, $proficiency_id)
    {
        $skill = resolve(Skill::class);

        $skill->title = $title;

        $skill->proficiency_id = $proficiency_id;

        $skill->save();

        return $skill->title;
    }

    public function deleteSkill($skill_id)
    {
        $skill = Skill::find($skill_id);

        $message = $skill->title;

        $skill->delete();

        return 'مهارت ' . $message . ' حذف شد';
    }

    public function products()
    {
        return Product::with('skills')->get();
    }

    public function newProduct($picture, $title, $price, $skill_id)
    {
        $skill = Skill::find($skill_id);

        $product = resolve(Product::class);

        $product->picture = $picture;

        $product->title = $title;

        $product->price = $price;

        $product->save();

        $product->skills()->attach($skill);

        return $product->title;
    }

    public function deleteProduct($product_id)
    {
        $product = Product::find($product_id);

        $message = $product->title;

        $product->delete();

        return $message . ' حذف شد';
    }

    public function discounts()
    {
        return DiscountCode::all();
    }

    public function newDiscount($picture, $code, $percent, $proficiency_id)
    {
        $discount = resolve(DiscountCode::class);

        $discount->picture = $picture;

        $discount->code = $code;

        $discount->percent = $percent;

        $discount->proficiency_id = $proficiency_id;

        $discount->save();

        return $discount->code;
    }

    public function deleteDiscount($discount_id)
    {
        $discount = DiscountCode::find($discount_id);

        $message = $discount->code;

        $discount->delete();

        return 'کد تخفیف ' . $message . ' حذف شد';
    }

    public function sendNotificationToUser($user, $title, $description)
    {
        $notification = resolve(Notification::class);

        $notification->user_id = $user->id;

        $notification->title = $title;

        $notification->description = $description;

        $notification->save();
    }

    public function sendNotificationToAll($title, $description)
    {
        $users = User::all();

        foreach ($users as $user) {

            $this->sendNotificationToUser($user, $title, $description);
        }
    }

    public function sendNotificationToTechnicians($title, $description)
    {
        $users = User::whereNotNull('technician_id')->get();

        foreach ($users as $user) {

            $this->sendNotificationToUser($user, $title, $description);
        }
    }

    public function sendNotificationToClients($title, $description)
    {
        $users = User::whereNotNull('client_id')->get();

        foreach ($users as $user) {

            $this->sendNotificationToUser($user, $title, $description);
        }
    }

    public function sendNotificationToUserId($user_id, $title, $description)
    {
        $users = User::where('id', $user_id)->get();

        foreach ($users as $user) {

            $this->sendNotificationToUser($user, $title, $description);
        }
    }

    public function allNotifications()
    {
        return NotificationResource::collection(DB::table('notifications')
            ->leftJoin('users', 'users.id', '=', 'notifications.user_id')
            ->leftJoin('clients', 'clients.id', '=', 'users.client_id')
            ->leftJoin('technicians', 'technicians.id', '=', 'users.technician_id')
            ->select('notifications.id', 'notifications.created_at', 'notifications.title', 'notifications.description', 'clients.first_name as client_fname', 'clients.last_name as client_lname', 'technicians.first_name as technician_fname', 'technicians.last_name as technician_lname')
            ->orderBy('notifications.created_at', 'desc')
            ->get());
    }

    public function allUsers()
    {
        return UserResource::collection(DB::table('users')
            ->leftJoin('clients', 'clients.id', '=', 'users.client_id')
            ->leftJoin('technicians', 'technicians.id', '=', 'users.technician_id')
            ->select('users.id', 'clients.first_name as client_fname', 'clients.last_name as client_lname', 'technicians.first_name as technician_fname', 'technicians.last_name as technician_lname')
            ->get());
    }

    public function settings($type, $value, $title)
    {
        $settings =  Setting::where('type', $type)
            ->update(['value' => $value, 'title' => $title]);

        if ($settings != 0) {

            return $title . ' با موفقیت ذخیره شد ';
        } else {

            $settings = resolve(Setting::class);

            $settings->type = $type;

            $settings->value = $value;

            $settings->title = $title;

            $settings->save();

            return $title . ' با موفقیت افزوده شد ';
        }
    }
}
