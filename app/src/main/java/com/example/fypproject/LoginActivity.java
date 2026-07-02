package com.example.fypproject;

import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class LoginActivity extends AppCompatActivity {

    private EditText etEmail, etPassword;
    private Button btnLogin;
    private TextView tvRegisterLink;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        // Check if already logged in
        SharedPreferences prefs = getSharedPreferences("UserPrefs", MODE_PRIVATE);
        String savedToken = prefs.getString("token", null);
        String savedTier = prefs.getString("runner_tier", null);

        if (savedToken != null) {
            Intent intent;
            // 🆕 If they logged in before but never finished onboarding, send them back to it
            if (needsRunnerAssessment(savedTier)) {
                intent = new Intent(LoginActivity.this, OnboardingActivity.class);
            } else {
                intent = new Intent(LoginActivity.this, MainActivity.class);
            }
            startActivity(intent);
            finish();
            return;
        }

        setContentView(R.layout.activity_login);

        etEmail = findViewById(R.id.et_email);
        etPassword = findViewById(R.id.et_password);
        btnLogin = findViewById(R.id.btn_login);
        tvRegisterLink = findViewById(R.id.tv_register_link);

        btnLogin.setOnClickListener(v -> {
            String email = etEmail.getText().toString().trim();
            String password = etPassword.getText().toString().trim();

            if (email.isEmpty() || password.isEmpty()) {
                Toast.makeText(LoginActivity.this, "Please enter email and password", Toast.LENGTH_SHORT).show();
            } else {
                performLogin(email, password);
            }
        });

        if (tvRegisterLink != null) {
            tvRegisterLink.setOnClickListener(v -> {
                Intent intent = new Intent(LoginActivity.this, RegisterActivity.class);
                startActivity(intent);
                finish();
            });
        }
    }

    private void performLogin(String email, String password) {
        LoginRequest request = new LoginRequest(email, password);

        RetrofitClient.getService().login(request).enqueue(new Callback<LoginResponse>() {
            @Override
            public void onResponse(Call<LoginResponse> call, Response<LoginResponse> response) {
                if (response.isSuccessful() && response.body() != null) {
                    LoginResponse loginResponse = response.body();
                    UserModel user = loginResponse.getUser();

                    // Save Token & User Info to "UserPrefs"
                    SharedPreferences prefs = getSharedPreferences("UserPrefs", MODE_PRIVATE);
                    SharedPreferences.Editor editor = prefs.edit();

                    editor.putString("token", loginResponse.getToken());

                    if (user != null) {
                        int realUserId = user.getId();
                        editor.putInt("user_id", realUserId);
                        editor.putString("userId", String.valueOf(realUserId));
                        editor.putString("name", user.getName());
                        editor.putInt("age", user.getAge());
                        editor.putString("gender", user.getGender());
                        editor.putString("running_goal", user.getRunningGoal());

                        // 🆕 Save the tier to prefs so we know if they are "Fresh" or not
                        editor.putString("runner_tier", user.getRunnerTier());
                    }
                    editor.apply();

                    Toast.makeText(LoginActivity.this, "Welcome, " + user.getName(), Toast.LENGTH_SHORT).show();

                    // 🆕 ONBOARDING GATEKEEPER LOGIC
                    Intent intent;
                    if (needsRunnerAssessment(user.getRunnerTier())) {
                        // Fresh account -> Go to Assessment screens
                        intent = new Intent(LoginActivity.this, OnboardingActivity.class);
                    } else {
                        // Existing runner -> Go to Home
                        intent = new Intent(LoginActivity.this, MainActivity.class);
                    }

                    startActivity(intent);
                    finish();

                } else {
                    Toast.makeText(LoginActivity.this, "Login Failed: Invalid Credentials", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(Call<LoginResponse> call, Throwable t) {
                Toast.makeText(LoginActivity.this, "Server Connection Failed", Toast.LENGTH_SHORT).show();
            }
        });
    }

    private boolean needsRunnerAssessment(String tier) {
        if (tier == null || tier.trim().isEmpty()) return true;

        String normalizedTier = tier.trim();
        return normalizedTier.equalsIgnoreCase("LOW")
                || normalizedTier.equalsIgnoreCase("MEDIUM")
                || normalizedTier.equalsIgnoreCase("HARD");
    }
}
