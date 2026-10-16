<?php

namespace Tests\Feature\Auth;

use App\Models\Assignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_register_as_student_with_student_profile(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertGuest();

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame(User::ROLE_STUDENT, $user->role);
        $this->assertNotNull($user->student, 'Profil student 1:1 harus dibuat saat registrasi.');

        $response->assertRedirect(route('login', absolute: false));
        $response->assertSessionHas('status');
    }

    public function test_new_student_receives_active_assignments_but_not_expired_ones(): void
    {
        $active = Assignment::factory()->create(['due_at' => now()->addDays(3)]);
        $noDeadline = Assignment::factory()->create(['due_at' => null]);
        Assignment::factory()->create(['due_at' => now()->subDay()]);

        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $student = User::where('email', 'test@example.com')->first()->student;

        $this->assertEqualsCanonicalizing(
            [$active->id, $noDeadline->id],
            $student->submissions()->pluck('assignment_id')->all(),
        );
    }

    public function test_registered_user_can_log_in_after_registration(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->get(route('login'))->assertSee('Registrasi berhasil!');

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('student.dashboard', absolute: false));
    }
}
