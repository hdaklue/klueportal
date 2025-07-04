<?php

declare(strict_types=1);

namespace App\Concerns\Progress;

use App\Services\Flow\TimeProgressService;
use App\ValueObjects\Percentage;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

trait HasTimeProgress
{
    /**
     * Boot the HasTimeProgress trait
     */
    public static function bootHasTimeProgress()
    {
        static::saving(function ($model) {
            if (! $model->hasValidProgressDates()) { // $this refers to the model
                throw new InvalidArgumentException('Model must have valid progress dates');
            }
        });

        static::updating(function ($model) {
            if (! $model->hasValidProgressDates()) { // $this refers to the model
                throw new InvalidArgumentException('Model must have valid progress dates');
            }
        });

    }

    /**
     * Get the start date for progress calculation
     * Override this method if your start date attribute has a different name
     */
    public function getProgressStartDate(): Carbon
    {
        $startDateAttribute = $this->getProgressStartDateAttribute();

        if (! $this->hasAttribute($startDateAttribute)) {
            throw new InvalidArgumentException(
                'Model ' . static::class . " does not have attribute '{$startDateAttribute}' for progress start date. " .
                'Override getProgressStartDateAttribute() to specify the correct attribute name.',
            );
        }

        return $this->{$startDateAttribute};
    }

    /**
     * Get the due date for progress calculation
     * Override this method if your due date attribute has a different name
     */
    public function getProgressDueDate(): Carbon
    {
        $dueDateAttribute = $this->getProgressDueDateAttribute();

        if (! $this->hasAttribute($dueDateAttribute)) {
            throw new InvalidArgumentException(
                'Model ' . static::class . " does not have attribute '{$dueDateAttribute}' for progress due date. " .
                'Override getProgressDueDateAttribute() to specify the correct attribute name.',
            );
        }

        return $this->{$dueDateAttribute};
    }

    /**
     * Get the due date for progress calculation
     * Override this method if your due date attribute has a different name
     */
    public function getProgressCompletedDate(): ?Carbon
    {
        $completedDateAttribute = $this->getProgressCompletedDateAttribute();

        if (! $this->hasAttribute($completedDateAttribute)) {
            throw new InvalidArgumentException(
                'Model ' . static::class . " does not have attribute '{$completedDateAttribute}' for progress due date. " .
                'Override getProgressDueDateAttribute() to specify the correct attribute name.',
            );
        }

        return $this->{$completedDateAttribute};
    }

    /**
     * Check if the progressable item has valid progress dates
     */
    public function hasValidProgressDates(): bool
    {
        try {
            $startDate = $this->getProgressStartDate();
            $dueDate = $this->getProgressDueDate();
            $completedDate = $this->getProgressCompletedDate();

            // Basic validation: start and due dates must exist and be valid
            $hasValidBasicDates = isset($startDate, $dueDate) &&
                                 $startDate instanceof Carbon &&
                                 $dueDate instanceof Carbon &&
                                 $dueDate->gte($startDate);

            if (! $hasValidBasicDates) {
                return false;
            }

            // Optional validation: if completed date exists, it should be valid
            if ($completedDate !== null) {
                return $completedDate instanceof Carbon &&
                       $completedDate->gte($startDate); // Completed date should be after start
            }

            return true;
        } catch (InvalidArgumentException $e) {
            // If attributes don't exist, dates are not valid
            return false;
        }
    }

    /**
     * Get a unique identifier for the progressable item
     * Override this method if your primary key has a different name
     */
    public function getProgressableId(): mixed
    {
        return $this->getKey();
    }

    // =======================
    // Convenience Methods
    // =======================

    /**
     * Get time-based progress percentage
     */
    public function getTimeProgressPercentage(): Percentage
    {
        return app(TimeProgressService::class)->calculateTimeProgress($this);
    }

    /**
     * Get current progress status
     */
    public function getCurrentProgressStatus(): string
    {
        return app(TimeProgressService::class)->getProgressStatus($this);
    }

    /**
     * Get progress color for UI
     */
    public function getProgressColor(): string
    {
        return app(TimeProgressService::class)->getProgressColor($this);
    }

    /**
     * Get comprehensive progress details
     */
    public function getProgressDetails(): array
    {
        return app(TimeProgressService::class)->getProgressDetails($this);
    }

    /**
     * Check if the item is overdue
     */
    public function isOverdue(): bool
    {
        return app(TimeProgressService::class)->isPastDue($this);
    }

    /**
     * Get days remaining (positive only)
     */
    public function getDaysRemainingPositive(): int
    {
        return app(TimeProgressService::class)->getDaysRemainingPositive($this);
    }

    /**
     * Get total project duration in days
     */
    public function getTotalDays(): int
    {
        return app(TimeProgressService::class)->getTotalDays($this);
    }

    /**
     * Get the attribute name for the start date
     * Override this method to customize the start date attribute
     */
    public function getProgressStartDateAttribute(): string
    {
        return 'start_date';
    }

    /**
     * Get the attribute name for the due date
     * Override this method to customize the due date attribute
     */
    public function getProgressDueDateAttribute(): string
    {
        return 'due_date';
    }

    /**
     * Get the attribute name for the status
     * Override this method to customize the status attribute
     */
    public function getProgressCompletedDateAttribute(): string
    {
        return 'completed_at';
    }
}
