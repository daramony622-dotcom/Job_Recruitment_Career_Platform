<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Company;
use App\Models\JobCategory;
use App\Models\JobPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApplicationTest extends TestCase
{
    use RefreshDatabase;

    private User $jobSeeker;
    private User $hr;
    private User $admin;
    private Company $company;
    private JobPost $jobPost;

    protected function setUp(): void
    {
        parent::setUp();

        // Create HR user and Company
        $this->hr = User::factory()->create(['role' => 'hr']);
        $this->company = Company::create([
            'user_id' => $this->hr->id,
            'name' => 'Tech Corp',
            'slug' => 'tech-corp',
            'email' => 'contact@techcorp.com',
            'status' => 'approved',
        ]);

        // Create Job Category & Job Post
        $category = JobCategory::create(['name' => 'Engineering', 'slug' => 'engineering']);
        $this->jobPost = JobPost::create([
            'company_id' => $this->company->id,
            'category_id' => $category->id,
            'title' => 'Software Engineer',
            'slug' => 'software-engineer',
            'description' => 'Great job opportunity.',
            'job_type' => 'full_time',
            'workplace_type' => 'remote',
            'status' => 'published',
        ]);

        // Create JobSeeker and Admin
        $this->jobSeeker = User::factory()->create(['role' => 'user']);
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_job_seeker_can_apply_for_job(): void
    {
        Sanctum::actingAs($this->jobSeeker);

        $response = $this->postJson('/api/user/applications', [
            'job_post_id' => $this->jobPost->id,
            'cover_letter' => 'I am very interested in this role.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.job_post_id', $this->jobPost->id);

        $this->assertDatabaseHas('applications', [
            'job_post_id' => $this->jobPost->id,
            'user_id' => $this->jobSeeker->id,
            'status' => 'pending',
        ]);
    }

    public function test_job_seeker_cannot_apply_twice_to_same_job(): void
    {
        Sanctum::actingAs($this->jobSeeker);

        Application::create([
            'job_post_id' => $this->jobPost->id,
            'user_id' => $this->jobSeeker->id,
            'status' => 'pending',
        ]);

        $response = $this->postJson('/api/user/applications', [
            'job_post_id' => $this->jobPost->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('job_post_id');
    }

    public function test_company_can_view_and_shortlist_applicant(): void
    {
        $application = Application::create([
            'job_post_id' => $this->jobPost->id,
            'user_id' => $this->jobSeeker->id,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($this->hr);

        $response = $this->patchJson("/api/company/applicants/{$application->id}/shortlist", [
            'hr_notes' => 'Great candidate resume.',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.status', 'shortlisted');

        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'status' => 'shortlisted',
            'hr_notes' => 'Great candidate resume.',
        ]);
    }

    public function test_company_can_reject_applicant(): void
    {
        $application = Application::create([
            'job_post_id' => $this->jobPost->id,
            'user_id' => $this->jobSeeker->id,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($this->hr);

        $response = $this->patchJson("/api/company/applicants/{$application->id}/reject", [
            'rejection_reason' => 'Not enough experience.',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.status', 'rejected');

        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'status' => 'rejected',
            'rejection_reason' => 'Not enough experience.',
        ]);
    }

    public function test_job_seeker_can_withdraw_application(): void
    {
        $application = Application::create([
            'job_post_id' => $this->jobPost->id,
            'user_id' => $this->jobSeeker->id,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($this->jobSeeker);

        $response = $this->patchJson("/api/user/applications/{$application->id}/withdraw");

        $response->assertOk()
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'status' => 'withdrawn',
        ]);
    }

    public function test_admin_can_view_all_applications(): void
    {
        Application::create([
            'job_post_id' => $this->jobPost->id,
            'user_id' => $this->jobSeeker->id,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/admin/applications');

        $response->assertOk()
            ->assertJsonPath('status', 'success');
    }
}
