package com.example.fypproject;

import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.ProgressBar;
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
    private RadioGroup rgDist, rgType, rgFreq;
    private int currentPage = 0;

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

        // --- NEXT BUTTON LOGIC ---
        btnNext.setOnClickListener(v -> {
            if (currentPage < 2) {
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
        btnNext.setText(currentPage == 2 ? "FINISH" : "NEXT");
    }

    private boolean isCurrentPageAnswered() {
        if (currentPage == 0) return rgDist.getCheckedRadioButtonId() != -1;
        if (currentPage == 1) return rgType.getCheckedRadioButtonId() != -1;
        return rgFreq.getCheckedRadioButtonId() != -1;
    }

    private void calculateAndSubmit() {
        int d = getPoints(rgDist);
        int t = getPoints(rgType);
        int f = getPoints(rgFreq);

        if (d == 0 || t == 0 || f == 0) {
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
}