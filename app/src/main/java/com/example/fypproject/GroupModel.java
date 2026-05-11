package com.example.fypproject;

import com.google.gson.annotations.SerializedName;

public class GroupModel {

    @SerializedName("id")
    private int id;

    @SerializedName("name")
    private String name;

    @SerializedName("description")
    private String description;

    @SerializedName("target_km")
    private double targetKm;

    @SerializedName("icon_url")
    private String iconUrl;

    @SerializedName("members_count")
    private int membersCount;

    @SerializedName("status")
    private String status;

    @SerializedName("creator_id")
    private int creatorId;

    // *** NEW FIELDS ADDED HERE ***
    @SerializedName("location")
    private String location;

    @SerializedName("banner_url")
    private String bannerUrl;

    // --- GETTERS ---
    public int getId() { return id; }
    public String getName() { return name; }
    public String getDescription() { return description; }
    public double getTargetKm() { return targetKm; }
    public String getIconUrl() { return iconUrl; }
    public int getMembersCount() { return membersCount; }
    public String getStatus() { return status; }
    public int getCreatorId() { return creatorId; }

    // *** NEW GETTERS (Fixes the error) ***
    public String getLocation() { return location; }
    public String getBannerUrl() { return bannerUrl; }
}