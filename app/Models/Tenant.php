<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasStaticTypeTrait;
use App\Concerns\Roles\RoleableEntity;
use App\Contracts\HasStaticType;
use App\Contracts\Roles\HasParticipants;
use App\Contracts\Roles\Roleable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model implements HasParticipants, HasStaticType, Roleable
{
    /** @use HasFactory<\Database\Factories\TenantFactory> */
    /** @use HasUlids */
    use HasFactory, HasStaticTypeTrait, HasUlids, RoleableEntity;

    protected $fillable = ['name'];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->using(TenantUser::class);
    }

    public function removeMember(User|array $user)
    {
        $this->members()->detach($user);
    }

    public function addMember(User|array $user)
    {
        $this->members()->attach($user);
    }

    public function systemRoles(): HasMany
    {
        return $this->hasMany(config('permission.models.role'), config('permission.column_names.team_foreign_key'));
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function getTypeName(): string
    {
        return 'Team';
    }

    public function flows(): HasMany
    {
        return $this->hasMany(Flow::class);
    }
}
