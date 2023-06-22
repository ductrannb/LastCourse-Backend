<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateBillRequest;
use App\Services\BillService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

class BillController extends Controller
{
    private $bill_service;

    public function __construct()
    {
        $this->bill_service = new BillService();
    }

    public function updateOrCreate(CreateBillRequest $request)
    {
        $data_validated = $request->validated();
        return $this->bill_service->updateOrCreate($data_validated, $request->id);
    }

    public function updateStatus(Request $request, Route $route)
    {
        if ($request->id) {
            if (str_ends_with($route->uri, "confirm")) {
                $status = 1;
            } else if (str_ends_with($route->uri, "delivery")) {
                $status = 2;
            } else if (str_ends_with($route->uri, "success")) {
                $status = 3;
            } else if (str_ends_with($route->uri, "return")) {
                $status = 4;
            } else if (str_ends_with($route->uri, "cancel")) {
                $status = 5;
            } else {
                $status = -1;
            }
            return $this->bill_service->updateStatus($request->id, $status);
        }
        return JsonResponse::error("Fail", JsonResponse::HTTP_CONFLICT);
    }
}