package com.example.fypproject;

import com.google.gson.annotations.SerializedName;

public class RegisterResponse {
    @SerializedName("message")
    private String message;

    @SerializedName("token")
    private String token;

    // 🛠️ CHANGE THIS: From User to UserModel
    @SerializedName("user")
    private UserModel user;

    public UserModel getUser() {
        return user;
    }

    public String getToken() {
        return token;
    }
}