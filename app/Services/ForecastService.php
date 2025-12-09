<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;   // <-- REQUIRED for logs

class ForecastService
{
    /**
     * Main method to calculate forecasting using SES and Holt-Winters.
     */
    public function forecast(array $sales)
    {
        Log::info("🚀 ForecastService CALLED", [
            'raw_input' => $sales
        ]);

        $sales = array_map('floatval', $sales);
        $n = count($sales);

        Log::info("🔢 Cleaned sales series", [
            'series' => $sales,
            'count' => $n
        ]);

        if ($n === 0) {
            Log::warning("⚠️ ForecastService received EMPTY series.");
            return [
                'ses' => null,
                'holtwinters' => null,
                'forecast' => 0
            ];
        }

        // SES always available
        $ses = $this->simpleExponentialSmoothing($sales);

        Log::info("📈 SES result", [
            'ses_value' => $ses
        ]);

        // Holt-Winters only when 24+ points (2 seasons)
        $holtwinters = null;
        if ($n >= 24) {
            $holtwinters = $this->holtWintersAdditive($sales, 12);
            Log::info("🌦 Holt-Winters result", [
                'holtwinters_value' => $holtwinters
            ]);
        } else {
            Log::info("⛔ Holt-Winters skipped — not enough data", [
                'points_needed' => 24,
                'points_given' => $n
            ]);
        }

        // Choose best model
        $forecast = $holtwinters ?? $ses;

        Log::info("🎯 FINAL FORECAST SELECTED", [
            'forecast' => $forecast,
            'algorithm_used' => $holtwinters !== null ? "holt-winters" : "ses"
        ]);

        return [
            'ses' => $ses,
            'holtwinters' => $holtwinters,
            'forecast' => $forecast
        ];
    }


    /**
     * Simple Exponential Smoothing (Level only)
     */
    private function simpleExponentialSmoothing(array $data, float $alpha = 0.3)
    {
        $s = $data[0];

        foreach ($data as $i => $x) {
            if ($i === 0) continue;
            $s = $alpha * $x + (1 - $alpha) * $s;
        }

        return $s;
    }


    /**
     * Holt-Winters Additive (Level + Trend + Seasonality)
     */
    private function holtWintersAdditive(
        array $data,
        int $seasonLength,
        float $alpha = 0.3,
        float $beta = 0.1,
        float $gamma = 0.3
    ) {
        $n = count($data);
        if ($n < $seasonLength * 2) {
            Log::warning("⚠️ Holt-Winters stopped: Not enough points", [
                'required' => $seasonLength * 2,
                'given' => $n
            ]);
            return null;
        }

        // Initial level and trend
        $L = array_sum(array_slice($data, 0, $seasonLength)) / $seasonLength;
        $T = (
            array_sum(array_slice($data, $seasonLength, $seasonLength)) / $seasonLength
            - $L
        ) / $seasonLength;

        // Initialize seasonality
        $S = [];
        for ($i = 0; $i < $seasonLength; $i++) {
            $S[$i] = $data[$i] - $L;
        }

        // Update loop
        for ($t = 0; $t < $n; $t++) {
            $i = $t % $seasonLength;
            $prev_L = $L;

            $L = $alpha * ($data[$t] - $S[$i]) + (1 - $alpha) * ($L + $T);
            $T = $beta * ($L - $prev_L) + (1 - $beta) * $T;
            $S[$i] = $gamma * ($data[$t] - $L) + (1 - $gamma) * $S[$i];
        }

        // Forecast next month
        $m = 1;
        $i = ($n + $m - 1) % $seasonLength;

        return $L + $m * $T + $S[$i];
    }
}
