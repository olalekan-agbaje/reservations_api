<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Office;
use App\Models\Reservation;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\OfficeResource;
use App\Models\Validators\OfficeValidator;
use App\Notifications\OfficePendingApprovalNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\ValidationException;

class OfficeController extends Controller
{
    public function index(): JsonResource
    {
        $userId = request('user_id');
        $visitorId = request('visitor_id');
        $latLng = request('lat') && request('lng');

        $offices = Office::query()
            ->where('approval_status', Office::APPROVAL_APPROVED)
            ->where('hidden', false)
            ->when($userId, fn ($builder) => $builder->whereUserId($userId))
            ->when($visitorId, fn ($builder) => $builder->whereRelation('reservations', 'user_id', '=', $visitorId))
            ->when(
                $latLng,
                fn ($builder) => $builder->nearestTo(request('lat'), request('lng')),
                fn ($builder) => $builder->orderBy('id', 'ASC'),
            )
            ->with(['images', 'tags', 'user'])
            ->withCount(['reservations' => fn ($builder) => $builder->where('status', Reservation::STATUS_ACTIVE)])
            ->paginate(20);

        return OfficeResource::collection($offices);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(): JsonResource
    {
        abort_unless(auth()->user()->tokenCan('office.create'), Response::HTTP_FORBIDDEN);

        $attributes = (new OfficeValidator())->validate(
            $office = new Office,
            request()->all()
        );

        $attributes['approval_status'] = Office::APPROVAL_PENDING;
        $attributes['user_id'] = auth()->id();

        $office = DB::transaction(function () use ($office, $attributes) {
            $office->fill(
                Arr::except($attributes, ['tags'])
            )->save();

            if (isset($attributes['tags'])) {
                $office->tags()->attach($attributes['tags']);
            }

            return $office;
        });

        Notification::send(User::isAdmin()->get(), new OfficePendingApprovalNotification($office));

        return OfficeResource::make($office->load(['images', 'tags', 'user']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\office  $office
     * @return \Illuminate\Http\Response
     */
    public function show(office $office)
    {
        $office->loadCount(['reservations' => fn ($builder) => $builder->where('status', Reservation::STATUS_ACTIVE)])
            ->load(['images', 'tags', 'user']);

        return OfficeResource::make($office);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\office  $office
     * @return \Illuminate\Http\Response
     */
    public function edit(office $office)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\office  $office
     * @return \Illuminate\Http\Response
     */
    public function update(office $office): JsonResource
    {
        abort_unless(auth()->user()->tokenCan('office.update'), Response::HTTP_FORBIDDEN);

        $this->authorize('update', $office);

        $attributes = (new OfficeValidator())->validate($office, request()->all());

        $office->fill(Arr::except($attributes, ['tags']));

        if ($notifyAdmin = $office->isDirty(['lat', 'lng', 'price_per_day'])) {
            $office->fill(['approval_status' => Office::APPROVAL_PENDING]);
        }

        DB::transaction(function () use ($office, $attributes) {
            $office->save();

            if (isset($attributes['tags'])) {
                $office->tags()->sync($attributes['tags']);
            }
        });

        if ($notifyAdmin) {
            Notification::send(User::isAdmin()->get(), new OfficePendingApprovalNotification($office));
        }
        return OfficeResource::make($office->load(['images', 'tags', 'user']));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\office  $office
     * @return \Illuminate\Http\Response
     */
    public function destroy(office $office)
    {
        abort_unless(auth()->user()->tokenCan('office.delete'), Response::HTTP_FORBIDDEN);

        $this->authorize('delete', $office);

        throw_if(
            $office->reservations()->where('status', Reservation::STATUS_ACTIVE)->count(),
            ValidationException::withMessages(['Office' => 'The office has active reservations! Cannot delete'])
        );

        $office->delete();
    }
}
