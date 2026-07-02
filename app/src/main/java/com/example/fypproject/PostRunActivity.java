package com.example.fypproject;

import android.graphics.Color;
import android.graphics.ColorSpace;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.ImageButton;
import android.widget.ProgressBar;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;
import androidx.appcompat.widget.AppCompatButton;
import androidx.appcompat.widget.SwitchCompat;

import org.json.JSONArray;
import org.osmdroid.api.IMapController;
import org.osmdroid.config.Configuration;
import org.osmdroid.tileprovider.tilesource.TileSourceFactory;
import org.osmdroid.util.GeoPoint;
import org.osmdroid.views.MapView;
import org.osmdroid.views.overlay.Polyline;

import android.net.Uri;
import android.widget.FrameLayout;
import android.widget.ImageView;
import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import java.io.File;
import java.io.FileOutputStream;
import java.io.InputStream;
import okhttp3.MediaType;
import okhttp3.MultipartBody;
import okhttp3.RequestBody;
import java.util.ArrayList;
import java.util.Locale;

import okhttp3.ResponseBody;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class PostRunActivity extends AppCompatActivity {

    private MapView mapView;
    private TextView tvDistance, tvDuration, tvPace;
    private TextView tvCurrentTier, tvModifiersLog;
    private TextView tvAiEvaluation;
    private ProgressBar pbAiLoading;
    private SwitchCompat switchShareFeed;
    private AppCompatButton btnSaveActivity, btnDiscardRun;
    private ImageButton btnClose;

    private Uri selectedImageUri;
    private FrameLayout btnSelectPhoto;
    private ImageView ivPhotoPreview;
    private ImageButton btnRemovePhoto;
    private View layoutPhotoPlaceholder;

    private final ActivityResultLauncher<String> pickImageLauncher = registerForActivityResult(
            new ActivityResultContracts.GetContent(),
            uri -> {
                if (uri != null) {
                    selectedImageUri = uri;
                    ivPhotoPreview.setImageURI(uri);
                    ivPhotoPreview.setVisibility(View.VISIBLE);
                    btnRemovePhoto.setVisibility(View.VISIBLE);
                    layoutPhotoPlaceholder.setVisibility(View.GONE);
                }
            }
    );

    private double distance;
    private int duration;
    private String pace;
    private String routePath;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        
        // Initialize OSMDroid config before layout inflation
        Configuration.getInstance().load(getApplicationContext(), getSharedPreferences("OsmPrefs", MODE_PRIVATE));
        
        setContentView(R.layout.activity_post_run);

        // Bind views
        mapView = findViewById(R.id.post_run_map);
        tvDistance = findViewById(R.id.tv_post_distance);
        tvDuration = findViewById(R.id.tv_post_duration);
        tvPace = findViewById(R.id.tv_post_pace);
        tvCurrentTier = findViewById(R.id.tv_current_tier);
        tvModifiersLog = findViewById(R.id.tv_modifiers_log);
        tvAiEvaluation = findViewById(R.id.tv_ai_evaluation);
        pbAiLoading = findViewById(R.id.pb_ai_loading);
        switchShareFeed = findViewById(R.id.switch_share_feed);
        btnSaveActivity = findViewById(R.id.btn_save_activity);
        btnDiscardRun = findViewById(R.id.btn_discard_run);
        btnClose = findViewById(R.id.btn_close);

        btnSelectPhoto = findViewById(R.id.btn_select_photo);
        ivPhotoPreview = findViewById(R.id.iv_photo_preview);
        btnRemovePhoto = findViewById(R.id.btn_remove_photo);
        layoutPhotoPlaceholder = findViewById(R.id.layout_photo_placeholder);

        // Fetch intent extras
        distance = getIntent().getDoubleExtra("distance", 0.0);
        duration = getIntent().getIntExtra("duration", 0);
        pace = getIntent().getStringExtra("pace");
        routePath = getIntent().getStringExtra("route_path");

        // Format stats values
        tvDistance.setText(String.format(Locale.getDefault(), "%.2f km", distance));
        tvDuration.setText(formatDuration(duration));
        tvPace.setText((pace != null && !pace.isEmpty() ? pace : "--:--") + " /km");

        // Set up the static route map path
        setupRouteMap();

        // Bind actions
        btnSelectPhoto.setOnClickListener(v -> pickImageLauncher.launch("image/*"));
        btnRemovePhoto.setOnClickListener(v -> {
            selectedImageUri = null;
            ivPhotoPreview.setImageURI(null);
            ivPhotoPreview.setVisibility(View.GONE);
            btnRemovePhoto.setVisibility(View.GONE);
            layoutPhotoPlaceholder.setVisibility(View.VISIBLE);
        });

        btnClose.setOnClickListener(v -> handleDiscardRun());
        btnDiscardRun.setOnClickListener(v -> handleDiscardRun());
        btnSaveActivity.setOnClickListener(v -> saveActivityToServer());
        
        // Populate pre-save user tier if available
        String currentSavedTier = getSharedPreferences("UserPrefs", MODE_PRIVATE).getString("runner_tier", "Beginner");
        tvCurrentTier.setText("[ Tier: " + currentSavedTier + " Runner ]");
    }

    private String formatDuration(int totalSeconds) {
        int h = totalSeconds / 3600;
        int m = (totalSeconds % 3600) / 60;
        int s = totalSeconds % 60;
        return String.format(Locale.getDefault(), "%02d:%02d:%02d", h, m, s);
    }

    private void setupRouteMap() {
        mapView.setTileSource(TileSourceFactory.MAPNIK);
        mapView.setMultiTouchControls(true);
        IMapController mapController = mapView.getController();
        mapController.setZoom(17.0);

        if (routePath != null && !routePath.isEmpty()) {
            try {
                JSONArray array = new JSONArray(routePath);
                ArrayList<GeoPoint> pts = new ArrayList<>();
                for (int i = 0; i < array.length(); i++) {
                    JSONArray pt = array.getJSONArray(i);
                    pts.add(new GeoPoint(pt.getDouble(0), pt.getDouble(1)));
                }

                if (!pts.isEmpty()) {
                    Polyline line = new Polyline();
                    line.setColor(Color.parseColor("#D4AF37")); // Gold theme highlights
                    line.setWidth(10f);
                    line.setPoints(pts);
                    mapView.getOverlays().add(line);
                    
                    // Center the map on the midpoint of the route path
                    GeoPoint centerPt = pts.get(pts.size() / 2);
                    mapController.setCenter(centerPt);
                }
            } catch (Exception e) {
                Log.e("POST_RUN_MAP", "Error deserializing run coordinates route path", e);
            }
        }
        mapView.invalidate();
    }

    private void handleDiscardRun() {
        new AlertDialog.Builder(this)
                .setTitle("Discard Run Session")
                .setMessage("Are you sure you want to discard this run? Telemetry details will be wiped safely from temporary memory.")
                .setPositiveButton("Discard", (dialog, which) -> {
                    Toast.makeText(PostRunActivity.this, "🗑️ Session data discarded.", Toast.LENGTH_SHORT).show();
                    setResult(RESULT_CANCELED);
                    finish();
                })
                .setNegativeButton("Keep Run", null)
                .show();
    }

    private void saveActivityToServer() {
        // Prepare request data as RequestBody parts for multipart format
        RequestBody distanceKm = RequestBody.create(MediaType.parse("text/plain"), String.valueOf(distance));
        RequestBody durationSeconds = RequestBody.create(MediaType.parse("text/plain"), String.valueOf(duration));
        RequestBody averagePace = RequestBody.create(MediaType.parse("text/plain"), pace != null ? pace : "");
        RequestBody routePathBody = RequestBody.create(MediaType.parse("text/plain"), routePath != null ? routePath : "");
        RequestBody shareToFeed = RequestBody.create(MediaType.parse("text/plain"), switchShareFeed.isChecked() ? "1" : "0");
        MultipartBody.Part imagePart = selectedImageUri != null ? prepareImagePart(selectedImageUri) : null;

        // Show loading progress indicators
        pbAiLoading.setVisibility(View.VISIBLE);
        tvAiEvaluation.setText("Waking up Coach Flash for prescriptive evaluation...");
        btnSaveActivity.setEnabled(false);
        btnDiscardRun.setEnabled(false);
        btnClose.setEnabled(false);

        String token = "Bearer " + getSharedPreferences("UserPrefs", MODE_PRIVATE).getString("token", "");
        
        RetrofitClient.getService().saveRun(token, distanceKm, durationSeconds, averagePace, routePathBody, shareToFeed, imagePart).enqueue(new Callback<SaveRunResponse>() {
            @Override
            public void onResponse(Call<SaveRunResponse> call, Response<SaveRunResponse> response) {
                pbAiLoading.setVisibility(View.GONE);
                
                if (response.isSuccessful() && response.body() != null) {
                    SaveRunResponse res = response.body();
                    Toast.makeText(PostRunActivity.this, "✅ Run Saved Permanently!", Toast.LENGTH_LONG).show();

                    // Update rule-based classification UI
                    if (res.getTierUpdate() != null) {
                        String newLabel = res.getTierUpdate().getLabel();
                        tvCurrentTier.setText("[ Tier: " + newLabel + " Runner ]");
                        
                        // Save updated tier locally in UserPrefs for offline dashboard synchronization
                        getSharedPreferences("UserPrefs", MODE_PRIVATE).edit()
                                .putString("runner_tier", newLabel)
                                .apply();
                    }

                    if (res.getModifiersLog() != null) {
                        tvModifiersLog.setText(res.getModifiersLog());
                    }

                    // Animate Generative AI Coaching Insights with fade-in transition
                    if (res.getAiEvaluation() != null) {
                        tvAiEvaluation.setText(res.getAiEvaluation());
                        tvAiEvaluation.setAlpha(0f);
                        tvAiEvaluation.animate().alpha(1f).setDuration(800).start();
                    } else {
                        tvAiEvaluation.setText("Run synced successfully, but Coach Flash evaluation is temporarily unavailable.");
                    }

                    // Trigger server-side caches clear endpoint in background
                    clearServerCache(token);

                    // Re-route Primary Button actions to finish activity back to Dashboard
                    btnSaveActivity.setEnabled(true);
                    btnSaveActivity.setText("Back to Dashboard");
                    btnSaveActivity.setOnClickListener(v -> {
                        setResult(RESULT_OK);
                        finish();
                    });
                    
                    // Hide Discard button and enable Close
                    btnDiscardRun.setVisibility(View.GONE);
                    btnClose.setEnabled(true);
                    btnClose.setOnClickListener(v -> {
                        setResult(RESULT_OK);
                        finish();
                    });

                } else {
                    pbAiLoading.setVisibility(View.GONE);
                    btnSaveActivity.setEnabled(true);
                    btnDiscardRun.setEnabled(true);
                    btnClose.setEnabled(true);
                    Toast.makeText(PostRunActivity.this, "❌ Error saving run: " + response.message(), Toast.LENGTH_LONG).show();
                    tvAiEvaluation.setText("Unable to sync telemetry. Please try again.");
                }
            }

            @Override
            public void onFailure(Call<SaveRunResponse> call, Throwable t) {
                pbAiLoading.setVisibility(View.GONE);
                btnSaveActivity.setEnabled(true);
                btnDiscardRun.setEnabled(true);
                btnClose.setEnabled(true);
                Toast.makeText(PostRunActivity.this, "❌ Connection error: " + t.getMessage(), Toast.LENGTH_LONG).show();
                tvAiEvaluation.setText("Connection failed. Check your internet connection and try again.");
            }
        });
    }

    private MultipartBody.Part prepareImagePart(Uri uri) {
        try {
            File file = new File(getCacheDir(), "run_upload.jpg");
            InputStream inputStream = getContentResolver().openInputStream(uri);
            FileOutputStream outputStream = new FileOutputStream(file);
            byte[] buffer = new byte[4096];
            int read;
            while ((read = inputStream.read(buffer)) != -1) {
                outputStream.write(buffer, 0, read);
            }
            outputStream.close();
            inputStream.close();

            String type = getContentResolver().getType(uri);
            RequestBody requestFile = RequestBody.create(MediaType.parse(type != null ? type : "image/jpeg"), file);
            return MultipartBody.Part.createFormData("image", file.getName(), requestFile);
        } catch (Exception e) {
            return null;
        }
    }

    private void clearServerCache(String token) {
        RetrofitClient.getService().clearCoachCache(token).enqueue(new Callback<ResponseBody>() {
            @Override
            public void onResponse(Call<ResponseBody> call, Response<ResponseBody> response) {
                Log.d("POST_RUN_CACHE", "Laravel coach runtime caches cleared successfully.");
            }

            @Override
            public void onFailure(Call<ResponseBody> call, Throwable t) {
                Log.e("POST_RUN_CACHE", "Failed to clear Laravel backend coach cache: " + t.getMessage());
            }
        });
    }

    @Override
    protected void onResume() {
        super.onResume();
        mapView.onResume();
    }

    @Override
    protected void onPause() {
        super.onPause();
        mapView.onPause();
    }
}
