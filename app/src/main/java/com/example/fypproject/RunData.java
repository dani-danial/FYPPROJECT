package com.example.fypproject;

import com.google.gson.annotations.SerializedName;

/**
 * Model class for Run Data.
 * Synchronized with Laravel RunSummary MySQL Table.
 */
public class RunData {

    @SerializedName("user_id")
    private int userId;

    @SerializedName("distance_km")
    private double distanceKm;

    @SerializedName("time")
    private String time; // Received from Server (HH:mm:ss)

    @SerializedName("duration_seconds")
    private int durationSeconds; // Sent to Server

    @SerializedName("average_pace")
    private String averagePace; // Sent to Server

    @SerializedName("pace")
    private String pace; // Received from Server

    @SerializedName("date")
    private String date;

    @SerializedName("route_path")
    private String routePath; // Serialized JSON coordinate array

    @SerializedName("share_to_feed")
    private boolean shareToFeed;

    @SerializedName("ai_evaluation")
    private String aiEvaluation;

    public RunData() {
        // Required empty constructor for Retrofit/Gson
    }

    // ===========================================
    // SETTERS (Used by MainActivity when saving)
    // ===========================================

    public void setUserId(int userId) {
        this.userId = userId;
    }

    public void setDistanceKm(double distanceKm) {
        this.distanceKm = distanceKm;
    }

    public void setDurationSeconds(int durationSeconds) {
        this.durationSeconds = durationSeconds;
    }

    public void setAveragePace(String averagePace) {
        this.averagePace = averagePace;
    }

    public void setDate(String date) {
        this.date = date;
    }

    // ===========================================
    // GETTERS (Used by Adapters and UI)
    // ===========================================

    /**
     * Alias for tvTime.setText() in Adapter.
     * Returns 'time' if available (from DB), otherwise returns duration as String.
     */
    public String getTimeDuration() {
        return (time != null && !time.isEmpty()) ? time : String.valueOf(durationSeconds);
    }

    /**
     * Alias for tvPace.setText() in Adapter.
     * Returns 'pace' (from DB) or 'averagePace' (calculated in app).
     */
    public String getPace() {
        if (pace != null && !pace.isEmpty()) return pace;
        if (averagePace != null && !averagePace.isEmpty()) return averagePace;
        return "--:--";
    }

    public int getUserId() {
        return userId;
    }

    public double getDistanceKm() {
        return distanceKm;
    }

    public int getDurationSeconds() {
        return durationSeconds;
    }

    public String getAveragePace() {
        return averagePace;
    }

    public String getDate() {
        return (date != null) ? date : "No Date";
    }

    public void setRoutePath(String routePath) {
        this.routePath = routePath;
    }

    public String getRoutePath() {
        return routePath;
    }

    public void setShareToFeed(boolean shareToFeed) {
        this.shareToFeed = shareToFeed;
    }

    public boolean isShareToFeed() {
        return shareToFeed;
    }

    public String getAiEvaluation() {
        return aiEvaluation;
    }

    public void setAiEvaluation(String aiEvaluation) {
        this.aiEvaluation = aiEvaluation;
    }
}