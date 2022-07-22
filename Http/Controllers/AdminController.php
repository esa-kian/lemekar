<?php

namespace App\Http\Controllers;

use App\DB\AdminRepo;
use App\DB\ChatRepo;
use App\DB\ClientRepo;
use App\DB\TechnicianRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

class AdminController extends Controller
{

    public function fetchDashboardReports(AdminRepo $adminRepo)
    {
        return response([
            'incomes_monthly' => $adminRepo->incomesMonthly(),
            'incomes_yearly' => $adminRepo->incomesYearly(),
            'incomes_total' => $adminRepo->incomesTotal(),
            'payments_monthly' => $adminRepo->paymentsMonthly(),
            'payments_yearly' => $adminRepo->paymentsYearly(),
            'payments_total' => $adminRepo->paymentsTotal()
        ], 200);
    }

    public function register(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'email' => 'required|email',
        //     'password' => 'required',
        //     'password_confirmation' => 'required|same:password',
        // ]);

        // if ($validator->fails()) {
        //     return response(['Validation Error.', $validator->errors()]);
        // }

        // $input = $request->all();

        // $input['password'] = bcrypt($input['password']);

        // try {

        //     $user = resolve(User::class);

        //     $user->email = $input['email'];

        //     $user->password = $input['password'];

        //     $user->save();
        // } catch (Throwable $th) {

        //     return response(['Unauthorised.', ['error' => 'Another account is using ' . $request->email]]);
        // }

        // $success['token'] =  $user->createToken('LEMEKAR')->accessToken;

        // $success['email'] =  $user->email;

        // return response([$success, 'User register successfully.']);
    }

    public function login(Request $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {

            $admin = Auth::user();

            $success['token'] =  $admin->createToken('LEMEKAR')->accessToken;

            $success['email'] = $request->email;

            if ($admin->technician_id == null && $admin->client_id == null) {

                return response([$success, 'User login successfully.']);
            }
        } else {
            return response(['Unauthorised.', ['error' => 'Unauthorised']]);
        }
    }

    public function logout(Request $request)
    {
        $token = $request->user()->token();
        $token->revoke();
        $response = ['message' => 'You have been successfully logged out!'];
        return response($response, 200);
    }

    public function fetchNotVerifiedTechnicians(AdminRepo $adminRepo)
    {
        return response(
            ['technicians' => $adminRepo->notVerifiedTechnicians()],
            200
        );
    }

    public function verifyTechnician(Request $request, AdminRepo $adminRepo)
    {
        if ($request->verify == true) {
            $verify = 1;
        } else {
            $verify = 0;
        }

        return response(
            [
                'message' => $adminRepo->verifyTechnician($request->technician_id, $verify),
                'verify' => $verify
            ],
            200
        );
    }

    public function setTechnicianVip(Request $request, AdminRepo $adminRepo)
    {
        if ($request->vip == true) {
            $vip = 1;
        } else {
            $vip = 0;
        }

        return response(
            [
                'message' => $adminRepo->vipTechnician($request->technician_id, $vip),
                'vip' => $vip
            ],
            200
        );
    }

    public function fetchCertificates(AdminRepo $adminRepo)
    {
        return response(['certificates' => $adminRepo->certificates()], 200);
    }

    public function addCertificate(Request $request, AdminRepo $adminRepo)
    {
        $fileName = time() . '_certificates'  . '.' . $request->photo->getClientOriginalExtension();

        $request->photo->move(public_path('/certificates/'), $fileName);

        return response(['certificate' => $adminRepo->newCertificate(
            '/certificates/' . $fileName,
            $request->title,
            $request->technician_id
        )], 200);
    }

    public function deleteCertificate($id, AdminRepo $adminRepo)
    {
        return response(['message' => $adminRepo->deleteCertificate($id)], 200);
    }

    public function fetchIncomes(AdminRepo $adminRepo)
    {
        return response(['incomes' => $adminRepo->incomes()], 200);
    }

    public function fetchIncomesDetail($id, AdminRepo $adminRepo)
    {
        return response(['incomes' => $adminRepo->incomesDetail($id)], 200);
    }

