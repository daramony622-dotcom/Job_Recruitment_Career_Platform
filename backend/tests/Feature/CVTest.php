<?php

namespace Tests\Feature;

use App\Models\CV;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CVTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_seeker_can_upload_a_cv(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $response = $this->post('/api/user/cv', [
            'title' => 'Backend CV',
            'file_path' => UploadedFile::fake()->create('backend-cv.pdf', 100, 'application/pdf'),
            'is_primary' => true,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.title', 'Backend CV')
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.is_primary', true);

        $cv = CV::firstOrFail();
        $this->assertSame($user->id, $cv->user_id);
        Storage::disk('public')->assertExists($cv->file_path);
    }

    public function test_cv_upload_requires_a_supported_file(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'user']));

        $response = $this->post('/api/user/cv', [
            'title' => 'Invalid CV',
            'file_path' => UploadedFile::fake()->create('cv.txt', 100, 'text/plain'),
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('file_path');
    }

    public function test_job_seeker_only_sees_and_changes_their_own_cvs(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $cv = CV::create([
            'user_id' => $otherUser->id,
            'title' => 'Private CV',
            'file_path' => 'cvs/private.pdf',
        ]);
        Sanctum::actingAs($user);

        $this->getJson('/api/user/cv')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->getJson("/api/user/cv/{$cv->id}")
            ->assertForbidden();

        $this->patchJson("/api/user/cv/{$cv->id}", ['title' => 'Changed'])
            ->assertForbidden();
    }

    public function test_setting_a_cv_as_primary_unsets_the_previous_primary_cv(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $this->post('/api/user/cv', [
            'title' => 'First CV',
            'file_path' => UploadedFile::fake()->create('first.pdf', 100, 'application/pdf'),
            'is_primary' => true,
        ])->assertOk();

        $this->post('/api/user/cv', [
            'title' => 'Second CV',
            'file_path' => UploadedFile::fake()->create('second.pdf', 100, 'application/pdf'),
            'is_primary' => true,
        ])->assertOk();

        $this->assertDatabaseHas('cvs', [
            'user_id' => $user->id,
            'title' => 'First CV',
            'is_primary' => false,
        ]);
        $this->assertDatabaseHas('cvs', [
            'user_id' => $user->id,
            'title' => 'Second CV',
            'is_primary' => true,
        ]);
    }

    public function test_job_seeker_can_replace_and_delete_a_cv_file(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $this->post('/api/user/cv', [
            'title' => 'Original CV',
            'file_path' => UploadedFile::fake()->create('original.pdf', 100, 'application/pdf'),
        ])->assertOk();

        $cv = CV::firstOrFail();
        $oldPath = $cv->file_path;

        $this->post("/api/user/cv/{$cv->id}", [
            '_method' => 'PATCH',
            'title' => 'Updated CV',
            'file_path' => UploadedFile::fake()->create('updated.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
        ])->assertOk();

        $cv->refresh();
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($cv->file_path);

        $this->deleteJson("/api/user/cv/{$cv->id}")
            ->assertOk()
            ->assertJsonPath('message', 'CV deleted successfully.');

        $this->assertDatabaseMissing('cvs', ['id' => $cv->id]);
        Storage::disk('public')->assertMissing($cv->file_path);
    }
}