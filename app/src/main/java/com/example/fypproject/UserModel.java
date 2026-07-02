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

    @SerializedName("age")
    private int age;

    @SerializedName("gender")
    private String gender;

    @SerializedName("running_goal")
    private String runningGoal;

    @SerializedName("about_me")
    private String aboutMe;

    @SerializedName("profile_photo_url")
    private String profilePhotoPath;

    @SerializedName("runner_tier")
    private String runnerTier;

    @SerializedName("total_runs")
    private int runsCount;

    @SerializedName("distance_km")
    private double totalDistance;

    @SerializedName("weight_kg")
    private double weightKg;

    @SerializedName("height_cm")
    private double heightCm;

    @SerializedName("base_pace_min_km")
    private String basePace;

    // 🆕 Social Fields from Laravel
    @SerializedName("followers_count")
    private int followersCount;

    @SerializedName("following_count")
    private int followingCount;

    @SerializedName("is_following")
    private boolean isFollowing;

    @SerializedName("status")
    private String status;

    @SerializedName("is_online")
    private boolean isOnline;

    public UserModel() {}

    // --- GETTERS ---
    public int getId() { return id; }
    public String getName() { return name; }
    public String getUsername() { return username; }
    public String getEmail() { return email; }
    public String getPhone() { return phone; }
    public int getAge() { return age; }
    public String getGender() { return gender; }
    public String getRunningGoal() { return runningGoal; }
    public String getAboutMe() { return aboutMe; }
    public String getProfilePhotoPath() { return profilePhotoPath; }

    public String getRunnerTier() {
        if (runnerTier == null) return "Beginner";
        switch (runnerTier.toUpperCase()) {
            case "LOW": return "Beginner";
            case "MEDIUM": return "Intermediate";
            case "HARD": return "Professional";
            default: return runnerTier;
        }
    }

    public int getRunsCount() { return runsCount; }
    public double getTotalDistance() { return totalDistance; }
    public double getWeightKg() { return weightKg; }
    public double getHeightCm() { return heightCm; }
    public String getBasePace() { return basePace; }

    public int getFollowersCount() { return followersCount; }
    public int getFollowingCount() { return followingCount; }
    public boolean isFollowing() { return isFollowing; }
    public String getStatus() { return status; }
    public boolean isOnline() {
        return isOnline || (status != null && status.equalsIgnoreCase("online"));
    }

    // --- SETTERS ---
    public void setId(int id) { this.id = id; }
    public void setName(String name) { this.name = name; }
    public void setUsername(String username) { this.username = username; }
    public void setEmail(String email) { this.email = email; }
    public void setAboutMe(String aboutMe) { this.aboutMe = aboutMe; }
    public void setProfilePhotoPath(String profilePhotoPath) { this.profilePhotoPath = profilePhotoPath; }
    public void setRunnerTier(String runnerTier) { this.runnerTier = runnerTier; }
    public void setRunsCount(int runsCount) { this.runsCount = runsCount; }
    public void setTotalDistance(double totalDistance) { this.totalDistance = totalDistance; }
    public void setFollowersCount(int followersCount) { this.followersCount = followersCount; }
    public void setFollowingCount(int followingCount) { this.followingCount = followingCount; }
    public void setFollowing(boolean following) { isFollowing = following; }
    public void setStatus(String status) { this.status = status; }
}
