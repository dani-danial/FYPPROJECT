package com.example.fypproject;

import com.google.gson.annotations.SerializedName;

public class Message {

    // Translates Laravel's "user_id" into Android's "senderId"
    @SerializedName("user_id")
    private int senderId;

    // Translates Laravel's "body" into Android's "text"
    @SerializedName("body")
    private String text;

    @SerializedName("created_at")
    private String timestamp;

    @SerializedName("user")
    private UserModel user;

    @SerializedName("read_at")
    private String readAt;

    public Message() { }

    public int getSenderId() {
        return senderId;
    }

    public void setSenderId(int senderId) {
        this.senderId = senderId;
    }

    public String getText() {
        return text;
    }

    public void setText(String text) {
        this.text = text;
    }

    public String getTimestamp() {
        return timestamp;
    }

    public void setTimestamp(String timestamp) {
        this.timestamp = timestamp;
    }

    public UserModel getUser() {
        return user;
    }

    public String getReadAt() {
        return readAt;
    }

    public boolean isRead() {
        return readAt != null && !readAt.isEmpty();
    }
}
