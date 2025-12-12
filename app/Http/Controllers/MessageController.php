<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Message;
use App\Models\Thread;
use Illuminate\Http\JsonResponse;

class MessageController extends Controller
{
    /**
     * @OA\Post(
     *     path="/threads/{thread}/messages",
     *     summary="Enviar mensaje en un thread existente",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="thread",
     *         in="path",
     *         description="ID del thread",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"body"},
     *             @OA\Property(property="body", type="string", example="Esta es mi respuesta...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Mensaje enviado exitosamente"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No autorizado (no es participante)"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Thread no encontrado"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación"
     *     )
     * )
     */
    public function store(StoreMessageRequest $request, Thread $thread): JsonResponse
    {
        $this->authorize('create', [Message::class, $thread]);

        $user = auth('api')->user();

        $message = $thread->messages()->create([
            'user_id' => $user->id,
            'body' => $request->body,
            'is_read' => false,
        ]);

        $thread->touch();

        $message->load('user:id,name,email');

        return response()->json(new MessageResource($message), 201);
    }
}
