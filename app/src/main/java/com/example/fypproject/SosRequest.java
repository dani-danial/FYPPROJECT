package com.example.fypproject;

import com.google.gson.annotations.SerializedName;

public class SosRequest {

    @SerializedName("user_name")
    private String userName;

    @SerializedName("user_identifier")
    private String userIdentifier;

    @SerializedName("phone_number")
    private String phoneNumber;

    @SerializedName("message") // This holds the "Emergency Type"
    private String message;

    @SerializedName("location_name")
    private String locationName;

    @SerializedName("latitude")
    private double latitude;

    @SerializedName("longitude")
    private double longitude;

    // --- Empty Constructor (Required for Retrofit) ---
    public SosRequest() {}

    // --- Full Constructor ---
    public SosRequest(String userName, String userIdentifier, String phoneNumber,
                      String message, String locationName, double latitude, double longitude) {
        this.userName = userName;
        this.userIdentifier = userIdentifier;
        this.phoneNumber = phoneNumber;
        this.message = message;
        this.locationName = locationName;
        this.latitude = latitude;
        this.longitude = longitude;
    }

    // ===========================================
    // GETTERS & SETTERS (Required for API Sync)
    // ===========================================

    public String getUserName() { return userName; }
    public void setUserName(String userName) { this.userName = userName; }

    public String getUserIdentifier() { return userIdentifier; }
    public void setUserIdentifier(String userIdentifier) { this.userIdentifier = userIdentifier; }

    public String getPhoneNumber() { return phoneNumber; }
    public void setPhoneNumber(String phoneNumber) { this.phoneNumber = phoneNumber; }

    public String getMessage() { return message; }
    public void setMessage(String message) { this.message = message; }

    public String getLocationName() { return locationName; }
    public void setLocationName(String locationName) { this.locationName = locationName; }

    public double getLatitude() { return latitude; }
    public void setLatitude(double latitude) { this.latitude = latitude; }

    public double getLongitude() { return longitude; }
    public void setLongitude(double longitude) { this.longitude = longitude; }
}