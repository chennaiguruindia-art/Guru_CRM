<?php

namespace App\Models\Scopes;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * A CRM record is read by the Super Admin, by the Admin of its own branch, and
 * by the salesperson who entered it — and by nobody else.
 *
 * So a colleague in the same branch does not see another salesperson's visits
 * or clients, and an Admin does not see past their own branch. An Admin with
 * no branch of their own is held to no branch at all, rather than quietly
 * falling back to every branch.
 *
 * Applied to the whole Lead and Customer models rather than to individual
 * controllers, so a new query cannot forget the rule by accident — the lists,
 * the dashboard figures, the attention strip and bell, the calendar, global
 * search and the reports are all held to it at once.
 */
class RecordVisibilityScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();

        // Console commands, queued jobs and tests without a signed-in user are
        // not a viewer, so there is nothing to hold them to.
        if (! $user instanceof User) {
            return;
        }

        if ($user->hasRole('super-admin')) {
            return;
        }

        if ($user->hasRole('admin')) {
            $branchId = $user->branchId();

            if ($branchId === null) {
                $builder->whereRaw('1 = 0');

                return;
            }

            $builder->where($model->qualifyColumn('branch_id'), $branchId);

            return;
        }

        // Everybody else — field marketers and any future role — sees only the
        // records they personally entered.
        $builder->where($model->qualifyColumn('created_by_id'), $user->id);
    }
}
