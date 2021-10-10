<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\ReservationResource;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;

class UserReservationController extends Controller
{
    public function index(): JsonResource
    {
        abort_unless(auth()->user()->tokenCan('reservations.show'), Response::HTTP_FORBIDDEN);

        validator(request()->all(), [
            'office_id' => ['integer'],
            'status' => [Rule::in([Reservation::STATUS_ACTIVE, Reservation::STATUS_CANCELLED,])],
            'from_date' => ['date', 'required_with:to_date', 'before_or_equal:to_date'],
            'to_date' => ['date', 'required_with:from_date', 'after_or_equal:from_date'],
        ])->validate();

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
                fn ($query) => $query->betweenDates($startDate, $endDate)
            )
            ->with(['office.images', 'office.featuredImage', 'office.tags'])
            ->paginate(20);

        return ReservationResource::collection($reservations);
    }

    public function create(): JsonResource
    {
        abort_unless(auth()->user()->tokenCan('reservations.make'), Response::HTTP_FORBIDDEN);
        // dd(request()->all());

        validator(request()->all(), [
            'office_id' => ['required', 'integer'],
            'from_date' => [
                'required', 'date:Y-m-d',
                'before_or_equal:to_date', 'after:' . now()->addDay()->toDateString()
            ],
            'to_date' => ['required', 'date:Y-m-d', 'after_or_equal:from_date'],
        ])->validate();

        $startDate = request('from_date');
        $endDate = request('to_date');

        try {
            $office = Office::findOrFail(request('office_id'));
        } catch (ModelNotFoundException $e) {
            throw ValidationException::withMessages([
                'office_id' => 'Invalid office_id'
            ]);
        }

        throw_if(
            $office->user_id == auth()->id(),
            ValidationException::withMessages([
                'office_id' => 'You cannot make a reservation on your own office.'
            ])
        );


        $reservation = Cache::lock('reservations_office_' . $office->id, 10)
            ->block(3, function () use ($office, $startDate, $endDate) {
                throw_if(
                    $office->reservations()->activeBetweenDates($startDate, $endDate)->exists(),
                    ValidationException::withMessages([
                        'office_id' => 'Your selected reservation dates are not available.'
                    ])
                );
                // todo: move price calculation, reservation days, discounts to model
                $numberOfDays = Carbon::parse($endDate)->endOfDay()->diffInDays(
                    Carbon::parse($startDate)->startOfDay()
                );

                $price = $numberOfDays * $office->price_per_day;

                if ($numberOfDays >= 28 && $office->monthly_discount) {
                    $price = $price - ($price * $office->monthly_discount / 100);
                }

                return Reservation::create([
                    'user_id' => auth()->id(),
                    'office_id' => $office->id,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'price' => $price,
                    'status' => Reservation::STATUS_ACTIVE

                ]);
            });

        return ReservationResource::make($reservation);
    }
}
