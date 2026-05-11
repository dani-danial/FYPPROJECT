package com.example.fypproject;

import android.content.Intent;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.os.Bundle;
import android.util.Base64;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;

import com.google.firebase.auth.FirebaseAuth;
import com.google.firebase.firestore.FirebaseFirestore;

public class UserProfileActivity extends AppCompatActivity {

    private String targetUserId;
    private String currentUserId;
    private FirebaseFirestore db;

    private ImageView ivProfile, btnBack;
    private TextView tvName, tvUsername, tvFollowers, tvFollowing, tvAbout;
    private Button btnFollow, btnMessage; // Added btnMessage
    private boolean isFollowing = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_user_profile);

        targetUserId = getIntent().getStringExtra("targetUserId");
        currentUserId = FirebaseAuth.getInstance().getCurrentUser().getUid();
        db = FirebaseFirestore.getInstance();

        initViews();
        loadUserProfile();
        checkFollowStatus();

        btnFollow.setOnClickListener(v -> toggleFollow());
        btnBack.setOnClickListener(v -> finish());

        // NEW: Message Button Logic
        btnMessage.setOnClickListener(v -> {
            Intent intent = new Intent(UserProfileActivity.this, ChatActivity.class);
            intent.putExtra("targetUserId", targetUserId);
            // Pass the name so the Chat Screen header shows "Chat with Haziq" etc.
            intent.putExtra("targetUserName", tvName.getText().toString());
            startActivity(intent);
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
        btnMessage = findViewById(R.id.btn_message_user); // Initialize Button
        btnBack = findViewById(R.id.btn_back);
    }

    private void loadUserProfile() {
        db.collection("users").document(targetUserId).get()
                .addOnSuccessListener(doc -> {
                    if (doc.exists()) {
                        tvName.setText(doc.getString("name"));
                        tvUsername.setText("@" + doc.getString("username"));
                        tvAbout.setText(doc.getString("about"));

                        String base64 = doc.getString("imageBase64");
                        if (base64 != null && !base64.isEmpty()) {
                            try {
                                byte[] decoded = Base64.decode(base64, Base64.DEFAULT);
                                Bitmap bmp = BitmapFactory.decodeByteArray(decoded, 0, decoded.length);
                                ivProfile.setImageBitmap(bmp);
                            } catch (Exception e) {}
                        }
                    }
                });

        db.collection("users").document(targetUserId).collection("followers").get()
                .addOnSuccessListener(s -> tvFollowers.setText(String.valueOf(s.size())));
        db.collection("users").document(targetUserId).collection("following").get()
                .addOnSuccessListener(s -> tvFollowing.setText(String.valueOf(s.size())));
    }

    private void checkFollowStatus() {
        db.collection("users").document(targetUserId)
                .collection("followers").document(currentUserId)
                .get().addOnSuccessListener(doc -> {
                    isFollowing = doc.exists();
                    updateFollowButton();
                });
    }

    private void toggleFollow() {
        if (isFollowing) {
            // Unfollow
            db.collection("users").document(targetUserId).collection("followers").document(currentUserId).delete();
            db.collection("users").document(currentUserId).collection("following").document(targetUserId).delete();
            isFollowing = false;
        } else {
            // Follow
            db.collection("users").document(targetUserId).collection("followers").document(currentUserId).set(new Object());
            db.collection("users").document(currentUserId).collection("following").document(targetUserId).set(new Object());
            isFollowing = true;
        }
        updateFollowButton();
    }

    private void updateFollowButton() {
        if (isFollowing) {
            btnFollow.setText("Following");
            btnFollow.setBackgroundColor(0xFF555555); // Dark Grey
        } else {
            btnFollow.setText("Follow");
            btnFollow.setBackgroundColor(0xFF2196F3); // Blue
        }
    }
}