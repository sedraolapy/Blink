<?php

namespace App\Services\WorkingYear;

use LogicException;

class WorkingYearContext
{
    private ?int $year = null;

    public function set(int $year): void
    {
        $this->year = $year;
    }

    public function get(): int
    {
        if ($this->year === null) {
            throw new LogicException(
                __('messages.working_year_not_initialized')
            );
        }

        return $this->year;
    }

    public function has(): bool
    {
        return $this->year !== null;
    }
}