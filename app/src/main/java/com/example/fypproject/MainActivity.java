package com.example.fypproject;

import android.Manifest;
import android.annotation.SuppressLint;
import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.content.IntentFilter;
import android.content.SharedPreferences;
import android.content.pm.PackageManager;
import android.graphics.Color;
import android.location.Location;
import android.location.LocationManager;
import android.net.Uri;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.os.SystemClock;
import android.text.InputType;
import android.util.Log;
import android.view.View;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.RelativeLayout;
import android.widget.ScrollView;
import android.widget.Spinner;
import android.widget.TextView;
import android.widget.Toast;

import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;
import androidx.cardview.widget.CardView;
import androidx.constraintlayout.widget.ConstraintLayout;
import androidx.core.app.ActivityCompat;
import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.bumptech.glide.Glide;
import com.bumptech.glide.load.engine.DiskCacheStrategy;
import com.google.android.material.bottomnavigation.BottomNavigationView;
import com.google.android.material.floatingactionbutton.FloatingActionButton;
import com.google.android.material.snackbar.Snackbar;

import org.json.JSONObject;
import org.osmdroid.api.IMapController;
import org.osmdroid.config.Configuration;
import org.osmdroid.tileprovider.tilesource.TileSourceFactory;
import org.osmdroid.util.GeoPoint;
import org.osmdroid.views.MapView;
import org.osmdroid.views.overlay.Polyline;
import org.osmdroid.views.overlay.mylocation.GpsMyLocationProvider;
import org.osmdroid.views.overlay.mylocation.MyLocationNewOverlay;

import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.util.ArrayList;
import java.util.List;
import java.util.Locale;

