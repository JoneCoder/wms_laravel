<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\DTOs\LoginDTO;
use App\DTOs\RegisterDTO;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * @OA\Post(
     *      path="/api/v1/auth/register",
     *      operationId="registerUser",
     *      tags={"Auth"},
     *      summary="Register a new user and organization",
     *      description="Registers a new tenant organization and creates an admin user for it.",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"organization_name", "name", "email", "password", "password_confirmation"},
     *              @OA\Property(property="organization_name", type="string", example="Acme Corp"),
     *              @OA\Property(property="name", type="string", example="John Doe"),
     *              @OA\Property(property="email", type="string", format="email", example="john@acme.com"),
     *              @OA\Property(property="password", type="string", format="password", example="password123"),
     *              @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
     *          )
     *      ),
     *      @OA\Response(response=201, description="User registered successfully"),
     *      @OA\Response(response=422, description="Validation error")
     * )
     */
    public function register(RegisterRequest $request)
    {
        try {
            $dto = RegisterDTO::fromRequest($request);
            $data = $this->authService->registerUser($dto);

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => $data
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during registration.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *      path="/api/v1/auth/login",
     *      operationId="loginUser",
     *      tags={"Auth"},
     *      summary="Log in an existing user",
     *      description="Authenticates a user and returns a Sanctum Bearer token.",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"email", "password"},
     *              @OA\Property(property="email", type="string", format="email", example="admin@example.com"),
     *              @OA\Property(property="password", type="string", format="password", example="password")
     *          )
     *      ),
     *      @OA\Response(response=200, description="Login successful"),
     *      @OA\Response(response=401, description="Invalid credentials"),
     *      @OA\Response(response=422, description="Validation error"),
     *      @OA\Response(response=429, description="Too many requests")
     * )
     */
    public function login(LoginRequest $request)
    {
        $throttleKey = Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many login attempts. Please try again in ' . RateLimiter::availableIn($throttleKey) . ' seconds.',
            ], 429);
        }

        try {
            $dto = LoginDTO::fromRequest($request);
            $data = $this->authService->loginUser($dto);

            RateLimiter::clear($throttleKey);

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => $data
            ]);
        } catch (ValidationException $e) {
            RateLimiter::hit($throttleKey, 300); // Lockout for 5 minutes after 5 failed attempts

            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
                'errors' => $e->errors()
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during login.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *      path="/api/v1/auth/logout",
     *      operationId="logoutUser",
     *      tags={"Auth"},
     *      summary="Log out the current user",
     *      description="Revokes the current Sanctum token.",
     *      security={{"sanctum":{}}},
     *      @OA\Response(response=200, description="Logged out successfully"),
     *      @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during logout.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
