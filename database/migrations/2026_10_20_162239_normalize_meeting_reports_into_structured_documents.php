<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('meetings') || ! Schema::hasColumn('meetings', 'reports')) {
            return;
        }

        DB::table('meetings')
            ->whereNotNull('reports')
            ->orderBy('id')
            ->chunk(100, function ($meetings) {
                foreach ($meetings as $meeting) {
                    $reports = json_decode($meeting->reports, true);

                    if (! is_array($reports)) {
                        continue;
                    }

                    $normalized = array_values(array_filter(array_map(function ($report) {
                        if (is_array($report)) {
                            return [
                                'title' => $report['title'] ?? null,
                                'description' => $report['description'] ?? null,
                                'url' => $report['url'] ?? null,
                            ];
                        }

                        if (! is_string($report) || $report === '') {
                            return null;
                        }

                        return [
                            'title' => null,
                            'description' => null,
                            'url' => $report,
                        ];
                    }, $reports)));

                    DB::table('meetings')
                        ->where('id', $meeting->id)
                        ->update(['reports' => json_encode($normalized)]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('meetings') || ! Schema::hasColumn('meetings', 'reports')) {
            return;
        }

        DB::table('meetings')
            ->whereNotNull('reports')
            ->orderBy('id')
            ->chunk(100, function ($meetings) {
                foreach ($meetings as $meeting) {
                    $reports = json_decode($meeting->reports, true);

                    if (! is_array($reports)) {
                        continue;
                    }

                    $urls = array_values(array_filter(array_map(
                        fn ($report) => is_array($report) ? ($report['url'] ?? null) : $report,
                        $reports
                    )));

                    DB::table('meetings')
                        ->where('id', $meeting->id)
                        ->update(['reports' => json_encode($urls)]);
                }
            });
    }
};
