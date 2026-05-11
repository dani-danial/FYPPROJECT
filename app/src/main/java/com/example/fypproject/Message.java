package com.example.fypproject;

import java.util.Date;

public class Message {
    private String senderId;
    private String text;
    private Date timestamp;

    public Message() { } // Empty constructor needed for Firebase

    public Message(String senderId, String text, Date timestamp) {
        this.senderId = senderId;
        this.text = text;
        this.timestamp = timestamp;
    }

    public String getSenderId() { return senderId; }
    public String getText() { return text; }
    public Date getTimestamp() { return timestamp; }
}