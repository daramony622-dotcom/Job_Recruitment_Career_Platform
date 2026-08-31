<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class JobSkill extends Pivot
{
    protected $table = 'job_skill';

    public $incrementing = true;

    public $timestamps = true; // ← Pivot disables timestamps by default; re-enable them

    protected $primaryKey = 'id';

    protected $fillable = ['job_post_id', 'skill_id', 'level', 'is_required'];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function jobPost(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(JobPost::class, 'job_post_id');
    }

    public function skill(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Skill::class, 'skill_id');
    }
}
