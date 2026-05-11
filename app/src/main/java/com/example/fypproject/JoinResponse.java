package com.example.fypproject;

import com.google.gson.annotations.SerializedName;

public class JoinResponse {
    @SerializedName("message")
    private String message;

    @SerializedName("data")
    private GroupModel group; // The updated group info

    public String getMessage() { return message; }
    public GroupModel getGroup() { return group; }
}