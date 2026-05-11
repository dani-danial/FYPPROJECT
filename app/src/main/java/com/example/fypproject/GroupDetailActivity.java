package com.example.fypproject;

import android.app.Activity;
import android.content.Intent;
import android.content.SharedPreferences;
import android.graphics.Bitmap;
import android.net.Uri;
import android.os.Bundle;
import android.provider.MediaStore;
import android.text.InputType;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.TextView;
import android.widget.Toast;

import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;
import androidx.cardview.widget.CardView;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.bumptech.glide.Glide;
import com.bumptech.glide.load.engine.DiskCacheStrategy;

import java.io.File;
import java.io.FileOutputStream;
import java.io.InputStream;
import java.util.List;

import okhttp3.MediaType;
import okhttp3.MultipartBody;
import okhttp3.RequestBody;
import okhttp3.ResponseBody;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class GroupDetailActivity extends AppCompatActivity {

    private int groupId;
    private int currentUserId;
    private GroupModel currentGroup;

    // Views
    private ImageView ivHeader, ivIcon, btnBack, btnEditBanner, btnEditTarget, btnEditIcon;
    private TextView tvName, tvType, tvDesc, tvProgress;
    private ProgressBar pbTarget;
    private View btnManageMembers;
    private RecyclerView rvFeed;
    private Button btnDeleteGroup; // 🛠️ Added for deletion

    private PostAdapter postAdapter;
    private LinearLayout btnShare, btnOverview, btnStats;

    // Image Picker Config
    private String imageTypeToUpload = ""; // "icon" or "banner"

    // Handles the Image Selection Result
    private final ActivityResultLauncher<Intent> imagePickerLauncher = registerForActivityResult(
            new ActivityResultContracts.StartActivityForResult(),
            result -> {
                if (result.getResultCode() == Activity.RESULT_OK && result.getData() != null) {
                    Uri uri = result.getData().getData();

                    if (imageTypeToUpload.equals("banner")) {
                        Glide.with(this).load(uri).centerCrop().into(ivHeader);
                        uploadImageUpdate(uri);
                    } else if (imageTypeToUpload.equals("icon")) {
                        Glide.with(this).load(uri).into(ivIcon);
                        uploadImageUpdate(uri);
                    }
                }
            }
    );

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_group_detail);

        groupId = getIntent().getIntExtra("groupId", -1);

        if (groupId == -1) {
            Toast.makeText(this, "Error: Invalid Group", Toast.LENGTH_SHORT).show();
            finish();
            return;
        }

        SharedPreferences prefs = getSharedPreferences("UserPrefs", MODE_PRIVATE);
        String userIdStr = prefs.getString("userId", "-1");
        try {
            currentUserId = Integer.parseInt(userIdStr);
        } catch (NumberFormatException e) {
            currentUserId = -1;
        }

        initViews();
        loadGroupDetails();
        loadPosts();
    }

    private void initViews() {
        ivHeader = findViewById(R.id.iv_header_image);
        ivIcon = findViewById(R.id.iv_group_icon);
        btnBack = findViewById(R.id.btn_back_group);

        btnEditBanner = findViewById(R.id.btn_edit_group);
        btnEditTarget = findViewById(R.id.btn_edit_target);
        btnEditIcon = findViewById(R.id.btn_edit_icon);
        btnManageMembers = findViewById(R.id.btn_manage_members);
        btnDeleteGroup = findViewById(R.id.btn_delete_group); // 🛠️ Match XML ID

        tvName = findViewById(R.id.tv_group_name);
        tvType = findViewById(R.id.tv_group_type);
        tvDesc = findViewById(R.id.tv_group_desc);
        tvProgress = findViewById(R.id.tv_group_progress);
        pbTarget = findViewById(R.id.progress_group_target);

        btnShare = findViewById(R.id.btn_action_share);
        btnOverview = findViewById(R.id.btn_action_overview);
        btnStats = findViewById(R.id.btn_action_stats);

        rvFeed = findViewById(R.id.rv_group_feed);
        rvFeed.setLayoutManager(new LinearLayoutManager(this));

        btnBack.setOnClickListener(v -> finish());

        btnShare.setOnClickListener(v -> {
            if (currentGroup == null) return;
            Intent shareIntent = new Intent(Intent.ACTION_SEND);
            shareIntent.setType("text/plain");
            shareIntent.putExtra(Intent.EXTRA_TEXT, "Join my running group '" + currentGroup.getName() + "'!");
            startActivity(Intent.createChooser(shareIntent, "Share Group"));
        });

        btnOverview.setOnClickListener(v -> {
            if (currentGroup == null) return;
            new AlertDialog.Builder(this)
                    .setTitle("Group Overview")
                    .setMessage(currentGroup.getDescription() + "\n\nLocation: " + (currentGroup.getLocation() != null ? currentGroup.getLocation() : "Global"))
                    .setPositiveButton("Close", null).show();
        });

        btnEditTarget.setOnClickListener(v -> showEditTargetDialog());
        btnEditBanner.setOnClickListener(v -> { imageTypeToUpload = "banner"; openGallery(); });
        btnEditIcon.setOnClickListener(v -> { imageTypeToUpload = "icon"; openGallery(); });

        // 🛠️ Delete Group logic
        btnDeleteGroup.setOnClickListener(v -> showDeleteConfirmation());
    }

    private void openGallery() {
        Intent intent = new Intent(Intent.ACTION_PICK, MediaStore.Images.Media.EXTERNAL_CONTENT_URI);
        imagePickerLauncher.launch(intent);
    }

    private void loadGroupDetails() {
        String token = "Bearer " + getSavedToken();
        RetrofitClient.getService().getGroupDetails(token, groupId).enqueue(new Callback<GroupModel>() {
            @Override
            public void onResponse(Call<GroupModel> call, Response<GroupModel> response) {
                if (response.isSuccessful() && response.body() != null) {
                    currentGroup = response.body();
                    displayData(currentGroup);
                }
            }
            @Override public void onFailure(Call<GroupModel> call, Throwable t) {}
        });
    }

    private void displayData(GroupModel group) {
        tvName.setText(group.getName());
        tvDesc.setText(group.getDescription());
        tvType.setText("Running • " + group.getMembersCount() + " Members");

        if (group.getIconUrl() != null) {
            Glide.with(this).load(group.getIconUrl())
                    .diskCacheStrategy(DiskCacheStrategy.NONE)
                    .skipMemoryCache(true)
                    .placeholder(android.R.drawable.ic_menu_myplaces)
                    .into(ivIcon);
        }

        if (group.getBannerUrl() != null) {
            Glide.with(this).load(group.getBannerUrl())
                    .diskCacheStrategy(DiskCacheStrategy.NONE)
                    .skipMemoryCache(true)
                    .centerCrop()
                    .placeholder(android.R.drawable.ic_menu_gallery)
                    .into(ivHeader);
        }

        double target = group.getTargetKm();
        tvProgress.setText("0 / " + target + " km");
        pbTarget.setMax((int) target);
        pbTarget.setProgress(0);

        int visibility = (currentUserId != -1 && currentUserId == group.getCreatorId()) ? View.VISIBLE : View.GONE;
        btnEditBanner.setVisibility(visibility);
        btnEditTarget.setVisibility(visibility);
        btnEditIcon.setVisibility(visibility);
        btnManageMembers.setVisibility(visibility);

        // Only show Delete button to the creator
        btnDeleteGroup.setVisibility(visibility);
    }

    private void showDeleteConfirmation() {
        new AlertDialog.Builder(this)
                .setTitle("Delete Group")
                .setMessage("Are you sure you want to permanently delete this group? All posts and data will be lost.")
                .setPositiveButton("Delete", (dialog, which) -> deleteGroup())
                .setNegativeButton("Cancel", null)
                .setIcon(android.R.drawable.ic_dialog_alert)
                .show();
    }

    private void deleteGroup() {
        String token = "Bearer " + getSavedToken();
        RetrofitClient.getService().deleteGroup(token, groupId).enqueue(new Callback<ResponseBody>() {
            @Override
            public void onResponse(Call<ResponseBody> call, Response<ResponseBody> response) {
                if (response.isSuccessful()) {
                    Toast.makeText(GroupDetailActivity.this, "Group deleted! 🍫", Toast.LENGTH_SHORT).show();
                    finish(); // Return to previous screen
                } else {
                    Toast.makeText(GroupDetailActivity.this, "Failed to delete: " + response.code(), Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(Call<ResponseBody> call, Throwable t) {
                Toast.makeText(GroupDetailActivity.this, "Network Error", Toast.LENGTH_SHORT).show();
            }
        });
    }

    private void showEditTargetDialog() {
        AlertDialog.Builder builder = new AlertDialog.Builder(this);
        builder.setTitle("Update Target (km)");
        final EditText input = new EditText(this);
        input.setInputType(InputType.TYPE_CLASS_NUMBER | InputType.TYPE_NUMBER_FLAG_DECIMAL);
        if(currentGroup != null) input.setText(String.valueOf(currentGroup.getTargetKm()));
        builder.setView(input);
        builder.setPositiveButton("Update", (dialog, which) -> {
            String val = input.getText().toString();
            if (!val.isEmpty()) updateGroupTarget(Double.parseDouble(val));
        });
        builder.setNegativeButton("Cancel", null);
        builder.show();
    }

    private void updateGroupTarget(double newTarget) {
        if (currentGroup == null) return;
        String token = "Bearer " + getSavedToken();

        RequestBody namePart = RequestBody.create(MediaType.parse("text/plain"), currentGroup.getName());
        RequestBody locPart = RequestBody.create(MediaType.parse("text/plain"), currentGroup.getLocation() != null ? currentGroup.getLocation() : "");
        RequestBody descPart = RequestBody.create(MediaType.parse("text/plain"), currentGroup.getDescription() != null ? currentGroup.getDescription() : "");
        RequestBody targetPart = RequestBody.create(MediaType.parse("text/plain"), String.valueOf(newTarget));

        RetrofitClient.getService().updateGroup(token, groupId, namePart, locPart, descPart, targetPart, null, null)
                .enqueue(new Callback<GroupModel>() {
                    @Override
                    public void onResponse(Call<GroupModel> call, Response<GroupModel> response) {
                        if (response.isSuccessful()) {
                            Toast.makeText(GroupDetailActivity.this, "Target Updated!", Toast.LENGTH_SHORT).show();
                            loadGroupDetails();
                        }
                    }
                    @Override public void onFailure(Call<GroupModel> call, Throwable t) {}
                });
    }

    private void uploadImageUpdate(Uri uri) {
        if (uri == null || currentGroup == null) return;
        File file = uriToFile(uri);
        if (file == null) return;

        RequestBody requestFile = RequestBody.create(MediaType.parse("image/*"), file);
        MultipartBody.Part imagePart = MultipartBody.Part.createFormData(imageTypeToUpload, file.getName(), requestFile);
        String token = "Bearer " + getSavedToken();

        RequestBody namePart = RequestBody.create(MediaType.parse("text/plain"), currentGroup.getName());
        RequestBody locPart = RequestBody.create(MediaType.parse("text/plain"), currentGroup.getLocation() != null ? currentGroup.getLocation() : "");
        RequestBody descPart = RequestBody.create(MediaType.parse("text/plain"), currentGroup.getDescription() != null ? currentGroup.getDescription() : "");
        RequestBody targetPart = RequestBody.create(MediaType.parse("text/plain"), String.valueOf(currentGroup.getTargetKm()));

        MultipartBody.Part iconPart = imageTypeToUpload.equals("icon") ? imagePart : null;
        MultipartBody.Part bannerPart = imageTypeToUpload.equals("banner") ? imagePart : null;

        RetrofitClient.getService().updateGroup(token, groupId, namePart, locPart, descPart, targetPart, iconPart, bannerPart)
                .enqueue(new Callback<GroupModel>() {
                    @Override
                    public void onResponse(Call<GroupModel> call, Response<GroupModel> response) {
                        if (response.isSuccessful()) {
                            Toast.makeText(GroupDetailActivity.this, "Upload Success!", Toast.LENGTH_SHORT).show();
                            loadGroupDetails();
                        }
                    }
                    @Override public void onFailure(Call<GroupModel> call, Throwable t) {}
                });
    }

    private void loadPosts() {
        String token = "Bearer " + getSavedToken();
        RetrofitClient.getService().getGroupPosts(token, groupId).enqueue(new Callback<List<PostModel>>() {
            @Override
            public void onResponse(Call<List<PostModel>> call, Response<List<PostModel>> response) {
                if (response.isSuccessful() && response.body() != null) {
                    postAdapter = new PostAdapter(response.body());
                    rvFeed.setAdapter(postAdapter);
                }
            }
            @Override public void onFailure(Call<List<PostModel>> call, Throwable t) {}
        });
    }

    private String getSavedToken() {
        SharedPreferences prefs = getSharedPreferences("UserPrefs", MODE_PRIVATE);
        return prefs.getString("token", "");
    }

    private File uriToFile(Uri uri) {
        try {
            InputStream inputStream = getContentResolver().openInputStream(uri);
            File file = new File(getCacheDir(), "upload_" + System.currentTimeMillis() + ".jpg");
            FileOutputStream outputStream = new FileOutputStream(file);
            Bitmap bitmap = MediaStore.Images.Media.getBitmap(getContentResolver(), uri);
            bitmap.compress(Bitmap.CompressFormat.JPEG, 70, outputStream);
            outputStream.close();
            return file;
        } catch (Exception e) { return null; }
    }
}