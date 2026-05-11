package com.example.fypproject;

import android.text.format.DateUtils;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.bumptech.glide.Glide;
import com.google.android.material.button.MaterialButton;

import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.List;
import java.util.Locale;
import java.util.TimeZone;

public class PostAdapter extends RecyclerView.Adapter<PostAdapter.PostViewHolder> {

    private List<PostModel> postList;

    public PostAdapter(List<PostModel> postList) {
        this.postList = postList;
    }

    @NonNull
    @Override
    public PostViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        // 🛠️ Ensure this matches the name of your new XML file!
        View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_community_post, parent, false);
        return new PostViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull PostViewHolder holder, int position) {
        PostModel post = postList.get(position);

        // Set Text Data
        holder.tvUserName.setText(post.getUserName());
        holder.tvPostContent.setText(post.getContent());

        // Format the relative time and make it uppercase to match the dark web UI
        String formattedTime = formatTime(post.getCreatedAt());
        holder.tvPostTime.setText(formattedTime.toUpperCase());

        // Load Member Profile Image
        Glide.with(holder.itemView.getContext())
                .load(post.getUserImage())
                .placeholder(android.R.drawable.sym_def_app_icon)
                .circleCrop()
                .into(holder.ivUserProfile);

        // Handle Post Content Image (Hide if null/empty)
        if (post.getImageUrl() != null && !post.getImageUrl().isEmpty()) {
            holder.ivPostImage.setVisibility(View.VISIBLE);
            Glide.with(holder.itemView.getContext())
                    .load(post.getImageUrl())
                    .centerCrop() // Fills the ImageView nicely
                    .into(holder.ivPostImage);
        } else {
            holder.ivPostImage.setVisibility(View.GONE);
        }

        // Note: Likes and Comments counts can be set here later if you add them to your database
        holder.btnLike.setText("0 Likes");
        holder.btnComment.setText("0 Comments");
    }

    @Override
    public int getItemCount() {
        return postList.size();
    }

    private String formatTime(String rawDate) {
        if (rawDate == null) return "";
        try {
            SimpleDateFormat sdf = new SimpleDateFormat("yyyy-MM-dd'T'HH:mm:ss.SSSSSS'Z'", Locale.getDefault());
            sdf.setTimeZone(TimeZone.getTimeZone("UTC"));
            Date date = sdf.parse(rawDate);

            if (date == null) return rawDate;

            return DateUtils.getRelativeTimeSpanString(
                    date.getTime(),
                    System.currentTimeMillis(),
                    DateUtils.MINUTE_IN_MILLIS
            ).toString();
        } catch (Exception e) {
            try {
                SimpleDateFormat simple = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss", Locale.getDefault());
                simple.setTimeZone(TimeZone.getTimeZone("UTC"));
                Date date = simple.parse(rawDate);
                return DateUtils.getRelativeTimeSpanString(
                        date.getTime(),
                        System.currentTimeMillis(),
                        DateUtils.MINUTE_IN_MILLIS
                ).toString();
            } catch (Exception ex) {
                return rawDate;
            }
        }
    }

    public static class PostViewHolder extends RecyclerView.ViewHolder {
        // 🛠️ Updated to match the new XML IDs exactly
        ImageView ivUserProfile, ivPostImage, btnEditPost, btnDeletePost;
        TextView tvUserName, tvPostTime, tvPostContent;
        MaterialButton btnViewPost, btnLike, btnComment;

        public PostViewHolder(@NonNull View itemView) {
            super(itemView);
            ivUserProfile = itemView.findViewById(R.id.ivUserProfile);
            ivPostImage = itemView.findViewById(R.id.ivPostImage);
            tvUserName = itemView.findViewById(R.id.tvUserName);
            tvPostTime = itemView.findViewById(R.id.tvPostTime);
            tvPostContent = itemView.findViewById(R.id.tvPostContent);

            // Buttons and Icons
            btnEditPost = itemView.findViewById(R.id.btnEditPost);
            btnDeletePost = itemView.findViewById(R.id.btnDeletePost);
            btnViewPost = itemView.findViewById(R.id.btnViewPost);
            btnLike = itemView.findViewById(R.id.btnLike);
            btnComment = itemView.findViewById(R.id.btnComment);
        }
    }
}