    public function paidTechnicianIncome(Request $request, AdminRepo $adminRepo)
    {
        return response(['message' => $adminRepo->paidIncome($request->income_id)]);
    }

    public function fetchTechnician($id, AdminRepo $adminRepo)
    {
        return response(['technician' => $adminRepo->technician($id)], 200);
    }

    public function fetchVerifiedTechnicians(AdminRepo $adminRepo)
    {
        return response(
            ['technicians' => $adminRepo->verifiedTechnicians()],
            200
        );
    }

    public function fetchAllTechnicians(AdminRepo $adminRepo)
    {
        return response(
            ['technicians' => $adminRepo->allTechnicians()],
            200
        );
    }

    public function fetchAllClients(AdminRepo $adminRepo)
    {
        return response(
            ['clients' => $adminRepo->allClients()],
            200
        );
    }

    public function fetchClient($id, AdminRepo $adminRepo)
    {
        return response(['client' => $adminRepo->client($id)], 200);
    }

    public function fetchBalanceClient(AdminRepo $adminRepo)
    {
        return response(['clients' => $adminRepo->balances()], 200);
    }

    public function fetchTransactionsClient($id, ClientRepo $clientRepo)
    {
        return response(['transactions' => $clientRepo->transactions($id)], 200);
    }

    public function fetchWithdrawRequests(AdminRepo $adminRepo)
    {
        return response(['withdraws' => $adminRepo->withdrawRequests()], 200);
    }

    public function setWithdrawStatus(Request $request, AdminRepo $adminRepo, ClientRepo $clientRepo)
    {
        $adminRepo->withdrawStatus($request->withdraw_id, $request->status);

        if ($request->status == 'done') {

            $clientRepo->transaction($request->client_id, $request->amount, 'withdraw');
        }

        return response(['message' => 'success'], 200);
    }

    public function fetchProjects($type, $status, AdminRepo $adminRepo)
    {
        return response(['projects' => $adminRepo->projects($type, $status)], 200);
    }

    public function fetchProject($id, AdminRepo $adminRepo)
    {
        return response(['project' => $adminRepo->project($id), 'photos' => $adminRepo->photos($id)], 200);
    }

    public function fetchVipTechnicians($proficiency_id, AdminRepo $adminRepo)
    {
        return response(
            ['technicians' => $adminRepo->vipTechnicians($proficiency_id)],
            200
        );
    }

    public function deleteBid($id, AdminRepo $adminRepo)
    {
        return response(['message' => $adminRepo->deleteBid($id)], 200);
    }

    public function submitBid(Request $request, TechnicianRepo $technicianRepo)
    {
        return response([
            'message' => $technicianRepo->bid(
                $request->technician_id,
                $request->project_id,
                $request->description,
                $request->amount
            )
        ], 200);
    }

    public function verifyProject(Request $request, AdminRepo $adminRepo)
    {
        if ($request->verify == true) {
            $verify = 1;
        } else {
            $verify = 0;
        }

        return response(
            [
                'message' => $adminRepo->verifyProject($request->project_id, $verify),
                'verify' => $verify
            ],
            200
        );
    }

    public function fetchCommentsOnProject(AdminRepo $adminRepo)
    {
        return response(['comments' => $adminRepo->comments('project')], 200);
    }

    public function fetchCommentsOnTechnician(AdminRepo $adminRepo)
    {
        return response(['comments' => $adminRepo->comments('technician')], 200);
    }

    public function fetchCommentsOnClient(AdminRepo $adminRepo)
    {
        return response(['comments' => $adminRepo->comments('client')], 200);
    }

    public function deleteComment($id, AdminRepo $adminRepo)
    {
        return response(['message' => $adminRepo->deleteComment($id)], 200);
    }

    public function fetchTechniciansMessages(ChatRepo $chatRepo)
    {
        return response(
            ['messages' => $chatRepo->messagesClient(auth()->guard('api')->id())],
            200
        );
    }

