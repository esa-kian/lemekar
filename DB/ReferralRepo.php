<?php

namespace App\DB;

use App\Models\InviteCode;
use App\Models\Project;
use App\Models\RegisteredCode;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ReferralRepo
{
    // customer is a user(client/technician) that invited a new user(new customer)
    public function technicianRegistered($invite_code, $new_customer)
    {
        if ($invite_code && $new_customer->technician_id) {
            $customer = User::find($invite_code->user_id);

            if ($customer) {
                $amount = 2000;

                if ($customer->technician_id) {

                    $this->creditToTechnician($customer->technician_id, $amount);
                } elseif ($customer->client_id) {

                    $this->creditToClient($customer->client_id, $amount);
                }
            }
        }
    }

    public function firstProjectCredit($technician_id, $user_id)
    {
        $project = Project::where('technician_id', $technician_id)
            ->where('technician_status', 'done')->where('vip', 1)->get(['proficiency_id']);

        if (count($project) == 1) {

            foreach ($project as $p) {

                if ($p->proficiency_id == 28) {

                    $amount = 30000;
                } else {

                    $amount = 15000;
                }
            }

            $registered = RegisteredCode::where('user_id', $user_id)->get();

            if (count($registered) > 0) {

                foreach ($registered as $r) {

                    $invite_code = InviteCode::find($r->invite_code_id);
                    if ($invite_code) {

                        $user = User::find($invite_code->user_id);

                        if ($user->technician_id) {

                            $this->creditToTechnician($user->technician_id, $amount);
                        } elseif ($user->client_id) {

                            $this->creditToClient($user->client_id, $amount);
                        }
                    }
                }
            }
        }
    }

    public function creditToTechnician($technician_id, $amount)
    {
        $technicianRepo = resolve(TechnicianRepo::class);

        $technicianRepo->income($technician_id, null, $amount, 'credit');
    }

    public function creditToClient($client_id, $amount)
    {
        $clientRepo = resolve(ClientRepo::class);

        $clientRepo->transaction($client_id, $amount, 'credit');
    }
}
