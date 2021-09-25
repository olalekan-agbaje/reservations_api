<?php

namespace App\Http\Controllers;

use App\Models\Office;
use Illuminate\Http\Request;
use App\Http\Resources\OfficeResource;
use App\Models\Reservation;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OfficeController extends Controller
{
    public function index(): AnonymousResourceCollection
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
            ->with(['images','tags','user'])
            ->withCount(['reservations' => fn ($builder) => $builder->where('status', Reservation::STATUS_ACTIVE)])
            ->paginate(20);

        return OfficeResource::collection($offices);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
        ->load(['images','tags','user']);

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
    public function update(Request $request, office $office)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\office  $office
     * @return \Illuminate\Http\Response
     */
    public function destroy(office $office)
    {
        //
    }
}