<?php

namespace App\Services;

use App\Models\RunSummary;
use App\Models\User;
use Carbon\Carbon;

class RunnerTierEngine
{
    public const BEGINNER = 'LOW';
    public const INTERMEDIATE = 'MEDIUM';
    public const PROFESSIONAL = 'HARD';

    public function classifyFromProfile(User $user, array $input): array
    {
        $score = 0;
        $score += $this->experienceScore((int) ($input['experience_score'] ?? 1));
        $score += $this->profilePaceScore($input);
        $score += $this->clamp((int) ($input['frequency_score'] ?? 1), 1, 3) * 8;
        $score += $this->profileWeeklyDistanceScore($input);
        $score += $this->clamp((int) ($input['type_score'] ?? 1), 1, 3) * 5;
        $score += $this->clamp((int) ($input['distance_score'] ?? 1), 1, 3) * 6;
        $score += $this->eventScore((int) ($input['event_score'] ?? 1));
        $score += $this->recoveryScore((int) ($input['recovery_score'] ?? 2));

        $age = (int) ($input['age'] ?? $user->age ?? 0);
        if ($age > 0) {
            if ($age < 16 || $age >= 55) $score -= 6;
            elseif ($age >= 20 && $age <= 40) $score += 3;
        }

        $goal = strtolower((string) ($input['goal_type'] ?? $input['running_goal'] ?? $user->running_goal ?? ''));
        $score += $this->goalScore($goal);
        $score += $this->injuryPenalty((string) ($input['injury_history'] ?? 'none'));
        $score += $this->hiddenModifierScore($input, $goal);

        return $this->result($score, 'profile_assessment');
    }

    public function updateFromWeeklyTraining(User $user): array
    {
        $runs = RunSummary::where('user_id', (string) $user->id)
            ->whereDate('date', '>=', Carbon::now()->subDays(7)->toDateString())
            ->get();

        $weeklyDistance = (float) $runs->sum('distance_km');
        $weeklyFrequency = $runs->count();
        $averagePace = $this->averagePace($runs);

        $score = $this->baseScoreForTier($user->runner_tier);
        $score += $this->weeklyDistanceBonus($weeklyDistance);
        $score += $this->weeklyFrequencyBonus($weeklyFrequency);
        if ($averagePace !== null) $score += $this->paceBonus($averagePace);
        if ($weeklyFrequency >= 5 && $weeklyDistance < 10) $score -= 8;
        if ($weeklyDistance >= 40 && $averagePace !== null && $averagePace <= 5.5) $score += 12;

        $result = $this->result($score, 'weekly_training');
        $oldTier = $this->normalizeTier($user->runner_tier);
        $newTier = $this->limitWeeklyTierMovement($oldTier, $result['tier']);

        $user->runner_tier = $newTier;
        $user->save();

        return array_merge($result, [
            'tier' => $newTier,
            'label' => $this->labelForTier($newTier),
            'previous_tier' => $oldTier,
            'weekly_distance_km' => round($weeklyDistance, 2),
            'weekly_frequency' => $weeklyFrequency,
            'average_pace_min_km' => $averagePace,
        ]);
    }

    public function compatibleEventTiers(?string $tier): array
    {
        $tier = $this->normalizeTier($tier);

        if ($tier === self::PROFESSIONAL) {
            return [self::PROFESSIONAL, 'Professional', self::INTERMEDIATE, 'Intermediate'];
        }

        if ($tier === self::INTERMEDIATE) {
            return [self::INTERMEDIATE, 'Intermediate', self::BEGINNER, 'Beginner'];
        }

        return [self::BEGINNER, 'Beginner'];
    }

    public function normalizeTier(?string $tier): string
    {
        $tier = strtoupper((string) $tier);

        if (in_array($tier, ['HARD', 'PROFESSIONAL', 'ADVANCED'], true)) {
            return self::PROFESSIONAL;
        }

        if (in_array($tier, ['MEDIUM', 'INTERMEDIATE'], true)) {
            return self::INTERMEDIATE;
        }

        return self::BEGINNER;
    }

    public function labelForTier(?string $tier): string
    {
        $tier = $this->normalizeTier($tier);

        if ($tier === self::PROFESSIONAL) return 'Professional';
        if ($tier === self::INTERMEDIATE) return 'Intermediate';
        return 'Beginner';
    }

    private function result(float $score, string $source): array
    {
        $tier = $score >= 120 ? self::PROFESSIONAL : ($score >= 70 ? self::INTERMEDIATE : self::BEGINNER);
        return ['tier' => $tier, 'label' => $this->labelForTier($tier), 'score' => round($score, 2), 'source' => $source];
    }

    private function experienceScore(int $score): int
    {
        $score = $this->clamp($score, 1, 4);
        if ($score === 4) return 30;
        if ($score === 3) return 22;
        if ($score === 2) return 12;
        return 5;
    }

    private function profilePaceScore(array $input): int
    {
        if (!empty($input['average_pace_min_km'])) {
            $pace = (float) $input['average_pace_min_km'];
            if ($pace <= 5.5) return 30;
            if ($pace <= 6.5) return 23;
            if ($pace <= 8.0) return 14;
            return 6;
        }

        $score = $this->clamp((int) ($input['pace_score'] ?? 1), 1, 3);
        if ($score === 3) return 30;
        if ($score === 2) return 20;
        return 8;
    }

