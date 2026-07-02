package com.example.fypproject;

import android.os.Bundle;
import android.os.Handler;
import android.text.TextUtils;
import android.util.Log;
import android.widget.EditText;
import android.widget.ImageButton;
import android.widget.ImageView;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.bumptech.glide.Glide;

import java.util.ArrayList;
import java.util.List;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class ChatActivity extends AppCompatActivity {

    private int conversationId;
    private String targetUserName;
    private String targetUserPhoto;
    private boolean targetUserOnline;
    private String currentUserId;

    private RecyclerView rvChat;
    private EditText etMessage;
    private ImageButton btnSend, btnBack;
    private ImageView ivChatAvatar;
    private TextView tvHeaderName, tvHeaderStatus;

    private MessageAdapter adapter;
    private List<Message> messageList;

    // Polling handler to simulate real-time chat without Firebase
    private Handler pollingHandler = new Handler();
    private Runnable pollingRunnable;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_chat);

        // 1. Get ID from SharedPreferences instead of Firebase
        currentUserId = getSharedPreferences("UserPrefs", MODE_PRIVATE).getString("userId", "0");

        // 2. Get passed data from Intent
        conversationId = getIntent().getIntExtra("conversationId", -1);
        targetUserName = getIntent().getStringExtra("targetUserName");
        targetUserPhoto = getIntent().getStringExtra("targetUserPhoto");
        targetUserOnline = getIntent().getBooleanExtra("targetUserOnline", false);

        initViews();

        if (conversationId != -1) {
            startPolling(); // Start fetching messages every 3 seconds
        } else {
            Toast.makeText(this, "Error: Invalid Chat Room", Toast.LENGTH_SHORT).show();
            finish();
        }
    }

    private void initViews() {
        rvChat = findViewById(R.id.rv_chat);
        etMessage = findViewById(R.id.et_message_input);
        btnSend = findViewById(R.id.btn_send_message);
        btnBack = findViewById(R.id.btn_chat_back);
        ivChatAvatar = findViewById(R.id.iv_chat_avatar);
        tvHeaderName = findViewById(R.id.tv_chat_header_name);
        tvHeaderStatus = findViewById(R.id.tv_chat_header_status);

        if (targetUserName != null && !targetUserName.isEmpty()) {
            tvHeaderName.setText(targetUserName);
        } else {
            tvHeaderName.setText("Chat");
        }
        tvHeaderStatus.setText(targetUserOnline ? "online" : "offline");
        tvHeaderStatus.setTextColor(getColor(targetUserOnline ? android.R.color.holo_green_light : android.R.color.darker_gray));

        Glide.with(this)
                .load(targetUserPhoto)
                .placeholder(android.R.drawable.sym_def_app_icon)
                .circleCrop()
                .into(ivChatAvatar);

        messageList = new ArrayList<>();

        // Ensure your MessageAdapter accepts currentUserId to distinguish sent vs received
        adapter = new MessageAdapter(messageList, currentUserId, targetUserPhoto);

        LinearLayoutManager manager = new LinearLayoutManager(this);
        manager.setStackFromEnd(true); // Push messages from bottom up
        rvChat.setLayoutManager(manager);
        rvChat.setAdapter(adapter);

        // Listeners
        btnSend.setOnClickListener(v -> sendMessage());
        btnBack.setOnClickListener(v -> finish());
    }

    private void fetchMessages() {
        String token = "Bearer " + getSharedPreferences("UserPrefs", MODE_PRIVATE).getString("token", "");

        RetrofitClient.getService().getMessages(token, conversationId).enqueue(new Callback<List<Message>>() {
            @Override
            public void onResponse(Call<List<Message>> call, Response<List<Message>> response) {
                if (response.isSuccessful() && response.body() != null) {
                    messageList.clear();
                    messageList.addAll(response.body());
                    adapter.notifyDataSetChanged();

                    // Auto-scroll to the latest message
                    if (!messageList.isEmpty()) {
                        rvChat.scrollToPosition(messageList.size() - 1);
                    }
                }
            }

            @Override
            public void onFailure(Call<List<Message>> call, Throwable t) {
                Log.e("ChatActivity", "Error fetching messages: " + t.getMessage());
            }
        });
    }

    private void sendMessage() {
        String text = etMessage.getText().toString().trim();
        if (TextUtils.isEmpty(text)) return;

        // Clear input field immediately for better user experience
        etMessage.setText("");

        String token = "Bearer " + getSharedPreferences("UserPrefs", MODE_PRIVATE).getString("token", "");

        // FIX: Pass the text directly into the constructor to match your SendMessageRequest class
        SendMessageRequest request = new SendMessageRequest(text);

        RetrofitClient.getService().sendMessage(token, conversationId, request).enqueue(new Callback<Message>() {
            @Override
            public void onResponse(Call<Message> call, Response<Message> response) {
                if (response.isSuccessful()) {
                    // Refresh the chat immediately after sending
                    fetchMessages();
                } else {
                    Toast.makeText(ChatActivity.this, "Failed to send message", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(Call<Message> call, Throwable t) {
                Toast.makeText(ChatActivity.this, "Network error", Toast.LENGTH_SHORT).show();
            }
        });
    }

    private void startPolling() {
        pollingRunnable = new Runnable() {
            @Override
            public void run() {
                fetchMessages();
                pollingHandler.postDelayed(this, 3000); // Poll every 3 seconds
            }
        };
        pollingHandler.post(pollingRunnable);
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        // Stop polling to prevent memory leaks and unnecessary API calls when user leaves chat
        if (pollingHandler != null && pollingRunnable != null) {
            pollingHandler.removeCallbacks(pollingRunnable);
        }
    }
}
