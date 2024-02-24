<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Clothes;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ClothesController extends Controller
{
    public function addClothe(Request $request): JsonResponse
    {
        try {
            // Validate the request data
            $validatedData = $request->validate([
                'name' => 'required|string|max:50',
                'material' => 'required|string|max:100',
                'type' => 'required|string|max:100',
                'colorway' => 'required|string|max:100',
                'washing_instructions' => 'required',
                'is_in_laundry' => 'boolean',
                'picture' => 'nullable|image',
            ]);

            $washingInstructionsArray = json_decode($request->washing_instructions, true);
            $validatedData['washing_instructions'] = json_encode($washingInstructionsArray);

            $validatedData['user_id'] = auth()->user()->id;

            // Create a new Clothe instance with the validated data
            $clothe = Clothes::create($validatedData);

            // Return a successful response
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'success',
                'data' => $clothe
            ], Response::HTTP_OK);
        } catch (ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $e) {
            // Handle any other errors
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'unknown_error_exception',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getClothes(): JsonResponse
    {
        try {
            $clothes = Clothes::where('user_id', auth()->user()->id)->get();
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'success',
                'data' => [
                    $clothes,
                ]
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'unknown_error_exception',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deleteClothe(Request $request): JsonResponse
    {
        try {
            $clothe_id = $request->input('clothe_id');
            if ($clothe_id == null) {
                return response()->json([
                    'status' => Response::HTTP_BAD_REQUEST,
                    'message' => 'clothe_id_required',
                ], Response::HTTP_BAD_REQUEST);
            }
            $clothe = Clothes::find($clothe_id);
            if ($clothe == null) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'clothe_not_found',
                ], Response::HTTP_NOT_FOUND);
            }
            if ($clothe->user_id != auth()->user()->id) {
                return response()->json([
                    'status' => Response::HTTP_FORBIDDEN,
                    'message' => 'forbidden',
                ], Response::HTTP_FORBIDDEN);
            }
            $clothe->delete();
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'success',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'unknown_error_exception',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
