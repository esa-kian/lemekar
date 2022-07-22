<?php

namespace App\Http\Controllers;

use App\DB\ClientRepo;
use App\DB\ProjectRepo;
use App\DB\ReferralRepo;
use App\DB\TechnicianRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProjectController extends Controller
{
    public function submitProject(Request $request, ProjectRepo $projectRepo)
    {
        $skills_clean = $this->removeBracket($request->skills);

        $photos_clean = $this->removeBracket($request->photos);

        $products_clean = $this->removeBracket($request->products);

        try {
            $project = $projectRepo->submit(
                auth()->guard('api')->user()->client_id,
                $request->proficiency_id,
                $request->details,
                $skills_clean,
                $photos_clean,
                $products_clean,
                $request->start_at,
                $request->budget,
                $request->city_id,
                $request->address_id,
                $request->discount_code,
                $request->vip,
            );

            return response([
                'message' => 'پروژه جدید ایجاد شد',
                'tracking_code' => $project
            ], 200);
        } catch (Throwable $e) {
            return response(['error' => $e], 403);
        }
    }

    public function checkDiscountCode($discount_code, ProjectRepo $projectRepo)
    {
        if ($discount_code != null) {

            $discount_code = $projectRepo->findDiscountCode($discount_code);

            if ($discount_code != null) {

                return response(['message' => 'کد تخفیف معتبر می باشد', 'valid' => true], 200);
            } else {

                return response(['message' => 'کد تخفیف نامعتبر می باشد', 'valid' => false], 200);
            }
        }
        else {

            return response(['message' => 'کد تخفیف نامعتبر می باشد', 'valid' => false], 200);
        }
    }

    public function uploadPhoto(Request $request, ProjectRepo $projectRepo)
    {
        try {

            if (auth()->guard('api')->user()->technician_id) {

                $photo = $projectRepo->photo($request->photo, 'technician');
            } elseif (auth()->guard('api')->user()->client_id) {

                $photo = $projectRepo->photo($request->photo, 'client');
            }

            return response(['photo_id' => $photo], 200);
        } catch (Throwable $e) {

            return response(['error' => $e], 400);
        }
    }

    public function submitPhotos(Request $request, ProjectRepo $projectRepo)
    {
        try {

            $projectRepo->setPhotosToProject($request->project_id, $request->photos);

            return response(['message' => 'تصاویر با موفقیت ثبت شد'], 200);
        } catch (Throwable $e) {

            return response(['error' => $e], 404);
        }
    }

    public function deletePhoto(Request $request, ProjectRepo $projectRepo)
    {
        try {

            $projectRepo->deletePhoto($request->photo_id);

            return response(['message' => 'success'], 200);
        } catch (Throwable $e) {

            return response(['error' => $e], 404);
        }
    }

    public function clientId()
    {
        $clientRepo = resolve(ClientRepo::class);

        return $clientRepo->getClientId(auth()->guard('api')->id());
    }

    public function technicianId()
    {
        $technicianRepo = resolve(TechnicianRepo::class);

        return $technicianRepo->getTechnicianId(auth()->guard('api')->id());
    }

    public function fetchProficiencies(ProjectRepo $projectRepo)
    {
        return response(['proficiencies' => $projectRepo->proficiencies()]);
    }

    public function fetchSkills($id)
    {
        $projectRepo = resolve(ProjectRepo::class);

        return response(['skills' => $projectRepo->skills($id)]);
    }

    public function fetchProducts(Request $request, ProjectRepo $projectRepo)
    {
        return response([
            'message' => 'محصولات یافت شد',
            'products' => $projectRepo->products(explode(",", $request->input('skills')))
        ], 200);
    }

    public function fetchCurrentOrders(ProjectRepo $projectRepo)
    {
        return response([
            'message' => 'درخواست ها یافت شد',
            'orders' => $projectRepo->projectsByStatus('current', auth()->guard('api')->user()->client_id)
        ], 200);
    }

    public function fetchAcceptedOrders(ProjectRepo $projectRepo)
    {
        return response([
            'message' => 'درخواست ها یافت شد',
            'orders' => $projectRepo->projectsByStatus('accepted', auth()->guard('api')->user()->client_id)
        ], 200);
    }

    public function cancelOrder(Request $request, ProjectRepo $projectRepo)
    {
        try {

            return response([
                'message' => $projectRepo->cancelOrder(
                    auth()->guard('api')->user()->client_id,
                    $request->project_id
                )
            ], 200);
        } catch (Throwable $e) {

            return response(['message' => $e], 403);
        }
    }
    public function fetchDoneOrders(ProjectRepo $projectRepo)
    {
        return response([
            'message' => 'درخواست ها یافت شد',
            'orders' => $projectRepo->projectsByStatus('done', auth()->guard('api')->user()->client_id)
        ], 200);
    }

    public function fetchCanceledOrders(ProjectRepo $projectRepo)
    {
        return response([
            'message' => 'درخواست ها یافت شد',
            'orders' => $projectRepo->projectsByStatus('cancel', auth()->guard('api')->user()->client_id)
        ], 200);
    }

    public function fetchCurrentOrder($id, ProjectRepo $projectRepo)
    {
        return response([
            'message' => 'درخواست مورد نظر یافت شد',
            'order' => $projectRepo->currentOrder($id, auth()->guard('api')->user()->client_id)
        ], 200);
    }

    public function fetchAcceptedOrder($id, ProjectRepo $projectRepo)
    {
        return response([
            'message' => 'درخواست مورد نظر یافت شد',
            'order' => $projectRepo->acceptedOrder($id, auth()->guard('api')->user()->client_id)
        ], 200);
    }

    public function fetchDoneOrder($id, ProjectRepo $projectRepo)
    {
        return response([
            'message' => 'درخواست ها یافت شد',
            'order' => $projectRepo->doneOrder($id, auth()->guard('api')->user()->client_id)
        ], 200);
    }

    public function fetchCanceledOrder($id, ProjectRepo $projectRepo)
    {
        return response([
            'message' => 'درخواست مورد نظر یافت شد',
            'order' => $projectRepo->canceledOrder($id, auth()->guard('api')->user()->client_id)
        ], 200);
    }

    public function searchOnCategories(Request $request, ProjectRepo $projectRepo)
    {
        return response([
            'message' => 'دسته بندی ها یافت شد',
            'categories' => $projectRepo->searchInSkills($request->keyword)
        ], 200);
    }

    public function fetchTasks(ProjectRepo $projectRepo)
    {
        return response([
            'message' => 'درخواست ها یافت شد',
            'tasks' => $projectRepo->tasks()
        ], 200);
    }

    public function fetchProjects(Request $request, ProjectRepo $projectRepo)
    {
        $towns_clean = $this->removeBracket($request->towns);

        return response([
            'message' => 'درخواست ها یافت شد',
            'projects' => $projectRepo->projects($request->order_by, $towns_clean, auth()->guard('api')->user()->technician_id)
        ], 200);
    }

    public function fetchTodoProjects(ProjectRepo $projectRepo)
    {
        return response([
            'message' => 'درخواست ها یافت شد',
            'projects' => $projectRepo->projectsByTechnicianStatus('todo', auth()->guard('api')->user()->technician_id)
        ], 200);
    }

    public function fetchTodoProject($id, ProjectRepo $projectRepo)
    {
        return response([
            'message' => 'درخواست مورد نظر یافت شد',
            'project' => $projectRepo->todoProject($id)
        ], 200);
    }

    public function fetchDoneProjects(ProjectRepo $projectRepo)
    {
        return response([
            'message' => 'درخواست ها یافت شد',
            'projects' => $projectRepo->projectsByTechnicianStatus('done', auth()->guard('api')->user()->technician_id)
        ], 200);
    }

    public function fetchDoneProject($id, ProjectRepo $projectRepo)
    {
        return response([
            'message' => 'درخواست مورد نظر یافت شد',
            'project' => $projectRepo->doneProject($id)
        ], 200);
    }

    public function fetchProject($id, ProjectRepo $projectRepo)
    {
        return response([
            'message' => 'درخواست مورد نظر یافت شد',
            'project' => $projectRepo->project($id),
            'similars' => $projectRepo->similarProjects($id)
        ], 200);
    }

    public function cancelProject(Request $request, ProjectRepo $projectRepo)
    {
        try {

            return response([
                'message' => $projectRepo->cancelProject(
                    auth()->guard('api')->user()->technician_id,
                    $request->project_id
                )
            ], 200);
        } catch (Throwable $e) {

            return response(['message' => $e], 403);
        }
    }

    public function acceptBid(Request $request, ProjectRepo $projectRepo)
    {
        return response([
            'message' => 'پیشنهاد پذیرفته شد',
            'bid' => $projectRepo->acceptBid(
                $request->technician_id,
                $request->project_id,
                $request->bid_id,
            )
        ]);
    }

    public function rateAndCommentToClient(Request $request, ProjectRepo $projectRepo, ReferralRepo $referralRepo)
    {
        $clientRepo = resolve(ClientRepo::class);
        $technicianRepo = resolve(TechnicianRepo::class);

        $projectRepo->rateAndCommentToClient(
            auth()->guard('api')->id(),
            $request->client_id,
            $request->vote,
            $request->comment
        );

        $projectRepo->finishProjectTechnician($request->project_id);

        $referralRepo->firstProjectCredit(auth()->guard('api')->user()->technician_id, auth()->guard('api')->id());

        $technician = $technicianRepo->getTechnician(auth()->guard('api')->user()->technician_id);

        $description = $technician->first_name . ' ' . $technician->last_name . '   پروژه با شناسه ' . $request->project_id . ' را به اتمام رساند  ';

        $projectRepo->sendNotificationToUser($clientRepo->getUserByClientId($request->client_id), 'پروژه به اتمام رسید', $description);

        return response([
            'message' => 'نظر و امتیاز شما ثبت شد'
        ], 200);
    }

    public function rateAndCommentToTechnician(Request $request, ProjectRepo $projectRepo)
    {
        $projectRepo->rateAndCommentToTechnician(
            auth()->guard('api')->id(),
            $request->technician_id,
            $request->vote,
            $request->comment
        );


        return response([
            'message' => 'نظر و امتیاز شما ثبت شد'
        ], 200);
    }

    public function creditPayment(Request $request, ProjectRepo $projectRepo)
    {
        try {

            $projectRepo->credit(
                auth()->guard('api')->user()->client_id,
                $request->payment,
                $request->tip,
                $request->project_id,
                $request->technician_id
            );

            $projectRepo->finishProjectClient($request->project_id);

            return response([
                'message' => ' پرداخت انجام شد'
            ], 200);
        } catch (Throwable $e) {

            return response([
                'message' => ' پرداخت انجام نشد'
                    . $e
            ], 403);
        }
    }

    public function cashPayment(Request $request, ProjectRepo $projectRepo)
    {

        try {

            $projectRepo->cash(
                auth()->guard('api')->user()->client_id,
                $request->payment,
                $request->project_id,
                $request->technician_id
            );

            $projectRepo->finishProjectClient($request->project_id);

            return response([
                'message' => ' عملیات با موفقیت انجام شد'
            ], 200);
        } catch (Throwable $e) {

            return response([
                'message' => 'عملیات با خطا مواجه شد'
                    . $e
            ], 403);
        }
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
}
