<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Student ID Prefix
    |--------------------------------------------------------------------------
    |
    | Prefix used when auto-generating student IDs, e.g. MDA produces MDA-001.
    |
    */

    'student_id_prefix' => env('STUDENT_ID_PREFIX', 'STD'),

    /*
    |--------------------------------------------------------------------------
    | Student ID Number Padding
    |--------------------------------------------------------------------------
    |
    | Number of digits used after the prefix, e.g. 3 produces MDA-001.
    |
    */

    'student_id_pad_length' => (int) env('STUDENT_ID_PAD_LENGTH', 3),

];
