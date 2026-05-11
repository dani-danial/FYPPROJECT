package com.example.fypproject;

import android.content.Intent;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.os.Bundle;
import android.util.Base64;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import com.google.firebase.auth.FirebaseAuth;
import com.google.firebase.firestore.DocumentSnapshot;
import com.google.firebase.firestore.FirebaseFirestore;
import java.util.List;

public class RecentChatsActivity extends AppCompatActivity {

    private RecyclerView recyclerView;
    private FirebaseFirestore db;
    private String currentUserId;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_recent_chats);

        recyclerView = findViewById(R.id.rv_recent_chats);
        recyclerView.setLayoutManager(new LinearLayoutManager(this));

        // Handle Back Button
        findViewById(R.id.btn_back_recent).setOnClickListener(v -> finish());

        db = FirebaseFirestore.getInstance();
        currentUserId = FirebaseAuth.getInstance().getCurrentUser().getUid();

        loadRecentChats();
    }

    private void loadRecentChats() {
        // Find chats where I am a participant
        db.collection("chats")
                .whereArrayContains("users", currentUserId)
                .orderBy("timestamp", com.google.firebase.firestore.Query.Direction.DESCENDING)
                .addSnapshotListener((snapshots, e) -> {
                    if (e != null || snapshots == null) return;
                    RecentChatAdapter adapter = new RecentChatAdapter(snapshots.getDocuments());
                    recyclerView.setAdapter(adapter);
                });
    }

    // --- UPDATED INTERNAL ADAPTER CLASS ---
    class RecentChatAdapter extends RecyclerView.Adapter<RecentChatAdapter.ChatViewHolder> {
        List<DocumentSnapshot> chatList;

        public RecentChatAdapter(List<DocumentSnapshot> chatList) { this.chatList = chatList; }

        @NonNull @Override
        public ChatViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
            // UPDATE 1: Inflate custom layout 'item_recent_chat.xml'
            View v = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_recent_chat, parent, false);
            return new ChatViewHolder(v);
        }

        @Override
        public void onBindViewHolder(@NonNull ChatViewHolder holder, int position) {
            DocumentSnapshot doc = chatList.get(position);

            // 1. GET DATA
            String lastMessage = doc.getString("lastMessage");
            List<String> users = (List<String>) doc.get("users");

            // Identify the OTHER user (Not me)
            String otherUserId = (users.get(0).equals(currentUserId)) ? users.get(1) : users.get(0);

            // 2. SET MESSAGE IMMEDIATELY
            holder.tvMessage.setText(lastMessage != null ? lastMessage : "Start chatting...");

            // 3. SET TEMP NAME & DEFAULT IMAGE
            holder.tvName.setText("Loading...");
            holder.ivAvatar.setImageResource(android.R.drawable.sym_def_app_icon);

            // 4. FETCH USER DETAILS (Name + Image)
            db.collection("users").document(otherUserId).get().addOnSuccessListener(userDoc -> {
                if (userDoc.exists()) {
                    String name = userDoc.getString("name");
                    holder.tvName.setText(name); // Update name

                    // --- NEW: Load Profile Picture ---
                    String base64 = userDoc.getString("imageBase64");
                    if (base64 != null && !base64.isEmpty()) {
                        try {
                            byte[] decoded = Base64.decode(base64, Base64.DEFAULT);
                            Bitmap bmp = BitmapFactory.decodeByteArray(decoded, 0, decoded.length);
                            holder.ivAvatar.setImageBitmap(bmp);
                        } catch (Exception e) {
                            // Keep default if decode fails
                            holder.ivAvatar.setImageResource(android.R.drawable.sym_def_app_icon);
                        }
                    }

                    // Update click listener with correct data
                    holder.itemView.setOnClickListener(v -> {
                        Intent intent = new Intent(RecentChatsActivity.this, ChatActivity.class);
                        intent.putExtra("targetUserId", otherUserId);
                        intent.putExtra("targetUserName", name);
                        startActivity(intent);
                    });
                }
            });
        }

        @Override public int getItemCount() { return chatList.size(); }

        // UPDATE 2: ChatViewHolder connects to custom layout IDs
        class ChatViewHolder extends RecyclerView.ViewHolder {
            ImageView ivAvatar;
            TextView tvName, tvMessage;

            public ChatViewHolder(@NonNull View itemView) {
                super(itemView);
                ivAvatar = itemView.findViewById(R.id.iv_chat_avatar);
                tvName = itemView.findViewById(R.id.tv_chat_name);
                tvMessage = itemView.findViewById(R.id.tv_chat_message);
            }
        }
    }
}