<?php

namespace Fingerprint\FingerprintClient;

use Illuminate\Http\Request;
use Fingerprint\FingerPrintClient;

class utility extends Controller
{
    static function client(){
        new FingerPrintClient("localhost:4134", [
            "credentials" => Grpc\ChannelCredentials::createInsecure(),
        ]);
    }
}
