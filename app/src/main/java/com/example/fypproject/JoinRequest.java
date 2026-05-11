package com.example.fypproject;

import com.google.gson.annotations.SerializedName;

public class JoinRequest {
    @SerializedName("group_id") // This MUST match Laravel's validation rule
    private int groupId;

    public JoinRequest(int groupId) {
        this.groupId = groupId;
    }
}