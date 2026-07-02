package com.example.fypproject;

import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.util.Log;
import android.widget.Toast;

import androidx.annotation.Nullable;
import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import java.io.IOException;
import java.util.ArrayList;
import java.util.List;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class ConversationsActivity extends AppCompatActivity {

    private RecyclerView rvConversations;
    private ConversationAdapter adapter;
    private List<ConversationModel> conversationList;

    @Override
    protected void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_conversations);

        rvConversations = findViewById(R.id.rv_conversations);
        conversationList = new ArrayList<>();

        adapter = new ConversationAdapter(
                conversationList,
                conversation -> {
                    // When a user is clicked, we get or create the conversation ID from the server
                    if (conversation.getReceiver() != null) {
                        startOrGetConversation(conversation.getReceiver());
                    } else {
                        Toast.makeText(this, "User data error", Toast.LENGTH_SHORT).show();
                    }
                });

        rvConversations.setLayoutManager(new LinearLayoutManager(this));
        rvConversations.setAdapter(adapter);

        // Fetch real users from SQL backend instead of dummy data
        loadFollowedUsers();
    }

    private void loadFollowedUsers() {
        String token = "Bearer " + getToken();

        RetrofitClient.getService().getFollowing(token).enqueue(new Callback<List<UserModel>>() {
            @Override
            public void onResponse(Call<List<UserModel>> call, Response<List<UserModel>> response) {
                if (response.isSuccessful() && response.body() != null) {
                    conversationList.clear();

                    // Map the followed users into the ConversationModel list
                    for (UserModel user : response.body()) {
                        ConversationModel model = new ConversationModel();
                        model.setReceiver(user); // Temporarily store the user here to display their details
                        conversationList.add(model);
                    }

                    adapter.notifyDataSetChanged();
                } else {
                    // NEW: Tell us exactly WHY it failed (e.g., Error 404, 500)
                    String errorMsg = "Server Error: " + response.code();

                    // Attempt to log the deep error body to Android Studio Logcat
                    try {
                        if (response.errorBody() != null) {
                            Log.e("ConversationsActivity", "Error Body: " + response.errorBody().string());
                        }
                    } catch (IOException e) {
                        e.printStackTrace();
                    }

                    Toast.makeText(ConversationsActivity.this, errorMsg, Toast.LENGTH_LONG).show();
                    Log.e("ConversationsActivity", "Failed to load. Response Code: " + response.code());
                }
            }

            @Override
            public void onFailure(Call<List<UserModel>> call, Throwable t) {
                Log.e("ConversationsActivity", "Network Error: " + t.getMessage());
                Toast.makeText(ConversationsActivity.this, "Network error: " + t.getMessage(), Toast.LENGTH_LONG).show();
            }
        });
    }

    private void startOrGetConversation(UserModel targetUser) {
        String token = "Bearer " + getToken();

        // Tell Laravel to find or create a chat between current user and targetUserId
        RetrofitClient.getService().startConversation(token, targetUser.getId()).enqueue(new Callback<ConversationModel>() {
            @Override
            public void onResponse(Call<ConversationModel> call, Response<ConversationModel> response) {
                if (response.isSuccessful() && response.body() != null) {
                    // We successfully got the actual SQL Conversation ID
                    int realConversationId = response.body().getId();

                    // Navigate to ChatActivity with the real Conversation ID
                    Intent intent = new Intent(ConversationsActivity.this, ChatActivity.class);
                    intent.putExtra("conversationId", realConversationId);
                    intent.putExtra("targetUserName", targetUser.getName());
                    intent.putExtra("targetUserPhoto", targetUser.getProfilePhotoPath());
                    intent.putExtra("targetUserOnline", targetUser.isOnline());
                    startActivity(intent);
                } else {
                    // NEW: Show error code if starting the chat fails
                    Toast.makeText(ConversationsActivity.this, "Failed to start chat. Server Error: " + response.code(), Toast.LENGTH_LONG).show();
                }
            }

            @Override
            public void onFailure(Call<ConversationModel> call, Throwable t) {
                Log.e("ConversationsActivity", "Error: " + t.getMessage());
                Toast.makeText(ConversationsActivity.this, "Network error starting chat", Toast.LENGTH_SHORT).show();
            }
        });
    }

    private String getToken() {
        SharedPreferences prefs = getSharedPreferences("UserPrefs", MODE_PRIVATE);
        return prefs.getString("token", "");
    }
}
