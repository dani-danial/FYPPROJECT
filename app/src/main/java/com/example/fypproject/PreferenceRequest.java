package com.example.fypproject;

public class PreferenceRequest {
    private int distance_score;
    private int type_score;
    private int frequency_score;
    private int experience_score;
    private int pace_score;
    private int weekly_distance_score;
    private int recovery_score;
    private int event_score;
    private Integer age;
    private String gender;
    private String running_goal;
    private String goal_type;
    private String injury_history;

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
    public Integer getAge() { return age; }
    public void setAge(Integer age) { this.age = age; }
    public String getGender() { return gender; }
    public void setGender(String gender) { this.gender = gender; }
    public String getRunningGoal() { return running_goal; }
    public void setRunningGoal(String running_goal) { this.running_goal = running_goal; }
    public int getExperienceScore() { return experience_score; }
    public void setExperienceScore(int experience_score) { this.experience_score = experience_score; }
    public int getPaceScore() { return pace_score; }
    public void setPaceScore(int pace_score) { this.pace_score = pace_score; }
    public int getWeeklyDistanceScore() { return weekly_distance_score; }
    public void setWeeklyDistanceScore(int weekly_distance_score) { this.weekly_distance_score = weekly_distance_score; }
    public int getRecoveryScore() { return recovery_score; }
    public void setRecoveryScore(int recovery_score) { this.recovery_score = recovery_score; }
    public int getEventScore() { return event_score; }
    public void setEventScore(int event_score) { this.event_score = event_score; }
    public String getGoalType() { return goal_type; }
    public void setGoalType(String goal_type) {
        this.goal_type = goal_type;
        this.running_goal = goal_type;
    }
    public String getInjuryHistory() { return injury_history; }
    public void setInjuryHistory(String injury_history) { this.injury_history = injury_history; }
}
