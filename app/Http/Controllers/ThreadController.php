<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreThreadRequest;
use App\Http\Resources\ThreadCollection;
use App\Http\Resources\ThreadResource;
use App\Models\Thread;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ThreadController extends Controller
{
    /**
     * @OA\Get(
     *     path="/threads",
     *     summary="Listar conversaciones del usuario autenticado",
     *     tags={"Threads"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número de página",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Resultados por página",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Buscar en subject",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="unread",
     *         in="query",
     *         description="Filtrar solo no leídos (true/false)",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de threads paginada"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth('api')->user();
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');
        $unread = $request->input('unread');

        $query = Thread::query()
            ->forUser($user->id)
            ->with([
                'latestMessage.user:id,name,email',
                'participants:id,name',
                'creator:id,name'
            ])
            ->withUnreadCount($user->id)
            ->search($search)
            ->orderBy('updated_at', 'desc');

        if ($unread === 'true' || $unread === true) {
            $query->having('unread_count', '>', 0);
        }

        $threads = $query->paginate($perPage);

        return response()->json(new ThreadCollection($threads));
    }

    /**
     * @OA\Get(
     *     path="/threads/{id}",
     *     summary="Obtener detalles de un thread con sus mensajes",
     *     tags={"Threads"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del thread",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Página de mensajes",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalles del thread"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No autorizado (no es participante)"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Thread no encontrado"
     *     )
     * )
     */
    public function show(Request $request, Thread $thread): JsonResponse
    {
        $this->authorize('view', $thread);

        $user = auth('api')->user();

        $thread->load([
            'participants:id,name,email',
            'creator:id,name'
        ]);

        $thread->setRelation('messages', $thread->messages()
            ->with('user:id,name,email')
            ->paginate(20));

        DB::table('messages')
            ->where('thread_id', $thread->id)
            ->where('user_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        DB::table('thread_participants')
            ->where('thread_id', $thread->id)
            ->where('user_id', $user->id)
            ->update(['last_read_at' => now()]);

        return response()->json(new ThreadResource($thread));
    }

    /**
     * @OA\Post(
     *     path="/threads",
     *     summary="Crear nuevo thread con primer mensaje",
     *     tags={"Threads"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"subject","body","participants"},
     *             @OA\Property(property="subject", type="string", example="Consulta sobre producto"),
     *             @OA\Property(property="body", type="string", example="Hola, tengo una pregunta..."),
     *             @OA\Property(
     *                 property="participants",
     *                 type="array",
     *                 @OA\Items(type="integer"),
     *                 example={2, 3}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Thread creado exitosamente"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación"
     *     )
     * )
     */
    public function store(StoreThreadRequest $request): JsonResponse
    {
        $user = auth('api')->user();

        $thread = DB::transaction(function () use ($request, $user) {
            $thread = Thread::create([
                'subject' => $request->subject,
                'created_by' => $user->id,
            ]);

            $thread->messages()->create([
                'user_id' => $user->id,
                'body' => $request->body,
                'is_read' => false,
            ]);

            $participantIds = array_unique(array_merge(
                [$user->id],
                $request->participants
            ));

            foreach ($participantIds as $participantId) {
                $thread->participants()->attach($participantId, [
                    'last_read_at' => $participantId === $user->id ? now() : null,
                ]);
            }

            $thread->load([
                'latestMessage.user:id,name,email',
                'participants:id,name',
                'creator:id,name'
            ]);

            return $thread;
        });

        return response()->json(new ThreadResource($thread), 201);
    }
}
