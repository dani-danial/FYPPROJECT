package com.example.fypproject;

import com.google.gson.annotations.SerializedName;

public class EventJoinRequest {
    @SerializedName("user_id")
    private int userId;

    @SerializedName("event_id")
    private int eventId;

    public EventJoinRequest(int userId, int eventId) {
        this.userId = userId;
        this.eventId = eventId;
    }
}