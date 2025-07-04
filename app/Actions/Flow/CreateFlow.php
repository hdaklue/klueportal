<?php

declare(strict_types=1);

namespace App\Actions\Flow;

use Throwable;
use App\Models\Flow;
use App\Models\User;
use App\Models\Stage;
use App\Models\Tenant;
use Illuminate\Bus\Batch;
use App\Enums\Role\RoleEnum;
use Illuminate\Support\Carbon;
use App\DTOs\Flow\CreateFlowDto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use App\Actions\Roleable\AddParticipant;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Exceptions\Flow\FlowCreationException;

// TODO:Move to top order after create
class CreateFlow
{
    use AsAction;

    public function handle(CreateFlowDto $data, Tenant $tenant, User $creator)
    {

        try {
            return DB::transaction(function () use ($creator, $tenant, $data) {
                $flow = $this->makeFlow($data);
                $flow->tenant()->associate($tenant);
                $flow->creator()->associate($creator);
       
                $flow->save();
                $this->attachFlowStages($flow, $data, $tenant);
                $flow->addParticipant($creator, RoleEnum::ADMIN->value, true);

                DB::afterCommit(function () use ($flow, $data, $tenant) {
                    if ($data->hasParticipants()) {
                        $this->dispatchAddParticipantBatchJobs($flow, $data->participants, $tenant);
                    }
                });

                return $flow;
            });

        } catch (QueryException $e) {
            Log::error('Database error creating flow', [
                'tenant_id' => $tenant->id,
                'creator_id' => $creator->id,
                'error' => $e->getMessage(),
            ]);
            throw new FlowCreationException('Failed to create flow due to database constraints');
        } catch (\Exception $e) {
            Log::error('Unexpected error creating flow', [
                'tenant_id' => $tenant->id,
                'creator_id' => $creator->id,
                'error' => $e->getMessage(),
            ]);
            throw new FlowCreationException('An unexpected error occurred while creating the flow');
        }
    }

    protected function attachFlowStages(Flow $flow, CreateFlowDto $data, Tenant $tenant)
    {
        $stages = $data->template->stages->map(function ($stage, $tenant) {
            $stage = Stage::make([
                'name' => $stage->name,
                'color' => $stage->color,
                'order' => $stage->order,
                'meta' => $stage->meta,
            ]);

            return $stage;
        });

        $flow->stages()->createMany($stages->toArray());
    }

    protected function makeFlow(CreateFlowDto $data): Flow
    {
        

        return Flow::make([
            'title' => $data->title,
            'start_date' => $data->start_date,
            'due_date' => $data->due_date,
            'status' => $data->status,
        ]);
    }

    protected function dispatchAddParticipantBatchJobs(Flow $flow, array $participants, Tenant $tenant)
    {
        $jobs = User::whereIn('id', $participants)
            ->get()->map(function ($user) use ($flow, $tenant) {
                $role = RoleEnum::from($user->rolesOn($tenant)->first()->name);

                return AddParticipant::makeJob($flow, $user, $role, $tenant);
            })->toArray();

        Bus::batch($jobs)
            ->name("Add participants to flow {$flow->id}")
            ->allowFailures()
            ->catch(function (Batch $batch, Throwable $e) use ($flow) {
                Log::error('Some participants failed to be added', [
                    'flow_id' => $flow->id,
                    'batch_id' => $batch->id,
                    'error' => $e->getMessage(),
                ]);
            })
            ->dispatch();
    }
}
