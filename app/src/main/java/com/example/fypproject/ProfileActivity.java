package com.example.fypproject;

import android.content.Intent;
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
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import org.osmdroid.views.MapView;
import java.util.ArrayList;
import java.util.List;

public class ProfileActivity extends AppCompatActivity {

    private ImageView ivProfile;
    private TextView tvDisplayName, tvDisplayUsername, tvDisplayAbout;
    private TextView tvTotalDistance, tvTotalRuns, tvRunnerLevel;

    // Display TextViews
    private TextView tvViewWeight, tvViewHeight, tvViewPace, tvViewPhone;

    // EditTexts (Edit Mode)
    private EditText etName, etUsername, etAbout, etWeight, etHeight, etPace, etPhone, etEmail;

    private Button btnEditProfile, btnAICoach, btnLogout;
    private LinearLayout llViewMode, llEditMode;
    private boolean isEditMode = false;
    private Bitmap selectedBitmap;
    private String currentUserEmail;

    private RecyclerView rvRunHistory;
    private RunHistoryAdapter runHistoryAdapter;

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
        fetchRunHistory();
        setupClickListeners();

        // 🛠️ CLUTTER FIX: Start in View Mode to prevent overlapping layouts
        toggleEditMode(false);
    }

    private void initViews() {
        ivProfile = findViewById(R.id.iv_profile);
        tvDisplayName = findViewById(R.id.tv_display_name);
        tvDisplayUsername = findViewById(R.id.tv_display_username);
        tvDisplayAbout = findViewById(R.id.tv_display_about);
        tvTotalDistance = findViewById(R.id.tv_profile_total_distance);
        tvTotalRuns = findViewById(R.id.tv_profile_total_runs);

        // Initialize new components from the redesigned UI
        tvRunnerLevel = findViewById(R.id.tv_runner_level);
        btnAICoach = findViewById(R.id.btn_ai_coach);
        btnLogout = findViewById(R.id.btn_logout);

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

        rvRunHistory = findViewById(R.id.rv_run_history);
        if (rvRunHistory != null) {
            rvRunHistory.setLayoutManager(new LinearLayoutManager(this));
        }

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

        // Update Runner Tier display
        if (tvRunnerLevel != null) {
            // Note: Ensure your UserModel has a getRunnerTier() method
            String tier = user.getRunnerTier();
            tvRunnerLevel.setText(tier != null ? tier : "Beginner");
        }

        String bio = user.getAboutMe();
        tvDisplayAbout.setText((bio != null && !bio.isEmpty()) ? bio : "No bio yet.");

        // View Mode formatting for cards
        if (tvViewWeight != null) tvViewWeight.setText(String.format(Locale.getDefault(), "Weight: %.1f kg", user.getWeightKg()));
        if (tvViewHeight != null) tvViewHeight.setText(String.format(Locale.getDefault(), "Height: %.0f cm", user.getHeightCm()));
        if (tvViewPace != null) tvViewPace.setText("Target Split: " + user.getBasePace());
        if (tvViewPhone != null) tvViewPhone.setText("Phone: " + (user.getPhone() != null ? user.getPhone() : "Not set"));

        etName.setText(user.getName());
        etUsername.setText(user.getUsername());
        etEmail.setText(user.getEmail());
        etAbout.setText(user.getAboutMe());
        etWeight.setText(String.valueOf(user.getWeightKg()));
        etHeight.setText(String.valueOf(user.getHeightCm()));
        etPace.setText(String.valueOf(user.getBasePace()));
        etPhone.setText(user.getPhone());

        tvTotalDistance.setText(String.format(Locale.getDefault(), "%.2f km", user.getTotalDistance()));
        tvTotalRuns.setText(String.valueOf(user.getRunsCount()));

        if (user.getProfilePhotoPath() != null && !user.getProfilePhotoPath().isEmpty()) {
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
                    populateUserData(response.body());
                    toggleEditMode(false);
                } else {
                    Log.e("SYNC_ERROR", "Code: " + response.code());
                    Toast.makeText(ProfileActivity.this, "Sync Failed", Toast.LENGTH_SHORT).show();
                }
            }
            @Override
            public void onFailure(Call<UserModel> call, Throwable t) {
                btnEditProfile.setEnabled(true);
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

        // 🛠️ FIXED: Casing changed to match your file 'AiChatActivity'
        if (btnAICoach != null) {
            btnAICoach.setOnClickListener(v -> {
                Intent intent = new Intent(this, AiChatActivity.class);
                startActivity(intent);
            });
        }

        // Logout Logic
        if (btnLogout != null) {
            btnLogout.setOnClickListener(v -> {
                getSharedPreferences("UserPrefs", MODE_PRIVATE).edit().clear().apply();
                // Assumes your login class is named LoginActivity
                Intent intent = new Intent(this, LoginActivity.class);
                intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
                startActivity(intent);
                finish();
            });
        }
    }

    private void toggleEditMode(boolean enable) {
        isEditMode = enable;
        llViewMode.setVisibility(enable ? View.GONE : View.VISIBLE);
        llEditMode.setVisibility(enable ? View.VISIBLE : View.GONE);
        btnEditProfile.setText(enable ? "Save Changes" : "Edit Profile");

        // Hide Coach/Logout buttons during editing to keep UI clean
        if (btnAICoach != null) btnAICoach.setVisibility(enable ? View.GONE : View.VISIBLE);
        if (btnLogout != null) btnLogout.setVisibility(enable ? View.GONE : View.VISIBLE);

        View changePhotoLabel = findViewById(R.id.tv_change_photo);
        if (changePhotoLabel != null) changePhotoLabel.setVisibility(enable ? View.VISIBLE : View.GONE);
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

    private void fetchRunHistory() {
        String token = "Bearer " + getToken();
        RetrofitClient.getService().getRunHistory(token).enqueue(new Callback<List<RunData>>() {
            @Override
            public void onResponse(Call<List<RunData>> call, Response<List<RunData>> response) {
                if (response.isSuccessful() && response.body() != null) {
                    List<RunData> history = response.body();
                    runHistoryAdapter = new RunHistoryAdapter(history, run -> showRunDetailMapDialog(run));
                    if (rvRunHistory != null) {
                        rvRunHistory.setAdapter(runHistoryAdapter);
                    }
                }
            }

            @Override
            public void onFailure(Call<List<RunData>> call, Throwable t) {
                Log.e("RUN_HISTORY", "Failed to load run history", t);
            }
        });
    }

    private void showRunDetailMapDialog(RunData run) {
        androidx.appcompat.app.AlertDialog.Builder builder = new androidx.appcompat.app.AlertDialog.Builder(this);
        View dialogView = getLayoutInflater().inflate(R.layout.dialog_run_detail_map, null);
        builder.setView(dialogView);

        TextView tvTitle = dialogView.findViewById(R.id.tv_dialog_title);
        TextView tvDate = dialogView.findViewById(R.id.tv_dialog_date);
        TextView tvDistance = dialogView.findViewById(R.id.tv_dialog_distance);
        TextView tvTime = dialogView.findViewById(R.id.tv_dialog_time);
        TextView tvPace = dialogView.findViewById(R.id.tv_dialog_pace);
        Button btnClose = dialogView.findViewById(R.id.btn_dialog_close);
        MapView dialogMap = dialogView.findViewById(R.id.dialog_map);
        
        View layoutAiEvaluation = dialogView.findViewById(R.id.layout_dialog_ai_evaluation);
        TextView tvAiEvaluation = dialogView.findViewById(R.id.tv_dialog_ai_evaluation);

        tvTitle.setText("Run Path Details");
        tvDate.setText(run.getDate());
        tvDistance.setText(String.format(Locale.getDefault(), "%.2f km", run.getDistanceKm()));
        tvTime.setText(run.getTimeDuration());
        tvPace.setText(run.getPace() + " /km");

        String aiEval = run.getAiEvaluation();
        if (aiEval != null && !aiEval.trim().isEmpty()) {
            tvAiEvaluation.setText(aiEval);
            layoutAiEvaluation.setVisibility(View.VISIBLE);
        } else {
            layoutAiEvaluation.setVisibility(View.GONE);
        }

        dialogMap.setTileSource(org.osmdroid.tileprovider.tilesource.TileSourceFactory.MAPNIK);
        org.osmdroid.api.IMapController mapController = dialogMap.getController();
        mapController.setZoom(17.5);
        dialogMap.setMultiTouchControls(true);

        String routePathJson = run.getRoutePath();
        if (routePathJson != null && !routePathJson.isEmpty()) {
            try {
                org.json.JSONArray array = new org.json.JSONArray(routePathJson);
                ArrayList<org.osmdroid.util.GeoPoint> pts = new ArrayList<>();
                for (int i = 0; i < array.length(); i++) {
                    org.json.JSONArray pt = array.getJSONArray(i);
                    double lat = pt.getDouble(0);
                    double lng = pt.getDouble(1);
                    pts.add(new org.osmdroid.util.GeoPoint(lat, lng));
                }

                if (!pts.isEmpty()) {
                    org.osmdroid.views.overlay.Polyline line = new org.osmdroid.views.overlay.Polyline();
                    line.setColor(android.graphics.Color.parseColor("#8D6E63"));
                    line.setWidth(10f);
                    line.setPoints(pts);
                    dialogMap.getOverlays().add(line);
                    
                    // Center map on start point
                    mapController.setCenter(pts.get(0));
                }
            } catch (Exception e) {
                Log.e("RUN_MAP_DIALOG", "Error rendering run path on map", e);
            }
        }
        dialogMap.invalidate();

        androidx.appcompat.app.AlertDialog dialog = builder.create();
        dialog.show();

        btnClose.setOnClickListener(v -> dialog.dismiss());
    }
}