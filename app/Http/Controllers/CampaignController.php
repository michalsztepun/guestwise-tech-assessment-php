<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Campaigns\ListRequest;
use App\Models\Brand;
use App\Models\Campaign;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;

class  CampaignController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ListRequest $request): View
    {
        $startDate = CarbonImmutable::create($request->start_date ?? now()->subDays(7));
        $endDate = CarbonImmutable::create($request->end_date ?? now())->endOfDay();

        $campaigns = Campaign::select('campaigns.name', 'brand_id')
            ->with(['brand' => function ($query) {
                $query->select('id', 'name');
            }])
            ->withCount([
                'impressions' => fn($query) => $query->whereBetween('occurred_at', [$startDate, $endDate]),
                'interactions' => fn($query) => $query->whereBetween('occurred_at', [$startDate, $endDate]),
                'conversions' => fn($query) => $query->whereBetween('occurred_at', [$startDate, $endDate]),
            ])
            ->when($request->brand, fn($query) => $query->where('brand_id', $request->brand))
            ->paginate();

        return view('campaigns.index', [
            'brands' => Brand::orderBy('name', 'asc')->get(),
            'campaigns' => $campaigns,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
        ]);
    }
}