import okhttp3.MediaType;
import okhttp3.MultipartBody;
import okhttp3.OkHttpClient;
import okhttp3.Request;
import okhttp3.RequestBody;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class MainActivity extends AppCompatActivity {

    private String userID;
    private Uri selectedIconUri = null;
    private Uri selectedBannerUri = null;

    // --- AI Proactive Logic ---
    private long lastSupportTriggerTime = 0;
    private static final long SUPPORT_COOLDOWN = 300000;

    // --- UI Variables ---
    private ScrollView layoutHome, layoutProfile;
    private RelativeLayout layoutRecord;
    private ConstraintLayout layoutGroup;
    private BottomNavigationView bottomNav;
    private EditText etSearch, etGroupSearch;
    private RecyclerView rvSearchResults, rvHomeFeed, rvGroups, rvRunHistory;
    private androidx.appcompat.widget.AppCompatButton btnSos, btnLogout, btnDeleteAccount, btnStartRun;
    private FloatingActionButton btnCreateGroup, btnSearchGroup, btnFindLocation;
    private Button btnEditProfile, btnAiCoach, btnAssessRunner; // 🆕 Added btnAssessRunner
    private ImageView ivProfileImage;
    private TextView tvProfileTotalDistance, tvProfileTotalRuns, tvRunnerTier; // 🆕 Added tvRunnerTier
    private TextView tvDisplayName, tvDisplayUsername, tvDisplayAbout;
    private TextView tvViewWeight, tvViewHeight, tvViewPace;

    // 🆕 Social Feed Variables
    private CardView btnOpenEvents, cvCreatePostTrigger;
    private TextView tvViewMorePosts;
    private PostAdapter homeFeedAdapter;

    // --- Map & Run Logic ---
    private MapView map;
    private IMapController mapController;
    private MyLocationNewOverlay locationOverlay;
    private TextView tvTimer, tvDistance, tvPace;
    private boolean isRunning = false;
    private boolean isPaused = false;
    private Polyline runPath;
    private ArrayList<GeoPoint> pathPoints;
    private double totalDistanceMeters = 0;
    private long startTime = 0L, timeBuff = 0L, updateTime = 0L;
    private Handler timerHandler = new Handler();
    private GeoPoint currentLastLocation = null;

    private List<GroupModel> allGroupsList = new ArrayList<>();
    private GroupAdapter groupAdapter;

    private final ActivityResultLauncher<String> pickIconLauncher = registerForActivityResult(
            new ActivityResultContracts.GetContent(), uri -> { if (uri != null) selectedIconUri = uri; });

    private final ActivityResultLauncher<String> pickBannerLauncher = registerForActivityResult(
            new ActivityResultContracts.GetContent(), uri -> { if (uri != null) selectedBannerUri = uri; });

    private Runnable timerRunnable = new Runnable() {
        @Override
        public void run() {
            long millis = SystemClock.uptimeMillis() - startTime;
            updateTime = timeBuff + millis;
            int secs = (int) (updateTime / 1000);
            int mins = secs / 60;
            secs %= 60; mins %= 60;
            if (tvTimer != null) tvTimer.setText(String.format(Locale.getDefault(), "%02d:%02d", mins, secs));
            timerHandler.postDelayed(this, 500);
        }
    };

    private final BroadcastReceiver locationReceiver = new BroadcastReceiver() {
        @Override
        public void onReceive(Context context, Intent intent) {
            if (intent != null && intent.getAction().equals("location_update")) {
                double lat = intent.getDoubleExtra("latitude", 0);
                double lon = intent.getDoubleExtra("longitude", 0);
                currentLastLocation = new GeoPoint(lat, lon);

                if (isRunning && !isPaused) {
                    if (pathPoints != null && !pathPoints.isEmpty()) {
                        GeoPoint lastPoint = pathPoints.get(pathPoints.size() - 1);
                        totalDistanceMeters += lastPoint.distanceToAsDouble(currentLastLocation);
                        double km = totalDistanceMeters / 1000.0;
                        if (tvDistance != null) tvDistance.setText(String.format(Locale.getDefault(), "%.2f", km));

                        if (km > 0.05) {
                            double paceValue = (updateTime / 60000.0) / km;
                            int paceMins = (int) paceValue;
                            int paceSecs = (int) ((paceValue - paceMins) * 60);
                            if (tvPace != null) tvPace.setText(String.format(Locale.getDefault(), "%d:%02d", paceMins, paceSecs));
                        }
                    }
                    if (runPath == null) {
                        runPath = new Polyline();
                        runPath.setColor(Color.parseColor("#8D6E63"));
                        runPath.setWidth(12f);
                        map.getOverlays().add(runPath);
                        pathPoints = new ArrayList<>();
                    }
                    pathPoints.add(currentLastLocation);
                    runPath.setPoints(pathPoints);
                    checkPaceAndTriggerSupport();
                }
                map.invalidate();
            }
        }
    };

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        userID = getSharedPreferences("UserPrefs", MODE_PRIVATE).getString("userId", "0");
        Configuration.getInstance().load(getApplicationContext(), getPreferences(MODE_PRIVATE));
        setContentView(R.layout.activity_main);

        initViews();
        setupNavigation();
        setupRunLogic();
        setupGroupLogic();
        setupProfileLogic();
        setupSosLogic();

        if (checkPermission()) setupLocationOverlay();
        else requestPermission();
    }

    private void initViews() {
        layoutHome = findViewById(R.id.layout_home);
        layoutRecord = findViewById(R.id.layout_record);
        layoutProfile = findViewById(R.id.layout_profile);
        layoutGroup = findViewById(R.id.layout_group);
        bottomNav = findViewById(R.id.bottom_navigation);

        btnSos = findViewById(R.id.btn_sos);
        btnStartRun = findViewById(R.id.btnStartRun);
        btnFindLocation = findViewById(R.id.btn_find_location);

        // 🆕 Home Screen Social & Events
        btnOpenEvents = findViewById(R.id.btn_open_events);
        cvCreatePostTrigger = findViewById(R.id.cv_create_post_trigger);
        tvViewMorePosts = findViewById(R.id.tv_view_more_posts);
        rvHomeFeed = findViewById(R.id.rv_home_feed);
        if (rvHomeFeed != null) rvHomeFeed.setLayoutManager(new LinearLayoutManager(this));

        if (btnOpenEvents != null) {
            btnOpenEvents.setOnClickListener(v -> startActivity(new Intent(MainActivity.this, EventsActivity.class)));
        }

        if (cvCreatePostTrigger != null) {
            cvCreatePostTrigger.setOnClickListener(v -> {
                Intent intent = new Intent(MainActivity.this, CreatePostActivity.class);
                startActivity(intent);
            });
        }

        if (tvViewMorePosts != null) {
            tvViewMorePosts.setOnClickListener(v -> {
                Toast.makeText(this, "Loading Full Feed...", Toast.LENGTH_SHORT).show();
            });
        }

        map = findViewById(R.id.map);
        if (map != null) {
            map.setTileSource(TileSourceFactory.MAPNIK);
            mapController = map.getController();
            mapController.setZoom(18.5);
            map.setMultiTouchControls(true);
        }

        tvTimer = findViewById(R.id.tvTimer);
        tvDistance = findViewById(R.id.tvDistance);
        tvPace = findViewById(R.id.tvPace);

        rvGroups = findViewById(R.id.rv_groups);
        if (rvGroups != null) rvGroups.setLayoutManager(new LinearLayoutManager(this));
        btnCreateGroup = findViewById(R.id.btn_create_group);
        btnSearchGroup = findViewById(R.id.btn_search_group);
        etGroupSearch = findViewById(R.id.et_group_search);

        ivProfileImage = findViewById(R.id.iv_profile_image);
        tvDisplayName = findViewById(R.id.tv_display_name);
        tvDisplayUsername = findViewById(R.id.tv_display_username);
        tvDisplayAbout = findViewById(R.id.tv_display_about);
        tvViewWeight = findViewById(R.id.tv_view_weight);
        tvViewHeight = findViewById(R.id.tv_view_height);
        tvViewPace = findViewById(R.id.tv_view_pace);
        tvRunnerTier = findViewById(R.id.tv_runner_tier); // 🆕 Initialize Runner Tier display

        btnEditProfile = findViewById(R.id.btn_edit_profile);
        btnAiCoach = findViewById(R.id.btn_ai_coach);
        btnAssessRunner = findViewById(R.id.btn_assess_runner); // 🆕 Initialize Assessment Button

        btnLogout = findViewById(R.id.btn_logout);
        btnDeleteAccount = findViewById(R.id.btn_delete_account);

        tvProfileTotalDistance = findViewById(R.id.tv_profile_total_distance);
        tvProfileTotalRuns = findViewById(R.id.tv_profile_total_runs);
        rvRunHistory = findViewById(R.id.rv_run_history);
        if (rvRunHistory != null) rvRunHistory.setLayoutManager(new LinearLayoutManager(this));
    }

    // ===========================================
    // 🆕 RULE-BASED ENGINE LOGIC
    // ===========================================

    private void showRunnerAssessmentDialog() {
        AlertDialog.Builder builder = new AlertDialog.Builder(this);
        builder.setTitle("Runner Profile Assessment");

        LinearLayout layout = new LinearLayout(this);
        layout.setOrientation(LinearLayout.VERTICAL);
        layout.setPadding(50, 40, 50, 10);

        // 1. Distance Preference
        final TextView tvDist = new TextView(this); tvDist.setText("Usual Running Distance:"); layout.addView(tvDist);
        final Spinner spinDist = new Spinner(this);
        String[] distOptions = {"Short (< 5km)", "Medium (5-15km)", "Long (> 15km)"};
        spinDist.setAdapter(new ArrayAdapter<>(this, android.R.layout.simple_spinner_dropdown_item, distOptions));
        layout.addView(spinDist);

        // 2. Run Type
        final TextView tvType = new TextView(this); tvType.setText("\nType of Run:"); layout.addView(tvType);
        final Spinner spinType = new Spinner(this);
        String[] typeOptions = {"Casual/Recovery", "Training/Tempo", "Competitive/Race"};
        spinType.setAdapter(new ArrayAdapter<>(this, android.R.layout.simple_spinner_dropdown_item, typeOptions));
        layout.addView(spinType);

        // 3. Frequency
        final TextView tvFreq = new TextView(this); tvFreq.setText("\nFrequency per week:"); layout.addView(tvFreq);
        final Spinner spinFreq = new Spinner(this);
        String[] freqOptions = {"1-2 times", "3-4 times", "5+ times"};
        spinFreq.setAdapter(new ArrayAdapter<>(this, android.R.layout.simple_spinner_dropdown_item, freqOptions));
        layout.addView(spinFreq);

        builder.setView(layout);
        builder.setPositiveButton("Calculate Level", (dialog, which) -> {
            // Convert selection to points for the Rule Engine
            int dScore = spinDist.getSelectedItemPosition() + 1; // 1, 2, or 3
            int tScore = spinType.getSelectedItemPosition() + 1;
            int fScore = spinFreq.getSelectedItemPosition() + 1;

            submitRunnerPreferences(dScore, tScore, fScore);
        });
        builder.setNegativeButton("Cancel", null);
        builder.show();
    }

    private void submitRunnerPreferences(int dist, int type, int freq) {
        String token = "Bearer " + getToken();

        // This uses the model classes we discussed (PreferenceRequest)
        PreferenceRequest request = new PreferenceRequest(dist, type, freq);

        RetrofitClient.getService().classifyRunner(token, request).enqueue(new Callback<CategoryResponse>() {
            @Override
            public void onResponse(Call<CategoryResponse> call, Response<CategoryResponse> response) {
                if (response.isSuccessful() && response.body() != null) {
                    String category = response.body().getCategory();
                    Toast.makeText(MainActivity.this, "Rule Engine: You are a " + category + " runner!", Toast.LENGTH_LONG).show();
                    fetchUserStatsAndHistory(); // Refresh profile to show new tier
                }
            }

            @Override
            public void onFailure(Call<CategoryResponse> call, Throwable t) {
                Toast.makeText(MainActivity.this, "Engine Error: " + t.getMessage(), Toast.LENGTH_SHORT).show();
            }
        });
    }

    // ===========================================
    // EXISTING LOGIC (REMAINING FUNCTIONS)
    // ===========================================

    private void loadHomeFeed() {
        String token = "Bearer " + getToken();
        RetrofitClient.getService().getHomeFeed(token).enqueue(new Callback<List<PostModel>>() {
            @Override
            public void onResponse(Call<List<PostModel>> call, Response<List<PostModel>> response) {
                if (response.isSuccessful() && response.body() != null) {
                    List<PostModel> allPosts = response.body();
                    if (!allPosts.isEmpty()) {
                        List<PostModel> previewPosts = allPosts.size() > 3 ?
                                new ArrayList<>(allPosts.subList(0, 3)) : allPosts;

                        homeFeedAdapter = new PostAdapter(previewPosts);
                        if (rvHomeFeed != null) rvHomeFeed.setAdapter(homeFeedAdapter);
                    }
                }
            }
            @Override
            public void onFailure(Call<List<PostModel>> call, Throwable t) {
                Log.e("API_ERROR", "Home Feed Failed", t);
            }
        });
    }

    private void setupSosLogic() {
        if (btnSos != null) {
            btnSos.setOnClickListener(v -> {
                if (checkPermission()) showEmergencyTypeDialog();
                else requestPermission();
            });
        }
    }

    private void showEmergencyTypeDialog() {
        String[] types = {"Fall/Injury 🤕", "Medical/Heart 🫀", "Personal Safety 🛡️", "Stray Animal 🐕"};
        new AlertDialog.Builder(this)
                .setTitle("🚨 EMERGENCY ALERT")
                .setItems(types, (dialog, which) -> dispatchSos(types[which]))
                .setNegativeButton("Cancel", null)
                .show();
    }

    private void dispatchSos(String emergencyType) {
        GeoPoint finalLocation = null;
        if (currentLastLocation != null) finalLocation = currentLastLocation;
        else if (locationOverlay != null && locationOverlay.getMyLocation() != null) finalLocation = locationOverlay.getMyLocation();

        if (finalLocation == null) {
            Toast.makeText(this, "🛰️ GPS signal weak. Move outdoors!", Toast.LENGTH_LONG).show();
            return;
        }

        String token = "Bearer " + getToken();
        String phone = getSharedPreferences("UserPrefs", MODE_PRIVATE).getString("userPhone", "+60123456789");

        SosRequest request = new SosRequest();
        request.setUserName(tvDisplayName.getText().toString());
        request.setPhoneNumber(phone);
        request.setMessage(emergencyType);
        request.setLocationName("GPS Live Location");
        request.setUserIdentifier(userID);
        request.setLatitude(finalLocation.getLatitude());
        request.setLongitude(finalLocation.getLongitude());

        RetrofitClient.getService().sendSosSignal(token, request).enqueue(new Callback<SosResponse>() {
            @Override
            public void onResponse(Call<SosResponse> call, Response<SosResponse> response) {
                if (response.isSuccessful()) Toast.makeText(MainActivity.this, "✅ HELP IS ON THE WAY!", Toast.LENGTH_LONG).show();
            }
            @Override
            public void onFailure(Call<SosResponse> call, Throwable t) {
                Toast.makeText(MainActivity.this, "📡 Network Error", Toast.LENGTH_SHORT).show();
            }
        });
    }

    private void setupLocationOverlay() {
        if (map == null) return;
        locationOverlay = new MyLocationNewOverlay(new GpsMyLocationProvider(this), map);
        locationOverlay.enableMyLocation();
        locationOverlay.enableFollowLocation();
        locationOverlay.setDrawAccuracyEnabled(true);
        map.getOverlays().add(locationOverlay);
    }

    private void setupNavigation() {
        if (bottomNav == null) return;
        bottomNav.setOnItemSelectedListener(item -> {
            int id = item.getItemId();
            layoutHome.setVisibility(View.GONE);
            layoutRecord.setVisibility(View.GONE);
            layoutGroup.setVisibility(View.GONE);
            layoutProfile.setVisibility(View.GONE);

            if (id == R.id.nav_home) {
                layoutHome.setVisibility(View.VISIBLE);
                loadHomeFeed();
            }
            else if (id == R.id.nav_record) layoutRecord.setVisibility(View.VISIBLE);
            else if (id == R.id.nav_group) { layoutGroup.setVisibility(View.VISIBLE); loadGroups(); }
            else if (id == R.id.nav_you) {
                layoutProfile.setVisibility(View.VISIBLE);
                fetchUserStatsAndHistory();
            }
            return true;
        });
    }

    private void setupProfileLogic() {
        if (btnEditProfile != null) btnEditProfile.setOnClickListener(v -> startActivity(new Intent(this, ProfileActivity.class)));
        if (btnAiCoach != null) btnAiCoach.setOnClickListener(v -> startActivity(new Intent(this, AiChatActivity.class)));

        // 🆕 Set up the Assessment Trigger
        if (btnAssessRunner != null) {
            btnAssessRunner.setOnClickListener(v -> showRunnerAssessmentDialog());
        }

        if (btnLogout != null) btnLogout.setOnClickListener(v -> {
            getSharedPreferences("UserPrefs", MODE_PRIVATE).edit().clear().apply();
            Intent intent = new Intent(this, LoginActivity.class);
            intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
            startActivity(intent);
            finish();
        });
        if (btnDeleteAccount != null) btnDeleteAccount.setOnClickListener(v -> {
            new AlertDialog.Builder(this).setTitle("Delete Account").setMessage("Are you sure?")
                    .setPositiveButton("Delete", (dialog, which) -> deleteAccountFromServer()).setNegativeButton("Cancel", null).show();
        });
    }

    private void deleteAccountFromServer() {
        RetrofitClient.getService().deleteAccount("Bearer " + getToken()).enqueue(new Callback<Void>() {
            @Override public void onResponse(Call<Void> call, Response<Void> response) { if (response.isSuccessful()) btnLogout.performClick(); }
            @Override public void onFailure(Call<Void> call, Throwable t) {}
        });
    }

    private void populateUserData(UserModel user) {
        if (tvDisplayName != null) tvDisplayName.setText(user.getName());
        if (tvDisplayUsername != null) tvDisplayUsername.setText("@" + user.getUsername());
        if (tvDisplayAbout != null) tvDisplayAbout.setText(user.getAboutMe());
        if (ivProfileImage != null && user.getProfilePhotoPath() != null) {
            Glide.with(this).load(user.getProfilePhotoPath()).diskCacheStrategy(DiskCacheStrategy.ALL).circleCrop().into(ivProfileImage);
        }
        if (tvViewWeight != null) tvViewWeight.setText(String.format(Locale.getDefault(), "%.1f kg", user.getWeightKg()));
        if (tvViewHeight != null) tvViewHeight.setText(String.format(Locale.getDefault(), "%.0f cm", user.getHeightCm()));
        if (tvViewPace != null) tvViewPace.setText(String.format(Locale.getDefault(), "%s", user.getBasePace()));
        if (tvProfileTotalDistance != null) tvProfileTotalDistance.setText(String.format(Locale.getDefault(), "%.2f km", user.getTotalDistance()));
        if (tvProfileTotalRuns != null) tvProfileTotalRuns.setText(String.valueOf(user.getRunsCount()));

        // 🆕 Populate the Runner Tier from the database
        if (tvRunnerTier != null) {
            String tier = user.getRunnerTier(); // Assuming UserModel has getRunnerTier()
            if (tier == null || tier.isEmpty()) {
                tvRunnerTier.setText("Level: Not Assessed");
                // Option: Trigger assessment automatically if null
                // showRunnerAssessmentDialog();
            } else {
                tvRunnerTier.setText("Runner Level: " + tier);
                if (tier.equals("HARD")) tvRunnerTier.setTextColor(Color.RED);
                else if (tier.equals("MEDIUM")) tvRunnerTier.setTextColor(Color.BLUE);
                else tvRunnerTier.setTextColor(Color.GRAY);
            }
        }
    }

    private void fetchUserStatsAndHistory() {
        RetrofitClient.getService().getUserProfile("Bearer " + getToken()).enqueue(new Callback<UserModel>() {
            @Override public void onResponse(Call<UserModel> call, Response<UserModel> response) {
                if (response.isSuccessful() && response.body() != null) populateUserData(response.body());
            }
            @Override public void onFailure(Call<UserModel> call, Throwable t) {}
        });
    }

    private void loadGroups() {
        RetrofitClient.getService().getGroups("Bearer " + getToken()).enqueue(new Callback<List<GroupModel>>() {
            @Override public void onResponse(Call<List<GroupModel>> call, Response<List<GroupModel>> response) {
                if (response.isSuccessful() && response.body() != null) {
                    allGroupsList = response.body();
                    if (groupAdapter == null) {
                        groupAdapter = new GroupAdapter(MainActivity.this, allGroupsList, userID, id -> {});
                        rvGroups.setAdapter(groupAdapter);
                    } else groupAdapter.updateList(allGroupsList);
                }
            }
            @Override public void onFailure(Call<List<GroupModel>> call, Throwable t) {}
        });
    }

    private void setupGroupLogic() {
        if (btnSearchGroup != null) btnSearchGroup.setOnClickListener(v -> { if (etGroupSearch != null) etGroupSearch.setVisibility(etGroupSearch.getVisibility() == View.VISIBLE ? View.GONE : View.VISIBLE); });
        if (btnCreateGroup != null) btnCreateGroup.setOnClickListener(v -> showCreateGroupDialog());
    }

    private void showCreateGroupDialog() {
        AlertDialog.Builder builder = new AlertDialog.Builder(this);
        builder.setTitle("Create New Group");
        LinearLayout layout = new LinearLayout(this);
        layout.setOrientation(LinearLayout.VERTICAL);
        layout.setPadding(50, 40, 50, 10);
        final EditText inputName = new EditText(this); inputName.setHint("Group Name"); layout.addView(inputName);
        final EditText inputLocation = new EditText(this); inputLocation.setHint("Location"); layout.addView(inputLocation);
        final EditText inputDesc = new EditText(this); inputDesc.setHint("Rules"); layout.addView(inputDesc);
        final EditText inputKm = new EditText(this); inputKm.setHint("Goal KM"); inputKm.setInputType(InputType.TYPE_CLASS_NUMBER); layout.addView(inputKm);
        Button bIcon = new Button(this); bIcon.setText("Select Icon"); bIcon.setOnClickListener(v -> pickIconLauncher.launch("image/*")); layout.addView(bIcon);
        Button bBanner = new Button(this); bBanner.setText("Select Banner"); bBanner.setOnClickListener(v -> pickBannerLauncher.launch("image/*")); layout.addView(bBanner);
        builder.setView(layout);
        builder.setPositiveButton("Create", (dialog, which) -> {
            String n = inputName.getText().toString(); String k = inputKm.getText().toString();
            if (!n.isEmpty() && !k.isEmpty()) sendGroupToServer(n, inputLocation.getText().toString(), inputDesc.getText().toString(), k, selectedIconUri, selectedBannerUri);
        });
        builder.show();
    }

    private void sendGroupToServer(String n, String l, String d, String k, Uri icon, Uri banner) {
        String t = "Bearer " + getToken();
        RequestBody rbN = RequestBody.create(MediaType.parse("text/plain"), n);
        RequestBody rbL = RequestBody.create(MediaType.parse("text/plain"), l);
        RequestBody rbD = RequestBody.create(MediaType.parse("text/plain"), d);
        RequestBody rbK = RequestBody.create(MediaType.parse("text/plain"), k);
        RequestBody rbS = RequestBody.create(MediaType.parse("text/plain"), "active");
        RequestBody rbC = RequestBody.create(MediaType.parse("text/plain"), userID);
        MultipartBody.Part pI = (icon != null) ? prepareImagePart("icon", icon) : null;
        MultipartBody.Part pB = (banner != null) ? prepareImagePart("banner", banner) : null;
        RetrofitClient.getService().createGroup(t, rbN, rbL, rbD, rbK, rbS, rbC, pI, pB).enqueue(new Callback<GroupModel>() {
            @Override public void onResponse(Call<GroupModel> call, Response<GroupModel> response) { if (response.isSuccessful()) loadGroups(); }
            @Override public void onFailure(Call<GroupModel> call, Throwable t) {}
        });
    }

    private MultipartBody.Part prepareImagePart(String name, Uri uri) {
        try {
            File f = new File(getCacheDir(), "temp_" + name + ".jpg");
            InputStream is = getContentResolver().openInputStream(uri);
            FileOutputStream os = new FileOutputStream(f);
            byte[] buf = new byte[1024]; int r; while ((r = is.read(buf)) != -1) os.write(buf, 0, r);
            os.close(); is.close();
            return MultipartBody.Part.createFormData(name, f.getName(), RequestBody.create(MediaType.parse(getContentResolver().getType(uri)), f));
        } catch (Exception e) { return null; }
    }

    private String getToken() { return getSharedPreferences("UserPrefs", MODE_PRIVATE).getString("token", ""); }

    private void setupRunLogic() {
        if (btnStartRun != null) btnStartRun.setOnClickListener(v -> { if (checkPermission()) { if (!isRunning) startRun(); else showRunOptionsDialog(); } else requestPermission(); });
        if (btnFindLocation != null) btnFindLocation.setOnClickListener(v -> {
            if (currentLastLocation != null && mapController != null) {
                mapController.animateTo(currentLastLocation);
                mapController.setZoom(18.5);
            } else Toast.makeText(this, "Acquiring GPS...", Toast.LENGTH_SHORT).show();
        });
    }

    private void startRun() {
        isRunning = true; isPaused = false;
        if (btnStartRun != null) btnStartRun.setText("STOP");
        startTime = SystemClock.uptimeMillis();
        startService(new Intent(this, LocationService.class));
        timerHandler.postDelayed(timerRunnable, 0);
    }

    private void showRunOptionsDialog() {
        pauseRun();
        new AlertDialog.Builder(this).setTitle("Run Paused").setPositiveButton("Resume", (d, w) -> resumeRun()).setNegativeButton("Finish", (d, w) -> finishRun()).show();
    }

    private void pauseRun() { if (!isPaused) { isPaused = true; timeBuff += SystemClock.uptimeMillis() - startTime; timerHandler.removeCallbacks(timerRunnable); } }
    private void resumeRun() { isPaused = false; startTime = SystemClock.uptimeMillis(); timerHandler.postDelayed(timerRunnable, 0); }

    private void finishRun() {
        isRunning = false; isPaused = false;
        if (btnStartRun != null) btnStartRun.setText("▶");
        stopService(new Intent(this, LocationService.class));
        timerHandler.removeCallbacks(timerRunnable);

        double km = totalDistanceMeters / 1000.0;
        int dur = (int) (updateTime / 1000);
        String p = tvPace.getText().toString();
        if (totalDistanceMeters > 0.05) saveRunToServer(km, dur, p);
        resetRunStats();
    }

    private void saveRunToServer(double dist, int dur, String pace) {
        RunData data = new RunData();
        data.setDistanceKm(dist);
        data.setDurationSeconds(dur);
        data.setAveragePace(pace);
        data.setUserId(Integer.parseInt(userID));
        RetrofitClient.getService().saveRun("Bearer " + getToken(), data).enqueue(new Callback<RunData>() {
            @Override public void onResponse(Call<RunData> call, Response<RunData> response) {
                if (response.isSuccessful()) Toast.makeText(MainActivity.this, "✅ Run Saved!", Toast.LENGTH_SHORT).show();
            }
            @Override public void onFailure(Call<RunData> call, Throwable t) {}
        });
    }

    private void resetRunStats() {
        totalDistanceMeters = 0; timeBuff = 0; updateTime = 0;
        if (tvTimer != null) tvTimer.setText("00:00");
        if (tvDistance != null) tvDistance.setText("0.00");
        if (tvPace != null) tvPace.setText("--:--");
        if (runPath != null) { map.getOverlays().remove(runPath); runPath = null; }
        map.invalidate();
    }

    private void checkPaceAndTriggerSupport() {
        if (totalDistanceMeters < 100) return;
        double km = totalDistanceMeters / 1000.0;
        double currentPace = (updateTime / 60000.0) / km;
        double targetPace = 6.0;
        try { targetPace = Double.parseDouble(tvViewPace.getText().toString().replace(" min/km", "")); } catch (Exception e) { }
        long currentTime = System.currentTimeMillis();
        if (currentPace > (targetPace * 1.15) && (currentTime - lastSupportTriggerTime > SUPPORT_COOLDOWN)) {
            lastSupportTriggerTime = currentTime;
            triggerCoachShoutout();
        }
    }

    private void triggerCoachShoutout() {
        String token = "Bearer " + getToken();
        OkHttpClient client = new OkHttpClient();
        Request request = new Request.Builder().url("https://runtracker.fun/api/ai-support").addHeader("Authorization", token).post(RequestBody.create("", MediaType.parse("application/json"))).build();
        client.newCall(request).enqueue(new okhttp3.Callback() {
            @Override public void onFailure(okhttp3.Call call, IOException e) {}
            @Override public void onResponse(okhttp3.Call call, okhttp3.Response response) throws IOException {
                if (response.isSuccessful()) {
                    try {
                        JSONObject json = new JSONObject(response.body().string());
                        String coachWord = json.getString("reply");
                        new Handler(Looper.getMainLooper()).post(() -> {
                            Snackbar snack = Snackbar.make(findViewById(android.R.id.content), "⚡ Coach Flash: " + coachWord, Snackbar.LENGTH_LONG);
                            snack.setBackgroundTint(Color.parseColor("#D4AF37"));
                            snack.setTextColor(Color.BLACK);
                            snack.show();
                        });
                    } catch (Exception e) { e.printStackTrace(); }
                }
            }
        });
    }

    private boolean checkPermission() { return ContextCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION) == PackageManager.PERMISSION_GRANTED; }
    private void requestPermission() { ActivityCompat.requestPermissions(this, new String[]{Manifest.permission.ACCESS_FINE_LOCATION}, 100); }

    @Override public void onResume() {
        super.onResume();
        if (map != null) map.onResume();
        if (locationOverlay != null) locationOverlay.enableMyLocation();
        loadGroups();
        loadHomeFeed();
        fetchUserStatsAndHistory();
        registerReceiver(locationReceiver, new IntentFilter("location_update"), Context.RECEIVER_NOT_EXPORTED);
    }

    @Override public void onPause() {
        super.onPause();
        if (map != null) map.onPause();
        if (locationOverlay != null) locationOverlay.disableMyLocation();
        unregisterReceiver(locationReceiver);
    }
}