    public function fetchClientsMessages(ChatRepo $chatRepo)
    {
        return response(
            ['messages' => $chatRepo->messagesTechnician(auth()->guard('api')->id())],
            200
        );
    }

    public function createCity(Request $request, AdminRepo $adminRepo)
    {
        return response(['city' => $adminRepo->newCity($request->title)], 200);
    }

    public function deleteCity($id, AdminRepo $adminRepo)
    {
        return response(['message' => $adminRepo->deleteCity($id)], 200);
    }

    public function fetchTowns(AdminRepo $adminRepo)
    {
        return response(['towns' => $adminRepo->towns()], 200);
    }

    public function createTown(Request $request, AdminRepo $adminRepo)
    {
        return response(['town' => $adminRepo->newTown($request->title, $request->city_id)], 200);
    }

    public function deleteTown($id, AdminRepo $adminRepo)
    {
        return response(['message' => $adminRepo->deleteTown($id)], 200);
    }

    public function fetchSkills(AdminRepo $adminRepo)
    {
        return response(['skills' => $adminRepo->skills()], 200);
    }

    public function createSkill(Request $request, AdminRepo $adminRepo)
    {
        return response(['skill' => $adminRepo->newSkill($request->title, $request->proficiency_id)], 200);
    }

    public function deleteSkill($id, AdminRepo $adminRepo)
    {
        return response(['message' => $adminRepo->deleteSkill($id)], 200);
    }

    public function fetchProducts(AdminRepo $adminRepo)
    {
        return response(['products' => $adminRepo->products()], 200);
    }

    public function createProduct(Request $request, AdminRepo $adminRepo)
    {
        $fileName = time() . '_product'  . '.' . $request->photo->getClientOriginalExtension();

        $request->photo->move(public_path('/products/'), $fileName);

        return response(['product' => $adminRepo->newProduct(
            '/products/' . $fileName,
            $request->title,
            $request->price,
            $request->skill_id
        )], 200);
    }

    public function deleteProduct($id, AdminRepo $adminRepo)
    {
        return response(['message' => $adminRepo->deleteProduct($id)], 200);
    }

    public function fetchDiscounts(AdminRepo $adminRepo)
    {
        return response(['discounts' => $adminRepo->discounts()], 200);
    }

    public function createDiscount(Request $request, AdminRepo $adminRepo)
    {
        $fileName = time() . '_discount'  . '.' . $request->photo->getClientOriginalExtension();

        $request->photo->move(public_path('/discounts/'), $fileName);


        return response(['discount' => $adminRepo->newDiscount(
            '/discounts/' . $fileName,
            $request->code,
            $request->percent,
            $request->proficiency_id
        )], 200);
    }

    public function deleteDiscount($id, AdminRepo $adminRepo)
    {
        return response(['message' => $adminRepo->deleteDiscount($id)], 200);
    }

    public function sendNotification(Request $request, AdminRepo $adminRepo)
    {
        // send to: all users/ only technicians/ only clients/ a user
        if ($request->send_to == 'all') {

            $adminRepo->sendNotificationToAll($request->title, $request->description);
        } elseif ($request->send_to == 'technicians') {

            $adminRepo->sendNotificationToTechnicians($request->title, $request->description);
        } elseif ($request->send_to == 'clients') {

            $adminRepo->sendNotificationToClients($request->title, $request->description);
        } else {

            $adminRepo->sendNotificationToUserId($request->send_to, $request->title, $request->description);
        }

        return response(['message' => 'اعلان ارسال شد'], 200);
    }

    public function fetchNotifications(AdminRepo $adminRepo)
    {
        return response(['notifications' => $adminRepo->allNotifications()], 200);
    }

    public function fetchUsers(AdminRepo $adminRepo)
    {
        return response(['users' => $adminRepo->allUsers()], 200);
    }

    public function submitSettings(Request $request, AdminRepo $adminRepo)
    {
        try {

            return response(
                ['message' => $adminRepo->settings(
                    $request->type,
                    $request->value,
                    $request->title
                )],
                200
            );
        } catch (Throwable $e) {
            return response(['error' => $e], 404);
        }
    }
}
