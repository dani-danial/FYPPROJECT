package com.example.fypproject;

public class RegisterRequest {
    private String name;
    private String username; // NEW FIELD
    private String email;
    private String password;

    public RegisterRequest(String name, String username, String email, String password) {
        this.name = name;
        this.username = username; // NEW ASSIGNMENT
        this.email = email;
        this.password = password;
    }
}