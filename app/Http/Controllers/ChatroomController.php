<?php

namespace App\Http\Controllers;

use App\Events\UserEnteredChatroom;
use App\Models\Chatroom;
use Illuminate\Http\Request;

class ChatroomController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/chatrooms",
     *     summary="Get chatrooms with infinite scroll support",
     *     description="Retrieves a cursor-paginated list of chatrooms.",
     *     tags={"Chatrooms"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="cursor",
     *         in="query",
     *         required=false,
     *         description="Cursor for pagination",
     *         @OA\Schema(type="string", example="1")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Number of chatrooms per page",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="A cursor-paginated list of chatrooms",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="General Chat"),
     *                     @OA\Property(property="max_members", type="integer", example=100),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-11-06T12:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-11-06T12:00:00Z")
     *                 )
     *             ),
     *             @OA\Property(property="next_cursor", type="string", nullable=true, example="eyJpdiI6Ijg3NjU0MyIsInZhbHVlIjoxfQ==")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Error message")
     *         )
     *     )
     * )
     */

    public function index()
    {
        $perPage = request('per_page', 10); // Jumlah item per page, default 10
        $chatrooms = Chatroom::cursorPaginate($perPage);
        return response()->json([
            'data' => $chatrooms->items(),
            'next_cursor' => $chatrooms->nextCursor() ? $chatrooms->nextCursor()->encode() : null
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/chatrooms",
     *     summary="Create a new chatroom",
     *     description="Creates a new chatroom with a specified name and maximum number of members.",
     *     tags={"Chatrooms"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name", "max_members"},
     *
     *             @OA\Property(property="name", type="string", example="General Chat"),
     *             @OA\Property(property="max_members", type="integer", example=50)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Chatroom created successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="General Chat"),
     *             @OA\Property(property="max_members", type="integer", example=50),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2024-11-06T12:00:00Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2024-11-06T12:00:00Z")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="error", type="string", example="The 'name' field is required.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="error", type="string", example="Error message")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'max_members' => 'required|integer',
        ]);

        $chatroom = Chatroom::create([
            'name' => $request->name,
            'max_members' => $request->max_members,
        ]);

        return response()->json($chatroom, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/chatrooms/my",
     *     summary="Get all chatrooms the user belongs to",
     *     description="Fetches a list of chatrooms that the authenticated user is a member of.",
     *     tags={"Chatrooms"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of chatrooms the user belongs to",
     *
     *         @OA\JsonContent(
     *             type="array",
     *
     *             @OA\Items(
     *                 type="object",
     *
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="General Chatroom"),
     *                 @OA\Property(property="max_members", type="integer", example=50),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-11-01T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-11-01T12:00:00Z")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized, the user must be logged in to access this resource",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="error", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function myChatrooms(Request $request)
    {
        $user = $request->user();

        $chatrooms = $user->chatrooms()->get();

        return response()->json($chatrooms);
    }

    /**
     * @OA\Post(
     *     path="/api/chatrooms/{chatroom}/enter",
     *     summary="Join a chatroom",
     *     description="Allows an authenticated user to join a chatroom if there is space and they are not already a member. Once the user joins, a WebSocket event 'UserEnteredChatroom' will be broadcasted on the 'chatroom.{chatroom_id}' channel.",
     *     tags={"Chatrooms"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="chatroom",
     *         in="path",
     *         required=true,
     *         description="ID of the chatroom to join",
     *
     *         @OA\Schema(type="string", example="13662cf0-ead2-48b7-a056-ded77823fe9c")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successfully joined the chatroom",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Joined chatroom")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="The chatroom is full",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="error", type="string", example="Chatroom is full")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=409,
     *         description="User is already a member of the chatroom",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="error", type="string", example="User already in chatroom")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized, the user must be logged in to access this resource",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="error", type="string", example="Unauthenticated")
     *         )
     *     )
     *

     * )
     */
    public function enter(Request $request, Chatroom $chatroom)
    {
        if ($chatroom->users()->count() >= intval($chatroom->max_members)) {
            return response()->json(['error' => 'Chatroom is full'], 403);
        }

        if ($chatroom->users()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['error' => 'User already in chatroom'], 409);
        }

        broadcast(new UserEnteredChatroom($chatroom, $request->user()));

        $chatroom->users()->attach($request->user()->id, ['joined_at' => now()]);

        return response()->json(['message' => 'Joined chatroom']);
    }

    /**
     * @OA\Post(
     *     path="/api/chatrooms/{chatroom}/leave",
     *     summary="Leave a chatroom",
     *     description="Allows an authenticated user to leave a chatroom.",
     *     tags={"Chatrooms"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="chatroom",
     *         in="path",
     *         required=true,
     *         description="ID of the chatroom to leave",
     *
     *         @OA\Schema(type="string", example="13662cf0-ead2-48b7-a056-ded77823fe9c")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successfully left the chatroom",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Left chatroom")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized, the user must be logged in to access this resource",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="error", type="string", example="Unauthenticated")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Chatroom not found or user is not a member of the chatroom",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="error", type="string", example="Chatroom not found or user is not a member")
     *         )
     *     )
     * )
     */
    public function leave(Request $request, Chatroom $chatroom)
    {
        $chatroom->users()->detach($request->user()->id);

        return response()->json(['message' => 'Left chatroom']);
    }
}
