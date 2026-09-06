<?php

namespace Tests\Feature;

use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SkillTest extends TestCase
{
    use RefreshDatabase;

    private const SKILLS_ENDPOINT = '/api/admin/skills';

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($this->admin);
    }

    public function test_admin_can_list_and_filter_skills(): void
    {
        Skill::create([
            'name' => 'PHP',
            'category' => 'Backend',
            'is_active' => true,
        ]);
        Skill::create([
            'name' => 'Photoshop',
            'category' => 'Design',
            'is_active' => false,
        ]);

        $response = $this->getJson(self::SKILLS_ENDPOINT . '?category=Backend&is_active=1');

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.name', 'PHP');
    }

    public function test_admin_can_create_show_update_and_delete_a_skill(): void
    {
        $createResponse = $this->postJson(self::SKILLS_ENDPOINT, [
            'name' => 'Laravel',
            'category' => 'Backend',
            'description' => 'PHP web framework',
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.name', 'Laravel')
            ->assertJsonPath('data.slug', 'laravel')
            ->assertJsonPath('data.is_active', true);

        $skill = Skill::where('name', 'Laravel')->firstOrFail();

        $this->getJson("/api/admin/skills/{$skill->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $skill->id);

        $this->putJson("/api/admin/skills/{$skill->id}", [
            'name' => 'Laravel Framework',
            'is_active' => false,
        ])->assertOk()
            ->assertJsonPath('data.name', 'Laravel Framework')
            ->assertJsonPath('data.is_active', false);

        $this->deleteJson("/api/admin/skills/{$skill->id}")
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseMissing('skills', ['id' => $skill->id]);
    }

    public function test_skill_creation_requires_a_unique_name(): void
    {
        Skill::create(['name' => 'PHP']);

        $this->postJson(self::SKILLS_ENDPOINT, ['name' => 'PHP'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

    public function test_skill_creation_validates_the_request_payload(): void
    {
        $this->postJson(self::SKILLS_ENDPOINT, [
            'name' => '',
            'category' => str_repeat('a', 256),
            'is_active' => 'not-a-boolean',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'category', 'is_active']);
    }

    public function test_skill_update_rejects_a_duplicate_name(): void
    {
        $existingSkill = Skill::create(['name' => 'PHP']);
        $skill = Skill::create(['name' => 'Laravel']);

        $this->putJson(self::SKILLS_ENDPOINT . "/{$skill->id}", [
            'name' => $existingSkill->name,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

    public function test_non_admin_cannot_manage_skills(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'user']));

        $this->getJson(self::SKILLS_ENDPOINT)->assertForbidden();
    }

    public function test_guest_cannot_access_skill_api(): void
    {
        Auth::guard('sanctum')->forgetUser();

        $this->getJson(self::SKILLS_ENDPOINT)->assertUnauthorized();
    }
}
