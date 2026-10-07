<?php

namespace App\Http\Controllers\API\Contract;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contract\StoreContractRequest;
use App\Http\Requests\Contract\UpdateContractRequest;
use App\Http\Resources\Contract\ContractResource;
use App\Services\Contract\ContractService;

class ContractController extends Controller
{
    public function __construct(private readonly ContractService $contractService)
    {}

    public function show(int $bookingId)
    {
        $contract = $this->contractService->show($bookingId);

        if (! $contract) {
            return sendResponse(
                __('messages.contract.not_uploaded'),
                null
            );
        }

        return sendResponse(
            __('messages.contract.show'),
            new ContractResource($contract)
        );
    }

    public function store(StoreContractRequest $request,int $bookingId)
    {
        $data = $request->validated();
        $contract = $this->contractService->store($bookingId, $data);

        return sendResponse(
            __('messages.contract.uploaded'),
            new ContractResource($contract),
            201
        );
    }

    public function update(UpdateContractRequest $request, int $bookingId)
    {
        $data = $request->validated();
        $contract = $this->contractService->update($bookingId,$data);

        return sendResponse(
            __('messages.contract.updated'),
            new ContractResource($contract)
        );
    }
}