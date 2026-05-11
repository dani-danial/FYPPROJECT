package com.example.fypproject;

import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.util.Base64;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageButton;
import android.widget.ImageView;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.google.firebase.auth.FirebaseAuth;
import com.google.firebase.firestore.DocumentSnapshot;
import com.google.firebase.firestore.FirebaseFirestore;

import java.util.HashMap;
import java.util.List;
import java.util.Map;

public class UserAdapter extends RecyclerView.Adapter<UserAdapter.UserViewHolder> {

    private List<DocumentSnapshot> userList;
    private final String currentUserId;
    private final FirebaseFirestore db;

    // 1. ADD: Listener variable
    private final OnUserClickListener listener;

    // 2. ADD: Interface for click events
    public interface OnUserClickListener {
        void onUserClick(String userId);
    }

    // 3. UPDATE: Constructor to accept the listener
    public UserAdapter(List<DocumentSnapshot> userList, OnUserClickListener listener) {
        this.userList = userList;
        this.listener = listener; // Initialize listener
        this.currentUserId = FirebaseAuth.getInstance().getUid();
        this.db = FirebaseFirestore.getInstance();
    }

    public void updateList(List<DocumentSnapshot> newList) {
        this.userList = newList;
        notifyDataSetChanged();
    }

    @NonNull
    @Override
    public UserViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_user, parent, false);
        return new UserViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull UserViewHolder holder, int position) {
        DocumentSnapshot document = userList.get(position);
        String otherUserId = document.getId();
        String name = document.getString("name");
        String username = document.getString("username");
        String imageBase64 = document.getString("imageBase64");

        holder.tvName.setText(name);
        holder.tvUsername.setText("@" + (username != null ? username : ""));

        // Load Base64 Image
        if (imageBase64 != null && !imageBase64.isEmpty()) {
            try {
                byte[] decodedString = Base64.decode(imageBase64, Base64.DEFAULT);
                Bitmap decodedByte = BitmapFactory.decodeByteArray(decodedString, 0, decodedString.length);
                holder.ivProfile.setImageBitmap(decodedByte);
            } catch (Exception e) {
                holder.ivProfile.setImageResource(android.R.drawable.sym_def_app_icon);
            }
        } else {
            holder.ivProfile.setImageResource(android.R.drawable.sym_def_app_icon);
        }

        // 4. ADD: Handle Item Click (Navigate to Profile)
        holder.itemView.setOnClickListener(v -> {
            if (listener != null) {
                listener.onUserClick(otherUserId);
            }
        });

        // Existing Follow Logic
        if (otherUserId.equals(currentUserId)) {
            holder.btnFollow.setVisibility(View.GONE);
        } else {
            holder.btnFollow.setVisibility(View.VISIBLE);
            checkFollowStatus(holder, otherUserId);
            holder.btnFollow.setOnClickListener(v -> toggleFollow(holder, otherUserId));
        }
    }

    private void checkFollowStatus(UserViewHolder holder, String otherUserId) {
        db.collection("users").document(currentUserId)
                .collection("following").document(otherUserId)
                .get()
                .addOnSuccessListener(documentSnapshot -> {
                    if (documentSnapshot.exists()) {
                        holder.btnFollow.setImageResource(android.R.drawable.checkbox_on_background);
                        holder.btnFollow.setTag("following");
                    } else {
                        holder.btnFollow.setImageResource(android.R.drawable.ic_input_add);
                        holder.btnFollow.setTag("not_following");
                    }
                });
    }

    private void toggleFollow(UserViewHolder holder, String otherUserId) {
        holder.btnFollow.setEnabled(false);
        String status = (String) holder.btnFollow.getTag();

        if ("not_following".equals(status)) {
            Map<String, Object> data = new HashMap<>();
            data.put("timestamp", System.currentTimeMillis());

            db.collection("users").document(currentUserId).collection("following").document(otherUserId).set(data);
            db.collection("users").document(otherUserId).collection("followers").document(currentUserId).set(data)
                    .addOnSuccessListener(aVoid -> {
                        holder.btnFollow.setImageResource(android.R.drawable.checkbox_on_background);
                        holder.btnFollow.setTag("following");
                        holder.btnFollow.setEnabled(true);
                    });
        } else {
            db.collection("users").document(currentUserId).collection("following").document(otherUserId).delete();
            db.collection("users").document(otherUserId).collection("followers").document(currentUserId).delete()
                    .addOnSuccessListener(aVoid -> {
                        holder.btnFollow.setImageResource(android.R.drawable.ic_input_add);
                        holder.btnFollow.setTag("not_following");
                        holder.btnFollow.setEnabled(true);
                    });
        }
    }

    @Override
    public int getItemCount() {
        return userList.size();
    }

    static class UserViewHolder extends RecyclerView.ViewHolder {
        ImageView ivProfile;
        TextView tvName, tvUsername;
        ImageButton btnFollow;

        public UserViewHolder(@NonNull View itemView) {
            super(itemView);
            ivProfile = itemView.findViewById(R.id.iv_profile_image);
            tvName = itemView.findViewById(R.id.tv_user_name);
            tvUsername = itemView.findViewById(R.id.tv_user_username);
            btnFollow = itemView.findViewById(R.id.btn_follow);
        }
    }
}