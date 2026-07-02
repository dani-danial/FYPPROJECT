package com.example.fypproject;

public class RegisterRequest {
    private String name;
    private String username;
    private String email;
    private String password;
    private Integer age;
    private String gender;
    private String running_goal;
    private String phone;

    public RegisterRequest(String name, String username, String email, String password) {
        this(name, username, email, password, null, null, null, null);
    }

    public RegisterRequest(String name, String username, String email, String password,
                           Integer age, String gender, String runningGoal, String phone) {
        this.name = name;
        this.username = username;
        this.email = email;
        this.password = password;
        this.age = age;
        this.gender = gender;
        this.running_goal = runningGoal;
        this.phone = phone;
    }
}
