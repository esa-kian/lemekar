<?php

namespace App\DB;

use App\Http\Resources\BidResource;
use App\Http\Resources\CommentResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\ProjectTechnicianResource;
use App\Http\Resources\ProjectTechnicianWithStatusResource;
use App\Models\Bid;
use App\Models\Comment;
use App\Models\DiscountCode;
use App\Models\Notification;
use App\Models\Photo;
use App\Models\Product;
use App\Models\Proficiency;
use App\Models\Project;
use App\Models\Rate;
use App\Models\Skill;
use App\Models\Technician;
use App\Models\Town;
use App\Notifications\NewNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProjectRepo
{
    public function submit(
        $client_id,
        $proficiency_id,
        $details,
        $skills,
        $photos,
        $products,
        $start_at,
        $budget,
        $city_id,
        $address_id,
        $discount_code,
        $vip
    ) {
        $project = resolve(Project::class);

        if ($this->findDiscountCode($discount_code)) {

            $discount = $this->findDiscountCode($discount_code)->percent;
        } else {

            $discount = 0;
        }

        $project->discount = $discount;

        $project->client_id = $client_id;

        $project->proficiency_id = $proficiency_id;

        $project->details = $details;

        $project->start_at = $start_at;

        $project->budget = $budget;

        $project->city_id  = $city_id;

        $project->address_id = $address_id;

        $project->client_status = 'current';

        $project->vip  = $vip;

        $project->save();

        if (!empty($skills[0])) {

            foreach ($skills as $skill) {

                $project->skills()->attach($skill);
            }
        }
        if (!empty($photos[0])) {

            foreach ($photos as $photo) {

                Photo::where('id', $photo)->update(['project_id' => $project->id]);
            }
        }

        if (!empty($products[0])) {

            foreach ($products as $product) {

                $project->products()->attach($product);
            }
        }

        return $project->id;
    }

    public function findDiscountCode($code)
    {
        $code_found = DiscountCode::where('code', $code)->first();

        if ($code_found) {
            DiscountCode::find($code_found->id);
        }

        return $code_found;
    }

    public function photo($photo, $type)
    {
        $fileName = time() . '_' . $type . '.' . $photo->getClientOriginalExtension();

        $photo->move(public_path('/projects/'), $fileName);

        $photo_instance = resolve(Photo::class);

        if ($type == 'technician') {

            $photo_instance->uploaded_by = $type;
        } elseif ($type == 'client') {

            $photo_instance->uploaded_by = $type;
        }

        $photo_instance->name = $fileName;

        $photo_instance->url = '/projects/' . $fileName;

        $photo_instance->save();

        return $photo_instance->id;
    }

    public function setPhotosToProject($project_id, $photos)
    {
        if (count($photos) > 0) {

            foreach ($photos as $photo) {

                Photo::where('id', $photo)->update(['project_id' => $project_id]);
            }
        }
    }

    public function deletePhoto($photo_id)
    {
        $photo = Photo::find($photo_id);

        if (File::exists(public_path($photo->url))) {
            File::delete(public_path($photo->url));
        }
        
        $photo->delete();
    }

    // fetch all proficiencies
    public function proficiencies()
    {
        return Proficiency::all('title', 'id', 'icon');
    }

    // fetch skills of proficiency
    public function skills($proficiency_id)
    {
        return Skill::select('title', 'id')->where('proficiency_id', $proficiency_id)->get();
    }

    public function searchInSkills($keyword)
    {
        return DB::table('proficiencies')
            ->leftJoin('skills', 'skills.proficiency_id', '=', 'proficiencies.id')
            ->select('proficiencies.title', 'proficiencies.id', 'proficiencies.icon')
            ->where('skills.title', 'LIKE', '%' . $keyword . '%')
            ->get()->unique('proficiencies.id');
    }

    // fetch products of skill
    public function products($skills)
    {
        $products = DB::table('skills')
            ->leftJoin('product_skill', 'product_skill.skill_id', '=', 'skills.id')
            ->leftJoin('products', 'products.id', '=', 'product_skill.product_id')
            ->whereIn('skills.id', $skills)
            ->get();

        return ProductResource::collection($products);
    }

    // fetch projects by status
    public function projectsByStatus($status, $client_id)
    {
        if ($status == 'current') {

            return Project::where('client_status', 'current')
                ->where('client_id', $client_id)
                ->orWhere('client_status', 'accepted')
                ->where('client_id', $client_id)
                ->with('proficiency:title,id')
                ->withCount(['bids' => function ($query) {
                    $query->select(DB::raw('coalesce(count(project_id),0)'));
                }])
                ->orderBy('updated_at', 'asc')
                ->get();
        } else {

            return Project::where('client_status', $status)
                ->where('client_id', $client_id)
                ->orderBy('updated_at', 'asc')
                ->with('proficiency:title,id')->get();
        }
    }

    public function currentOrder($id, $client_id)
    {
        $order = $this->order($id, $client_id, 'current');

        $skills = $this->projectSkills($id);

        $bids = $this->bids($id);

        $comments = $this->comments($id);

        return [
            'detail' => $order,
            'skills' => $skills,
            'bids' => $bids,
            'comments' => $comments
        ];
    }

    public function acceptedOrder($id, $client_id)
    {
        $order = $this->order($id, $client_id, 'accepted');

        $skills = $this->projectSkills($id);

        return [
            'detail' => $order,
            'skills' => $skills,
        ];
    }

    public function doneOrder($id, $client_id)
    {
        $clientRepo = resolve(ClientRepo::class);

        $technicianRepo = resolve(TechnicianRepo::class);

        $order = $this->order($id, $client_id, 'done');

        $skills = $this->projectSkills($id);

        foreach ($order as $o) {

            foreach ($clientRepo->getUserByClientId($client_id) as $client) {
                $voter_id = $client->id;
            }

            foreach ($technicianRepo->getUserByTechnicianId($o->technician_id) as $technician) {
                $voted_id = $technician->id;
            }

            $rate = $this->rate($voter_id, $voted_id, 'to_technician');
        }

        return [
            'detail' => $order,
            'skills' => $skills,
            'rate' => $rate
        ];
    }

    public function cancelOrder($client_id, $project_id)
    {
        Project::where('client_id', $client_id)
            ->where('id', $project_id)
            ->update([
                'client_status' => 'cancel',
                'technician_status' => null,
                'technician_id' => null
            ]);
    }

    public function canceledOrder($id, $client_id)
    {

        $order = $this->order($id, $client_id, 'cancel');

        $skills = $this->projectSkills($id);


        return [
            'detail' => $order,
            'skills' => $skills,
        ];
    }

    public function order($project_id, $client_id, $status)
    {
        if ($status == 'done' || $status == 'accepted') {

            return ProjectResource::collection(Project::where('id', $project_id)
                ->where('client_id', $client_id)
                ->where('client_status', $status)
                ->with('proficiency:title,id', 'address:id,city_id,town_id,description', 'address.city:title,id', 'address.town:title,id', 'technician:first_name,last_name,profile_picture,id,phone_number', 'photos')
                ->get(['id', 'details', 'budget', 'proficiency_id', 'start_at', 'address_id', 'technician_id', 'client_status', 'vip']));
        } else {

            return ProjectResource::collection(Project::where('id', $project_id)
                ->where('client_id', $client_id)
                ->where('client_status', $status)
                ->with('proficiency:title,id', 'address:id,city_id,town_id,description', 'address.city:title,id', 'address.town:title,id', 'photos')
                ->get(['id', 'details', 'budget', 'proficiency_id', 'start_at', 'address_id', 'client_status', 'vip']));
        }
    }

    public function bids($project_id)
    {
        return BidResource::collection(DB::table('bids')
            ->where('project_id', $project_id)
            ->leftJoin('technicians', 'bids.technician_id', '=', 'technicians.id')
            ->leftJoin('users', 'technicians.id', '=', 'users.technician_id')
            ->leftJoin('rates', 'rates.voted_id', '=', 'users.id')
            ->select('bids.id', 'users.id as user_id', 'bids.technician_id', 'technicians.profile_picture', 'technicians.first_name', 'technicians.last_name', 'bids.description', 'bids.amount', 'bids.created_at')
            ->selectRaw('ifnull(AVG(rates.vote),0) as rate')
            ->selectRaw('ifnull(COUNT(rates.voted_id),0) as votes')
            ->groupBy('bids.id', 'rates.voted_id', 'technicians.id', 'technicians.first_name', 'bids.technician_id', 'bids.description', 'technicians.last_name', 'bids.amount', 'bids.created_at')
            ->get());
    }

    public function comments($project_id)
    {
        return CommentResource::collection(Comment::select('comments.content', 'comments.created_at', 'comments.id', 'technicians.profile_picture', 'technicians.first_name', 'technicians.last_name')
            ->leftJoin('projects', 'projects.id', '=', 'comments.project_id')
            ->leftJoin('users', 'users.id', '=', 'comments.user_id')
            ->leftJoin('technicians', 'technicians.id', '=', 'users.technician_id')
            ->where('comments.project_id', $project_id)
            ->get());
    }

    public function tasks()
    {
        return Project::with('proficiency:title,id', 'address:id,city_id,town_id', 'address.city:title,id', 'address.town:title,id')
            ->orderBy('created_at', 'desc')->get(['id', 'start_at', 'budget', 'address_id', 'proficiency_id']);
    }

    public function projects($order_by, $towns, $technician_id)
    {
        $bids = Bid::select('project_id')
            ->where('status', 'accepted')
            ->where('technician_id', $technician_id)
            ->orWhere('status', 'pending')
            ->where('technician_id', $technician_id)
            ->get();

        $technician = Technician::find($technician_id);
        
        $project_ids = array();

        foreach ($bids as $bid) {
            $project_ids[] = $bid->project_id;
        }

        if (!empty($towns[0])) {

            if ($order_by == 'asc') {

                return $this->filteredProjectsOrderByAsc($towns, $project_ids, $technician->city_id, $technician->proficiency_id);
            } elseif ($order_by == 'desc') {

                return $this->filteredProjectsOrderByDesc($towns, $project_ids, $technician->city_id, $technician->proficiency_id);
            } else {

                return $this->filterProjectsByTown(
                    Project::with(
                        'proficiency:title,id',
                        'address:id,city_id,town_id',
                        'address.city:title,id',
                        'address.town:title,id'
                    )->where('technician_status', null)
                        ->where('client_status', 'current')
                        ->where('vip', 0)
                        ->where('verified', 1)
                        ->where('city_id', $technician->city_id)
                        ->where('proficiency_id', $technician->proficiency_id)
                        ->whereNotIn('projects.id', $project_ids)
                        ->get([
                            'id',
                            'start_at',
                            'budget',
                            'address_id',
                            'proficiency_id'
                        ]),
                    $towns
                );
            }
        } else {
            if ($order_by == 'asc') {

                return $this->filteredProjectsOrderByAsc($towns, $project_ids, $technician->city_id, $technician->proficiency_id);
            } elseif ($order_by == 'desc') {

                return $this->filteredProjectsOrderByDesc($towns, $project_ids, $technician->city_id, $technician->proficiency_id);
            } else {
                return
                    Project::with(
                        'proficiency:title,id',
                        'address:id,city_id,town_id',
                        'address.city:title,id',
                        'address.town:title,id'
                    )->where('technician_status', null)
                    ->where('client_status', 'current')
                    ->where('vip', 0)
                    ->where('verified', 1)
                    ->where('city_id', $technician->city_id)
                    ->where('proficiency_id', $technician->proficiency_id)
                    ->whereNotIn('projects.id', $project_ids)
                    ->get([
                        'id',
                        'start_at',
                        'budget',
                        'address_id',
                        'proficiency_id'
                    ]);
            }
        }
    }

    public function filteredProjectsOrderByAsc($towns, $project_ids, $city_id, $proficiency_id)
    {
        $projects = Project::with(
            'proficiency:title,id',
            'address:id,city_id,town_id',
            'address.city:title,id',
            'address.town:title,id'
        )->where('technician_status', null)
            ->where('client_status', 'current')
            ->where('verified', 1)
            ->where('city_id', $city_id)
            ->where('vip', 0)
            ->where('city_id', $city_id)
            ->where('proficiency_id', $proficiency_id)
            ->whereNotIn('projects.id', $project_ids)
            ->orderBy('budget', 'asc')
            ->get([
                'id',
                'start_at',
                'budget',
                'address_id',
                'proficiency_id'
            ]);

        if (!empty($towns[0])) {

            return $this->filterProjectsByTown($projects, $towns);
        } else {

            return $projects;
        }
    }

    public function filteredProjectsOrderByDesc($towns, $project_ids, $city_id, $proficiency_id)
    {
        $projects =  Project::with(
            'proficiency:title,id',
            'address:id,city_id,town_id',
            'address.city:title,id',
            'address.town:title,id'
        )->where('technician_status', null)
            ->where('client_status', 'current')
            ->where('vip', 0)
            ->where('verified', 1)
            ->where('city_id', $city_id)
            ->where('proficiency_id', $proficiency_id)
            ->whereNotIn('projects.id', $project_ids)
            ->orderBy('budget', 'desc')
            ->get([
                'id',
                'start_at',
                'budget',
                'address_id',
                'proficiency_id'
            ]);
        if (!empty($towns[0])) {


            return $this->filterProjectsByTown($projects, $towns);
        } else {
            return $projects;
        }
    }

    public function filterProjectsByTown($projects, $towns)
    {
        $filtered_projects = array();

        for ($i = 0; $i < count($projects); $i++) {

            foreach ($towns as $town) {

                if ($projects[$i]->address->town->id == $town) {

                    $filtered_projects[] = $projects[$i];
                }
            }
        }
        return $filtered_projects;
    }

    public function projectsByTechnicianStatus($status, $technician_id)
    {

        $accepted =  DB::table('projects')
            ->where('projects.technician_status', $status)
            ->where('projects.technician_id', $technician_id)
            ->where('projects.verified', 1)
            ->leftJoin('proficiencies', 'proficiencies.id', '=', 'projects.proficiency_id')
            ->leftJoin('addresses', 'addresses.id', '=', 'projects.address_id')
            ->leftJoin('cities', 'cities.id', '=', 'addresses.city_id')
            ->leftJoin('towns', 'towns.id', '=', 'addresses.town_id')
            ->get([
                'projects.id',
                'projects.budget',
                'projects.proficiency_id',
                'projects.start_at',
                'projects.address_id',
                'projects.technician_id',
                'projects.vip',
                'proficiencies.title as proficiency',
                'cities.title as city',
                'towns.title as town'
            ]);

        if ($status == 'todo') {

            $bids = Bid::select('project_id')
                ->where('status', 'pending')
                ->where('technician_id', $technician_id)
                ->get();

            $project_ids = array();

            foreach ($bids as $bid) {
                $project_ids[] = $bid->project_id;
            }

            $projects =
                DB::table('projects')
                ->whereIn('projects.id', $project_ids)
                ->where('projects.technician_status', null)
                ->where('projects.technician_id', null)
                ->where('projects.verified', 1)
                ->leftJoin('proficiencies', 'proficiencies.id', '=', 'projects.proficiency_id')
                ->leftJoin('addresses', 'addresses.id', '=', 'projects.address_id')
                ->leftJoin('cities', 'cities.id', '=', 'addresses.city_id')
                ->leftJoin('towns', 'towns.id', '=', 'addresses.town_id')
                ->get([
                    'projects.id',
                    'projects.budget',
                    'projects.proficiency_id',
                    'projects.start_at',
                    'projects.address_id',
                    'projects.vip',
                    'projects.technician_id',
                    'proficiencies.title as proficiency',
                    'cities.title as city',
                    'towns.title as town'
                ]);

            return $accepted->union($projects);
        } else {

            return $accepted;
        }
    }

    public function todoProject($id)
    {
        $project = $this->detail($id, 'todo');

        $skills = $this->projectSkills($id);

        return [
            'detail' => $project,
            'skills' => $skills,
        ];
    }

    public function doneProject($id)
    {
        $clientRepo = resolve(ClientRepo::class);

        $technicianRepo = resolve(TechnicianRepo::class);

        $project = $this->detail($id, 'done');

        $skills = $this->projectSkills($id);

        foreach ($project as $p) {

            foreach ($clientRepo->getUserByClientId($p->client_id) as $client) {
                $voted_id = $client->id;
            }

            foreach ($technicianRepo->getUserByTechnicianId($p->technician_id) as $technician) {
                $voter_id = $technician->id;
            }

            $rate = $this->rate($voter_id, $voted_id, 'to_client');
        }
        return [
            'detail' => $project,
            'skills' => $skills,
            'rate' => $rate
        ];
    }

    public function project($id)
    {
        $project = $this->detail($id, null);

        $skills = $this->projectSkills($id);

        return [
            'detail' => $project,
            'skills' => $skills,
        ];
    }

    public function cancelProject($technician_id, $project_id)
    {
        Bid::where('technician_id', $technician_id)
            ->where('project_id', $project_id)
            ->update(['status' => 'cancel']);

        Project::where('technician_id', $technician_id)
            ->where('id', $project_id)
            ->update([
                'client_status' => 'current',
                'technician_status' => null,
                'technician_id' => null
            ]);
    }

    public function similarProjects($project_id)
    {
        $project = $this->project($project_id);

        foreach ($project['detail'] as $p) {

            return Project::with(
                'proficiency:title,id',
                'address:id,city_id,town_id',
                'address.city:title,id',
                'address.town:title,id'
            )->where('proficiency_id', $p->proficiency_id)
                ->where('verified', 1)
                ->where('vip', 0)
                ->where('id', '<>', $project_id)
                ->take(3)->get([
                    'id',
                    'start_at',
                    'budget',
                    'address_id',
                    'proficiency_id'
                ]);
        }
    }

    public function detail($project_id, $status)
    {
        if ($status) {

            return ProjectTechnicianResource::collection(Project::where('id', $project_id)
                ->where('technician_status', $status)
                ->where('verified', 1)
                ->with('proficiency:title,id', 'address:id,city_id,town_id,description,lat,long', 'address.city:title,id', 'address.town:title,id', 'client:first_name,last_name,id,created_at,profile_picture', 'photos')
                ->get(['id', 'details', 'budget', 'vip',  'proficiency_id', 'start_at', 'address_id', 'client_id', 'technician_id', 'technician_status']));
        } else {

            return ProjectTechnicianResource::collection(Project::where('id', $project_id)
                ->where('technician_status', $status)
                ->where('verified', 1)
                ->with('proficiency:title,id', 'address:id,city_id,town_id', 'address.city:title,id', 'address.town:title,id', 'client:first_name,last_name,id,created_at,profile_picture', 'photos', 'skills')
                ->get(['id', 'details', 'budget', 'proficiency_id', 'start_at', 'address_id', 'client_id', 'technician_id']));
        }
    }

    public function projectSkills($project_id)
    {
        return Project::find($project_id)->skills()->select('title', 'skill_id')->get();
    }

    public function rate($voter_id, $voted_id, $type)
    {
        try {
            $rate = Rate::where('voter_id', $voter_id)
                ->where('voted_id', $voted_id)
                ->where('type', $type)
                ->get();

            if (count($rate) > 0) {

                foreach ($rate as $r) {

                    return $r->vote;
                }
            } else {

                return 0;
            }
        } catch (Throwable $e) {

            return ['error' => $e];
        }
    }

    public function acceptBid($technician_id, $project_id, $bid_id)
    {
        try {
            Bid::where('id', $bid_id)
                ->update([
                    'status' => 'accepted'
                ]);

            $bid = Bid::find($bid_id);

            Project::where('id', $project_id)
                ->update([
                    'client_status' => 'accepted',
                    'technician_id' => $technician_id,
                    'technician_status' => 'todo',
                    'budget' => $bid->amount
                ]);

            return response(['message' => ' پیشنهاد ذخیره شد', $bid], 200);
        } catch (Throwable $e) {

            return response(['message' => 'خطایی رخ داد. پیشنهاد ذخیره نشد', 'error' => $e], 500);
        }
    }

    public function rateAndCommentToClient($user_id, $client_id, $vote, $comment)
    {
        $clientRepo = resolve(ClientRepo::class);

        foreach ($clientRepo->getUserByClientId($client_id) as $user) {
            $voted_id = $user->id;
        }

        $has_vote = Rate::where('voter_id', $user_id)
            ->where('voted_id', $voted_id)
            ->where('type', 'to_client')
            ->get();

        if (count($has_vote) == 0) {

            try {

                $this->vote($user_id, $voted_id, 'to_client', $vote);
            } catch (Throwable $e) {

                return response(['error' => 'خطا در ثبت امتیاز رخ داد' . $e], 403);
            }
        } else {

            Rate::where('voter_id', $user_id)
                ->where('voted_id', $voted_id)
                ->update(['vote' => $vote]);
        }

        try {

            $this->commentToClient($user_id, $client_id, $comment);
        } catch (Throwable $e) {

            return response(['error' => 'خطا در ثبت نظر رخ داد' . $e], 403);
        }
    }

    public function rateAndCommentToTechnician($user_id, $technician_id, $vote, $comment)
    {
        $technicianRepo = resolve(TechnicianRepo::class);

        foreach ($technicianRepo->getUserByTechnicianId($technician_id) as $user) {
            $voted_id = $user->id;
        }

        $has_vote = Rate::where('voter_id', $user_id)
            ->where('voted_id', $voted_id)
            ->where('type', 'to_technician')
            ->get();

        if (count($has_vote) == 0) {

            try {

                $this->vote($user_id, $voted_id, 'to_technician', $vote);
            } catch (Throwable $e) {

                return response(['error' => 'خطا در ثبت امتیاز رخ داد' . $e], 403);
            }
        } else {

            Rate::where('voter_id', $user_id)
                ->where('voted_id', $voted_id)
                ->update(['vote' => $vote]);
        }

        try {

            $this->commentToTechnician($user_id, $technician_id, $comment);
        } catch (Throwable $e) {

            return response(['error' => 'خطا در ثبت نظر رخ داد' . $e], 403);
        }
    }

    public function vote($voter_id, $voted_id, $to, $vote)
    {
        $rate = resolve(Rate::class);

        $rate->voter_id = $voter_id;

        $rate->voted_id = $voted_id;

        $rate->type =  $to;

        $rate->vote = $vote;

        if (0 < $vote && $vote < 6) {

            $rate->save();
        }
    }

    public function commentToClient($user_id, $client_id, $content)
    {
        $comment = resolve(Comment::class);

        $comment->user_id = $user_id;

        $comment->client_id = $client_id;

        $comment->content = $content;

        $comment->save();

        return 'نظر شما ثبت شد';
    }

    public function commentToTechnician($user_id, $technician_id, $content)
    {
        $comment = resolve(Comment::class);

        $comment->user_id = $user_id;

        $comment->technician_id = $technician_id;

        $comment->content = $content;

        $comment->save();

        return 'نظر شما ثبت شد';
    }

    public function finishProjectTechnician($project_id)
    {
        Project::where('id', $project_id)->update(['technician_status' => 'done']);
    }

    public function finishProjectClient($project_id)
    {
        Project::where('id', $project_id)->update(['client_status' => 'done']);
    }

    public function credit($client_id, $amount, $tip, $project_id, $technician_id)
    {
        $clientRepo = resolve(ClientRepo::class);

        $clientRepo->transaction($client_id, $amount, 'payment');

        $technicianRepo = resolve(TechnicianRepo::class);

        $technicianRepo->income($technician_id, $project_id, $amount, 'income');

        $client = $clientRepo->getClient($client_id);

        $title = 'پرداخت انجام شد';

        $description = $client->first_name . ' ' . $client->last_name . '  پرداخت پروژه با شناسه ' . $project_id . ' را انجام داد  ';

        $this->sendNotificationToUser($technicianRepo->getUserByTechnicianId($technician_id), $title, $description);

        if ($tip != 0) {

            $clientRepo->transaction($client_id, $tip, 'tip');

            $technicianRepo->income($technician_id, $project_id, $tip, 'tip');
        }
    }

    public function cash($client_id, $amount, $project_id, $technician_id)
    {
        $clientRepo = resolve(ClientRepo::class);

        $technicianRepo = resolve(TechnicianRepo::class);

        $technicianRepo->income($technician_id, $project_id, $amount, 'cash');

        $client = $clientRepo->getClient($client_id);

        $title = 'پرداخت انجام شد';

        $description = $client->first_name . ' ' . $client->last_name . '  پرداخت پروژه با شناسه ' . $project_id . ' را بصورت نقدی انجام داد  ';

        $this->sendNotificationToUser($technicianRepo->getUserByTechnicianId($technician_id), $title, $description);
    }

    public function sendNotificationToUser($user, $title, $description)
    {
        $notification = resolve(Notification::class);

        foreach ($user as $u) {

            $notification->user_id = $u->id;
        }

        $notification->title = $title;

        $notification->description = $description;

        $notification->save();
    }
}
