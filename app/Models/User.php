<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\ResetPasswordNotification;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'name', 'email', 'password', 'status', 'country', 'phone', 'locale',
    'identity_document_path', 'identity_verified_at', 'identity_verified_by', 'identity_rejected_reason',
])]
#[Hidden(['password', 'remember_token', 'mfa_secret', 'mfa_recovery_codes', 'identity_document_path'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $appends = ['identity_document_available'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'mfa_enabled' => 'boolean',
            'mfa_secret' => 'encrypted',
            'mfa_recovery_codes' => 'encrypted:array',
            'last_login_at' => 'datetime',
            'identity_verified_at' => 'datetime',
        ];
    }

    protected function identityDocumentAvailable(): Attribute
    {
        return Attribute::get(fn () => ! empty($this->identity_document_path));
    }

    /**
     * Overrides Laravel's default, which links to a `password.reset` named route —
     * this API has no Blade views, so the link must point at the Next.js site instead.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function identityVerifier(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'identity_verified_by');
    }

    public function mentorProfile(): HasOne
    {
        return $this->hasOne(MentorProfile::class);
    }

    public function menteeProfile(): HasOne
    {
        return $this->hasOne(MenteeProfile::class);
    }

    public function memberProfile(): HasOne
    {
        return $this->hasOne(MemberProfile::class);
    }

    public function menteePairings(): HasMany
    {
        return $this->hasMany(MentorshipPairing::class, 'mentee_id');
    }

    public function mentorPairings(): HasMany
    {
        return $this->hasMany(MentorshipPairing::class, 'mentor_id');
    }

    public function badges(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'badge_user')
            ->withPivot(['awarded_at', 'awarded_by'])
            ->withTimestamps();
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'mentee_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'actor_id');
    }
}
