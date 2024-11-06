<?php

namespace App\Http\Controllers;

use App\Jobs\SendMessageJob;
use App\Models\Chatroom;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/chatrooms/{chatroom}/messages",
     *     summary="Get messages in a chatroom",
     *     description="Fetches all messages in a specific chatroom with user information.",
     *     tags={"Messages"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="chatroom",
     *         in="path",
     *         required=true,
     *         description="The ID of the chatroom",
     *
     *         @OA\Schema(type="string", example="13662cf0-ead2-48b7-a056-ded77823fe9c")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="A list of messages in the chatroom",
     *
     *         @OA\JsonContent(
     *             type="array",
     *
     *             @OA\Items(
     *                 type="object",
     *                 properties={
     *
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="text", type="string", example="Hello!"),
     *                     @OA\Property(property="attachment_path", type="string", example="storage/attachment.png"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-11-06T12:34:56"),
     *                     @OA\Property(property="user", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="John Doe")
     *                     )
     *                 }
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Chatroom not found",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Chatroom not found")
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
    public function index(Chatroom $chatroom)
    {
        $messages = $chatroom->messages()->with('user')->get();

        return response()->json($messages);
    }

    /**
     * @OA\Post(
     *     path="/api/chatrooms/{chatroomId}/messages",
     *     summary="Send a message to a chatroom",
     *     description="This endpoint retrieves messages for the specified chatroom. To receive real-time updates, use WebSocket to subscribe to the 'chat.{chatroom_id}' channel.",
     *     tags={"Messages"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="chatroomId",
     *         in="path",
     *         required=true,
     *         description="The ID of the chatroom",
     *
     *         @OA\Schema(type="string", example="13662cf0-ead2-48b7-a056-ded77823fe9c")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *
     *             @OA\Schema(
     *                 type="object",
     *
     *                 @OA\Property(property="message", type="string", example="Hello everyone!"),
     *                 @OA\Property(
     *                     property="attachment",
     *                     type="string",
     *                     format="binary",
     *                     description="Optional file attachment (image/video)"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=202,
     *         description="Message queued for processing",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Message queued for processing"),
     *             @OA\Property(property="status", type="string", example="pending")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Bad request (either message or attachment is required, or invalid file type)",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="error", type="array", items=@OA\Items(type="string", example="Message or attachment is required")),
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
     *
     * )
     */
    public function sendMessage(Request $request, $chatroomId)
    {
        $request->validate([
            'message' => 'nullable|string',
            'attachment' => 'nullable|file',
        ]);

        $chatroom = Chatroom::findOrFail($chatroomId);

        if (! $request->has('message') && ! $request->hasFile('attachment')) {
            return response()->json(['error' => 'Message or attachment is required'], 400);
        }

        $messageData = [
            'chatroom_id' => $chatroom->id,
            'user_id' => $request->user()->id,
            'text' => $request->message,
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileType = $file->getMimeType();

            if (strpos($fileType, 'image') !== false) {
                $directory = 'picture';
            } elseif (strpos($fileType, 'video') !== false) {
                $directory = 'video';
            } else {
                return response()->json(['error' => 'Invalid file type'], 400);
            }

            $filePath = $file->store("public/{$directory}");
            $fileUrl = Storage::url($filePath);
            $messageData['attachment_path'] = $fileUrl;
        }

        SendMessageJob::dispatch($messageData);

        return response()->json([
            'message' => 'Message queued for processing',
            'status' => 'pending',
        ], 202); // Using 202 Accepted for async operations
    }
}
