<?php

namespace App\Models\Traits;

use App\Models\Scopes\OrganizationScope;
use Illuminate\Database\Eloquent\Model;

trait BelongsToOrganization
{
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new OrganizationScope);

        static::creating(function (Model $model) {
            if (auth()->check() && auth()->user()->organization_id && empty($model->organization_id)) {
                $model->organization_id = auth()->user()->organization_id;
            }
        });
    }

    public function organization()
    {
        return $this->belongsTo(\App\Models\Organization::class);
    }
}
