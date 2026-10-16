<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Authorization test — memastikan student tidak bisa mengakses
 * area teacher dan sebaliknya (spec #4/#5/#34).
 */
class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /*
    |--------------------------------------------------------------------------
    | Student area
    |--------------------------------------------------------------------------
    */

    public function test_student_can_access_student_dashboard(): void
    {
        $student = User::factory()->student()->create();

        $response = $this->actingAs($student)->get(route('student.dashboard'));

        $response->assertOk();
        $response->assertSee($student->name);
    }

    public function test_guest_is_redirected_to_login_when_accessing_student_area(): void
    {
        $response = $this->get(route('student.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_teacher_cannot_access_student_area(): void
    {
        $teacher = User::factory()->teacher()->create();

        $response = $this->actingAs($teacher)->get(route('student.dashboard'));

        $response->assertRedirect(route('teacher.dashboard'));
        $response->assertSessionHas('error');
    }

    /*
    |--------------------------------------------------------------------------
    | Teacher area
    |--------------------------------------------------------------------------
    */

    public function test_teacher_can_access_teacher_dashboard(): void
    {
        $teacher = User::factory()->teacher()->create();

        $response = $this->actingAs($teacher)->get(route('teacher.dashboard'));

        $response->assertOk();
        $response->assertSee($teacher->name);
    }

    public function test_guest_is_redirected_to_login_when_accessing_teacher_area(): void
    {
        $response = $this->get(route('teacher.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_student_cannot_access_teacher_area(): void
    {
        $student = User::factory()->student()->create();

        $response = $this->actingAs($student)->get(route('teacher.dashboard'));

        $response->assertRedirect(route('student.dashboard'));
        $response->assertSessionHas('error');
    }

    /*
    |--------------------------------------------------------------------------
    | Landing page
    |--------------------------------------------------------------------------
    */

    public function test_landing_page_can_be_rendered_by_guest(): void
    {
        $response = $this->get(route('landing'));

        $response->assertOk();
        $response->assertSee('SignVerse');
    }
}
