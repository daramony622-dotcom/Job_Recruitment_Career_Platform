<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'logo',
        'cover_image',
        'website',
        'email',
        'phone',
        'description',
        'industry',
        'company_size',
        'founded_year',
        'country',
        'city',
        'address',
        'status',
        'is_verified',
        'verified_at',
    ];

    protected $casts = [
        'is_verified'  => 'boolean',
        'verified_at'  => 'datetime',
        'founded_year' => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(JobPost::class);
    }
}
