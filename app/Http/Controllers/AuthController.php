<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Karyawan;
use PragmaRX\Google2FA\Google2FA;
use Google\Authenticator\GoogleAuthenticator;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\Geofence;

class AuthController extends Controller
{
    public function proseslogin(Request $request)
    {
        // Lakukan validasi input
        $request->validate([
            'nik' => 'required',
            'password' => 'required',
        ]);

        // Autentikasi pengguna menggunakan guard 'karyawan'
        if (Auth::guard('karyawan')->attempt(['nik' => $request->nik, 'password' => $request->password])) {
            $user = Auth::guard('karyawan')->user(); // Retrieve the authenticated user

            // Verifikasi geofence
            $isLocationAllowed = $this->verifyGeofence($request->input('user_location'));

            if ($isLocationAllowed) {
                // Jika lokasi diizinkan, lanjutkan dengan proses 2FA (jika diperlukan)
                if ($user->google2fa_secret && !$user->google2fa_verified) {
                    $google2fa = new Google2FA();
                    $secret = $user->google2fa_secret;
                    $qrCodeUrl = $google2fa->getQRCodeUrl(
                        $user->nama_lengkap,
                        $request->ip(),
                        $secret
                    );
        
                    $qrCodeImage = QrCode::size(200)->generate($qrCodeUrl);
                    session()->put('qrCodeImage', $qrCodeImage);
        
                    return view('auth.2fa', compact('qrCodeImage'));
                } else {
                    // Jika tidak perlu 2FA atau sudah diverifikasi, langsung redirect ke dashboard atau halaman lain
                    return redirect()->intended(route('dashboard'));
                }
            } else {
                // Jika lokasi tidak diizinkan, logout user dan kembalikan ke halaman login
                Auth::guard('karyawan')->logout();
                return redirect('/')->withErrors(['location' => 'Lokasi Anda tidak diizinkan untuk login.']);
            }
        } else {
            // Jika autentikasi gagal, kembalikan dengan pesan error
            return redirect('/')->withErrors(['nik' => 'NIK atau password salah.']);
        }
    }

    // Method untuk verifikasi geofence
    private function verifyGeofence($userLocation)
{
    // Ambil data geofence dari tabel menggunakan model Geofence
    $geofences = Geofence::all(); // Mengambil semua data geofence

    // Lakukan logika verifikasi dengan data geofence yang didapat
    foreach ($geofences as $geofence) {
        // Ambil data latitude, longitude, dan radius dari setiap geofence
        $latitude = $geofence->latitude;
        $longitude = $geofence->longitude;
        $radius = $geofence->radius;

        // Misalnya, cek apakah lokasi pengguna berada di dalam geofence yang diizinkan
        if ($this->isPointInRadius($userLocation, $latitude, $longitude, $radius)) {
            return true; // Lokasi diizinkan
        }
    }

    return false; // Lokasi tidak diizinkan
}

// Method untuk menentukan apakah titik berada dalam radius geofence
private function isPointInRadius($userLocation, $latitude, $longitude, $radius)
{
    $userLocation = "-6.902545,-252.201360"; // Contoh nilai variabel $userLocation
    $coordinates = explode(',', $userLocation);
    // Ubah string userLocation menjadi array koordinat
    if (count($coordinates) >= 2) {
        $userLat = (float) $coordinates[0];
        $userLng = (float) $coordinates[1];

    // Hitung jarak antara lokasi pengguna dan pusat geofence
    $distance = $this->calculateDistance($userLat, $userLng, $latitude, $longitude);

    // Verifikasi apakah jarak lebih kecil dari radius geofence
    return $distance <= $radius;
}
}
// Method untuk menghitung jarak antara dua titik koordinat menggunakan formula haversine
private function calculateDistance($lat1, $lng1, $lat2, $lng2)
{
    $earthRadius = 6371; // Radius bumi dalam kilometer

    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);

    $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) * sin($dLng / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    $distance = $earthRadius * $c; // Jarak dalam kilometer

    return $distance;
}

public function proses2fa(Request $request)
{
    $request->validate([
        'code' => 'required|numeric|digits:6', // adjust the digits value according to your 2FA code length
    ]);

    $user = Auth::guard('karyawan')->user(); // or Auth::user() depending on your guard
    $google2fa = new Google2FA();
    $secretKey = $user->google2fa_secret;

    $verified = $google2fa->verifyKey($secretKey, $request->input('code'));

    if (!$verified) {
        return back()->withErrors(['code' => 'Invalid 2FA code. Please try again.']);
    }

    // If the code is valid, log the user in and redirect to the dashboard
    Auth::login($user);
    return redirect()->intended(route('dashboard'));
}


public function prosesloginadmin(Request $request)
{
    if (Auth::guard('user')->attempt(['nik' => $request->nik, 'password' => $request->password])) {
        $user = Auth::guard('user')->user(); // Retrieve the authenticated user

        if (!$user->google2fa_secret) {
            // Generate a new secret key if it doesn't exist
            $google2fa = new Google2FA();
            $secret = $google2fa->generateSecretKey();
            $user->google2fa_secret = $secret;
            $user->save(); // Update the user model
        }

        if ($user->google2fa_secret &&!$user->google2fa_verified) {
            // User has 2FA secret but hasn't verified it yet
            $google2fa = new Google2FA();
            $secret = $user->google2fa_secret;
            $qrCodeUrl = $google2fa->getQRCodeUrl(
                $user->name,
                $request->ip(),
                $secret
            );
            session()->put('qrCodeUrl', $qrCodeUrl); // Store in session
            return view('auth.2fa', compact('qrCodeUrl'));
        } else {
            // User has already verified 2FA or doesn't have 2FA secret
            return redirect()->to('/panel/dashboardadmin'); // Redirect to the dashboard
        }
    } else {
        return redirect('/panel')->with(['warning' => 'Email / Password Salah']);
    }
}

public function proses2faadmin(Request $request)
{
    $user = Auth::guard('user')->user();
    $google2fa = new Google2FA();
    $secret = $user->google2fa_secret;
    $code = $request->input('code');
    if ($google2fa->verifyKey($secret, $code)) {
        // 2FA code is valid, mark as verified and log the user in
        $user->google2fa_verified = true;
        $user->save(); // Update the user model
        Auth::guard('user')->login($user);
        return redirect()->to('/panel/dashboardadmin');
    } else {
        // 2FA code is invalid, show an error message
        return redirect()->back()->with(['error' => 'Invalid 2FA code']);
}
}
}