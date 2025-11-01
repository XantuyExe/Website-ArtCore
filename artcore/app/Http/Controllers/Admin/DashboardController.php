<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, Unit, Rental, Payment, Category};
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $now = now();
        $activeRentalQuery = Rental::whereIn('status', ['ACTIVE','RETURN_REQUESTED','AWAITING_PENALTY']);
        $stats = [
            'units_total'        => Unit::count(),
            'units_available'    => Unit::where('is_available', true)->where('is_sold', false)->count(),
            'rentals_active'     => (clone $activeRentalQuery)->count(),
            'rentals_active_late'=> (clone $activeRentalQuery)->where('rental_end_plan','<', $now)->count(),
            'deposits_held'      => Payment::where('type', 'DEPOSIT')->sum('amount'),
            'return_requests'    => Rental::whereIn('status', ['RETURN_REQUESTED','AWAITING_PENALTY'])->count(),
            'users_total'        => User::count(),
        ];
        $stats['rentals_active_on_time'] = max(0, $stats['rentals_active'] - $stats['rentals_active_late']);

        $latestUnits = Unit::with('category')->statusOrdering()->orderByDesc('created_at')->limit(6)->get();
        $recentUsers = User::latest()->limit(6)->get();
        $categories  = Category::withCount('units')->orderBy('name')->limit(8)->get();
        $availableUnits = Unit::with('category')
            ->where('is_available', true)
            ->where('is_sold', false)
            ->latest()
            ->limit(5)
            ->get();
        $activeRentals = Rental::with('user','unit.category')
            ->whereIn('status',['ACTIVE','RETURN_REQUESTED','AWAITING_PENALTY'])
            ->orderByDesc('rental_start')
            ->limit(5)
            ->get();
        $purchasedRentals = Rental::with('user','unit.category')
            ->where('status','PURCHASED')
            ->orderByDesc('rental_end_actual')
            ->limit(5)
            ->get();
        $returnRequests = Rental::with('user','unit.category')
            ->whereIn('status',['RETURN_REQUESTED','AWAITING_PENALTY'])
            ->orderByDesc('return_requested_at')
            ->limit(6)
            ->get();
        $rentalHistory = Rental::with('user','unit.category')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('admin.dashboard', [
            'stats'           => $stats,
            'latestUnits'     => $latestUnits,
            'recentUsers'     => $recentUsers,
            'categories'      => $categories,
            'availableUnits'  => $availableUnits,
            'activeRentals'   => $activeRentals,
            'purchasedRentals'=> $purchasedRentals,
            'returnRequests'  => $returnRequests,
            'rentalHistory'   => $rentalHistory,
        ]);
    }
}
