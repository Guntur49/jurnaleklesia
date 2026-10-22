<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

#[Fillable(['name', 'username', 'position', 'role', 'password', 'journal_start_time', 'journal_end_time'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function journals(): HasMany
    {
        return $this->hasMany(Journal::class);
    }

    public function canSubmitJournalAt(?Carbon $now = null): bool
    {
        if ($this->role !== 'staff' || ! $this->journal_start_time || ! $this->journal_end_time) {
            return false;
        }

        $now ??= now();
        $start = $now->copy()->setTimeFromTimeString((string) $this->journal_start_time);
        $end = $now->copy()->setTimeFromTimeString((string) $this->journal_end_time);

        if ($end->lessThanOrEqualTo($start)) {
            if ($now->lessThan($start)) {
                $start->subDay();
            } else {
                $end->addDay();
            }
        }

        return $now->betweenIncluded($start, $end);
    }

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
        ];
    }
}
