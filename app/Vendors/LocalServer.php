<?php
namespace App\Vendors;

use App\Http\Traits\ResponseTrait;

class LocalServer {
    use ResponseTrait;


    public function sendAirtime() {
        return 1111;
    }

}
?>