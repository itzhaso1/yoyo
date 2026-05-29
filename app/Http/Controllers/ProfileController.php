<?php

namespace App\Http\Controllers;

use App\Models\OperationLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.current_password' => 'كلمة المرور الحالية غير صحيحة.',
            'password.confirmed' => 'تأكيد كلمة المرور الجديدة غير مطابق.',
            'password.min' => 'كلمة المرور الجديدة يجب أن تكون 8 أحرف على الأقل.',
        ]);

        $request->user()->update([
            'password' => $data['password'],
        ]);

        OperationLog::record('تغيير كلمة المرور');

        return back()->with('status', 'تم تغيير كلمة المرور بنجاح.');
    }
}
