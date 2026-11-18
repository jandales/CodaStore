<?php

namespace App\Services\Reports;

use App\Models\GeneralSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * A report's date range, in the store's timezone (Settings > General, Asia/Manila by default).
 * Orders are stored in UTC, so queries use utcBounds() and grouping converts back.
 */
class ReportRange
{
    public const PRESETS = [
        'today' => 'Today',
        'yesterday' => 'Yesterday',
        '7d' => 'Last 7 days',
        '30d' => 'Last 30 days',
        'this_month' => 'This month',
        'last_month' => 'Last month',
        'this_year' => 'This year',
        'custom' => 'Custom',
    ];

    public const DEFAULT_TIMEZONE = 'Asia/Manila';
    private const MAX_YEARS = 5;

    public Carbon $from;
    public Carbon $to;
    public string $preset;
    public string $tz;

    public function __construct(Carbon $from, Carbon $to, string $preset = 'custom', ?string $tz = null)
    {
        $this->tz = $tz ?: self::timezone();
        $this->from = $from->copy()->setTimezone($this->tz)->startOfDay();
        $this->to = $to->copy()->setTimezone($this->tz)->endOfDay();
        $this->preset = $preset;
    }

    /** The store timezone, or Asia/Manila when it's empty or unknown. */
    public static function timezone(): string
    {
        $tz = optional(GeneralSetting::find(1))->timezone;

        return $tz && in_array($tz, timezone_identifiers_list(), true) ? $tz : self::DEFAULT_TIMEZONE;
    }

    public static function preset(string $preset, ?string $tz = null): self
    {
        $tz = $tz ?: self::timezone();
        $today = Carbon::now($tz);

        switch ($preset) {
            case 'today':      return new self($today, $today, $preset, $tz);
            case 'yesterday':  return new self($today->copy()->subDay(), $today->copy()->subDay(), $preset, $tz);
            case '7d':         return new self($today->copy()->subDays(6), $today, $preset, $tz);
            case 'this_month': return new self($today->copy()->startOfMonth(), $today, $preset, $tz);
            case 'last_month':
                $last = $today->copy()->startOfMonth()->subMonth();
                return new self($last, $last->copy()->endOfMonth(), $preset, $tz);
            case 'this_year':  return new self($today->copy()->startOfYear(), $today, $preset, $tz);
            default:           return new self($today->copy()->subDays(29), $today, '30d', $tz);
        }
    }

    /** ?range=7d, or ?range=custom&from=2026-11-01&to=2026-11-30. Bad input falls back to the last 30 days. */
    public static function fromRequest(Request $request): self
    {
        $preset = (string) $request->query('range', '30d');
        if ($preset !== 'custom') {
            return self::preset(array_key_exists($preset, self::PRESETS) ? $preset : '30d');
        }

        $tz = self::timezone();
        $from = self::parseDate($request->query('from'), $tz);
        $to = self::parseDate($request->query('to'), $tz);
        if (!$from || !$to) {
            return self::preset('30d', $tz);
        }
        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }
        $today = Carbon::now($tz);
        if ($to->gt($today)) {
            $to = $today;
        }
        if ($from->gt($to)) {
            $from = $to->copy();
        }
        if ($from->lt($to->copy()->subYears(self::MAX_YEARS))) {
            $from = $to->copy()->subYears(self::MAX_YEARS);
        }

        return new self($from, $to, 'custom', $tz);
    }

    private static function parseDate($value, string $tz): ?Carbon
    {
        if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }
        try {
            $date = Carbon::createFromFormat('!Y-m-d', $value, $tz);
        } catch (\Throwable $e) {
            return null;
        }

        return $date && $date->format('Y-m-d') === $value ? $date : null;
    }

    /** [from, to] in UTC, for whereBetween on created_at. */
    public function utcBounds(): array
    {
        return [$this->from->copy()->utc()->toDateTimeString(), $this->to->copy()->utc()->toDateTimeString()];
    }

    public function days(): int
    {
        return (int) $this->from->copy()->startOfDay()->diffInDays($this->to->copy()->startOfDay()) + 1;
    }

    /** The same number of days just before this range. */
    public function previous(): self
    {
        $to = $this->from->copy()->subDay();

        return new self($to->copy()->subDays($this->days() - 1), $to, 'custom', $this->tz);
    }

    /** Chart bars: per day up to a month, per week up to six months, else per month. */
    public function bucket(): string
    {
        $days = $this->days();

        return $days <= 31 ? 'day' : ($days <= 186 ? 'week' : 'month');
    }

    /** Bucket key for a UTC timestamp. */
    public function bucketKey($utc): string
    {
        $date = Carbon::parse($utc, 'UTC')->setTimezone($this->tz);

        return $this->keyFor($date);
    }

    private function keyFor(Carbon $date): string
    {
        switch ($this->bucket()) {
            case 'week':  return $date->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
            case 'month': return $date->format('Y-m');
            default:      return $date->format('Y-m-d');
        }
    }

    /** Every bucket in the range, in order: key => label. */
    public function buckets(): array
    {
        $out = [];
        $cursor = $this->from->copy();
        while ($cursor->lte($this->to)) {
            $key = $this->keyFor($cursor);
            if (!isset($out[$key])) {
                $out[$key] = $this->bucket() === 'month'
                    ? $cursor->format($this->from->year === $this->to->year ? 'M' : 'M Y')
                    : ($this->bucket() === 'week' ? 'Week of ' . Carbon::parse($key, $this->tz)->format('j M') : $cursor->format('j M'));
            }
            $cursor->addDay();
        }

        return $out;
    }

    /** "1 Nov – 30 Nov 2026" */
    public function label(): string
    {
        if ($this->from->isSameDay($this->to)) {
            return $this->from->format('j M Y');
        }
        $sameYear = $this->from->year === $this->to->year;

        return $this->from->format($sameYear ? 'j M' : 'j M Y') . ' – ' . $this->to->format('j M Y');
    }

    /** "2026-11-01-to-2026-11-30", for file names. */
    public function slug(): string
    {
        return $this->from->format('Y-m-d') . '-to-' . $this->to->format('Y-m-d');
    }

    /** Query string that reproduces this range. */
    public function query(): array
    {
        return $this->preset === 'custom'
            ? ['range' => 'custom', 'from' => $this->from->format('Y-m-d'), 'to' => $this->to->format('Y-m-d')]
            : ['range' => $this->preset];
    }
}
