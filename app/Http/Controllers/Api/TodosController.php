<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Todos;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class TodosController extends Controller
{
    public function addTodo(Request $request): JsonResponse
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

            $todo = new Todos([
                'user_id' => $user_id,
                'name' => $name,
                'description' => $description,
                'due_date' => $due_date
            ]);
            $todo->save();

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'added_todo',
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

    public function getTodos(Request $request): JsonResponse
    {
        try {
            $user_id = auth()->user()->id;

            if ($request->input('active')) {
                $todos = Todos::where('user_id', $user_id)->where('is_completed', false)->get();
            } elseif ($request->input('completed')) {
                $todos = Todos::where('user_id', $user_id)->where('is_completed', true)->get();
            } else {
                $todos = Todos::where('user_id', $user_id)->get();
            }

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'success',
                'data' => [
                    $todos
                ],
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function toggleTodo(Request $request): JsonResponse
    {
        try {
            $user_id = auth()->user()->id;
            $todo_id = $request->input('todo_id');
            $is_completed = $request->input('is_completed');

            Validator::make(compact('todo_id', 'is_completed'), [
                'todo_id' => 'required',
                'is_completed' => 'required',
            ])->validate();

            $todo = Todos::where('user_id', $user_id)->where('id', $todo_id)->first();
            if (!$todo) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'todo_not_found',
                ], Response::HTTP_NOT_FOUND);
            }

            $todo->is_completed = $is_completed ? 1 : 0;
            $todo->completed_at = $is_completed ? now() : null;
            $todo->save();

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

    public function deleteTodo(Request $request): JsonResponse
    {
        try {
            $user_id = auth()->user()->id;
            $todo_id = $request->input('todo_id');

            Validator::make(compact('todo_id'), [
                'todo_id' => 'required',
            ])->validate();

            $todo = Todos::where('user_id', $user_id)->where('id', $todo_id)->first();
            if (!$todo) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'todo_not_found',
                ], Response::HTTP_NOT_FOUND);
            }

            $todo->delete();

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

    public function updateTodo(Request $request): JsonResponse
    {
        try {
            $user_id = auth()->user()->id;
            $todo_id = $request->input('todo_id');
            $name = $request->input('name');
            $description = $request->input('description');
            $due_date = $request->input('due_date');

            Validator::make(compact('todo_id', 'name'), [
                'todo_id' => 'required',
                'name' => 'required|string',

            ])->validate();

            $todo = Todos::where('user_id', $user_id)->where('id', $todo_id)->first();
            if (!$todo) {
                return response()->json([
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'todo_not_found',
                ], Response::HTTP_NOT_FOUND);
            }

            $todo->name = $name;
            $todo->description = $description;
            $todo->due_date = $due_date;
            $todo->save();

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
