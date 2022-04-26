<?php

namespace App\Http\Controllers\Fingerprint;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Biometric;

class ServicesController extends Controller
{
    public function enroll(Request $request){
        $data = $request->all();

        $user = Biometric::where('user_id', $data['id'])->get();

        if($user < 1){
            $is_fraud = $this->is_duplicate($data->index_finger);
            if($is_fraud == false){
                $curr_finger_data = $data->index_finger;
            
                $enrolled_finger = enroll_fingerprint($curr_finger_data);
            
                if ($enrolled_finger !== "enrollment failed"){
                    # todo: return the enrolled fmds instead

                    Biometric::create([
                        'user_id' => $data['id'],
                        'finger_print' => $data['index_finger']
                    ]);
                    
                    return response()->json("successful");
                }
                else {
                    return response()->json("enrollment failed!");
                }
            }else{
                $verify = $this->verify($data['id'], $data['index_finger']);
                if($verify == "match"){
                    return response()->json("User has previously enrolled");
                }else{
                    return response()->json("Thumb print exist agaist wrong name");
                }
            }
        }
    }

    public function verify($user_id, $curr_finger_data){
        try{
            $enrolled_finger_data = Biometric::where('user_id', $user_id)->pluck('finger_print');

            $curr_finger_data = $data->pre_enrolled_finger_data;

            $verified_index_finger = verify_fingerprint($enrolled_finger_data, $curr_finger_data);

            if ($verified_index_finger !== "verification failed" && $verified_index_finger){
                return "match";
            }
            else {
                return "no_match";
            }
        }catch(\Exception $e){
            return $e;
        }
    }

    public function is_duplicate($curr_finger_data){
        try{
            $enrolled_hands_list = Biometric::pluck('finger_print')->toArray();
            
            $is_duplicate = check_duplicate($enrolled_hands_list, $curr_finger_data,);
            if ($is_duplicate){
                return true;
            }
            else{
                return false;
            }
        }catch(\Exception $e){
            return $e;
        }
    }
}
