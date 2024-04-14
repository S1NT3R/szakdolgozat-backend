<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClothesResource;
use App\Models\Clothes;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
                'temperature' => 'required|integer',
                'is_in_laundry' => 'boolean',
                'picture' => 'nullable|image',
            ]);

            if ($request->has('washing_instructions')) {
                $washingInstructionsArray = $request->input('washing_instructions');
                $validatedData['washing_instructions'] = json_encode($washingInstructionsArray);
            }

            if ($request->hasFile('picture')) {
                $path = Storage::disk('public')->putFile('images', $request->file('picture'));
                $validatedData['picture'] = $path;
            }

            $validatedData['user_id'] = auth()->user()->id;

//            if ($request->hasFile('picture')) {
//                $image = $request->file('picture');
//                $imageName = time() . '.' . $image->extension();
//                $image->move(public_path('images'), $imageName);
//                $validatedData['picture'] = $imageName;
//            }
            Clothes::create($validatedData);
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'success',
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
            foreach ($clothes as $clothe) {
                $clothe->washing_instructions = json_decode($clothe->washing_instructions, true);
                $avatarUrl = $clothe->picture ? 'http://' . $_SERVER["HTTP_HOST"] . '/storage/' . $clothe->picture : null;

                $clothe->picture = $avatarUrl;
            }
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'success',
                'data' =>
                    ClothesResource::collection($clothes),

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

    public function toggleClothe(Request $request): JsonResponse
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
            $clothe->is_in_laundry = !$clothe->is_in_laundry;
            $clothe->save();
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

    public function updateClothe(Request $request): JsonResponse
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
            $validatedData = $request->validate([
                'name' => 'required|string|max:50',
                'material' => 'required|string|max:100',
                'type' => 'required|string|max:100',
                'colorway' => 'required|string|max:100',
                'temperature' => 'required|integer',
                'is_in_laundry' => 'boolean',
                'picture' => 'nullable|image',
            ]);

            if ($request->has('washing_instructions')) {
                $washingInstructionsArray = $request->input('washing_instructions');
                $validatedData['washing_instructions'] = json_encode($washingInstructionsArray);
            }

            if ($request->hasFile('picture')) {
                if ($clothe->picture) {
                    Storage::disk('public')->delete($clothe->picture);
                }
                $path = Storage::disk('public')->putFile('images', $request->file('picture'));
                $validatedData['picture'] = $path;
            } else {
                if ($clothe->picture) {
                    Storage::disk('public')->delete($clothe->picture);
                }
                $validatedData['picture'] = null;
            }

            $validatedData['user_id'] = auth()->user()->id;

