package com.example.fypproject;

import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.ProgressBar;
import android.widget.RadioButton;
import android.widget.RadioGroup;
import android.widget.Toast;
import android.widget.ViewFlipper;
import androidx.appcompat.app.AppCompatActivity;

import java.io.IOException;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class OnboardingActivity extends AppCompatActivity {

    private ViewFlipper viewFlipper;
    private Button btnNext, btnBack;
    private ProgressBar progressBar;
    private RadioGroup rgDist, rgType, rgFreq, rgExperience, rgPace, rgWeeklyDistance, rgGoal, rgRecovery, rgEvents, rgInjury;
    private int currentPage = 0;
    private static final int LAST_PAGE = 8;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_onboarding);

        // Initialize UI
        viewFlipper = findViewById(R.id.view_flipper);
        btnNext = findViewById(R.id.btn_next);
        btnBack = findViewById(R.id.btn_back);
        progressBar = findViewById(R.id.onboarding_progress);

        rgDist = findViewById(R.id.rg_distance);
        rgType = findViewById(R.id.rg_type);
        rgFreq = findViewById(R.id.rg_freq);
        rgExperience = findViewById(R.id.rg_experience);
        rgPace = findViewById(R.id.rg_pace);
        rgWeeklyDistance = findViewById(R.id.rg_weekly_distance);
        rgGoal = findViewById(R.id.rg_goal);
        rgRecovery = findViewById(R.id.rg_recovery);
        rgEvents = findViewById(R.id.rg_events);
        rgInjury = findViewById(R.id.rg_injury);

        // --- NEXT BUTTON LOGIC ---
        btnNext.setOnClickListener(v -> {
            if (currentPage < LAST_PAGE) {
                if (isCurrentPageAnswered()) {
                    currentPage++;
                    viewFlipper.showNext();
                    updateUI();
                } else {
                    Toast.makeText(this, "Please select an option first!", Toast.LENGTH_SHORT).show();
                }
            } else {
                calculateAndSubmit();
            }
        });

        // --- BACK BUTTON LOGIC ---
        btnBack.setOnClickListener(v -> {
            if (currentPage > 0) {
                currentPage--;
                viewFlipper.showPrevious();
                updateUI();
            }
        });
    }

    private void updateUI() {
        progressBar.setProgress(currentPage + 1);
        btnBack.setVisibility(currentPage == 0 ? View.GONE : View.VISIBLE);
        btnNext.setText(currentPage == LAST_PAGE ? "FINISH" : "NEXT");
    }

    private boolean isCurrentPageAnswered() {
        if (currentPage == 0) return rgDist.getCheckedRadioButtonId() != -1;
        if (currentPage == 1) return rgType.getCheckedRadioButtonId() != -1;
        if (currentPage == 2) return rgFreq.getCheckedRadioButtonId() != -1;
        if (currentPage == 3) return rgExperience.getCheckedRadioButtonId() != -1;
        if (currentPage == 4) return rgPace.getCheckedRadioButtonId() != -1;
        if (currentPage == 5) return rgWeeklyDistance.getCheckedRadioButtonId() != -1;
        if (currentPage == 6) return rgGoal.getCheckedRadioButtonId() != -1;
        if (currentPage == 7) return rgRecovery.getCheckedRadioButtonId() != -1;
        return rgEvents.getCheckedRadioButtonId() != -1 && rgInjury.getCheckedRadioButtonId() != -1;
    }

    private void calculateAndSubmit() {
        int d = getPoints(rgDist);
        int t = getPoints(rgType);
        int f = getPoints(rgFreq);
        int experience = getPoints(rgExperience);
        int pace = getPoints(rgPace);
        int weeklyDistance = getPoints(rgWeeklyDistance);
        int recovery = getPoints(rgRecovery);
        int events = getPoints(rgEvents);

        if (d == 0 || t == 0 || f == 0 || experience == 0 || pace == 0 || weeklyDistance == 0 || recovery == 0 || events == 0 || rgGoal.getCheckedRadioButtonId() == -1 || rgInjury.getCheckedRadioButtonId() == -1) {
            Toast.makeText(this, "Please answer all questions!", Toast.LENGTH_SHORT).show();
            return;
        }

        btnNext.setEnabled(false);
        btnNext.setText("Saving...");

        // 🛠️ RETRIEVE TOKEN
        SharedPreferences prefs = getSharedPreferences("UserPrefs", MODE_PRIVATE);
        String savedToken = prefs.getString("token", "");

        if (savedToken.isEmpty()) {
            Log.e("AUTH_DEBUG", "CRITICAL: No token found in SharedPreferences.");
            Toast.makeText(this, "Authentication missing. Please login again.", Toast.LENGTH_LONG).show();
            btnNext.setEnabled(true);
            btnNext.setText("FINISH");
            return;
        }

        // Add Bearer prefix
        String authHeader = "Bearer " + savedToken;
        Log.d("AUTH_DEBUG", "Auth Header: " + authHeader);

        PreferenceRequest request = new PreferenceRequest(d, t, f);
        request.setExperienceScore(experience);
        request.setPaceScore(pace);
        request.setWeeklyDistanceScore(weeklyDistance);
        request.setRecoveryScore(recovery);
        request.setEventScore(events);
        request.setGoalType(getSelectedText(rgGoal));
        request.setInjuryHistory(getInjuryCode());
        int age = prefs.getInt("age", 0);
        if (age > 0) request.setAge(age);
        request.setGender(prefs.getString("gender", null));
        if (prefs.getString("running_goal", null) != null && getSelectedText(rgGoal).isEmpty()) {
            request.setRunningGoal(prefs.getString("running_goal", null));
        }

        // Call Service
        RetrofitClient.getService().classifyRunner(authHeader, request).enqueue(new Callback<CategoryResponse>() {
            @Override
            public void onResponse(Call<CategoryResponse> call, Response<CategoryResponse> response) {
                if (response.isSuccessful() && response.body() != null) {
                    // SAVE LOCALLY
                    String category = response.body().getCategory();
                    prefs.edit().putString("runner_tier", category).apply();

                    Toast.makeText(OnboardingActivity.this, "Runner Profile: " + category, Toast.LENGTH_SHORT).show();

                    // MOVE TO MAIN
                    Intent intent = new Intent(OnboardingActivity.this, MainActivity.class);
                    intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
                    startActivity(intent);
                    finish();
                } else {
                    btnNext.setEnabled(true);
                    btnNext.setText("FINISH");

                    try {
                        String errorBody = response.errorBody() != null ? response.errorBody().string() : "Empty error body";
                        Log.e("API_DEBUG", "Status Code: " + response.code());
                        Log.e("API_DEBUG", "Server Response: " + errorBody);

                        if (response.code() == 401) {
                            Toast.makeText(OnboardingActivity.this, "Session expired. Try logging in again.", Toast.LENGTH_LONG).show();
                        } else {
                            Toast.makeText(OnboardingActivity.this, "Server rejected request.", Toast.LENGTH_SHORT).show();
                        }
                    } catch (IOException e) {
                        e.printStackTrace();
                    }
                }
            }

            @Override
            public void onFailure(Call<CategoryResponse> call, Throwable t) {
                btnNext.setEnabled(true);
                btnNext.setText("FINISH");
                Log.e("API_DEBUG", "Failure: " + t.getMessage());
                Toast.makeText(OnboardingActivity.this, "Network Connection Failed", Toast.LENGTH_SHORT).show();
            }
        });
    }

    private int getPoints(RadioGroup rg) {
        int id = rg.getCheckedRadioButtonId();
        if (id == -1) return 0;
        View radioButton = rg.findViewById(id);
        return rg.indexOfChild(radioButton) + 1;
    }

    private String getSelectedText(RadioGroup rg) {
        int id = rg.getCheckedRadioButtonId();
        if (id == -1) return "";
        RadioButton radioButton = rg.findViewById(id);
        return radioButton != null ? radioButton.getText().toString() : "";
    }

    private String getInjuryCode() {
        int points = getPoints(rgInjury);
        if (points == 2) return "minor";
        if (points == 3) return "recovering";
        return "none";
    }
}
