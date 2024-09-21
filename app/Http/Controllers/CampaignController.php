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

        $sortDirection = $request->order ?? 'asc';
        $orderBy = $this->getOrderByField($request->sort);

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
            ->join('brands', 'campaigns.brand_id', '=', 'brands.id')
            ->orderByRaw("{$orderBy} {$sortDirection}")
            ->paginate();

        return view('campaigns.index', [
            'brands' => Brand::orderBy('name', 'asc')->get(),
            'campaigns' => $campaigns,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'sort_by' => $request->sort ?? 'name',
            'order_by' => $request->order ?? 'asc',
        ]);
    }

    private function getOrderByField(?string $sort): string
    {
        return match ($sort) {
            'impressions' => 'impressions_count',
            'interactions' => 'interactions_count',
            'conversions' => 'conversions_count',
            'brand' => 'brands.name',
            'conversion_rate' => '(conversions_count / impressions_count) * 100',
            default => 'campaigns.name',
        };
    }
}
