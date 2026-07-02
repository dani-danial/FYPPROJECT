package com.example.fypproject;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.bumptech.glide.Glide;

import java.util.List;

public class CommentAdapter extends RecyclerView.Adapter<CommentAdapter.CommentViewHolder> {
    private final List<CommentModel> comments;

    public CommentAdapter(List<CommentModel> comments) {
        this.comments = comments;
    }

    @NonNull
    @Override
    public CommentViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_comment, parent, false);
        return new CommentViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull CommentViewHolder holder, int position) {
        CommentModel comment = comments.get(position);
        holder.tvCommentUser.setText(comment.getUserName());
        holder.tvCommentBody.setText(comment.getBody());
        Glide.with(holder.itemView.getContext())
                .load(comment.getUserImage())
                .placeholder(android.R.drawable.sym_def_app_icon)
                .circleCrop()
                .into(holder.ivCommentUser);
    }

    @Override
    public int getItemCount() {
        return comments.size();
    }

    static class CommentViewHolder extends RecyclerView.ViewHolder {
        ImageView ivCommentUser;
        TextView tvCommentUser, tvCommentBody;

        CommentViewHolder(@NonNull View itemView) {
            super(itemView);
            ivCommentUser = itemView.findViewById(R.id.iv_comment_user);
            tvCommentUser = itemView.findViewById(R.id.tv_comment_user);
            tvCommentBody = itemView.findViewById(R.id.tv_comment_body);
        }
    }
}
