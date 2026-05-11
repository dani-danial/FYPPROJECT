package com.example.fypproject;

import android.app.Activity;
import android.content.SharedPreferences;
import android.graphics.Bitmap;
import android.net.Uri;
import android.os.Bundle;
import android.provider.MediaStore;
import android.util.Log;
import android.view.View;
import android.widget.*;
import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.appcompat.app.AppCompatActivity;
import com.bumptech.glide.Glide;
import com.bumptech.glide.load.engine.DiskCacheStrategy;
import com.bumptech.glide.signature.ObjectKey;
import java.io.*;
import java.util.Locale;
import okhttp3.MediaType;
import okhttp3.MultipartBody;
import okhttp3.RequestBody;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class ProfileActivity extends AppCompatActivity {

    private ImageView ivProfile;
    private TextView tvDisplayName, tvDisplayUsername, tvDisplayAbout;
    private TextView tvTotalDistance, tvTotalRuns;

    // Display TextViews
    private TextView tvViewWeight, tvViewHeight, tvViewPace, tvViewPhone;

    // EditTexts (Edit Mode)
    private EditText etName, etUsername, etAbout, etWeight, etHeight, etPace, etPhone, etEmail;

    private Button btnEditProfile;
    private LinearLayout llViewMode, llEditMode;
    private boolean isEditMode = false;
    private Bitmap selectedBitmap;
    private String currentUserEmail;

    private final ActivityResultLauncher<String> pickImage = registerForActivityResult(
            new ActivityResultContracts.GetContent(),
            uri -> {
                if (uri != null) {
                    try {
                        selectedBitmap = MediaStore.Images.Media.getBitmap(getContentResolver(), uri);
                        ivProfile.setImageBitmap(selectedBitmap);
                    } catch (IOException e) {
                        e.printStackTrace();
                        Toast.makeText(this, "Failed to load image", Toast.LENGTH_SHORT).show();
                    }
                }
            }
    );

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_profile);
        initViews();
        fetchUserProfile();
        setupClickListeners();
    }

    private void initViews() {
        ivProfile = findViewById(R.id.iv_profile);
        tvDisplayName = findViewById(R.id.tv_display_name);
        tvDisplayUsername = findViewById(R.id.tv_display_username);
        tvDisplayAbout = findViewById(R.id.tv_display_about);
        tvTotalDistance = findViewById(R.id.tv_profile_total_distance);
        tvTotalRuns = findViewById(R.id.tv_profile_total_runs);

        tvViewWeight = findViewById(R.id.tv_view_weight);
        tvViewHeight = findViewById(R.id.tv_view_height);
        tvViewPace = findViewById(R.id.tv_view_pace);
        tvViewPhone = findViewById(R.id.tv_view_phone);

        etName = findViewById(R.id.et_name);
        etUsername = findViewById(R.id.et_username);
        etAbout = findViewById(R.id.et_about);
        etWeight = findViewById(R.id.et_weight);
        etHeight = findViewById(R.id.et_height);
        etPace = findViewById(R.id.et_pace);
        etPhone = findViewById(R.id.et_phone);
        etEmail = findViewById(R.id.et_email);

        btnEditProfile = findViewById(R.id.btn_edit_profile);
        llViewMode = findViewById(R.id.ll_view_mode);
        llEditMode = findViewById(R.id.ll_edit_mode);

        findViewById(R.id.btn_back).setOnClickListener(v -> finish());
    }

    private void fetchUserProfile() {
        String token = "Bearer " + getToken();
        RetrofitClient.getService().getUserProfile(token).enqueue(new Callback<UserModel>() {
            @Override
            public void onResponse(Call<UserModel> call, Response<UserModel> response) {
                if (response.isSuccessful() && response.body() != null) {
                    populateUserData(response.body());
                }
            }
            @Override
            public void onFailure(Call<UserModel> call, Throwable t) {
                Toast.makeText(ProfileActivity.this, "Network Error", Toast.LENGTH_SHORT).show();
            }
        });
    }

    private void populateUserData(UserModel user) {
        currentUserEmail = user.getEmail();

        tvDisplayName.setText(user.getName());
        tvDisplayUsername.setText("@" + user.getUsername());

        // 🛠️ FIX: Use getAboutMe() which maps to "about_me" in DB
        String bio = user.getAboutMe();
        tvDisplayAbout.setText((bio != null && !bio.isEmpty()) ? bio : "No bio yet.");

        if (tvViewWeight != null) tvViewWeight.setText(String.format(Locale.getDefault(), "%.1f kg", user.getWeightKg()));
        if (tvViewHeight != null) tvViewHeight.setText(String.format(Locale.getDefault(), "%.0f cm", user.getHeightCm()));
        if (tvViewPace != null) tvViewPace.setText(String.valueOf(user.getBasePace()));
        if (tvViewPhone != null) tvViewPhone.setText(user.getPhone() != null ? user.getPhone() : "Not set");

        etName.setText(user.getName());
        etUsername.setText(user.getUsername());
        etEmail.setText(user.getEmail());
        etAbout.setText(user.getAboutMe());
        etWeight.setText(String.valueOf(user.getWeightKg()));
        etHeight.setText(String.valueOf(user.getHeightCm()));
        etPace.setText(String.valueOf(user.getBasePace()));
        etPhone.setText(user.getPhone());

        // 🛠️ STATS FIX: Ensure these are updated after every sync
        tvTotalDistance.setText(String.format(Locale.getDefault(), "%.2f km", user.getTotalDistance()));
        tvTotalRuns.setText(String.valueOf(user.getRunsCount()));

        // 🛠️ GLIDE FIX: Using current time as signature to bypass cache
        if (user.getProfilePhotoPath() != null && !user.getProfilePhotoPath().isEmpty()) {
            Log.d("PROFILE_DEBUG", "Loading: " + user.getProfilePhotoPath());
            Glide.with(this)
                    .load(user.getProfilePhotoPath())
                    .signature(new ObjectKey(System.currentTimeMillis()))
                    .diskCacheStrategy(DiskCacheStrategy.ALL)
                    .circleCrop()
                    .placeholder(android.R.drawable.ic_menu_gallery)
                    .error(android.R.drawable.stat_notify_error)
                    .into(ivProfile);
        }
    }

    private void updateProfile() {
        String name = etName.getText().toString().trim();
        String userStr = etUsername.getText().toString().trim();
        String email = etEmail.getText().toString().trim();
        String about = etAbout.getText().toString().trim();
        String phone = etPhone.getText().toString().trim();
        String weightVal = etWeight.getText().toString().trim();
        String heightVal = etHeight.getText().toString().trim();
        String paceVal = etPace.getText().toString().trim();

        if (name.isEmpty() || userStr.isEmpty()) {
            Toast.makeText(this, "Name and Username are required", Toast.LENGTH_SHORT).show();
            return;
        }

        btnEditProfile.setEnabled(false);
        String token = "Bearer " + getToken();
        Call<UserModel> call;

        // 🛠️ VALIDATION FIX: Send "" (empty string) instead of "0" if empty
        // This prevents the "Between 1 and 30" Error 422 on the backend
        String weightToSend = weightVal.isEmpty() ? "" : weightVal;
        String heightToSend = heightVal.isEmpty() ? "" : heightVal;
        String paceToSend = paceVal.isEmpty() ? "" : paceVal;

        if (selectedBitmap != null) {
            File file = bitmapToFile(selectedBitmap);

            RequestBody namePart = RequestBody.create(MediaType.parse("text/plain"), name);
            RequestBody userPart = RequestBody.create(MediaType.parse("text/plain"), userStr);
            RequestBody emailPart = RequestBody.create(MediaType.parse("text/plain"), email);
            RequestBody aboutPart = RequestBody.create(MediaType.parse("text/plain"), about);
            RequestBody phonePart = RequestBody.create(MediaType.parse("text/plain"), phone);
            RequestBody weightPart = RequestBody.create(MediaType.parse("text/plain"), weightToSend);
            RequestBody heightPart = RequestBody.create(MediaType.parse("text/plain"), heightToSend);
            RequestBody pacePart = RequestBody.create(MediaType.parse("text/plain"), paceToSend);
            RequestBody removePhoto = RequestBody.create(MediaType.parse("text/plain"), "0");

            MultipartBody.Part img = MultipartBody.Part.createFormData("profile_picture", file.getName(),
                    RequestBody.create(MediaType.parse("image/*"), file));

            call = RetrofitClient.getService().updateProfileWithImage(
                    token, namePart, userPart, emailPart, aboutPart, phonePart, weightPart, heightPart, pacePart, removePhoto, img
            );
        } else {
            call = RetrofitClient.getService().updateProfile(
                    token, name, userStr, email, about, phone, weightToSend, heightToSend, paceToSend
            );
        }

        call.enqueue(new Callback<UserModel>() {
            @Override
            public void onResponse(Call<UserModel> call, Response<UserModel> response) {
                btnEditProfile.setEnabled(true);
                if (response.isSuccessful() && response.body() != null) {
                    Toast.makeText(ProfileActivity.this, "Profile Synced! 🍫", Toast.LENGTH_SHORT).show();
                    selectedBitmap = null;

                    // 🛠️ Refresh the whole UI with the fresh data from the server
                    populateUserData(response.body());
                    toggleEditMode(false);
                } else {
                    Log.e("SYNC_ERROR", "Code: " + response.code());
                    Toast.makeText(ProfileActivity.this, "Sync Failed: Check logs", Toast.LENGTH_SHORT).show();
                }
            }
            @Override
            public void onFailure(Call<UserModel> call, Throwable t) {
                btnEditProfile.setEnabled(true);
                Log.e("SYNC_FAILURE", t.getMessage());
                Toast.makeText(ProfileActivity.this, "Network Error", Toast.LENGTH_SHORT).show();
            }
        });
    }

    private void setupClickListeners() {
        btnEditProfile.setOnClickListener(v -> {
            if (isEditMode) updateProfile();
            else toggleEditMode(true);
        });
        ivProfile.setOnClickListener(v -> {
            if (isEditMode) pickImage.launch("image/*");
        });
    }

    private void toggleEditMode(boolean enable) {
        isEditMode = enable;
        llViewMode.setVisibility(enable ? View.GONE : View.VISIBLE);
        llEditMode.setVisibility(enable ? View.VISIBLE : View.GONE);
        btnEditProfile.setText(enable ? "Save Changes" : "Edit Profile");
        findViewById(R.id.tv_change_photo).setVisibility(enable ? View.VISIBLE : View.GONE);
    }

    private String getToken() {
        return getSharedPreferences("UserPrefs", MODE_PRIVATE).getString("token", "");
    }

    private File bitmapToFile(Bitmap bitmap) {
        try {
            File f = new File(getCacheDir(), "profile_upd_" + System.currentTimeMillis() + ".jpg");
            ByteArrayOutputStream bos = new ByteArrayOutputStream();
            bitmap.compress(Bitmap.CompressFormat.JPEG, 85, bos);
            FileOutputStream fos = new FileOutputStream(f);
            fos.write(bos.toByteArray());
            fos.close();
            return f;
        } catch (Exception e) {
            e.printStackTrace();
            return null;
        }
    }
}