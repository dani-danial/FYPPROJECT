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
    private String currentUserId;
    private OnPostActionListener listener;

    public interface OnPostActionListener {
        void onViewPost(PostModel post);
        void onLikePost(PostModel post, int position);
        void onCommentPost(PostModel post);
        void onDeletePost(PostModel post, int position);
    }

    public PostAdapter(List<PostModel> postList) {
        this.postList = postList;
    }

    public PostAdapter(List<PostModel> postList, String currentUserId, OnPostActionListener listener) {
        this.postList = postList;
        this.currentUserId = currentUserId;
        this.listener = listener;
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

        holder.btnLike.setText(post.getLikesCount() + (post.getLikesCount() == 1 ? " Like" : " Likes"));
        holder.btnComment.setText(post.getCommentsCount() + (post.getCommentsCount() == 1 ? " Comment" : " Comments"));
        holder.btnLike.setTextColor(holder.itemView.getContext().getColor(post.isLikedByMe() ? android.R.color.holo_red_light : android.R.color.holo_red_dark));

        boolean canDelete = currentUserId != null && post.isOwnedBy(currentUserId);
        holder.btnEditPost.setVisibility(View.GONE);
        holder.btnDeletePost.setVisibility(canDelete ? View.VISIBLE : View.GONE);

        holder.btnViewPost.setOnClickListener(v -> {
            if (listener != null) listener.onViewPost(post);
        });
        holder.btnComment.setOnClickListener(v -> {
            if (listener != null) listener.onCommentPost(post);
        });
        holder.btnLike.setOnClickListener(v -> {
            if (listener != null) listener.onLikePost(post, holder.getAdapterPosition());
        });
        holder.btnDeletePost.setOnClickListener(v -> {
            if (listener != null) listener.onDeletePost(post, holder.getAdapterPosition());
        });
    }

    @Override
    public int getItemCount() {
        return postList.size();
    }

    public void updatePosts(List<PostModel> posts) {
        this.postList = posts;
        notifyDataSetChanged();
    }

    public void removePostAt(int position) {
        if (position < 0 || position >= postList.size()) return;
        postList.remove(position);
        notifyItemRemoved(position);
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
