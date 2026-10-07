<?php

namespace App\Services\Contract;

use App\Enums\ContractStatusEnum;
use App\Models\Booking;
use App\Models\Contract;
use App\Services\WorkingYear\WorkingYearContext;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ContractService
{
    public function __construct(private readonly WorkingYearContext $workingYearContext)
    {}

    public function show(int $bookingId): ?Contract
    {
        $year = $this->workingYearContext->get();

        $booking = Booking::query()
            ->where('year', $year)
            ->findOrFail($bookingId);

        return Contract::query()
            ->where('booking_id', $booking->id)
            ->first();
    }

    public function store(int $bookingId, array $data)
    {
        return DB::transaction(function () use ($bookingId, $data) {
            $year = $this->workingYearContext->get();

            $booking = Booking::query()
                ->where('year', $year)
                ->lockForUpdate()
                ->findOrFail($bookingId);

            $this->ensureContractDoesNotExist($booking);

            $contract = Contract::query()->create([
                'booking_id' => $booking->id,
                'contract_number' => $data['contract_number'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'status' => $this->resolveStatus(
                    $data['start_date'],
                    $data['end_date']
                )->value,
            ]);

            foreach ($data['contract_images'] as $image) {
                $contract
                    ->addMedia($image)
                    ->toMediaCollection('contract_images');
            }

            foreach ($data['attachment_images'] ?? [] as $image) {
                $contract
                    ->addMedia($image)
                    ->toMediaCollection('attachment_images');
            }

            return $contract->refresh();
        });
    }

    private function ensureContractDoesNotExist(Booking $booking)
    {
        $exists = Contract::query()
            ->where('booking_id', $booking->id)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'booking_id' => [
                    __('messages.contract.already_exists'),
                ],
            ]);
        }
    }

    private function resolveStatus(string $startDate,string $endDate)
    {
        $today = now()->startOfDay();

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();

        if ($today->lt($start)) {
            return ContractStatusEnum::WAITING_START;
        }

        if ($today->gt($end)) {
            return ContractStatusEnum::FINISHED;
        }

        return ContractStatusEnum::IN_PROGRESS;
    }

    public function update(int $bookingId,array $data): Contract
    {
        return DB::transaction(function () use ($bookingId, $data) {
            $year = $this->workingYearContext->get();

            $booking = Booking::query()
                ->where('year', $year)
                ->lockForUpdate()
                ->findOrFail($bookingId);

            $contract = Contract::query()
                ->where('booking_id', $booking->id)
                ->lockForUpdate()
                ->firstOrFail();

            $contract->update([
                'contract_number' => $data['contract_number'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'status' => $this->resolveStatus(
                    $data['start_date'],
                    $data['end_date']
                )->value,
            ]);

            $this->deleteMedia(
                $contract,
                'contract_images',
                $data['deleted_contract_image_ids'] ?? []
            );

            $this->deleteMedia(
                $contract,
                'attachment_images',
                $data['deleted_attachment_image_ids'] ?? []
            );

            foreach ($data['contract_images'] ?? [] as $image) {
                $contract
                    ->addMedia($image)
                    ->toMediaCollection('contract_images');
            }

            foreach ($data['attachment_images'] ?? [] as $image) {
                $contract
                    ->addMedia($image)
                    ->toMediaCollection('attachment_images');
            }

            return $contract->refresh();
        });
    }

    private function deleteMedia(Contract $contract,string $collection,array $mediaIds)
    {
        if (empty($mediaIds)) {
            return;
        }

        $contract
            ->media()
            ->where('collection_name', $collection)
            ->whereIn('id', $mediaIds)
            ->get()
            ->each
            ->delete();
    }

}