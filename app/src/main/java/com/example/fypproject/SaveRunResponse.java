package com.example.fypproject;

import com.google.gson.annotations.SerializedName;

/**
 * Model class representing the response from saving a run to the Laravel backend.
 */
public class SaveRunResponse {
    @SerializedName("message")
    private String message;

    @SerializedName("data")
    private RunData data;

    @SerializedName("modifiers_log")
    private String modifiersLog;

    @SerializedName("ai_evaluation")
    private String aiEvaluation;

    @SerializedName("tier_update")
    private TierUpdate tierUpdate;

    public String getMessage() {
        return message;
    }

    public RunData getData() {
        return data;
    }

    public String getModifiersLog() {
        return modifiersLog;
    }

    public String getAiEvaluation() {
        return aiEvaluation;
    }

    public TierUpdate getTierUpdate() {
        return tierUpdate;
    }

    public static class TierUpdate {
        @SerializedName("tier")
        private String tier;

        @SerializedName("label")
        private String label;

        @SerializedName("score")
        private double score;

        @SerializedName("previous_tier")
        private String previousTier;

        @SerializedName("weekly_distance_km")
        private double weeklyDistanceKm;

        @SerializedName("weekly_frequency")
        private int weeklyFrequency;

        public String getTier() {
            return tier;
        }

        public String getLabel() {
            return label;
        }

        public double getScore() {
            return score;
        }

        public String getPreviousTier() {
            return previousTier;
        }

        public double getWeeklyDistanceKm() {
            return weeklyDistanceKm;
        }

        public int getWeeklyFrequency() {
            return weeklyFrequency;
        }
    }
}
