package com.example.fypproject;

import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.util.Base64;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.cardview.widget.CardView;
import androidx.recyclerview.widget.RecyclerView;
import com.google.firebase.firestore.DocumentSnapshot;
import com.google.firebase.firestore.FirebaseFirestore;
import java.text.SimpleDateFormat;
import java.util.List;
import java.util.Locale;

public class GroupPostAdapter extends RecyclerView.Adapter<GroupPostAdapter.PostViewHolder> {

    private List<DocumentSnapshot> postList;
    private FirebaseFirestore db;

    public GroupPostAdapter(List<DocumentSnapshot> postList) {
        this.postList = postList;
        this.db = FirebaseFirestore.getInstance();
    }

    @NonNull
    @Override
    public PostViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_group_post, parent, false);
        return new PostViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull PostViewHolder holder, int position) {
        DocumentSnapshot document = postList.get(position);

        String content = document.getString("content");
        String imageBase64 = document.getString("imageBase64");
        String userId = document.getString("userId");
        java.util.Date timestamp = document.getDate("timestamp");

        // 1. Set Text Content
        holder.tvContent.setText(content);

        if (timestamp != null) {
            SimpleDateFormat sdf = new SimpleDateFormat("MMM dd, HH:mm", Locale.getDefault());
            holder.tvDate.setText(sdf.format(timestamp));
        }

        // 2. Load Post Image (Base64)
        if (imageBase64 != null && !imageBase64.isEmpty()) {
            holder.cvImageContainer.setVisibility(View.VISIBLE);
            try {
                byte[] decodedString = Base64.decode(imageBase64, Base64.DEFAULT);
                Bitmap decodedByte = BitmapFactory.decodeByteArray(decodedString, 0, decodedString.length);
                holder.ivPostImage.setImageBitmap(decodedByte);
            } catch (Exception e) {
                holder.cvImageContainer.setVisibility(View.GONE);
            }
        } else {
            holder.cvImageContainer.setVisibility(View.GONE);
        }

        // 3. Load Author Info (Async)
        if (userId != null) {
            db.collection("users").document(userId).get().addOnSuccessListener(userDoc -> {
                if (userDoc.exists()) {
                    holder.tvName.setText(userDoc.getString("name"));
                    String profileBase64 = userDoc.getString("imageBase64");
                    if (profileBase64 != null && !profileBase64.isEmpty()) {
                        try {
                            byte[] decodedString = Base64.decode(profileBase64, Base64.DEFAULT);
                            Bitmap decodedByte = BitmapFactory.decodeByteArray(decodedString, 0, decodedString.length);
                            holder.ivUser.setImageBitmap(decodedByte);
                        } catch (Exception e) {}
                    }
                }
            });
        }
    }

    @Override
    public int getItemCount() {
        return postList.size();
    }

    public static class PostViewHolder extends RecyclerView.ViewHolder {
        TextView tvName, tvDate, tvContent;
        ImageView ivUser, ivPostImage;
        CardView cvImageContainer;

        public PostViewHolder(@NonNull View itemView) {
            super(itemView);
            tvName = itemView.findViewById(R.id.tv_post_user_name);
            tvDate = itemView.findViewById(R.id.tv_post_date);
            tvContent = itemView.findViewById(R.id.tv_post_content);
            ivUser = itemView.findViewById(R.id.iv_post_user);
            ivPostImage = itemView.findViewById(R.id.iv_post_image);
            cvImageContainer = itemView.findViewById(R.id.cv_post_image);
        }
    }
}