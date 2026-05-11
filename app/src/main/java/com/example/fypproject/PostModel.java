package com.example.fypproject;

import com.google.gson.annotations.SerializedName;

public class PostModel {
    private int id;
    private String content;

    // The backend now sends a nested user object
    @SerializedName("user")
    private UserModel user;

    @SerializedName("image_url")
    private String imageUrl;  // For the actual post image if any

    @SerializedName("created_at")
    private String createdAt;

    @SerializedName("author_name")
    private String authorName;

    @SerializedName("author_username")
    private String authorUsername;

    @SerializedName("user_image")
    private String userImage;

    // --- Getters ---
    public int getId() { return id; }
    public String getContent() { return content; }

    // Use this to get the nested user object containing the profile picture
    public UserModel getUser() { return user; }

    public String getImageUrl() { return imageUrl; }
    public String getCreatedAt() { return createdAt; }

    // Fallback getters for legacy compatibility
    public String getUserName() {
        return (user != null) ? user.getName() : authorName;
    }

    public String getUserImage() {
        return (user != null) ? user.getProfilePhotoPath() : userImage;
    }
}