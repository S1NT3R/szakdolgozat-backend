<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chores;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ChoresController extends Controller
{
    public function addChore(Request $request): JsonResponse
    {
        try {
            $user_id = auth()->user()->id;
            $name = $request->input('name');
            $description = $request->input('description');
            $due_date = $request->input('due_date');

            Validator::make(compact('name', 'description', 'due_date'), [
                'name' => 'required|string',
                'description' => 'string|nullable',
                'due_date' => 'date|nullable',
            ])->validate();

            $chore = new Chores([
                'user_id' => $user_id,
                'name' => $name,
                'description' => $description,
                'due_date' => $due_date
            ]);
            $chore->save();

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'added_chore',
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

    public function getChores(Request $request): JsonResponse
    {
        try {
            $user_id = auth()->user()->id;

            if ($request->input('active')) {
                $chores = Chores::where('user_id', $user_id)->where('is_completed', false)->get();
            } elseif ($request->input('completed')) {
                $chores = Chores::where('user_id', $user_id)->where('is_completed', true)->get();
            } else {
                $chores = Chores::where('user_id', $user_id)->get();
            }

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'success',
                'data' => [
                    $chores
                ],
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function toggleChore(Request $request): JsonResponse
    {
        try {
            $user_id = auth()->user()->id;
            $chore_id = $request->input('chore_id');
            $is_completed = $request->input('is_completed');

            Validator::make(compact('chore_id', 'is_completed'), [
                'chore_id' => 'required',
                'is_completed' => 'required',
            ])->validate();

            $chore = Chores::where('user_id', $user_id)->where('id', $chore_id)->first();
            if (!$chore) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'chore_not_found',
                ], Response::HTTP_NOT_FOUND);
            }

            $chore->is_completed = $is_completed ? 1 : 0;
            $chore->completed_at = $is_completed ? now() : null;
            $chore->save();

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'success',
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

    public function deleteChore(Request $request): JsonResponse
    {
        try {
            $user_id = auth()->user()->id;
            $chore_id = $request->input('chore_id');

            Validator::make(compact('chore_id'), [
                'chore_id' => 'required',
            ])->validate();

            $chore = Chores::where('user_id', $user_id)->where('id', $chore_id)->first();
            if (!$chore) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'chore_not_found',
                ], Response::HTTP_NOT_FOUND);
            }

            $chore->delete();

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'success',
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

    public function updateChore(Request $request): JsonResponse
    {
        try {
            $user_id = auth()->user()->id;
            $chore_id = $request->input('chore_id');
            $name = $request->input('name');
            $description = $request->input('description');
            $due_date = $request->input('due_date');

            Validator::make(compact('chore_id', 'name'), [
                'chore_id' => 'required',
                'name' => 'required|string',

            ])->validate();

            $chore = Chores::where('user_id', $user_id)->where('id', $chore_id)->first();
            if (!$chore) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'chore_not_found',
                ], Response::HTTP_NOT_FOUND);
            }

            $chore->name = $name;
            $chore->description = $description;
            $chore->due_date = $due_date;
            $chore->save();

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'success',
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

}
