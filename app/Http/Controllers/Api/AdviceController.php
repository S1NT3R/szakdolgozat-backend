<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Advices;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;


class AdviceController extends Controller
{
    public function getAdvice(Request $request): JsonResponse
    {
        try {
            $type = $request->input('type');
            if ($type) {
                $advice = Advices::where('type', $type)->inRandomOrder()->first();
                return response()->json([
                    'status' => Response::HTTP_OK,
                    'message' => 'success: ' . $type,
                    'data' => [
                        $advice,
                    ]
                ], Response::HTTP_OK);
            }
            $advice = Advices::inRandomOrder()->first();
            return response()->json([
                'status' => Response::HTTP_OK,
                'advice' => $advice,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'unknown_error_exception',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getAllAdvicesRandomizedByType(): JsonResponse
    {
        try {
            // Get all distinct types
            $types = Advices::select('type')->distinct()->pluck('type');

            $result = [];

            // For each type, get all advices of that type and randomize them
            foreach ($types as $type) {
                $advices = Advices::where('type', $type)->inRandomOrder()->get();
                $result[$type] = $advices;
            }

            // Return the result
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'success',
                'data' => [
                    $result
                ],
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'unknown_error_exception',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function addAdvice(Request $request): JsonResponse
    {
        try {
            $user_id = auth()->user()->id;
            $type = $request->input('type');
            $advice = $request->input('advice');

            Validator::make(compact('type', 'advice'), [
                'type' => 'required',
                'advice' => 'required',
            ])->validate();
            $advice = new Advices([
                'user_id' => $user_id,
                'type' => $type,
                'advice' => $request->input('advice'),
                'is_personal' => true,
            ]);
            $advice->save();
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'added_advice',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            if ($e instanceof ValidationException)
                return response()->json([
                    'status' => Response::HTTP_BAD_REQUEST,
                    'message' => 'validation_error',
                ], Response::HTTP_BAD_REQUEST);
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deleteAdvice(Request $request): JsonResponse
    {
        try {
            $advice_id = $request->input('advice_id');
            $advice = Advices::find($advice_id);
            if ($advice) {
                if ($advice->is_personal) {
                    $advice->delete();
                    return response()->json([
                        'status' => Response::HTTP_OK,
                        'message' => 'success',
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'status' => Response::HTTP_FORBIDDEN,
                        'message' => 'cannot_delete_non_personal_advice',
                    ], Response::HTTP_FORBIDDEN);
                }
            }
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'advice_not_found',
            ], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'unknown_error_exception',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
