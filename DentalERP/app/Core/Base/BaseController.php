<?php

declare(strict_types=1);

namespace App\Core\Base;

use Illuminate\Routing\Controller;

/**
 * BaseController
 *
 * Abstract base class for all API controllers in the application.
 * Extends Laravel's base Controller to inherit middleware capabilities.
 *
 * Layer rule: No business logic. No direct DB queries.
 * Controllers validate input → call Service → return ApiResponse.
 */
abstract class BaseController extends Controller
{
    //
}
