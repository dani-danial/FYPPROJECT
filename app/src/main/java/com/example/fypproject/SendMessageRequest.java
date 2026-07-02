package com.example.fypproject;

public class SendMessageRequest {

    private String body;

    public SendMessageRequest(String body) {
        this.body = body;
    }

    public String getBody() {
        return body;
    }

    public void setBody(String body) {
        this.body = body;
    }
}