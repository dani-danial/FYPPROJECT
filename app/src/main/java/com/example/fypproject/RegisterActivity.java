package com.example.fypproject;

import android.app.DatePickerDialog;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.RadioGroup;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;

import java.util.Calendar;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class RegisterActivity extends AppCompatActivity {

    private EditText etName, etUsername, etEmail, etPassword, etAge, etRunningGoal, etEmergencyPhone;
    private RadioGroup rgGender;
    private Button btnRegister;
    private TextView tvLogin;
    private int calculatedAge = -1;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_register);

        // 1. Initialize Views
        etName = findViewById(R.id.et_register_name);
        etUsername = findViewById(R.id.et_register_username); // Make sure this ID exists in XML
        etEmail = findViewById(R.id.et_register_email);
        etPassword = findViewById(R.id.et_register_password);
        etAge = findViewById(R.id.et_register_age);
        etRunningGoal = findViewById(R.id.et_register_running_goal);
        etEmergencyPhone = findViewById(R.id.et_register_emergency_phone);
        rgGender = findViewById(R.id.rg_register_gender);
        btnRegister = findViewById(R.id.btn_register);
        tvLogin = findViewById(R.id.tv_login_link); // Assuming you have a "Login here" link

        etAge.setOnClickListener(v -> showDatePickerDialog());

        // 2. Setup Register Button Click
        btnRegister.setOnClickListener(v -> {
            String name = etName.getText().toString().trim();
            String username = etUsername.getText().toString().trim();
            String email = etEmail.getText().toString().trim();
            String password = etPassword.getText().toString().trim();
            String runningGoal = etRunningGoal.getText().toString().trim();
            String emergencyPhone = etEmergencyPhone.getText().toString().trim();
            String gender = getSelectedGender();

            if (name.isEmpty() || username.isEmpty() || email.isEmpty() || password.isEmpty()
                    || calculatedAge == -1 || gender == null || runningGoal.isEmpty() || emergencyPhone.isEmpty()) {
                Toast.makeText(RegisterActivity.this, "Please fill all fields", Toast.LENGTH_SHORT).show();
            } else {
                performRegistration(name, username, email, password, calculatedAge, gender, runningGoal, emergencyPhone);
            }
        });

        // 3. Setup Login Link Click (Go back to Login)
        if (tvLogin != null) {
            tvLogin.setOnClickListener(v -> {
                startActivity(new Intent(RegisterActivity.this, LoginActivity.class));
                finish();
            });
        }
    }

    // --- The Method that was causing errors (Now correctly placed inside the class) ---
    private void performRegistration(String name, String username, String email, String password,
                                     int age, String gender, String runningGoal, String emergencyPhone) {

        // Create the request object with all fields
        RegisterRequest request = new RegisterRequest(name, username, email, password, age, gender, runningGoal, emergencyPhone);

        // Call the API
        RetrofitClient.getService().register(request).enqueue(new Callback<LoginResponse>() {
            @Override
            public void onResponse(Call<LoginResponse> call, Response<LoginResponse> response) {
                if (response.isSuccessful() && response.body() != null) {
                    Toast.makeText(RegisterActivity.this, "Registration Successful!", Toast.LENGTH_SHORT).show();

                    LoginResponse loginResponse = response.body();
                    UserModel user = loginResponse.getUser();

                    getSharedPreferences("UserPrefs", MODE_PRIVATE)
                            .edit()
                            .putString("token", loginResponse.getToken())
                            .putInt("user_id", user != null ? user.getId() : 0)
                            .putString("userId", user != null ? String.valueOf(user.getId()) : "0")
                            .putString("name", user != null ? user.getName() : name)
                            .putInt("age", age)
                            .putString("gender", gender)
                            .putString("running_goal", runningGoal)
                            .putString("userPhone", emergencyPhone)
                            .remove("runner_tier")
                            .apply();

                    Intent intent = new Intent(RegisterActivity.this, OnboardingActivity.class);
                    intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
                    startActivity(intent);
                    finish();

                } else {
                    // Check for specific error codes (like 422 for duplicate email/username)
                    if (response.code() == 422) {
                        Toast.makeText(RegisterActivity.this, "Username or Email already taken!", Toast.LENGTH_SHORT).show();
                    } else {
                        Toast.makeText(RegisterActivity.this, "Registration Failed: " + response.code(), Toast.LENGTH_SHORT).show();
                    }
                }
            }

            @Override
            public void onFailure(Call<LoginResponse> call, Throwable t) {
                Toast.makeText(RegisterActivity.this, "Error: " + t.getMessage(), Toast.LENGTH_SHORT).show();
            }
        });
    }

    private String getSelectedGender() {
        int selectedId = rgGender.getCheckedRadioButtonId();
        if (selectedId == R.id.rb_gender_male) return "male";
        if (selectedId == R.id.rb_gender_female) return "female";
        if (selectedId == R.id.rb_gender_other) return "other";
        if (selectedId == R.id.rb_gender_prefer_not) return "prefer_not_to_say";
        return null;
    }

    private void showDatePickerDialog() {
        final Calendar c = Calendar.getInstance();
        int year = c.get(Calendar.YEAR) - 20; // Default to 20 years ago for convenience
        int month = c.get(Calendar.MONTH);
        int day = c.get(Calendar.DAY_OF_MONTH);

        DatePickerDialog datePickerDialog = new DatePickerDialog(this,
                (view, selectedYear, selectedMonth, selectedDay) -> {
                    Calendar dob = Calendar.getInstance();
                    dob.set(selectedYear, selectedMonth, selectedDay);

                    Calendar today = Calendar.getInstance();
                    int age = today.get(Calendar.YEAR) - dob.get(Calendar.YEAR);
                    if (today.get(Calendar.DAY_OF_YEAR) < dob.get(Calendar.DAY_OF_YEAR)) {
                        age--;
                    }

                    if (age < 0) {
                        Toast.makeText(this, "Invalid birthdate", Toast.LENGTH_SHORT).show();
                    } else {
                        calculatedAge = age;
                        etAge.setText(age + " (Born " + selectedYear + "-" + (selectedMonth + 1) + "-" + selectedDay + ")");
                    }
                }, year, month, day);
        datePickerDialog.show();
    }
}
