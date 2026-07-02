package com.example.fypproject;

import android.os.Bundle;
import android.view.View;
import android.widget.EditText;
import android.widget.ImageButton;
import android.widget.ImageView;
import android.widget.ProgressBar;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.bumptech.glide.Glide;
import com.google.android.material.button.MaterialButton;

import java.util.ArrayList;
import java.util.List;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class PostDetailActivity extends AppCompatActivity {

    private int postId;
    private PostModel currentPost;
    private ImageView ivPostImage, ivPostUser;
    private TextView tvPostUser, tvPostTime, tvPostContent, tvNoComments;
    private MaterialButton btnLike;
    private EditText etComment;
    private ProgressBar progressPost;
    private CommentAdapter commentAdapter;
    private final List<CommentModel> comments = new ArrayList<>();

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_post_detail);

        postId = getIntent().getIntExtra("post_id", 0);
        ivPostImage = findViewById(R.id.iv_detail_post_image);
        ivPostUser = findViewById(R.id.iv_detail_user);
        tvPostUser = findViewById(R.id.tv_detail_user);
        tvPostTime = findViewById(R.id.tv_detail_time);
        tvPostContent = findViewById(R.id.tv_detail_content);
        tvNoComments = findViewById(R.id.tv_no_comments);
        btnLike = findViewById(R.id.btn_detail_like);
        etComment = findViewById(R.id.et_comment);
        progressPost = findViewById(R.id.progress_post);
        ImageButton btnSendComment = findViewById(R.id.btn_send_comment);
        TextView tvBack = findViewById(R.id.tv_back_detail);
        RecyclerView rvComments = findViewById(R.id.rv_comments);

        commentAdapter = new CommentAdapter(comments);
        rvComments.setLayoutManager(new LinearLayoutManager(this));
        rvComments.setAdapter(commentAdapter);

        tvBack.setOnClickListener(v -> finish());
        btnLike.setOnClickListener(v -> toggleLike());
        btnSendComment.setOnClickListener(v -> sendComment());

        loadPost();
    }

    private void loadPost() {
        if (postId == 0) {
            finish();
            return;
        }

        progressPost.setVisibility(View.VISIBLE);
        RetrofitClient.getService().getPost(getBearerToken(), postId).enqueue(new Callback<PostModel>() {
            @Override
            public void onResponse(Call<PostModel> call, Response<PostModel> response) {
                progressPost.setVisibility(View.GONE);
                if (response.isSuccessful() && response.body() != null) {
                    currentPost = response.body();
                    bindPost();
                } else {
                    Toast.makeText(PostDetailActivity.this, "Post not found", Toast.LENGTH_SHORT).show();
                    finish();
                }
            }

            @Override
            public void onFailure(Call<PostModel> call, Throwable t) {
                progressPost.setVisibility(View.GONE);
                Toast.makeText(PostDetailActivity.this, "Unable to load post", Toast.LENGTH_SHORT).show();
            }
        });
    }

    private void bindPost() {
        tvPostUser.setText(currentPost.getUserName());
        tvPostTime.setText(currentPost.getCreatedAt() != null ? currentPost.getCreatedAt() : "");
        tvPostContent.setText(currentPost.getContent());
        btnLike.setText(currentPost.getLikesCount() + (currentPost.getLikesCount() == 1 ? " Like" : " Likes"));

        Glide.with(this)
                .load(currentPost.getUserImage())
                .placeholder(android.R.drawable.sym_def_app_icon)
                .circleCrop()
                .into(ivPostUser);

        if (currentPost.getImageUrl() == null || currentPost.getImageUrl().isEmpty()) {
            ivPostImage.setVisibility(View.GONE);
        } else {
            ivPostImage.setVisibility(View.VISIBLE);
            Glide.with(this).load(currentPost.getImageUrl()).centerCrop().into(ivPostImage);
        }

        comments.clear();
        comments.addAll(currentPost.getComments());
        commentAdapter.notifyDataSetChanged();
        tvNoComments.setVisibility(comments.isEmpty() ? View.VISIBLE : View.GONE);
    }

    private void toggleLike() {
        if (currentPost == null) return;
        RetrofitClient.getService().togglePostLike(getBearerToken(), currentPost.getId()).enqueue(new Callback<PostInteractionResponse>() {
            @Override
            public void onResponse(Call<PostInteractionResponse> call, Response<PostInteractionResponse> response) {
                if (response.isSuccessful() && response.body() != null) {
                    currentPost.setLikedByMe(response.body().isLiked());
                    currentPost.setLikesCount(response.body().getCount());
                    btnLike.setText(currentPost.getLikesCount() + (currentPost.getLikesCount() == 1 ? " Like" : " Likes"));
                }
            }

            @Override
            public void onFailure(Call<PostInteractionResponse> call, Throwable t) {
                Toast.makeText(PostDetailActivity.this, "Could not update like", Toast.LENGTH_SHORT).show();
            }
        });
    }

    private void sendComment() {
        String comment = etComment.getText().toString().trim();
        if (comment.isEmpty() || currentPost == null) return;

        RetrofitClient.getService().addPostComment(getBearerToken(), currentPost.getId(), comment).enqueue(new Callback<CommentModel>() {
            @Override
            public void onResponse(Call<CommentModel> call, Response<CommentModel> response) {
                if (response.isSuccessful() && response.body() != null) {
                    comments.add(response.body());
                    currentPost.setCommentsCount(comments.size());
                    commentAdapter.notifyItemInserted(comments.size() - 1);
                    tvNoComments.setVisibility(View.GONE);
                    etComment.setText("");
                } else {
                    Toast.makeText(PostDetailActivity.this, "Could not add comment", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(Call<CommentModel> call, Throwable t) {
                Toast.makeText(PostDetailActivity.this, "Could not add comment", Toast.LENGTH_SHORT).show();
            }
        });
    }

    private String getBearerToken() {
        return "Bearer " + getSharedPreferences("UserPrefs", MODE_PRIVATE).getString("token", "");
    }
}
