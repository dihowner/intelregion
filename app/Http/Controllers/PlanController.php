<?php

namespace App\Http\Controllers;

use App\Services\PlanService;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    protected $planService; 
    public function __construct(PlanService $planService)
    {
        $this->planService = $planService;
    }

    public function deletePlan($id) {
        return $this->planService->deletePlan($id);
    }
}