//            if ($request->hasFile('picture')) {
//                $image = $request->file('picture');
//                $imageName = time() . '.' . $image->extension();
//                $image->move(public_path('images'), $imageName);
//                $validatedData['picture'] = $imageName;
//            }
            $clothe->update($validatedData);
            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'success',
            ], Response::HTTP_OK);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $e) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'unknown_error_exception',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function generateLaundry(): JsonResponse
    {
        try {
            $clothes = Clothes::where('user_id', auth()->user()->id)
                ->where('is_in_laundry', true)
                ->get();

            $washableMaterials = [
                'material_cotton' => ['material_cotton', 'material_polyester', 'material_denim'],
                'material_polyester' => ['material_cotton', 'material_polyester', 'material_denim'],
                'material_silk' => ['material_silk'],
                'material_wool' => ['material_wool'],
                'material_denim' => ['material_cotton', 'material_polyester', 'material_denim'],
            ];

            $washableRestrictions = [
                'clothe_normal' => ['clothe_normal', 'clothe_easier_ironing', 'clothe_any_chemical', 'clothe_any_except_tchlorine', 'clothe_wet_clean', 'clothe_bleaching'],
                'clothe_cold' => ['clothe_cold', 'clothe_delicate', 'clothe_hand_wash', 'clothe_dont_bleach'],
                'clothe_warm' => ['clothe_warm', 'clothe_easier_ironing', 'clothe_any_chemical', 'clothe_any_except_tchlorine', 'clothe_wet_clean', 'clothe_bleaching'],
                'clothe_hot' => ['clothe_hot', 'clothe_easier_ironing', 'clothe_any_chemical', 'clothe_any_except_tchlorine', 'clothe_wet_clean', 'clothe_bleaching'],
                'clothe_dont_wash' => ['clothe_dont_wash', 'clothe_dont_bleach'],
                'clothe_delicate' => ['clothe_delicate', 'clothe_hand_wash', 'clothe_dont_bleach'],
                'clothe_hand_wash' => ['clothe_hand_wash', 'clothe_dont_bleach'],
                'clothe_easier_ironing' => ['clothe_easier_ironing', 'clothe_any_chemical', 'clothe_any_except_tchlorine', 'clothe_wet_clean', 'clothe_bleaching'],
                'clothe_any_chemical' => ['clothe_any_chemical', 'clothe_any_except_tchlorine', 'clothe_wet_clean', 'clothe_bleaching'],
                'clothe_any_except_tchlorine' => ['clothe_any_except_tchlorine', 'clothe_wet_clean', 'clothe_dont_bleach'],
                'clothe_only_petroleum' => ['clothe_only_petroleum', 'clothe_dont_bleach'],
                'clothe_wet_clean' => ['clothe_wet_clean', 'clothe_dont_bleach'],
                'clothe_bleaching' => ['clothe_bleaching', 'clothe_normal', 'clothe_warm', 'clothe_hot', 'clothe_easier_ironing', 'clothe_any_chemical'], // Added compatible restrictions
                'clothe_dont_bleach' => ['clothe_dont_bleach', 'clothe_cold', 'clothe_dont_wash', 'clothe_delicate', 'clothe_hand_wash', 'clothe_any_except_tchlorine', 'clothe_only_petroleum', 'clothe_wet_clean'], // Added compatible restrictions
            ];

            $laundryLists = [];

            foreach ($clothes as $clothe) {
                $material = $clothe->material ?? '';
                $washingInstructions = json_decode($clothe->washing_instructions, true) ?? [];
                $temperature = $clothe->temperature ?? 0;
                $colorway = $clothe->colorway ?? '';

                $canAddToExistingList = false;

                // Check all existing groups first
                foreach ($laundryLists as &$list) {
                    if (!empty($list)) {
                        $compatibleMaterial = in_array($material, $washableMaterials[$list[0]->material] ?? []);
                        $compatibleInstructions = false;
                        $compatibleColorway = false;

                        // Check if the washing instructions of the new clothes are a subset or a superset of the washing instructions of any clothes in the list
                        foreach ($list as $existingClothe) {
                            $existingInstructions = $washableRestrictions[$existingClothe->washing_instructions[0]] ?? [];
                            if (count(array_intersect($washingInstructions, $existingInstructions)) == count($washingInstructions) || count(array_intersect($washingInstructions, $existingInstructions)) == count($existingInstructions)) {
                                $compatibleInstructions = true;
                            }

                            // Check if the colorway of the new clothes is compatible with the colorway of the existing clothes
                            if ($colorway == 'colorway_light') {
                                $compatibleColorway = true;
                                foreach ($list as $existingClothe) {
                                    if ($existingClothe->colorway != 'colorway_light') {
                                        $compatibleColorway = false;
                                        break;
                                    }
                                }
                            } else if ($colorway == 'colorway_colorful') {
                                $compatibleColorway = true;
                            }
                        }

                        if ($compatibleMaterial && $compatibleInstructions && $compatibleColorway) {
                            $list[] = $clothe;
                            $canAddToExistingList = true;
                            break;
                        }
                    }
                }

                // If the piece of clothing doesn't fit into any of the existing groups, create a new group
                if (!$canAddToExistingList) {
                    $laundryLists[] = [$clothe];
                }
            }

            foreach ($laundryLists as &$list) {
                foreach ($list as &$clothe) {
                    $clothe->washing_instructions = json_decode($clothe->washing_instructions, true);
                    $avatarUrl = $clothe->picture ? 'http://' . $_SERVER["HTTP_HOST"] . '/storage/' . $clothe->picture : null;

                    $clothe->picture = $avatarUrl;

                }
                $list = ClothesResource::collection($list);
            }

            return response()->json([
                'status' => Response::HTTP_OK,
                'message' => 'success',
                'data' => $laundryLists,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'unknown_error_exception',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
