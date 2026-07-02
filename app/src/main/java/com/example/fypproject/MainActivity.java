package com.example.fypproject;

import android.widget.ImageButton;
import android.Manifest;
import android.annotation.SuppressLint;
import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.content.IntentFilter;
import android.content.SharedPreferences;
import android.content.pm.PackageManager;
import android.graphics.Bitmap;
import android.graphics.Color;
import android.location.Location;
import android.location.LocationManager;
import android.net.Uri;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.os.SystemClock;
import android.provider.MediaStore;
import android.text.Editable;
import android.text.InputType;
import android.text.TextWatcher;
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
import com.bumptech.glide.signature.ObjectKey;
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

import java.io.ByteArrayOutputStream;
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
    private ImageButton btnHomeMessages;

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
    private RecyclerView rvSearchResults, rvHomeFeed, rvJoinedGroups, rvAvailableGroups, rvRunHistory;
    private androidx.appcompat.widget.AppCompatButton btnLogout, btnDeleteAccount, btnStartRun;
    private FloatingActionButton btnCreateGroup, btnSearchGroup, btnFindLocation;
    private Button btnEditProfile, btnAiCoach, btnAssessRunner;
    private ImageView ivProfileImage;
    private TextView tvProfileTotalDistance, tvProfileTotalRuns, tvRunnerTier;
    private TextView tvDisplayName, tvDisplayUsername, tvDisplayAbout;
    private TextView tvViewWeight, tvViewHeight, tvViewPace;

    private UserSearchAdapter userSearchAdapter;
    private List<UserModel> searchResultsList = new ArrayList<>();

    // Social Feed Variables
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
    private GroupAdapter joinedGroupAdapter, availableGroupAdapter;
    private TextView tvJoinedGroupsHeader;

    // Custom bottom nav bar elements
    private LinearLayout btnNavHome, btnNavRun, btnNavGroup, btnNavProfile;
    private View btnSos; // Redefined from AppCompatButton to View to support CardView
    private ImageView ivNavHome, ivNavRun, ivNavGroup, ivNavProfile;
    private TextView tvNavHome, tvNavRun, tvNavGroup, tvNavProfile;

    // SOS screen views and state
    private ConstraintLayout layoutSos;
    private MapView mapSos;
    private IMapController mapControllerSos;
    private MyLocationNewOverlay locationOverlaySos;
    private FloatingActionButton btnSosFindLocation;
    private CardView cvSosInjury, cvSosMedical, cvSosSafety, cvSosAnimal;
    private Button btnSosTrigger;
    private TextView tvSosLocation;
    private String selectedEmergencyType = "General Alert 🚨";

    // Profile local edit mode views and state
    private LinearLayout llViewMode, llEditMode;
    private EditText etName, etUsername, etEmail, etPhone, etWeight, etHeight, etPace, etAbout;
    private boolean isEditMode = false;
    private Bitmap selectedProfileBitmap = null;
    private RunHistoryAdapter runHistoryAdapter;

    private TextView tvHomeGreeting, tvHomeTotalDistance, tvHomeTotalRuns, tvHomeRunnerTier;

    private final ActivityResultLauncher<String> pickIconLauncher = registerForActivityResult(
            new ActivityResultContracts.GetContent(), uri -> { if (uri != null) selectedIconUri = uri; });

    private final ActivityResultLauncher<String> pickBannerLauncher = registerForActivityResult(
            new ActivityResultContracts.GetContent(), uri -> { if (uri != null) selectedBannerUri = uri; });

    private final ActivityResultLauncher<String> pickProfilePhotoLauncher = registerForActivityResult(
            new ActivityResultContracts.GetContent(),
            uri -> {
                if (uri != null) {
                    try {
                        selectedProfileBitmap = android.provider.MediaStore.Images.Media.getBitmap(getContentResolver(), uri);
                        if (ivProfileImage != null) ivProfileImage.setImageBitmap(selectedProfileBitmap);
                    } catch (IOException e) {
                        e.printStackTrace();
                        Toast.makeText(this, "Failed to load image", Toast.LENGTH_SHORT).show();
                    }
                }
            }
    );

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
        btnHomeMessages = findViewById(R.id.btn_home_messages);
        btnHomeMessages.setOnClickListener(v -> {
            startActivity(new Intent(MainActivity.this, ConversationsActivity.class));
        });

        initViews();
        setupNavigation();
        setupRunLogic();
        setupGroupLogic();
        setupProfileLogic();
        setupSosLogic();
        setupSearchLogic();

        if (checkPermission()) setupLocationOverlay();
        else requestPermission();
    }

    private void initViews() {
        layoutHome = findViewById(R.id.layout_home);
        layoutRecord = findViewById(R.id.layout_record);
        layoutProfile = findViewById(R.id.layout_profile);
        layoutGroup = findViewById(R.id.layout_group);
        layoutSos = findViewById(R.id.layout_sos);

        // Bind custom bottom navigation bar elements
        btnNavHome = findViewById(R.id.btn_nav_home);
        btnNavRun = findViewById(R.id.btn_nav_run);
        btnNavGroup = findViewById(R.id.btn_nav_group);
        btnNavProfile = findViewById(R.id.btn_nav_profile);
        btnSos = findViewById(R.id.btn_sos);

        ivNavHome = findViewById(R.id.iv_nav_home);
        tvNavHome = findViewById(R.id.tv_nav_home);
        ivNavRun = findViewById(R.id.iv_nav_run);
        tvNavRun = findViewById(R.id.tv_nav_run);
        ivNavGroup = findViewById(R.id.iv_nav_group);
        tvNavGroup = findViewById(R.id.tv_nav_group);
        ivNavProfile = findViewById(R.id.iv_nav_profile);
        tvNavProfile = findViewById(R.id.tv_nav_profile);

        // Bind new home stats cards
        tvHomeGreeting = findViewById(R.id.tv_home_greeting);
        tvHomeTotalDistance = findViewById(R.id.tv_home_total_distance);
        tvHomeTotalRuns = findViewById(R.id.tv_home_total_runs);
        tvHomeRunnerTier = findViewById(R.id.tv_home_runner_tier);

        btnStartRun = findViewById(R.id.btnStartRun);
        btnFindLocation = findViewById(R.id.btn_find_location);

        etSearch = findViewById(R.id.et_search);
        rvSearchResults = findViewById(R.id.rv_search_results);
        if (rvSearchResults != null) {
            rvSearchResults.setLayoutManager(new LinearLayoutManager(this));
            userSearchAdapter = new UserSearchAdapter(searchResultsList, user -> {
                Intent intent = new Intent(MainActivity.this, UserProfileActivity.class);
                intent.putExtra("targetUserId", String.valueOf(user.getId()));
                startActivity(intent);
                etSearch.setText("");
                rvSearchResults.setVisibility(View.GONE);
            });
            rvSearchResults.setAdapter(userSearchAdapter);
        }

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
                Intent intent = new Intent(MainActivity.this, FeedActivity.class);
                startActivity(intent);
            });
        }

        if (tvViewMorePosts != null) {
            tvViewMorePosts.setOnClickListener(v -> {
                startActivity(new Intent(MainActivity.this, FeedActivity.class));
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

        rvJoinedGroups = findViewById(R.id.rv_joined_groups);
        rvAvailableGroups = findViewById(R.id.rv_available_groups);
        tvJoinedGroupsHeader = findViewById(R.id.tv_joined_groups_header);
        if (rvJoinedGroups != null) rvJoinedGroups.setLayoutManager(new LinearLayoutManager(this));
        if (rvAvailableGroups != null) rvAvailableGroups.setLayoutManager(new LinearLayoutManager(this));
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
        tvRunnerTier = findViewById(R.id.tv_runner_tier);

        // Bind profile edit components
        llViewMode = findViewById(R.id.ll_view_mode);
        llEditMode = findViewById(R.id.ll_edit_mode);

        etName = findViewById(R.id.et_name);
        etUsername = findViewById(R.id.et_username);
        etEmail = findViewById(R.id.et_email);
        etPhone = findViewById(R.id.et_phone);
        etWeight = findViewById(R.id.et_weight);
        etHeight = findViewById(R.id.et_height);
        etPace = findViewById(R.id.et_pace);
        etAbout = findViewById(R.id.et_about);

        btnEditProfile = findViewById(R.id.btn_edit_profile);
        btnAiCoach = findViewById(R.id.btn_ai_coach);
        btnLogout = findViewById(R.id.btn_logout);
        btnDeleteAccount = findViewById(R.id.btn_delete_account);

        tvProfileTotalDistance = findViewById(R.id.tv_profile_total_distance);
        tvProfileTotalRuns = findViewById(R.id.tv_profile_total_runs);
        rvRunHistory = findViewById(R.id.rv_run_history);
        if (rvRunHistory != null) rvRunHistory.setLayoutManager(new LinearLayoutManager(this));
    }

    private void setupSearchLogic() {
        etSearch.setFocusableInTouchMode(true);
        etSearch.setClickable(true);
        etSearch.setOnClickListener(v -> {
            if (etSearch.getText().toString().isEmpty()) {
                performUserSearch("");
            }
        });

        etSearch.addTextChangedListener(new TextWatcher() {
            @Override public void beforeTextChanged(CharSequence s, int start, int count, int after) {}
            @Override public void onTextChanged(CharSequence s, int start, int before, int count) {
                performUserSearch(s.toString());
            }
            @Override public void afterTextChanged(Editable s) {}
        });
    }

    private void performUserSearch(String query) {
        String token = "Bearer " + getToken();
        RetrofitClient.getService().searchUsers(token, query).enqueue(new Callback<List<UserModel>>() {
            @Override
            public void onResponse(Call<List<UserModel>> call, Response<List<UserModel>> response) {
                if (response.isSuccessful() && response.body() != null) {
                    searchResultsList = response.body();
                    userSearchAdapter.updateList(searchResultsList);
                    rvSearchResults.setVisibility(searchResultsList.isEmpty() ? View.GONE : View.VISIBLE);
                }
            }
            @Override public void onFailure(Call<List<UserModel>> call, Throwable t) {}
        });
    }

    private void showRunnerAssessmentDialog() {
        AlertDialog.Builder builder = new AlertDialog.Builder(this);
        builder.setTitle("Runner Profile Assessment");

        LinearLayout layout = new LinearLayout(this);
        layout.setOrientation(LinearLayout.VERTICAL);
        layout.setPadding(50, 40, 50, 10);

        final TextView tvDist = new TextView(this); tvDist.setText("Usual Running Distance:"); layout.addView(tvDist);
        final Spinner spinDist = new Spinner(this);
        String[] distOptions = {"Short (< 5km)", "Medium (5-15km)", "Long (> 15km)"};
        spinDist.setAdapter(new ArrayAdapter<>(this, android.R.layout.simple_spinner_dropdown_item, distOptions));
        layout.addView(spinDist);

        final TextView tvType = new TextView(this); tvType.setText("\nType of Run:"); layout.addView(tvType);
        final Spinner spinType = new Spinner(this);
        String[] typeOptions = {"Casual/Recovery", "Training/Tempo", "Competitive/Race"};
        spinType.setAdapter(new ArrayAdapter<>(this, android.R.layout.simple_spinner_dropdown_item, typeOptions));
        layout.addView(spinType);

        final TextView tvFreq = new TextView(this); tvFreq.setText("\nFrequency per week:"); layout.addView(tvFreq);
        final Spinner spinFreq = new Spinner(this);
        String[] freqOptions = {"1-2 times", "3-4 times", "5+ times"};
        spinFreq.setAdapter(new ArrayAdapter<>(this, android.R.layout.simple_spinner_dropdown_item, freqOptions));
        layout.addView(spinFreq);

        builder.setView(layout);
        builder.setPositiveButton("Calculate Level", (dialog, which) -> {
            int dScore = spinDist.getSelectedItemPosition() + 1;
            int tScore = spinType.getSelectedItemPosition() + 1;
            int fScore = spinFreq.getSelectedItemPosition() + 1;
            submitRunnerPreferences(dScore, tScore, fScore);
        });
        builder.setNegativeButton("Cancel", null);
        builder.show();
    }

    private void submitRunnerPreferences(int dist, int type, int freq) {
        String token = "Bearer " + getToken();
        PreferenceRequest request = new PreferenceRequest(dist, type, freq);
        RetrofitClient.getService().classifyRunner(token, request).enqueue(new Callback<CategoryResponse>() {
            @Override
            public void onResponse(Call<CategoryResponse> call, Response<CategoryResponse> response) {
                if (response.isSuccessful() && response.body() != null) {
                    String category = response.body().getCategory();
                    Toast.makeText(MainActivity.this, "Rule Engine: You are a " + category + " runner!", Toast.LENGTH_LONG).show();
                    fetchUserStatsAndHistory();
                }
            }
            @Override public void onFailure(Call<CategoryResponse> call, Throwable t) {}
        });
    }

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
                        homeFeedAdapter = new PostAdapter(previewPosts, userID, new PostAdapter.OnPostActionListener() {
                            @Override
                            public void onViewPost(PostModel post) {
                                Intent intent = new Intent(MainActivity.this, PostDetailActivity.class);
                                intent.putExtra("post_id", post.getId());
                                startActivity(intent);
                            }

                            @Override
                            public void onLikePost(PostModel post, int position) {
                                RetrofitClient.getService().togglePostLike("Bearer " + getToken(), post.getId()).enqueue(new Callback<PostInteractionResponse>() {
                                    @Override
                                    public void onResponse(Call<PostInteractionResponse> call, Response<PostInteractionResponse> response) {
                                        if (response.isSuccessful() && response.body() != null) {
                                            post.setLikedByMe(response.body().isLiked());
                                            post.setLikesCount(response.body().getCount());
                                            homeFeedAdapter.notifyItemChanged(position);
                                        }
                                    }

                                    @Override
                                    public void onFailure(Call<PostInteractionResponse> call, Throwable t) {
                                        Toast.makeText(MainActivity.this, "Could not update like", Toast.LENGTH_SHORT).show();
                                    }
                                });
                            }

                            @Override
                            public void onCommentPost(PostModel post) {
                                onViewPost(post);
                            }

                            @Override
                            public void onDeletePost(PostModel post, int position) {
                                Toast.makeText(MainActivity.this, "Open full feed to delete posts", Toast.LENGTH_SHORT).show();
                            }
                        });
                        if (rvHomeFeed != null) rvHomeFeed.setAdapter(homeFeedAdapter);
                    }
                }
            }
            @Override public void onFailure(Call<List<PostModel>> call, Throwable t) {}
        });
    }

    private void setupSosLogic() {
        layoutSos = findViewById(R.id.layout_sos);
        mapSos = findViewById(R.id.map_sos);
        tvSosLocation = findViewById(R.id.tv_sos_location);
        btnSosFindLocation = findViewById(R.id.btn_sos_find_location);

        if (mapSos != null) {
            mapSos.setTileSource(TileSourceFactory.MAPNIK);
            mapControllerSos = mapSos.getController();
            mapControllerSos.setZoom(18.5);
            mapSos.setMultiTouchControls(true);

            if (checkPermission()) {
                locationOverlaySos = new MyLocationNewOverlay(new GpsMyLocationProvider(this), mapSos);
                locationOverlaySos.enableMyLocation();
                locationOverlaySos.enableFollowLocation();
                mapSos.getOverlays().add(locationOverlaySos);
            }
        }

        if (btnSosFindLocation != null) {
            btnSosFindLocation.setOnClickListener(v -> {
                GeoPoint loc = null;
                if (currentLastLocation != null) loc = currentLastLocation;
                else if (locationOverlaySos != null && locationOverlaySos.getMyLocation() != null) loc = locationOverlaySos.getMyLocation();
                if (loc != null && mapControllerSos != null) {
                    mapControllerSos.animateTo(loc);
                    mapControllerSos.setZoom(18.5);
                }
            });
        }

        // Emergency Type selector cards
        cvSosInjury = findViewById(R.id.cv_sos_injury);
        cvSosMedical = findViewById(R.id.cv_sos_medical);
        cvSosSafety = findViewById(R.id.cv_sos_safety);
        cvSosAnimal = findViewById(R.id.cv_sos_animal);
        btnSosTrigger = findViewById(R.id.btn_sos_trigger);

        View.OnClickListener emergencyTypeSelector = v -> {
            int defaultBg = Color.parseColor("#2A1A4E");
            int selectedBg = Color.parseColor("#E11D48"); // Glowing red

            if (cvSosInjury != null) cvSosInjury.setCardBackgroundColor(defaultBg);
            if (cvSosMedical != null) cvSosMedical.setCardBackgroundColor(defaultBg);
            if (cvSosSafety != null) cvSosSafety.setCardBackgroundColor(defaultBg);
            if (cvSosAnimal != null) cvSosAnimal.setCardBackgroundColor(defaultBg);

            int id = v.getId();
            if (id == R.id.cv_sos_injury) {
                selectedEmergencyType = "Injury 🤕";
                if (cvSosInjury != null) cvSosInjury.setCardBackgroundColor(selectedBg);
            } else if (id == R.id.cv_sos_medical) {
                selectedEmergencyType = "Medical 🫀";
                if (cvSosMedical != null) cvSosMedical.setCardBackgroundColor(selectedBg);
            } else if (id == R.id.cv_sos_safety) {
                selectedEmergencyType = "Safety Threat 🛡️";
                if (cvSosSafety != null) cvSosSafety.setCardBackgroundColor(selectedBg);
            } else if (id == R.id.cv_sos_animal) {
                selectedEmergencyType = "Animal Threat 🐕";
                if (cvSosAnimal != null) cvSosAnimal.setCardBackgroundColor(selectedBg);
            }
        };

        if (cvSosInjury != null) cvSosInjury.setOnClickListener(emergencyTypeSelector);
        if (cvSosMedical != null) cvSosMedical.setOnClickListener(emergencyTypeSelector);
        if (cvSosSafety != null) cvSosSafety.setOnClickListener(emergencyTypeSelector);
        if (cvSosAnimal != null) cvSosAnimal.setOnClickListener(emergencyTypeSelector);

        // Perform click on first item by default
        if (cvSosInjury != null) cvSosInjury.performClick();

        if (btnSosTrigger != null) {
            btnSosTrigger.setOnClickListener(v -> {
                if (checkPermission()) {
                    dispatchSos(selectedEmergencyType);
                } else {
                    requestPermission();
                }
            });
        }

        // Keep compatibility with old floating SOS button click logic to switch to SOS tab
        if (btnSos != null) {
            btnSos.setOnClickListener(v -> selectTab(R.id.btn_sos));
        }
    }

    private void dispatchSos(String emergencyType) {
        GeoPoint finalLocation = null;
        if (currentLastLocation != null) finalLocation = currentLastLocation;
        else if (locationOverlay != null && locationOverlay.getMyLocation() != null) finalLocation = locationOverlay.getMyLocation();
        else if (locationOverlaySos != null && locationOverlaySos.getMyLocation() != null) finalLocation = locationOverlaySos.getMyLocation();
        if (finalLocation == null) return;

        String token = "Bearer " + getToken();
        String phone = getSharedPreferences("UserPrefs", MODE_PRIVATE).getString("userPhone", "+60123456789");
        SosRequest request = new SosRequest();
        request.setUserName(tvDisplayName != null ? tvDisplayName.getText().toString() : "Runner");
        request.setPhoneNumber(phone);
        request.setMessage(emergencyType);
        request.setLocationName("GPS Live Location");
        request.setUserIdentifier(userID);
        request.setLatitude(finalLocation.getLatitude());
        request.setLongitude(finalLocation.getLongitude());

        RetrofitClient.getService().sendSosSignal(token, request).enqueue(new Callback<SosResponse>() {
            @Override
            public void onResponse(Call<SosResponse> call, Response<SosResponse> response) {
                if (response.isSuccessful()) {
                    Toast.makeText(MainActivity.this, "✅ HELP IS ON THE WAY!", Toast.LENGTH_LONG).show();
                }
            }
            @Override public void onFailure(Call<SosResponse> call, Throwable t) {}
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

    private void setSectionVisible(View view, boolean visible) {
        if (view == null) return;
        if (visible) {
            if (view.getVisibility() != View.VISIBLE) {
                view.setVisibility(View.VISIBLE);
                view.setAlpha(0f);
                view.setTranslationY(30f); // subtle slide up
                view.animate()
                    .alpha(1f)
                    .translationY(0f)
                    .setDuration(250)
                    .start();
            }
        } else {
            view.setVisibility(View.GONE);
            view.animate().cancel();
        }
    }

    private void selectTab(int selectedId) {
        setSectionVisible(layoutHome, selectedId == R.id.btn_nav_home);
        setSectionVisible(layoutRecord, selectedId == R.id.btn_nav_run);
        setSectionVisible(layoutSos, selectedId == R.id.btn_sos);
        setSectionVisible(layoutGroup, selectedId == R.id.btn_nav_group);
        setSectionVisible(layoutProfile, selectedId == R.id.btn_nav_profile);

        if (rvSearchResults != null) rvSearchResults.setVisibility(View.GONE);

        int highlight = Color.parseColor("#A78BFA");
        int dim = Color.parseColor("#7C7289");

        if (ivNavHome != null) ivNavHome.setColorFilter(selectedId == R.id.btn_nav_home ? highlight : dim);
        if (tvNavHome != null) tvNavHome.setTextColor(selectedId == R.id.btn_nav_home ? highlight : dim);

        if (ivNavRun != null) ivNavRun.setColorFilter(selectedId == R.id.btn_nav_run ? highlight : dim);
        if (tvNavRun != null) tvNavRun.setTextColor(selectedId == R.id.btn_nav_run ? highlight : dim);

        if (ivNavGroup != null) ivNavGroup.setColorFilter(selectedId == R.id.btn_nav_group ? highlight : dim);
        if (tvNavGroup != null) tvNavGroup.setTextColor(selectedId == R.id.btn_nav_group ? highlight : dim);

        if (ivNavProfile != null) ivNavProfile.setColorFilter(selectedId == R.id.btn_nav_profile ? highlight : dim);
        if (tvNavProfile != null) tvNavProfile.setTextColor(selectedId == R.id.btn_nav_profile ? highlight : dim);

        if (selectedId == R.id.btn_nav_group) {
            loadGroups();
        } else if (selectedId == R.id.btn_nav_profile) {
            fetchUserStatsAndHistory();
        }
    }

    private void setupNavigation() {
        if (btnNavHome != null) btnNavHome.setOnClickListener(v -> selectTab(R.id.btn_nav_home));
        if (btnNavRun != null) btnNavRun.setOnClickListener(v -> selectTab(R.id.btn_nav_run));
        if (btnNavGroup != null) btnNavGroup.setOnClickListener(v -> selectTab(R.id.btn_nav_group));
        if (btnNavProfile != null) btnNavProfile.setOnClickListener(v -> selectTab(R.id.btn_nav_profile));
        if (btnSos != null) btnSos.setOnClickListener(v -> selectTab(R.id.btn_sos));

        // Default tab selection
        selectTab(R.id.btn_nav_home);
    }

    private void setupProfileLogic() {
        if (btnEditProfile != null) {
            btnEditProfile.setOnClickListener(v -> {
                if (isEditMode) {
                    updateProfile();
                } else {
                    toggleEditMode(true);
                }
            });
        }

        if (ivProfileImage != null) {
            ivProfileImage.setOnClickListener(v -> {
                if (isEditMode) {
                    pickProfilePhotoLauncher.launch("image/*");
                }
            });
        }

        if (btnAiCoach != null) btnAiCoach.setOnClickListener(v -> startActivity(new Intent(this, AiChatActivity.class)));
        if (btnAssessRunner != null) btnAssessRunner.setOnClickListener(v -> showRunnerAssessmentDialog());

        // Bind the action pill on homepage that opens Coach
        View btnHomeAiCoach = findViewById(R.id.btn_home_ai_coach);
        if (btnHomeAiCoach != null) btnHomeAiCoach.setOnClickListener(v -> startActivity(new Intent(this, AiChatActivity.class)));

        if (btnLogout != null) btnLogout.setOnClickListener(v -> {
            getSharedPreferences("UserPrefs", MODE_PRIVATE).edit().clear().apply();
            Intent intent = new Intent(this, LoginActivity.class);
            intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
            startActivity(intent);
            finish();
        });

        if (btnDeleteAccount != null) {
            btnDeleteAccount.setOnClickListener(v -> {
                new AlertDialog.Builder(this)
                        .setTitle("Delete Account")
                        .setMessage("Are you sure you want to permanently delete your account? This action cannot be undone.")
                        .setPositiveButton("Delete", (dialog, which) -> deleteAccount())
                        .setNegativeButton("Cancel", null)
                        .show();
            });
        }
    }

    private void toggleEditMode(boolean enable) {
        isEditMode = enable;
        if (llViewMode != null) llViewMode.setVisibility(enable ? View.GONE : View.VISIBLE);
        if (llEditMode != null) llEditMode.setVisibility(enable ? View.VISIBLE : View.GONE);
        if (btnEditProfile != null) btnEditProfile.setText(enable ? "Save Changes" : "Edit Profile");

        TextView tvChangePhoto = findViewById(R.id.tv_change_photo);
        if (tvChangePhoto != null) tvChangePhoto.setVisibility(enable ? View.VISIBLE : View.GONE);
    }

    private void updateProfile() {
        String name = etName != null ? etName.getText().toString().trim() : "";
        String userStr = etUsername != null ? etUsername.getText().toString().trim() : "";
        String email = etEmail != null ? etEmail.getText().toString().trim() : "";
        String about = etAbout != null ? etAbout.getText().toString().trim() : "";
        String phone = etPhone != null ? etPhone.getText().toString().trim() : "";
        String weightVal = etWeight != null ? etWeight.getText().toString().trim() : "";
        String heightVal = etHeight != null ? etHeight.getText().toString().trim() : "";
        String paceVal = etPace != null ? etPace.getText().toString().trim() : "";

        if (name.isEmpty() || userStr.isEmpty()) {
            Toast.makeText(this, "Name and Username are required", Toast.LENGTH_SHORT).show();
            return;
        }

        if (btnEditProfile != null) btnEditProfile.setEnabled(false);
        String token = "Bearer " + getToken();
        Call<UserModel> call;

        String weightToSend = weightVal.isEmpty() ? "" : weightVal;
        String heightToSend = heightVal.isEmpty() ? "" : heightVal;
        String paceToSend = paceVal.isEmpty() ? "" : paceVal;

        if (selectedProfileBitmap != null) {
            File file = bitmapToFile(selectedProfileBitmap);
            if (file != null) {
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
        } else {
            call = RetrofitClient.getService().updateProfile(
                    token, name, userStr, email, about, phone, weightToSend, heightToSend, paceToSend
            );
        }

        call.enqueue(new Callback<UserModel>() {
            @Override
            public void onResponse(Call<UserModel> call, Response<UserModel> response) {
                if (btnEditProfile != null) btnEditProfile.setEnabled(true);
                if (response.isSuccessful() && response.body() != null) {
                    Toast.makeText(MainActivity.this, "Profile Synced! 🍫", Toast.LENGTH_SHORT).show();
                    selectedProfileBitmap = null;
                    populateUserData(response.body());
                    toggleEditMode(false);
                } else {
                    Toast.makeText(MainActivity.this, "Sync Failed", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(Call<UserModel> call, Throwable t) {
                if (btnEditProfile != null) btnEditProfile.setEnabled(true);
                Toast.makeText(MainActivity.this, "Network Error", Toast.LENGTH_SHORT).show();
            }
        });
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

    private void deleteAccount() {
        String token = "Bearer " + getToken();
        RetrofitClient.getService().deleteAccount(token).enqueue(new Callback<Void>() {
            @Override
            public void onResponse(Call<Void> call, Response<Void> response) {
                if (response.isSuccessful()) {
                    Toast.makeText(MainActivity.this, "Account Deleted", Toast.LENGTH_SHORT).show();
                    getSharedPreferences("UserPrefs", MODE_PRIVATE).edit().clear().apply();
                    Intent intent = new Intent(MainActivity.this, LoginActivity.class);
                    intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
                    startActivity(intent);
                    finish();
                } else {
                    Toast.makeText(MainActivity.this, "Failed to delete account", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(Call<Void> call, Throwable t) {
                Toast.makeText(MainActivity.this, "Network Error", Toast.LENGTH_SHORT).show();
            }
        });
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
        TextView tvDistanceVal = dialogView.findViewById(R.id.tv_dialog_distance);
        TextView tvTimeVal = dialogView.findViewById(R.id.tv_dialog_time);
        TextView tvPaceVal = dialogView.findViewById(R.id.tv_dialog_pace);
        Button btnClose = dialogView.findViewById(R.id.btn_dialog_close);
        MapView dialogMap = dialogView.findViewById(R.id.dialog_map);

        View layoutAiEvaluation = dialogView.findViewById(R.id.layout_dialog_ai_evaluation);
        TextView tvAiEvaluation = dialogView.findViewById(R.id.tv_dialog_ai_evaluation);

        if (tvTitle != null) tvTitle.setText("Run Path Details");
        if (tvDate != null) tvDate.setText(run.getDate());
        if (tvDistanceVal != null) tvDistanceVal.setText(String.format(Locale.getDefault(), "%.2f km", run.getDistanceKm()));
        if (tvTimeVal != null) tvTimeVal.setText(run.getTimeDuration());
        if (tvPaceVal != null) tvPaceVal.setText(run.getPace() + " /km");

        String aiEval = run.getAiEvaluation();
        if (aiEval != null && !aiEval.trim().isEmpty()) {
            if (tvAiEvaluation != null) tvAiEvaluation.setText(aiEval);
            if (layoutAiEvaluation != null) layoutAiEvaluation.setVisibility(View.VISIBLE);
        } else {
            if (layoutAiEvaluation != null) layoutAiEvaluation.setVisibility(View.GONE);
        }

        if (dialogMap != null) {
            dialogMap.setTileSource(org.osmdroid.tileprovider.tilesource.TileSourceFactory.MAPNIK);
            org.osmdroid.api.IMapController dialogMapController = dialogMap.getController();
            dialogMapController.setZoom(17.5);
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
                        dialogMapController.setCenter(pts.get(0));
                    }
                } catch (Exception e) {
                    Log.e("RUN_MAP_DIALOG", "Error rendering run path on map", e);
                }
            }
            dialogMap.invalidate();
        }

        androidx.appcompat.app.AlertDialog dialog = builder.create();
        dialog.show();

        if (btnClose != null) btnClose.setOnClickListener(v -> dialog.dismiss());
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
        if (tvViewPace != null) tvViewPace.setText(user.getBasePace());
        if (tvProfileTotalDistance != null) tvProfileTotalDistance.setText(String.format(Locale.getDefault(), "%.2f km", user.getTotalDistance()));
        if (tvProfileTotalRuns != null) tvProfileTotalRuns.setText(String.valueOf(user.getRunsCount()));

        if (tvRunnerTier != null) {
            String tier = user.getRunnerTier();
            tvRunnerTier.setText("Runner Level: " + (tier != null ? tier : "Not Assessed"));
        }

        // Fill dynamic greeting on home
        int hour = java.util.Calendar.getInstance().get(java.util.Calendar.HOUR_OF_DAY);
        String greetingPrefix = "Good morning";
        if (hour >= 12 && hour < 17) greetingPrefix = "Good afternoon";
        else if (hour >= 17) greetingPrefix = "Good evening";

        if (tvHomeGreeting != null) tvHomeGreeting.setText(greetingPrefix + ", " + user.getName() + "!");
        if (tvHomeTotalDistance != null) tvHomeTotalDistance.setText(String.format(Locale.getDefault(), "%.2f km", user.getTotalDistance()));
        if (tvHomeTotalRuns != null) tvHomeTotalRuns.setText(String.valueOf(user.getRunsCount()));
        if (tvHomeRunnerTier != null) {
            String tier = user.getRunnerTier();
            tvHomeRunnerTier.setText(tier != null ? tier : "Beginner");
        }

        // Fill Edit Mode fields
        if (etName != null) etName.setText(user.getName());
        if (etUsername != null) etUsername.setText(user.getUsername());
        if (etEmail != null) etEmail.setText(user.getEmail());
        if (etAbout != null) etAbout.setText(user.getAboutMe());
        if (etWeight != null) etWeight.setText(String.valueOf(user.getWeightKg()));
        if (etHeight != null) etHeight.setText(String.valueOf(user.getHeightCm()));
        if (etPace != null) etPace.setText(user.getBasePace());
        if (etPhone != null) etPhone.setText(user.getPhone());

        // Cache emergency phone number locally for SOS dispatch
        if (user.getPhone() != null && !user.getPhone().isEmpty()) {
            getSharedPreferences("UserPrefs", MODE_PRIVATE)
                    .edit()
                    .putString("userPhone", user.getPhone())
                    .apply();
        }
    }

    private void fetchUserStatsAndHistory() {
        RetrofitClient.getService().getUserProfile("Bearer " + getToken()).enqueue(new Callback<UserModel>() {
            @Override public void onResponse(Call<UserModel> call, Response<UserModel> response) {
                if (response.isSuccessful() && response.body() != null) populateUserData(response.body());
            }
            @Override public void onFailure(Call<UserModel> call, Throwable t) {}
        });
        fetchRunHistory();
    }

    private void loadGroups() {
        RetrofitClient.getService().getGroups("Bearer " + getToken()).enqueue(new Callback<List<GroupModel>>() {
            @Override public void onResponse(Call<List<GroupModel>> call, Response<List<GroupModel>> response) {
                if (response.isSuccessful() && response.body() != null) {
                    allGroupsList = response.body();
                    
                    List<GroupModel> joinedGroups = new ArrayList<>();
                    List<GroupModel> availableGroups = new ArrayList<>();
                    for (GroupModel g : allGroupsList) {
                        if (g.isMember()) joinedGroups.add(g);
                        else availableGroups.add(g);
                    }

                    if (joinedGroups.isEmpty()) {
                        if (tvJoinedGroupsHeader != null) tvJoinedGroupsHeader.setVisibility(View.GONE);
                        if (rvJoinedGroups != null) rvJoinedGroups.setVisibility(View.GONE);
                    } else {
                        if (tvJoinedGroupsHeader != null) tvJoinedGroupsHeader.setVisibility(View.VISIBLE);
                        if (rvJoinedGroups != null) rvJoinedGroups.setVisibility(View.VISIBLE);
                    }

                    GroupAdapter.OnGroupInteractionListener listener = new GroupAdapter.OnGroupInteractionListener() {
                        @Override
                        public void onJoinClick(int groupId) {
                            joinGroup(groupId);
                        }

                        @Override
                        public void onLeaveClick(int groupId) {
                            leaveGroup(groupId);
                        }
                    };

                    if (rvJoinedGroups != null) {
                        if (joinedGroupAdapter == null) {
                            joinedGroupAdapter = new GroupAdapter(MainActivity.this, joinedGroups, userID, listener);
                            rvJoinedGroups.setAdapter(joinedGroupAdapter);
                        } else {
                            joinedGroupAdapter.updateList(joinedGroups);
                        }
                    }

                    if (rvAvailableGroups != null) {
                        if (availableGroupAdapter == null) {
                            availableGroupAdapter = new GroupAdapter(MainActivity.this, availableGroups, userID, listener);
                            rvAvailableGroups.setAdapter(availableGroupAdapter);
                        } else {
                            availableGroupAdapter.updateList(availableGroups);
                        }
                    }
                }
            }
            @Override public void onFailure(Call<List<GroupModel>> call, Throwable t) {}
        });
    }

    private void joinGroup(int groupId) {
        RetrofitClient.getService().joinGroup("Bearer " + getToken(), new JoinRequest(groupId)).enqueue(new Callback<JoinResponse>() {
            @Override
            public void onResponse(Call<JoinResponse> call, Response<JoinResponse> response) {
                if (response.isSuccessful()) {
                    Toast.makeText(MainActivity.this, "Joined group", Toast.LENGTH_SHORT).show();
                    loadGroups();
                } else {
                    Toast.makeText(MainActivity.this, "Could not join group: " + response.code(), Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(Call<JoinResponse> call, Throwable t) {
                Toast.makeText(MainActivity.this, "Network error joining group", Toast.LENGTH_SHORT).show();
            }
        });
    }

    private void leaveGroup(int groupId) {
        RetrofitClient.getService().leaveGroup("Bearer " + getToken(), new JoinRequest(groupId)).enqueue(new Callback<JoinResponse>() {
            @Override
            public void onResponse(Call<JoinResponse> call, Response<JoinResponse> response) {
                if (response.isSuccessful()) {
                    Toast.makeText(MainActivity.this, "Left group", Toast.LENGTH_SHORT).show();
                    loadGroups();
                } else {
                    Toast.makeText(MainActivity.this, "Could not leave group: " + response.code(), Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(Call<JoinResponse> call, Throwable t) {
                Toast.makeText(MainActivity.this, "Network error leaving group", Toast.LENGTH_SHORT).show();
            }
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
            }
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
        
        if (totalDistanceMeters > 0.05) {
            Intent intent = new Intent(this, PostRunActivity.class);
            intent.putExtra("distance", km);
            intent.putExtra("duration", dur);
            intent.putExtra("pace", p);
            
            // Serialize path points
            String routePathStr = "";
            if (pathPoints != null && !pathPoints.isEmpty()) {
                try {
                    org.json.JSONArray array = new org.json.JSONArray();
                    for (org.osmdroid.util.GeoPoint pt : pathPoints) {
                        org.json.JSONArray point = new org.json.JSONArray();
                        point.put(pt.getLatitude());
                        point.put(pt.getLongitude());
                        array.put(point);
                    }
                    routePathStr = array.toString();
                } catch (Exception e) {
                    Log.e("SERIALIZE_RUN", "Error serializing route path", e);
                }
            }
            intent.putExtra("route_path", routePathStr);
            startActivity(intent);
        } else {
            Toast.makeText(this, "Run distance too short to record.", Toast.LENGTH_SHORT).show();
        }
        
        resetRunStats();
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
                    } catch (Exception e) {}
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
        if (mapSos != null) mapSos.onResume();
        if (locationOverlaySos != null) locationOverlaySos.enableMyLocation();
        loadGroups();
        loadHomeFeed();
        fetchUserStatsAndHistory();
        registerReceiver(locationReceiver, new IntentFilter("location_update"), Context.RECEIVER_NOT_EXPORTED);
    }

    @Override public void onPause() {
        super.onPause();
        if (map != null) map.onPause();
        if (locationOverlay != null) locationOverlay.disableMyLocation();
        if (mapSos != null) mapSos.onPause();
        if (locationOverlaySos != null) locationOverlaySos.disableMyLocation();
        unregisterReceiver(locationReceiver);
    }
}
