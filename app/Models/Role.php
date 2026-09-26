<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function givePermission(Permission|string $permission): void
    {
        if (is_string($permission)) {
            $permission = Permission::firstOrCreate(['slug' => $permission], [
                'name' => ucwords(str_replace(['-', '.'], ' ', $permission)),
            ]);
        }

        $this->permissions()->syncWithoutDetaching([$permission->id]);
    }
}
