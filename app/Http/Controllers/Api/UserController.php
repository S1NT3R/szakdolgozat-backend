<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{

    public function register(Request $request): JsonResponse
    {
        try {
            $name = $request->input('name');
            $email = $request->input('email');
            $password = $request->input('password');

            Validator::make(compact('name', 'email', 'password'), [
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8'
            ])->validate();

            $user = new User([
                'name' => $name,
                'email' => $email,
                'password' => bcrypt($password)
            ]);

            $user->save();

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'Successfully created user!'
            ], Response::HTTP_OK);
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == 1062) {
                return response()->json([
                    'status' => Response::HTTP_CONFLICT,
                    'message' => 'user_already_exists'
                ], Response::HTTP_CONFLICT);
            }
        } catch (ValidationException $e) {
            return response()->json([
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'unknown_error_exception'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response()->json([
            'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
            'message' => 'unknown_error_exception'
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function login(Request $request): JsonResponse
    {
        try {
            $email = $request->input('email');
            $password = $request->input('password');

            Validator::make(compact('email', 'password'), [
                'email' => 'required|email',
                'password' => 'required'
            ])->validate();

            $token = JWTAuth::attempt(['email' => $email, 'password' => $password], ['exp' => now()->addWeek()->timestamp]);

            if (!$token) {
                return response()->json([
                    'status' => Response::HTTP_UNAUTHORIZED,
                    'message' => 'invalid_credentials'
                ], Response::HTTP_UNAUTHORIZED);
            }

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'successful_login',
                'data' => [
                    'token' => $token
                ]
            ], Response::HTTP_OK);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => 'validation_error'
            ], Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'unknown_error_exception'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);

        }

    }

    public function logout(): JsonResponse
    {
        try {
            JWTAuth::parseToken()->invalidate();
            auth()->logout();
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'successful_logout'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'unknown_error_exception'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getUser(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'successfully_retrieved_user',
                'data' => [
                    $user
                ]
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'unknown_error_exception'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function uploadAvatar(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if ($user->picture == null && $request->file('picture') == null) {
                return response()->json([
                    'status' => Response::HTTP_BAD_REQUEST,
                    'message' => 'no_picture_provided'
                ], Response::HTTP_BAD_REQUEST);
            }
            if (!$request->hasFile('picture') && $user->picture != null) {
                Storage::delete($user->picture);
                $user->picture = null;
                $user->save();
                return response()->json([
                    'status' => Response::HTTP_OK,
                    'message' => 'picture_deleted'
                ], Response::HTTP_OK);
            }
            $path = Storage::putFile('picture', $request->file('picture'));
            $user->picture = $path;
            $user->save();
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'successfully_updated_profile_picture',

            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'unknown_error_exception'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateStreak(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $lastStreakUpdate = $user->last_streak_update ? Carbon::parse($user->last_streak_update) : null;

            if (!$lastStreakUpdate || $lastStreakUpdate->toDateString() != now()->toDateString()) {
                $user->streak += 1;
                $user->last_streak_update = now();
                $user->save();

                return response()->json([
                    'status' => Response::HTTP_OK,
                    'message' => 'successfully_increased_streak',
                ], Response::HTTP_OK);
            }
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'streak_already_increased_today',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'unknown_error_exception'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateUserDetails(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $name = $request->input('name');
            if (!$name)
                return response()->json([
                    'status' => Response::HTTP_BAD_REQUEST,
                    'message' => 'no_name_provided'
                ], Response::HTTP_BAD_REQUEST);

            $user->name = $name;
            $user->save();
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'successfully_updated_user_details',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'unknown_error_exception'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function changePassword(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $password = $request->input('password');
            if (!$password)
                return response()->json([
                    'status' => Response::HTTP_BAD_REQUEST,
                    'message' => 'no_password_provided'
                ], Response::HTTP_BAD_REQUEST);

            $user->password = bcrypt($password);
            $user->save();
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'successfully_changed_password',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'unknown_error_exception'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deleteUser(Request $request): JsonResponse
    {
        try {
            if ($request->user() == null) {
                return response()->json([
                    'status' => Response::HTTP_BAD_REQUEST,
                    'message' => 'no_user_found'
                ], Response::HTTP_BAD_REQUEST);
            }
            $user = $request->user();
            if ($user->picture) {
                Storage::delete($user->picture);
            }
            $user->delete();
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'successfully_deleted_user',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'unknown_error_exception'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
