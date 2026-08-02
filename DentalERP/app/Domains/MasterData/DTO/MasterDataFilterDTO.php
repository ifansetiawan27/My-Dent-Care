<?php

declare(strict_types=1);

namespace App\Domains\MasterData\DTO;

/**
 * MasterDataFilterDTO
 *
 * Immutable value object for paginate / list query parameters.
 * Used by MasterDataServiceInterface::paginate() to pass query options
 * from the Controller to the Service without using raw arrays.
 *
 * Shared across all 18 Master Data reference tables.
 */
final readonly class MasterDataFilterDTO
{
    /**
     * @param  int         $perPage     Records per page. Default: 15.
     * @param  string|null $search      Search keyword across code and name.
     * @param  bool        $activeOnly  When true, returns only active records.
     * @param  string      $sortBy      Column to sort by. Default: name.
     * @param  string      $sortDir     Sort direction: asc | desc.
     */
    public function __construct(
        public readonly int     $perPage    = 15,
        public readonly ?string $search     = null,
        public readonly bool    $activeOnly = false,
        public readonly string  $sortBy     = 'name',
        public readonly string  $sortDir    = 'asc',
    ) {}

    /**
     * Create a DTO from a raw params array (e.g. from $request->all()).
     * Controller calls this static factory to avoid exposing raw arrays to the Service.
     *
     * @param  array<string, mixed> $params
     */
    public static function fromArray(array $params): self
    {
        return new self(
            perPage:    (int)    ($params['per_page']    ?? 15),
            search:     isset($params['search']) && $params['search'] !== ''
                            ? (string) $params['search']
                            : null,
            activeOnly: (bool)   ($params['active_only'] ?? false),
            sortBy:     (string) ($params['sort_by']     ?? 'name'),
            sortDir:    strtolower((string) ($params['sort_dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc',
        );
    }

    /**
     * Return a DTO pre-configured for dropdown/select lists.
     * Always active only, sorted by name, with a high per_page limit.
     */
    public static function forDropdown(): self
    {
        return new self(
            perPage:    500,
            search:     null,
            activeOnly: true,
            sortBy:     'name',
            sortDir:    'asc',
        );
    }
}
