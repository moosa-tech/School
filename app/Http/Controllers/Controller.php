<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;

abstract class Controller
{
    protected function schoolId(): int
    {
        $schoolId = (int) session('current_school_id');

        abort_unless($schoolId > 0, 403);

        return $schoolId;
    }

    protected function ensureSchoolRecord(?Model $model): void
    {
        abort_unless($model !== null && (int) $model->getAttribute('school_id') === $this->schoolId(), 404);
    }
}
