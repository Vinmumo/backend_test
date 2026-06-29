<?php

namespace App\Http\Controllers\Api;

use App\Actions\TicketTiers\CreateTicketTierAction;
use App\Actions\TicketTiers\DeleteTicketTierAction;
use App\Actions\TicketTiers\PublishTicketTierAction;
use App\Actions\TicketTiers\UpdateTicketTierAction;
use App\Data\CreateTicketTierData;
use App\Data\UpdateTicketTierData;
use App\Http\Controllers\Controller;
use App\Http\Resources\MutationResource;
use App\Http\Resources\TicketTierResource;
use App\Models\TicketTier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class TicketTierController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', TicketTier::class);

        $ticketTiers = QueryBuilder::for(TicketTier::query()->latest())
            ->allowedFilters([
                AllowedFilter::exact('event_id'),
                AllowedFilter::callback('channel', fn ($query, $value) => $query->availableOnChannel($value)),
            ])
            ->allowedSorts(['name', 'price', 'created_at'])
            ->allowedIncludes(['event'])
            ->paginate($this->perPage($request))
            ->appends($request->query());

        return TicketTierResource::collection($ticketTiers);
    }

    public function store(Request $request, CreateTicketTierAction $action)
    {
        $this->authorize('create', TicketTier::class);

        DB::beginTransaction();

        try {
            $ticketTier = $action->execute(CreateTicketTierData::from($request));

            DB::commit();

            return (new MutationResource(
                new TicketTierResource($ticketTier),
                __('Ticket tier created successfully.')
            ))->response()->setStatusCode(201);
        } catch (ValidationException $exception) {
            DB::rollBack();

            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Failed to create ticket tier.', ['exception' => $exception]);

            throw new HttpException(500, __('Unable to create ticket tier.'), $exception);
        }
    }

    public function show(TicketTier $ticketTier)
    {
        $this->authorize('view', $ticketTier);

        return new TicketTierResource($ticketTier);
    }

    public function update(Request $request, TicketTier $ticketTier, UpdateTicketTierAction $action)
    {
        $this->authorize('update', $ticketTier);

        DB::beginTransaction();

        try {
            $ticketTier = $action->execute($ticketTier, UpdateTicketTierData::from($request));

            DB::commit();

            return new MutationResource(
                new TicketTierResource($ticketTier),
                __('Ticket tier updated successfully.')
            );
        } catch (ValidationException $exception) {
            DB::rollBack();

            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Failed to update ticket tier.', ['exception' => $exception]);

            throw new HttpException(500, __('Unable to update ticket tier.'), $exception);
        }
    }

    public function destroy(TicketTier $ticketTier, DeleteTicketTierAction $action)
    {
        $this->authorize('delete', $ticketTier);

        DB::beginTransaction();

        try {
            $resource = new TicketTierResource($ticketTier);

            $action->execute($ticketTier);

            DB::commit();

            return new MutationResource($resource, __('Ticket tier deleted successfully.'));
        } catch (ValidationException $exception) {
            DB::rollBack();

            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Failed to delete ticket tier.', ['exception' => $exception]);

            throw new HttpException(500, __('Unable to delete ticket tier.'), $exception);
        }
    }

    public function publish(TicketTier $ticketTier, PublishTicketTierAction $action)
    {
        $this->authorize('publish', $ticketTier);

        DB::beginTransaction();

        try {
            $ticketTier = $action->execute($ticketTier);

            DB::commit();

            return new MutationResource(
                new TicketTierResource($ticketTier),
                __('Ticket tier published successfully.')
            );
        } catch (ValidationException $exception) {
            DB::rollBack();

            throw $exception;
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Failed to publish ticket tier.', ['exception' => $exception]);

            throw new HttpException(500, __('Unable to publish ticket tier.'), $exception);
        }
    }

    private function perPage(Request $request): int
    {
        return min(max($request->integer('per_page', 15), 1), 100);
    }
}
