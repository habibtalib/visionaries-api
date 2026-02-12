<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'display_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'display_name' => $data['display_name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'auth_provider' => 'email',
        ]);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => ['Invalid credentials.']]);
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'display_name' => 'sometimes|string|max:100',
            'avatar_url' => 'sometimes|nullable|string',
            'language' => 'sometimes|string|in:en,ms,ar',
            'niyyah' => 'sometimes|nullable|string',
            'onboarding_completed' => 'sometimes|boolean',
        ]);

        $request->user()->update($data);
        return response()->json($request->user()->fresh());
    }

    /**
     * Redirect to Google OAuth (web flow).
     */
    public function googleRedirect()
    {
        $url = Socialite::driver('google')
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        return response()->json(['url' => $url]);
    }

    /**
     * Handle Google OAuth callback (web flow).
     */
    public function googleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Google authentication failed.'], 401);
        }

        $result = $this->findOrCreateGoogleUser($googleUser->getId(), $googleUser->getEmail(), $googleUser->getName(), $googleUser->getAvatar());

        $token = $result->createToken('api')->plainTextToken;

        return response()->json(['user' => $result, 'token' => $token]);
    }

    /**
     * Validate Google ID token from mobile/SPA and return Sanctum token.
     */
    public function googleToken(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string',
        ]);

        // Verify the ID token with Google
        $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
            'id_token' => $request->id_token,
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Invalid Google ID token.'], 401);
        }

        $payload = $response->json();

        // Verify the audience matches our client ID
        $clientId = config('services.google.client_id');
        if ($payload['aud'] !== $clientId) {
            return response()->json(['error' => 'Token audience mismatch.'], 401);
        }

        $user = $this->findOrCreateGoogleUser(
            $payload['sub'],
            $payload['email'],
            $payload['name'] ?? $payload['email'],
            $payload['picture'] ?? null
        );

        $token = $user->createToken('api')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token]);
    }

    /**
     * Find or create a user from Google data.
     */
    private function findOrCreateGoogleUser(string $googleId, string $email, string $name, ?string $avatar): User
    {
        // First try to find by google_id
        $user = User::where('google_id', $googleId)->first();
        if ($user) {
            return $user;
        }

        // Then try by email (link existing account)
        $user = User::where('email', $email)->first();
        if ($user) {
            $user->update([
                'google_id' => $googleId,
                'auth_provider' => 'google',
                'email_verified' => true,
            ]);
            return $user;
        }

        // Create new user
        return User::create([
            'email' => $email,
            'display_name' => $name,
            'avatar_url' => $avatar,
            'google_id' => $googleId,
            'auth_provider' => 'google',
            'email_verified' => true,
        ]);
    }
}
