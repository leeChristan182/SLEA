<?php

namespace App\Http\Controllers;

use App\Models\Otp;
use App\Models\LogIn;
use Illuminate\Http\Request;

class OtpController extends Controller
{
    // List OTPs
   public function index()
{
    $otps = Otp::with('login')->latest()->paginate(10);
    $logins = LogIn::all();
    return view('otp.index', compact('otps', 'logins'));
}


    // Show form to create an OTP (optional UI)
    public function create()
    {
        // provide logins to select the FK
        $logins = LogIn::orderByDesc('login_datetime')->limit(100)->get(['log_id','email_address']);
        return view('otp.create', compact('logins')); // create this blade if using UI
    }

    // Save new OTP
    public function store(Request $request)
    {
        $data = $request->validate([
            'log_id'   => ['required','integer','exists:log_in,log_id'],
            'otp_code' => ['required','string','max:255'],
        ]);

        $otp = Otp::create($data);

        return redirect()
            ->route('otp.show', $otp->otp_id)
            ->with('success', 'OTP created.');
    }

    // View one OTP
    public function show(Otp $otp)
    {
        $otp->load('login');
        return view('otp.show', compact('otp')); // optional blade
    }

    // Delete OTP
    public function destroy(Otp $otp)
    {
        $otp->delete();
        return redirect()->route('otp.index')->with('success', 'OTP deleted.');
    }
}
