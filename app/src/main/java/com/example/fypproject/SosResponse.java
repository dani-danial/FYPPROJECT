package com.example.fypproject;

public class SosResponse {
    private String message;
    private Object data; // Laravel returns the created signal object

    public String getMessage() { return message; }
}