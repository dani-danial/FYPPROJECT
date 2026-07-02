package com.example.fypproject;

import com.google.gson.annotations.SerializedName;

public class CommentModel {
    private int id;
    private String body;

    @SerializedName("created_at")
    private String createdAt;

    @SerializedName("user")
    private UserModel user;

    public int getId() {
        return id;
    }

    public String getBody() {
        return body;
    }

    public String getCreatedAt() {
        return createdAt;
    }

    public UserModel getUser() {
        return user;
    }

    public String getUserName() {
        return user != null ? user.getName() : "Runner";
    }

    public String getUserImage() {
        return user != null ? user.getProfilePhotoPath() : null;
    }
}
