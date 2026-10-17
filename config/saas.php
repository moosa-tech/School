<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Support access into a school tenant
    |--------------------------------------------------------------------------
    |
    | When false (default), platform Super Admins cannot enter school-admin
    | operational routes or impersonate a school context. Enable only with an
    | explicit, audited support process.
    |
    */
    'support_access_enabled' => (bool) env('SAAS_SUPPORT_ACCESS_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Allowed school membership roles (never includes platform Super Admin)
    |--------------------------------------------------------------------------
    */
    'school_roles' => [
        'school_admin',
        'principal',
        'teacher',
        'accountant',
        'staff',
        'student',
        'parent',
    ],

];
