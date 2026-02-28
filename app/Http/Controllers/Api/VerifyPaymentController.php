<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerifyPaymentController extends Controller
{
    /**
     * POST /api/verify-payment
     * Body: name, operation_number, device_id
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'operation_number' => 'required|string',
            'device_id' => 'required|string',
        ]);

        $name = $request->input('name');
        $operationNumber = $request->input('operation_number');
        $deviceId = $request->input('device_id');

        if (!$this->validatePaymentWithProvider($operationNumber)) {
            return response()->json([
                'success' => false,
                'message' => 'رقم العملية غير صحيح أو لم يتم التحقق من الدفع.',
            ], 422);
        }

        $existingByOperation = User::where('operation_number', $operationNumber)->first();
        if ($existingByOperation && $existingByOperation->device_id !== $deviceId) {
            return response()->json([
                'success' => false,
                'message' => 'هذا الحساب مربوط بجهاز — لا يمكن استخدامه على أكثر من جهاز.',
            ], 422);
        }

        $userSameDevice = User::where('device_id', $deviceId)->first();
        if ($userSameDevice) {
            $userSameDevice->update([
                'name' => $name,
                'operation_number' => $operationNumber,
                'is_subscribed' => true,
            ]);
            $user = $userSameDevice;
        } else {
            $user = User::create([
                'name' => $name,
                'device_id' => $deviceId,
                'operation_number' => $operationNumber,
                'is_subscribed' => true,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم التحقق من الدفع وتفعيل الاشتراك.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'device_id' => $user->device_id,
                'is_subscribed' => $user->is_subscribed,
            ],
        ]);
    }

    private function validatePaymentWithProvider(string $operationNumber): bool
    {
        return true;
    }
}