    private function profileWeeklyDistanceScore(array $input): int
    {
        if (!empty($input['weekly_distance_km'])) {
            $km = (float) $input['weekly_distance_km'];
            if ($km >= 50) return 28;
            if ($km >= 25) return 22;
            if ($km >= 10) return 14;
            return 6;
        }

        $score = $this->clamp((int) ($input['weekly_distance_score'] ?? 1), 1, 4);
        if ($score === 4) return 28;
        if ($score === 3) return 22;
        if ($score === 2) return 14;
        return 6;
    }

    private function eventScore(int $score): int
    {
        $score = $this->clamp($score, 1, 4);
        if ($score === 4) return 18;
        if ($score === 3) return 13;
        if ($score === 2) return 7;
        return 0;
    }

    private function recoveryScore(int $score): int
    {
        $score = $this->clamp($score, 1, 3);
        if ($score === 3) return 8;
        if ($score === 2) return 4;
        return -6;
    }

    private function goalScore(string $goal): int
    {
        if ($this->contains($goal, 'marathon')) return 16;
        if ($this->containsAny($goal, ['competitive', 'race', 'speed'])) return 13;
        if ($this->containsAny($goal, ['half', '10k'])) return 10;
        if ($this->containsAny($goal, ['5k', 'fitness'])) return 5;
        if ($this->containsAny($goal, ['weight', 'health', 'casual'])) return 1;
        return 3;
    }

    private function injuryPenalty(string $injury): int
    {
        $injury = strtolower($injury);
        if (in_array($injury, ['chronic', 'high'], true)) return -20;
        if (in_array($injury, ['recovering', 'frequent'], true)) return -12;
        if (in_array($injury, ['minor', 'occasional'], true)) return -5;
        return 0;
    }

    private function hiddenModifierScore(array $input, string $goal): int
    {
        $score = 0;
        $frequency = $this->clamp((int) ($input['frequency_score'] ?? 1), 1, 3);
        $pace = $this->clamp((int) ($input['pace_score'] ?? 1), 1, 3);
        $experience = $this->clamp((int) ($input['experience_score'] ?? 1), 1, 4);
        $weeklyDistance = $this->clamp((int) ($input['weekly_distance_score'] ?? 1), 1, 4);
        $recovery = $this->clamp((int) ($input['recovery_score'] ?? 2), 1, 3);
        $events = $this->clamp((int) ($input['event_score'] ?? 1), 1, 4);

        if ($frequency === 3 && $pace === 1) $score -= 8;
        if ($frequency === 3 && $recovery === 1) $score -= 10;
        if ($this->containsAny($goal, ['competitive', 'race']) && $frequency === 1) $score -= 12;
        if ($experience === 1 && $frequency === 3) $score -= 8;
        if ($experience >= 3 && $events >= 3 && $weeklyDistance >= 3) $score += 10;
        if ($pace === 3 && $weeklyDistance >= 3 && $experience >= 3) $score += 15;

        return $score;
    }

    private function weeklyDistanceBonus(float $km): int
    {
        if ($km >= 45) return 24;
        if ($km >= 25) return 16;
        if ($km >= 12) return 8;
        if ($km > 0 && $km < 6) return -4;
        return 0;
    }

    private function weeklyFrequencyBonus(int $runs): int
    {
        if ($runs >= 5) return 18;
        if ($runs >= 3) return 10;
        if ($runs === 1) return -3;
        return 0;
    }

    private function paceBonus(float $pace): int
    {
        if ($pace <= 5.0) return 16;
        if ($pace <= 6.5) return 9;
        if ($pace <= 8.0) return 3;
        if ($pace >= 9.5) return -5;
        return 0;
    }

    private function baseScoreForTier(?string $tier): int
    {
        $tier = $this->normalizeTier($tier);
        if ($tier === self::PROFESSIONAL) return 120;
        if ($tier === self::INTERMEDIATE) return 86;
        return 52;
    }

    private function limitWeeklyTierMovement(string $oldTier, string $newTier): string
    {
        $rank = [self::BEGINNER => 1, self::INTERMEDIATE => 2, self::PROFESSIONAL => 3];
        $oldRank = $rank[$oldTier] ?? 1;
        $newRank = $rank[$newTier] ?? 1;
        if ($newRank > $oldRank + 1) $newRank = $oldRank + 1;
        return array_search($newRank, $rank, true) ?: self::BEGINNER;
    }

    private function averagePace($runs): ?float
    {
        $paces = [];
        foreach ($runs as $run) {
            $pace = $this->parsePace($run->pace);
            if ($pace !== null) $paces[] = $pace;
        }
        return empty($paces) ? null : round(array_sum($paces) / count($paces), 2);
    }

    private function parsePace(?string $pace): ?float
    {
        if (!$pace || $pace === '--:--') return null;
        $parts = explode(':', $pace);
        if (count($parts) !== 2) return is_numeric($pace) ? (float) $pace : null;
        return ((int) $parts[0]) + (((int) $parts[1]) / 60);
    }

    private function clamp(int $value, int $min, int $max): int
    {
        return max($min, min($max, $value));
    }

    private function contains(string $haystack, string $needle): bool
    {
        return strpos($haystack, $needle) !== false;
    }

    private function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($this->contains($haystack, $needle)) {
                return true;
            }
        }
        return false;
    }
}
