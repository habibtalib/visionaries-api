<?php
namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuid, SoftDeletes;

    protected $fillable = [
        'email', 'password', 'display_name', 'avatar_url', 'language',
        'onboarding_completed', 'niyyah', 'auth_provider', 'email_verified',
    ];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'onboarding_completed' => 'boolean',
            'email_verified' => 'boolean',
        ];
    }
    public function vision() { return $this->hasOne(Vision::class); }
    public function visionVersions() { return $this->hasMany(VisionVersion::class); }
    public function userTraits() { return $this->hasMany(UserTrait::class); }
    public function actions() { return $this->hasMany(Action::class); }
    public function actionCheckIns() { return $this->hasMany(ActionCheckIn::class); }
    public function journalEntries() { return $this->hasMany(JournalEntry::class); }
    public function reviews() { return $this->hasMany(Review::class); }
    public function checkIns() { return $this->hasMany(CheckIn::class); }
    public function quizAttempts() { return $this->hasMany(QuizAttempt::class); }
    public function timelineEvents() { return $this->hasMany(TimelineEvent::class); }
}
