<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterInformationRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\SendOtpRequest;
use App\Http\Requests\VerifyAccountRequest;
use App\Models\User;
use App\Services\AuthService;
use App\Utils\MessageResource;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    private $auth_service;

    public function __construct()
    {
        $this->auth_service = new AuthService();
    }

    public function login(LoginRequest $request)
    {
        $data_validated = $request->validated();
        $validator = Validator::make(
            ["email" => $data_validated["account"]],
            ["email" => "email"]
        );
        if ($validator->fails()) {
            $credentials = [
                "username" => $data_validated["account"],
                "password" => $data_validated["password"],
            ];
        } else {
            $credentials = [
                "email" => $data_validated["account"],
                "password" => $data_validated["password"],
            ];
        }
        if (!$token = auth()->attempt($credentials)) {
            return JsonResponse::unauthorized();
        }
        $token_data = collect(JsonResponse::makeTokenData($token))->merge([
            "title" => MessageResource::LOGIN_SUCCESS_TITLE,
            "message" => MessageResource::LOGIN_SUCCESS_MESSAGE,
        ])->all();
        return JsonResponse::successWithData($token_data);
    }

    public function register(RegisterRequest $request)
    {
            $data_validated = $request->validated();
            $this->auth_service->register($data_validated);
            return JsonResponse::success(
                MessageResource::REGISTER_SUCCESS_TITLE,
                MessageResource::REGISTER_SUCCESS_MESSAGE
            );
    }

    public function verifyAccount(VerifyAccountRequest $request)
    {
        $data_validated = $request->validated();
        if ($this->auth_service->verifyAccount($data_validated)) {
            return JsonResponse::success(
                MessageResource::DEFAULT_SUCCESS_TITLE,
                MessageResource::REGISTER_VERIFY_SUCCESS
            );
        }
        return JsonResponse::error(
            MessageResource::OTP_INVALID,
            Response::HTTP_NOT_ACCEPTABLE
        );
    }

    public function registerInformation(RegisterInformationRequest $request)
    {
        try {
            $data_validated = $request->validated();
            $status = $this->auth_service->registerInformation($data_validated);
            switch ($status) {
                case User::STATUS_OK:
                    return JsonResponse::error(
                        MessageResource::REGISTER_INFORMATION_UPDATED,
                        Response::HTTP_NOT_ACCEPTABLE
                    );
                case User::STATUS_NOT_VERIFY:
                    return JsonResponse::error(
                        MessageResource::REGISTER_NOT_VERIFY,
                        Response::HTTP_NOT_ACCEPTABLE
                    );
                case User::STATUS_NOT_EXIST:
                    return JsonResponse::error(
                        MessageResource::ACCOUNT_NOT_EXIST,
                        Response::HTTP_NOT_ACCEPTABLE
                    );
                case User::STATUS_NOT_REGISTER_INFORMATION:
                    return JsonResponse::success(
                        MessageResource::DEFAULT_SUCCESS_TITLE,
                        MessageResource::REGISTER_INFORMATION_SUCCESS
                    );
            }
            return JsonResponse::error(MessageResource::DEFAULT_FAIL_MESSAGE, Response::HTTP_CONFLICT);
        } catch (QueryException $exception) {
            if ($exception->getCode() == 23000 && Str::contains($exception->getMessage(), "Duplicate")) {
                return Response::error(
                    MessageResource::REGISTER_USERNAME_EXIST,
                    Response::HTTP_NOT_ACCEPTABLE
                );
            }
            return JsonResponse::exceptionError($exception);
        }
    }

    public function sendOtp(SendOtpRequest $request)
    {
        $data_validated = $request->validated();
        return JsonResponse::successWithData(
            $this->auth_service->sendOtp($data_validated["email"])
        );
    }

    public function me()
    {
        return JsonResponse::successWithData(auth()->user());
    }

    public function logout()
    {
        auth()->logout();
        return JsonResponse::success(
            MessageResource::DEFAULT_SUCCESS_TITLE,
            MessageResource::LOGOUT_SUCCESS_MESSAGE
        );
    }

    public function refresh()
    {
        return JsonResponse::successWithData(
            JsonResponse::makeTokenData(auth()->refresh())
        );
    }
}