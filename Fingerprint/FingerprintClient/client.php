<?php

namespace Fingerprint\FingerPrintClient;

use Illuminate\Http\Request;
use Fingerprint\CheckDuplicateResponse;
use Fingerprint\EnrollmentFMD;
use Fingerprint\EnrollmentRequest;
use Fingerprint\FingerPrintClient;
use Fingerprint\PreEnrolledFMD;
use Fingerprint\VerificationRequest;
use Fingerprint\VerificationResponse;
use App\Http\Controllers\utility\client;

if (! function_exists('enroll_fingerprint')) {
    function enroll_fingerprint($pre_reg_fmd_list) {
        $enrollment_request = new EnrollmentRequest();

        $pre_enrolled_fmds = array();
        
        foreach($pre_reg_fmd_list as $pre_reg_fmd) {
            $pre_enrollment_fmd = new PreEnrolledFMD();
            $pre_enrollment_fmd->setBase64PreEnrolledFMD($pre_reg_fmd);
            array_push($pre_enrolled_fmds, $pre_enrollment_fmd);
        }
    
        $enrollment_request->setFmdCandidates($pre_enrolled_fmds);
    
        list($enrolled_fmd, $status) = client::EnrollFingerprint($enrollment_request)->wait();
        
        if ($status->code === Grpc\STATUS_OK) {
            echo $enrolled_fmd->getBase64EnrolledFMD();
        }
        else {
            echo "Error: " . $status->code . " " . $status->details ;
        }
    }
}

if (! function_exists('verify_fingerprint')) {
    function verify_fingerprint($enrolled_finger_data, $curr_finger_data) {
        $pre_enrolled_fmd = new PreEnrolledFMD();
        $pre_enrolled_fmd->setBase64PreEnrolledFMD($enrolled_finger_data);
    
        $enrolled_cand_fmd = new EnrolledFMD();
        $enrolled_cand_fmd->setBase64EnrolledFMD($curr_finger_data);
    
        $verification_request = new VerificationRequest(array("targetFMD" => $pre_enrolled_fmd));
        //$verification_request->setTargetFMD($pre_enrolled_fmd);
        $verification_request->setFmdCandidates(array($enrolled_cand_fmd));
    
        list($verification_response, $status) = client::VerifyFingerprint($verification_request)->wait();
    
        if ($status->code === Grpc\STATUS_OK) {
            echo $verification_response->getMatch();
        }
        else {
            echo "Error: " . $status->code . " " . $status->details ;
        }
    }
}

if (! function_exists('check_duplicate')) {
    function check_duplicate($reg_fmd_list, $curr_finger_data) {
        $pre_enrolled_fmd = new PreEnrolledFMD(array("base64PreEnrolledFMD" => $curr_finger_data));
        $verification_request = new VerificationRequest(array("targetFMD" => $pre_enrolled_fmd));
    
        $enrolled_fmds = array();
    
        foreach($reg_fmd_list as $reg_fmd) {
            array_push($enrolled_fmds, new EnrolledFMD(array("base64EnrolledFMD" => $reg_fmd)));
        }
    
        $verification_request->setFmdCandidates($enrolled_fmds);
    
        list($response, $status) = client::CheckDuplicate($verification_request)->wait();
        echo $response->getIsDuplicate();
    
    }
}
