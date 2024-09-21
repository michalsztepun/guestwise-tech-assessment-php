<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

abstract class AbstractGuestActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function seedGuestActivity(string $table, array $randRange): void
    {
        $campaigns = \App\Models\Campaign::all();
        $chunkSize = 10000;
        $now = Carbon::now();

        DB::transaction(function () use ($table, $randRange, $campaigns, $chunkSize, $now) {
            $this->command->withProgressBar($campaigns, function ($campaign) use ($table, $randRange, $chunkSize, $now) {
                for ($i = 1; $i <= 30; $i++) {
                    $records = [];
                    $day = $now->copy()->subDays($i)->format('Y-m-d');
                    $activity = rand(...$randRange);

                    for ($j = 0; $j < $activity; $j++) {
                        $occurredAt = $day . ' ' . mt_rand(0, 23) . ':' . mt_rand(0,59) . ':' . mt_rand(0,59);

                            $records[] = [
                            'brand_id' => $campaign->brand_id,
                            'campaign_id' => $campaign->id,
                            'occurred_at' => $occurredAt,
                        ];

                        if (count($records) >= $chunkSize) {
                            DB::table($table)->insert($records);
                            $records = [];
                        }
                    }

                    if (!empty($records)) {
                        DB::table($table)->insert($records);
                    }
                }
            });
        });
    }
}
