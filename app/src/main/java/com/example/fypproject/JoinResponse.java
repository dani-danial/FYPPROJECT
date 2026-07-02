package com.example.fypproject;

import com.google.gson.annotations.SerializedName;

public class JoinResponse {
    @SerializedName("message")
    private String message;

    @SerializedName("data")
    private GroupModel group; // The updated group info

    @SerializedName("status")
    private String status;

    @SerializedName("payment_url")
    private String paymentUrl;

    public String getMessage() { return message; }
    public GroupModel getGroup() { return group; }
    public String getStatus() { return status; }
    public String getPaymentUrl() { return paymentUrl; }
}