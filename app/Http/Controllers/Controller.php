<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * Base controller. Controllers contain no business logic;
 * they delegate to Services and Repositories only.
 */
abstract class Controller
{
    use AuthorizesRequests;
}
