<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use App\Http\Resources\ReservationResource;
use Illuminate\Http\Resources\Json\JsonResource;

class HostReservationController extends Controller
{
    public function index(): JsonResource
    {
        abort_unless(auth()->user()->tokenCan('reservations.show'), Response::HTTP_FORBIDDEN);

        validator(request()->all(), [
            'office_id' => ['integer'],
            'user_id' => ['integer'],
            'status' => [Rule::in([Reservation::STATUS_ACTIVE, Reservation::STATUS_CANCELLED,])],
            'from_date' => ['date', 'required_with:to_date', 'before_or_equal:to_date'],
            'to_date' => ['date', 'required_with:from_date', 'after_or_equal:from_date'],
        ])->validate();

        $userId = auth()->id();
        $officeId = request('office_id');
        $reservationUserId = request('user_id');
        $status = request('status');
        $startDate = request('from_date');
        $endDate = request('to_date');
        $dateSearch = $startDate && $endDate;

        $reservations = Reservation::query()
            ->whereRelation('office', 'user_id', '=', $userId)
            ->when(
                $officeId,
                fn ($query) => $query->where('office_id', $officeId)
            )
            ->when(
                $reservationUserId,
                fn ($query) => $query->where('user_id', $reservationUserId)
            )
            ->when(
                $status,
                fn ($query) => $query->where('status', $status)
            )
            ->when(
                $dateSearch,
                fn ($query) => $query->betweenDates($startDate, $endDate)
            )
            ->with(['office.images', 'office.featuredImage', 'office.tags'])
            ->paginate(20);

        return ReservationResource::collection($reservations);
    }
}
