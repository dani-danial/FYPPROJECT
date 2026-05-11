package com.example.fypproject;

public class PreferenceRequest {
    private int distance_score;
    private int type_score;
    private int frequency_score;

    public PreferenceRequest(int distance_score, int type_score, int frequency_score) {
        this.distance_score = distance_score;
        this.type_score = type_score;
        this.frequency_score = frequency_score;
    }

    // Getters and Setters
    public int getDistanceScore() { return distance_score; }
    public void setDistanceScore(int distance_score) { this.distance_score = distance_score; }
    public int getTypeScore() { return type_score; }
    public void setTypeScore(int type_score) { this.type_score = type_score; }
    public int getFrequencyScore() { return frequency_score; }
    public void setFrequencyScore(int frequency_score) { this.frequency_score = frequency_score; }
}