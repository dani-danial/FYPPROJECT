package com.example.fypproject;

import android.content.Context;
import android.content.Intent;
import android.graphics.Color;
import android.os.Bundle;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;

import com.bumptech.glide.Glide;
import com.bumptech.glide.load.engine.DiskCacheStrategy;

import okhttp3.ResponseBody;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class UserProfileActivity extends AppCompatActivity {

    private String targetUserId;
    private ImageView ivProfile, btnBack;
    private TextView tvName, tvUsername, tvFollowers, tvFollowing, tvAbout;
    private Button btnFollow, btnMessage;
    private boolean isFollowing = false;
    private UserModel targetUser;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_user_profile);

        targetUserId = getIntent().getStringExtra("targetUserId");

        initViews();
        loadUserProfile();

        btnFollow.setOnClickListener(v -> toggleFollow());
        btnBack.setOnClickListener(v -> finish());

        // Message Button Logic: Start or get a conversation via Laravel
        btnMessage.setOnClickListener(v -> {
            String token = "Bearer " + getToken();
            RetrofitClient.getService().startConversation(token, Integer.parseInt(targetUserId)).enqueue(new Callback<ConversationModel>() {
                @Override
                public void onResponse(Call<ConversationModel> call, Response<ConversationModel> response) {
                    if (response.isSuccessful() && response.body() != null) {
                        Intent intent = new Intent(UserProfileActivity.this, ChatActivity.class);
                        intent.putExtra("conversationId", response.body().getId());
                        intent.putExtra("targetUserId", targetUserId);
                        intent.putExtra("targetUserName", targetUser != null ? targetUser.getName() : tvName.getText().toString());
                        intent.putExtra("targetUserPhoto", targetUser != null ? targetUser.getProfilePhotoPath() : null);
                        intent.putExtra("targetUserOnline", targetUser != null && targetUser.isOnline());
                        startActivity(intent);
                    } else {
                        Toast.makeText(UserProfileActivity.this, "Failed to start chat", Toast.LENGTH_SHORT).show();
                    }
                }

                @Override
                public void onFailure(Call<ConversationModel> call, Throwable t) {
                    Toast.makeText(UserProfileActivity.this, "Network Error", Toast.LENGTH_SHORT).show();
                }
            });
        });
    }

    private void initViews() {
        ivProfile = findViewById(R.id.iv_other_profile_image);
        tvName = findViewById(R.id.tv_other_display_name);
        tvUsername = findViewById(R.id.tv_other_username);
        tvAbout = findViewById(R.id.tv_other_about);
        tvFollowers = findViewById(R.id.tv_other_followers);
        tvFollowing = findViewById(R.id.tv_other_following);

        btnFollow = findViewById(R.id.btn_follow_user);
        btnMessage = findViewById(R.id.btn_message_user);
        btnBack = findViewById(R.id.btn_back);
    }

    private void loadUserProfile() {
        String token = "Bearer " + getToken();
        RetrofitClient.getService().getUserProfileById(token, targetUserId).enqueue(new Callback<UserModel>() {
            @Override
            public void onResponse(Call<UserModel> call, Response<UserModel> response) {
                if (response.isSuccessful() && response.body() != null) {
                    UserModel user = response.body();
                    targetUser = user;
                    tvName.setText(user.getName());
                    tvUsername.setText("@" + user.getUsername());
                    tvAbout.setText(user.getAboutMe());
                    tvFollowers.setText(String.valueOf(user.getFollowersCount()));
                    tvFollowing.setText(String.valueOf(user.getFollowingCount()));
                    isFollowing = user.isFollowing();
                    updateFollowButton();

                    if (user.getProfilePhotoPath() != null) {
                        Glide.with(UserProfileActivity.this)
                                .load(user.getProfilePhotoPath())
                                .diskCacheStrategy(DiskCacheStrategy.ALL)
                                .circleCrop()
                                .into(ivProfile);
                    }
                }
            }

            @Override
            public void onFailure(Call<UserModel> call, Throwable t) {
                Toast.makeText(UserProfileActivity.this, "Error loading profile", Toast.LENGTH_SHORT).show();
            }
        });
    }

    private void toggleFollow() {
        String token = "Bearer " + getToken();
        RetrofitClient.getService().toggleFollow(token, targetUserId).enqueue(new Callback<ResponseBody>() {
            @Override
            public void onResponse(Call<ResponseBody> call, Response<ResponseBody> response) {
                if (response.isSuccessful()) {
                    isFollowing = !isFollowing;
                    updateFollowButton();
                    loadUserProfile(); // Refresh counts
                }
            }

            @Override
            public void onFailure(Call<ResponseBody> call, Throwable t) {
                Toast.makeText(UserProfileActivity.this, "Action failed", Toast.LENGTH_SHORT).show();
            }
        });
    }

    private void updateFollowButton() {
        if (isFollowing) {
            btnFollow.setText("Following");
            btnFollow.setBackgroundColor(Color.parseColor("#555555"));
        } else {
            btnFollow.setText("Follow");
            btnFollow.setBackgroundColor(Color.parseColor("#2196F3"));
        }
    }

    private String getToken() {
        return getSharedPreferences("UserPrefs", Context.MODE_PRIVATE).getString("token", "");
    }
}
