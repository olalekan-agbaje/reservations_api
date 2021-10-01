<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class UserReservationController extends Controller
{
    public function index(): JsonResource
    {
        abort_unless(auth()->user()->tokenCan('reservations.show'), Response::HTTP_FORBIDDEN);

        $userId = auth()->id();
        $officeId = request('office_id');
        $status = request('status');
        $startDate = request('from_date');
        $endDate = request('to_date');
        $dateSearch = $startDate && $endDate;

        $reservations = Reservation::query()
            ->where('user_id', $userId)
            ->when(
                $officeId,
                fn ($query) => $query->where('office_id', $officeId)
            )
            ->when(
                $status,
                fn ($query) => $query->where('status', $status)
            )
            ->when(
                $dateSearch, 
                function($query) use ($startDate, $endDate){
                    $query->whereBetween('start_date',[$startDate, $endDate])
                    ->orWhereBetween('end_date',[$startDate, $endDate]);
                }
            )
            ->with(['office.images','office.featuredImage','office.tags'])
            ->paginate(20);

            return ReservationResource::collection($reservations);
    }
}
