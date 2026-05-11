package com.example.fypproject;

import com.google.gson.annotations.SerializedName;

public class LoginResponse {

    @SerializedName("token")
    private String token;

    @SerializedName("user")
    private UserModel user;

    // Getters
    public String getToken() {
        return token;
    }

    public UserModel getUser() {
        return user;
    }

    // --- INNER USER CLASS ---
    public static class User {
        @SerializedName("id")
        private int id;

        @SerializedName("name")
        private String name;

        @SerializedName("email")
        private String email;

        // Getters needed for your LoginActivity
        public int getId() {
            return id;
        }

        public String getName() {
            return name;
        }

        public String getEmail() {
            return email;
        }
    }
}