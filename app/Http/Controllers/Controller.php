<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * @param  list<int>  $allowed
     */
    protected function resolvePerPage(Request $request, string $param = 'per_page', int $default = 5, array $allowed = [5, 10, 25, 50, 75, 100]): int
    {
        $value = (int) $request->query($param, $default);

        return in_array($value, $allowed, true) ? $value : $default;
    }
}
