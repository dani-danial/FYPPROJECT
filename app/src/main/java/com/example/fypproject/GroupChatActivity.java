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

public class GroupChatActivity extends AppCompatActivity {

    private int groupId;
    private String groupName;
    private String groupIcon;
    private String currentUserId;

    private RecyclerView rvChat;
    private EditText etMessage;
    private ImageButton btnSend, btnBack;
    private ImageView ivChatAvatar;
    private TextView tvHeaderName, tvHeaderStatus;

    private GroupMessageAdapter adapter;
    private List<Message> messageList;

    private Handler pollingHandler = new Handler();
    private Runnable pollingRunnable;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_group_chat);

        currentUserId = getSharedPreferences("UserPrefs", MODE_PRIVATE).getString("userId", "0");

        groupId = getIntent().getIntExtra("groupId", -1);
        groupName = getIntent().getStringExtra("groupName");
        groupIcon = getIntent().getStringExtra("groupIcon");

        initViews();

        if (groupId != -1) {
            startPolling();
        } else {
            Toast.makeText(this, "Error: Invalid Group Chat Room", Toast.LENGTH_SHORT).show();
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

        if (groupName != null && !groupName.isEmpty()) {
            tvHeaderName.setText(groupName);
        } else {
            tvHeaderName.setText("Group Chat");
        }
        tvHeaderStatus.setText("Running Group");

        Glide.with(this)
                .load(groupIcon)
                .placeholder(android.R.drawable.ic_menu_myplaces)
                .circleCrop()
                .into(ivChatAvatar);

        messageList = new ArrayList<>();
        adapter = new GroupMessageAdapter(messageList, currentUserId);

        LinearLayoutManager manager = new LinearLayoutManager(this);
        manager.setStackFromEnd(true);
        rvChat.setLayoutManager(manager);
        rvChat.setAdapter(adapter);

        btnSend.setOnClickListener(v -> sendMessage());
        btnBack.setOnClickListener(v -> finish());
    }

    private void fetchMessages() {
        String token = "Bearer " + getSharedPreferences("UserPrefs", MODE_PRIVATE).getString("token", "");

        RetrofitClient.getService().getGroupMessages(token, groupId).enqueue(new Callback<List<Message>>() {
            @Override
            public void onResponse(Call<List<Message>> call, Response<List<Message>> response) {
                if (isFinishing() || isDestroyed()) return;
                if (response.isSuccessful() && response.body() != null) {
                    int oldSize = messageList.size();
                    messageList.clear();
                    messageList.addAll(response.body());
                    adapter.notifyDataSetChanged();

                    // Only auto-scroll to bottom if list changed size to avoid interrupting user scroll
                    if (!messageList.isEmpty() && messageList.size() != oldSize) {
                        rvChat.scrollToPosition(messageList.size() - 1);
                    }
                }
            }

            @Override
            public void onFailure(Call<List<Message>> call, Throwable t) {
                Log.e("GroupChatActivity", "Error fetching messages: " + t.getMessage());
            }
        });
    }

    private void sendMessage() {
        String text = etMessage.getText().toString().trim();
        if (TextUtils.isEmpty(text)) return;

        etMessage.setText("");

        String token = "Bearer " + getSharedPreferences("UserPrefs", MODE_PRIVATE).getString("token", "");
        SendMessageRequest request = new SendMessageRequest(text);

        RetrofitClient.getService().sendGroupMessage(token, groupId, request).enqueue(new Callback<Message>() {
            @Override
            public void onResponse(Call<Message> call, Response<Message> response) {
                if (response.isSuccessful()) {
                    fetchMessages();
                } else {
                    Toast.makeText(GroupChatActivity.this, "Failed to send message", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(Call<Message> call, Throwable t) {
                Toast.makeText(GroupChatActivity.this, "Error: Network failure", Toast.LENGTH_SHORT).show();
            }
        });
    }

    private void startPolling() {
        pollingRunnable = new Runnable() {
            @Override
            public void run() {
                fetchMessages();
                pollingHandler.postDelayed(this, 3000);
            }
        };
        pollingHandler.post(pollingRunnable);
    }

    private void stopPolling() {
        if (pollingRunnable != null) {
            pollingHandler.removeCallbacks(pollingRunnable);
        }
    }

    @Override
    protected void onResume() {
        super.onResume();
        if (groupId != -1) {
            startPolling();
        }
    }

    @Override
    protected void onPause() {
        super.onPause();
        stopPolling();
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        stopPolling();
    }
}
