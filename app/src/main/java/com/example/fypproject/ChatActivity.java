package com.example.fypproject;

import android.os.Bundle;
import android.text.TextUtils;
import android.widget.EditText;
import android.widget.ImageButton;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.google.firebase.auth.FirebaseAuth;
import com.google.firebase.firestore.DocumentChange;
import com.google.firebase.firestore.FieldValue;
import com.google.firebase.firestore.FirebaseFirestore;
import com.google.firebase.firestore.Query;

import java.util.ArrayList;
import java.util.Arrays;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

public class ChatActivity extends AppCompatActivity {

    private String chatRoomId;
    private String targetUserId, targetUserName;
    private String currentUserId;
    private FirebaseFirestore db;

    private RecyclerView rvChat;
    private EditText etMessage;
    private ImageButton btnSend, btnBack; // Added btnBack
    private TextView tvHeaderName;
    private MessageAdapter adapter;
    private List<Message> messageList;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_chat);

        targetUserId = getIntent().getStringExtra("targetUserId");
        targetUserName = getIntent().getStringExtra("targetUserName");
        currentUserId = FirebaseAuth.getInstance().getUid();
        db = FirebaseFirestore.getInstance();

        // Consistent Chat Room ID Generation
        if (currentUserId.compareTo(targetUserId) < 0) {
            chatRoomId = currentUserId + "_" + targetUserId;
        } else {
            chatRoomId = targetUserId + "_" + currentUserId;
        }

        initViews();
        setupChat();
    }

    private void initViews() {
        rvChat = findViewById(R.id.rv_chat);
        etMessage = findViewById(R.id.et_message_input);
        btnSend = findViewById(R.id.btn_send_message);
        btnBack = findViewById(R.id.btn_chat_back); // Initialize Back Button
        tvHeaderName = findViewById(R.id.tv_chat_header_name);

        tvHeaderName.setText(targetUserName);

        messageList = new ArrayList<>();
        adapter = new MessageAdapter(messageList);
        LinearLayoutManager manager = new LinearLayoutManager(this);
        manager.setStackFromEnd(true);
        rvChat.setLayoutManager(manager);
        rvChat.setAdapter(adapter);

        // Listeners
        btnSend.setOnClickListener(v -> sendMessage());
        btnBack.setOnClickListener(v -> finish()); // Go back to previous screen
    }

    private void setupChat() {
        db.collection("chats").document(chatRoomId).collection("messages")
                .orderBy("timestamp", Query.Direction.ASCENDING)
                .addSnapshotListener((snapshots, e) -> {
                    if (e != null) return;
                    if (snapshots != null) {
                        for (DocumentChange dc : snapshots.getDocumentChanges()) {
                            if (dc.getType() == DocumentChange.Type.ADDED) {
                                Message msg = dc.getDocument().toObject(Message.class);
                                messageList.add(msg);
                                adapter.notifyItemInserted(messageList.size() - 1);
                                rvChat.scrollToPosition(messageList.size() - 1);
                            }
                        }
                    }
                });
    }

    private void sendMessage() {
        String text = etMessage.getText().toString().trim();
        if (TextUtils.isEmpty(text)) return;

        etMessage.setText("");

        Map<String, Object> messageData = new HashMap<>();
        messageData.put("senderId", currentUserId);
        messageData.put("text", text);
        messageData.put("timestamp", FieldValue.serverTimestamp());

        // 1. Add to message history
        db.collection("chats").document(chatRoomId).collection("messages").add(messageData);

        // 2. Update the Chat Metadata
        Map<String, Object> chatMeta = new HashMap<>();
        chatMeta.put("lastMessage", text);
        chatMeta.put("timestamp", FieldValue.serverTimestamp());

        // --- NEW: Save WHO sent this message ---
        chatMeta.put("lastSenderId", currentUserId);

        chatMeta.put("users", Arrays.asList(currentUserId, targetUserId));

        db.collection("chats").document(chatRoomId).set(chatMeta, com.google.firebase.firestore.SetOptions.merge());
    }
}