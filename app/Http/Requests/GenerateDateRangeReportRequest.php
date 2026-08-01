<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class GenerateDateRangeReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route group already enforces auth + Admin role (role:1).
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $today = Carbon::now('Asia/Manila')->toDateString();

        return [
            'start_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:'.$today],
            'end_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:'.$today],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $maxDays = (int) config('reports.max_range_days', 31);

        return [
            'start_date.required' => 'Please select a start date.',
            'start_date.date_format' => 'The start date must use the YYYY-MM-DD format.',
            'start_date.before_or_equal' => 'Future dates are not allowed.',
            'end_date.required' => 'Please select an end date.',
            'end_date.date_format' => 'The end date must use the YYYY-MM-DD format.',
            'end_date.before_or_equal' => 'Future dates are not allowed.',
            'end_date.after_or_equal' => 'The end date must be on or after the start date.',
            'range.min_days' => 'The selected range must cover at least two days.',
            'range.max_days' => 'The selected date range cannot exceed '.$maxDays.' days.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $startRaw = (string) $this->input('start_date');
            $endRaw = (string) $this->input('end_date');

            try {
                $start = Carbon::createFromFormat('Y-m-d', $startRaw, 'Asia/Manila')?->startOfDay();
                $end = Carbon::createFromFormat('Y-m-d', $endRaw, 'Asia/Manila')?->startOfDay();
            } catch (\Throwable $e) {
                return;
            }

            if (! $start || ! $end || $start->format('Y-m-d') !== $startRaw || $end->format('Y-m-d') !== $endRaw) {
                return;
            }

            if ($end->lt($start)) {
                $validator->errors()->add('end_date', 'The end date must be on or after the start date.');

                return;
            }

            $inclusiveDays = $start->diffInDays($end) + 1;
            $maxDays = (int) config('reports.max_range_days', 31);

            if ($inclusiveDays < 2) {
                $validator->errors()->add('end_date', 'The selected range must cover at least two days.');

                return;
            }

            if ($inclusiveDays > $maxDays) {
                $validator->errors()->add('end_date', 'The selected date range cannot exceed '.$maxDays.' days.');
            }
        });
    }
}
