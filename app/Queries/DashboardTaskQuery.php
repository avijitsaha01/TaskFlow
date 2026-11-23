<?php

namespace App\Queries;

use App\Models\Task;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;

final class DashboardTaskQuery
{
    public readonly string $search;

    public readonly string $priority;

    public readonly string $status;

    public readonly string $deadline;

    public readonly string $sort;

    public function __construct(
        private readonly User $owner,
        private readonly CarbonInterface $today,
        array $parameters,
    ) {
        $this->search = self::text($parameters, 'search');
        $this->priority = self::option($parameters, 'priority', ['all', 'low', 'medium', 'high'], 'all');
        $this->status = self::option($parameters, 'filter', ['all', 'today', 'active', 'completed'], $owner->default_filter ?: 'all');
        $this->deadline = self::option($parameters, 'due', ['overdue', 'today', 'week'], '');
        $this->sort = self::option($parameters, 'sort', ['newest', 'oldest', 'priority', 'due'], $owner->default_sort ?: 'newest');
    }

    /** @return Collection<int, Task> */
    public function get(): Collection
    {
        $query = $this->owner->tasks()->search($this->search);
        $today = $this->today->toDateString();

        if ($this->priority !== 'all') {
            $query->byPriority($this->priority);
        }

        match ($this->status) {
            'active' => $query->active(),
            'completed' => $query->completed(),
            'today' => $query->where('due_date', $today),
            default => null,
        };

        match ($this->deadline) {
            'overdue' => $query->active()->where('due_date', '<', $today),
            'today' => $query->where('due_date', $today),
            'week' => $query->active()->whereBetween('due_date', [$today, $this->today->copy()->addDays(7)->toDateString()]),
            default => null,
        };

        match ($this->sort) {
            'oldest' => $query->oldest()->orderBy('id'),
            'priority' => $query->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 END")->latest()->orderByDesc('id'),
            'due' => $query->orderByRaw('due_date IS NULL')->orderBy('due_date')->latest()->orderByDesc('id'),
            default => $query->latest()->orderByDesc('id'),
        };

        return $query->get();
    }

    private static function text(array $parameters, string $key): string
    {
        $value = $parameters[$key] ?? '';

        return is_string($value) ? trim($value) : '';
    }

    private static function option(array $parameters, string $key, array $options, string $default): string
    {
        $value = self::text($parameters, $key);

        return in_array($value, $options, true) ? $value : $default;
    }
}
