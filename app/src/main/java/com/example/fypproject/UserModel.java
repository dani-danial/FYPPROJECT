package com.example.fypproject;

import com.google.gson.annotations.SerializedName;

public class UserModel {
    @SerializedName("id")
    private int id;

    @SerializedName("name")
    private String name;

    @SerializedName("username")
    private String username;

    @SerializedName("email")
    private String email;

    @SerializedName("phone")
    private String phone;

    @SerializedName("about_me")
    private String aboutMe;

    @SerializedName("profile_photo_url")
    private String profilePhotoPath;

    // 🆕 Added: This maps to the 'runner_tier' column in your Laravel database
    @SerializedName("runner_tier")
    private String runnerTier;

    // 🛠️ FIXED: Matches the 'total_runs' column in your phpMyAdmin
    @SerializedName("total_runs")
    private int runsCount;

    // 🛠️ FIXED: Matches the 'distance_km' column in your phpMyAdmin
    @SerializedName("distance_km")
    private double totalDistance;

    @SerializedName("weight_kg")
    private double weightKg;

    @SerializedName("height_cm")
    private double heightCm;

    @SerializedName("base_pace_min_km")
    private String basePace; // Changed to String to match your MainActivity display logic

    // Empty constructor for GSON
    public UserModel() {}

    // --- GETTERS ---
    public int getId() { return id; }
    public String getName() { return name; }
    public String getUsername() { return username; }
    public String getEmail() { return email; }
    public String getPhone() { return phone; }
    public String getAboutMe() { return aboutMe; }
    public String getProfilePhotoPath() { return profilePhotoPath; }

    // 🆕 Added Getter for the Rule-Based Engine
    public String getRunnerTier() { return runnerTier; }

    public int getRunsCount() { return runsCount; }
    public double getTotalDistance() { return totalDistance; }
    public double getWeightKg() { return weightKg; }
    public double getHeightCm() { return heightCm; }
    public String getBasePace() { return basePace; }

    // --- SETTERS ---
    public void setId(int id) { this.id = id; }
    public void setName(String name) { this.name = name; }
    public void setUsername(String username) { this.username = username; }
    public void setEmail(String email) { this.email = email; }
    public void setPhone(String phone) { this.phone = phone; }
    public void setAboutMe(String aboutMe) { this.aboutMe = aboutMe; }
    public void setProfilePhotoPath(String profilePhotoPath) { this.profilePhotoPath = profilePhotoPath; }

    // 🆕 Added Setter
    public void setRunnerTier(String runnerTier) { this.runnerTier = runnerTier; }

    public void setRunsCount(int runsCount) { this.runsCount = runsCount; }
    public void setTotalDistance(double totalDistance) { this.totalDistance = totalDistance; }
    public void setWeightKg(double weightKg) { this.weightKg = weightKg; }
    public void setHeightCm(double heightCm) { this.heightCm = heightCm; }
    public void setBasePace(String basePace) { this.basePace = basePace; }
}