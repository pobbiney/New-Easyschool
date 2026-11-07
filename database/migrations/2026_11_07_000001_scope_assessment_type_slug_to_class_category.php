<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('assessment_types')) {
            return;
        }

        foreach ($this->uniqueIndexesOn('assessment_types', ['slug']) as $indexName) {
            Schema::table('assessment_types', function (Blueprint $table) use ($indexName) {
                $table->dropUnique($indexName);
            });
        }

        foreach ($this->uniqueIndexesOn('assessment_types', ['school_id', 'slug']) as $indexName) {
            Schema::table('assessment_types', function (Blueprint $table) use ($indexName) {
                $table->dropUnique($indexName);
            });
        }

        if (! Schema::hasColumn('assessment_types', 'class_category_id')) {
            return;
        }

        $remaining = $this->indexNames('assessment_types');

        Schema::table('assessment_types', function (Blueprint $table) use ($remaining) {
            if (! in_array('assessment_types_category_slug_unique', $remaining, true)) {
                $table->unique(['class_category_id', 'slug'], 'assessment_types_category_slug_unique');
            }

            if (! in_array('assessment_types_category_name_unique', $remaining, true)) {
                $table->unique(['class_category_id', 'name'], 'assessment_types_category_name_unique');
            }
        });
    }

    public function down(): void
    {
        // The slug stays unique per class category. A school-wide slug unique cannot be restored
        // once the same assessment name exists in more than one category.
    }

    /**
     * @param  list<string>  $columns
     * @return list<string>
     */
    private function uniqueIndexesOn(string $table, array $columns): array
    {
        $matches = [];

        foreach ($this->indexes($table) as $name => $index) {
            if ($name === 'PRIMARY' || ! $index['unique']) {
                continue;
            }

            if ($index['columns'] === $columns) {
                $matches[] = $name;
            }
        }

        return $matches;
    }

    /**
     * @return list<string>
     */
    private function indexNames(string $table): array
    {
        return array_keys($this->indexes($table));
    }

    /**
     * @return array<string, array{unique: bool, columns: list<string>}>
     */
    private function indexes(string $table): array
    {
        $indexes = [];

        foreach (DB::select('SHOW INDEX FROM `'.$table.'`') as $row) {
            $name = (string) $row->Key_name;
            $indexes[$name]['unique'] = (int) $row->Non_unique === 0;
            $indexes[$name]['columns'][(int) $row->Seq_in_index] = (string) $row->Column_name;
        }

        foreach ($indexes as $name => $index) {
            ksort($index['columns']);
            $indexes[$name]['columns'] = array_values($index['columns']);
        }

        return $indexes;
    }
};
