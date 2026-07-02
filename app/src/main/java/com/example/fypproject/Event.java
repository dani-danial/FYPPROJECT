package com.example.fypproject;

import com.google.gson.annotations.SerializedName;

public class Event {
    private int id;
    private String title;
    private String date;
    private String time;
    private String location;
    private String state;

    @SerializedName("distance_km")
    private double distanceKm;

    @SerializedName("run_type")
    private String runType;

    @SerializedName("entry_fee")
    private double entryFee;

    private String description;

    private double latitude;
    private double longitude;

    // 🛠️ FIXED: Changed from "participants_count" to "users_count"
    // to match Laravel's withCount('users') output.
    @SerializedName("users_count")
    private int participantsCount;

    private String status;

    @SerializedName("logo_path")
    private String logoPath;

    @SerializedName("runner_tier")
    private String runnerTier;

    @SerializedName("is_joined")
    private boolean isJoined;

    @SerializedName("recommendation_status")
    private String recommendationStatus;

    public Event() {}

    // --- Getters ---
    public String getRecommendationStatus() { return recommendationStatus; }
    public int getId() { return id; }
    public String getTitle() { return title; }
    public String getDate() { return date; }
    public String getTime() { return time; }
    public String getLocation() { return location; }
    public String getState() { return state; }
    public double getDistanceKm() { return distanceKm; }
    public String getRunType() { return runType; }
    public double getEntryFee() { return entryFee; }
    public String getDescription() { return description; }
    public double getLatitude() { return latitude; }
    public double getLongitude() { return longitude; }

    // This will now return the correct number from the database
    public int getParticipantsCount() { return participantsCount; }

    public String getStatus() { return status; }
    public String getLogoPath() { return logoPath; }
    public String getRunnerTier() { return runnerTier; }
    public boolean isJoined() { return isJoined; }
}