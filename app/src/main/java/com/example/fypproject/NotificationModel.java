package com.example.fypproject;

public class NotificationModel {
    String title, message, time, type;
    // Type can be "run", "post", "group"

    public NotificationModel(String title, String message, String time, String type) {
        this.title = title;
        this.message = message;
        this.time = time;
        this.type = type;
    }
}