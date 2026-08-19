<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Enable Row Level Security (RLS) on every ordinary base table in the `public`
 * schema using a DENY-BY-DEFAULT posture: RLS is ENABLED but NO policies are
 * created.
 */
return new class extends Migration
{
    protected $connection = 'pgsql';

    /**
     * Base tables in `public` intentionally left untouched.
     *
     * @var array<int, string>
     */
    private const EXCLUDED_TABLES = [
        'migrations',
    ];

    public function up(): void
    {
        foreach ($this->publicBaseTables(rlsEnabled: false) as $table) {
            DB::statement(
                sprintf('ALTER TABLE public.%s ENABLE ROW LEVEL SECURITY', $this->quoteIdentifier($table))
            );
        }
    }

    public function down(): void
    {
        foreach ($this->publicBaseTables(rlsEnabled: true) as $table) {
            DB::statement(
                sprintf('ALTER TABLE public.%s DISABLE ROW LEVEL SECURITY', $this->quoteIdentifier($table))
            );
        }
    }

    /**
     * Ordinary base tables in `public`, filtered by their current RLS state.
     *
     * @return array<int, string>
     */
    private function publicBaseTables(bool $rlsEnabled): array
    {
        $state = $rlsEnabled ? 'true' : 'false';

        $rows = DB::select(
            "SELECT c.relname AS table_name
               FROM pg_class c
               JOIN pg_namespace n ON n.oid = c.relnamespace
              WHERE n.nspname = 'public'
                AND c.relkind = 'r'
                AND c.relrowsecurity IS {$state}
              ORDER BY c.relname"
        );

        return array_values(array_filter(
            array_map(static fn (object $row): string => $row->table_name, $rows),
            static fn (string $name): bool => ! in_array($name, self::EXCLUDED_TABLES, true)
        ));
    }

    private function quoteIdentifier(string $identifier): string
    {
        return '"' . str_replace('"', '""', $identifier) . '"';
    }
};
