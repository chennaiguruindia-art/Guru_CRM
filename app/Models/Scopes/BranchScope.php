<?php

namespace App\Models\Scopes;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * A branch sees its own visits and nobody else's.
 *
 * Applied to the whole Lead model rather than to individual controllers, so a
 * new query cannot forget the rule by accident — the visit lists, the dashboard
 * figures, the attention strip and bell, the calendar, global search and the
 * reports are all held to it at once.
 *
 * Administration is exempt and may narrow further with a branch filter.
 */
class BranchScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();

        // Console commands, queued jobs and tests without a signed-in user are
        // not a branch, so there is nothing to hold them to.
        if (! $user instanceof User) {
            return;
        }

        if ($user->canSeeAllBranches()) {
            return;
        }

        $branchId = $user->branchId();

        // Fails closed. An account whose employee record carries no branch
        // sees nothing at all rather than everything — a half-made user must
        // never quietly expose every other branch's customers.
        if ($branchId === null) {
            $builder->whereRaw('1 = 0');

            return;
        }

        $builder->where($model->qualifyColumn('branch_id'), $branchId);
    }
}
