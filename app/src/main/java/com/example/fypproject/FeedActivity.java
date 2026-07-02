package com.example.fypproject;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.ProgressBar;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.google.android.material.floatingactionbutton.FloatingActionButton;

import java.util.ArrayList;
import java.util.List;

import okhttp3.ResponseBody;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class FeedActivity extends AppCompatActivity implements PostAdapter.OnPostActionListener {

    private RecyclerView rvFeed;
    private ProgressBar progressFeed;
    private TextView tvEmptyFeed;
    private PostAdapter postAdapter;
    private final List<PostModel> posts = new ArrayList<>();

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_feed);

        rvFeed = findViewById(R.id.rv_feed);
        progressFeed = findViewById(R.id.progress_feed);
        tvEmptyFeed = findViewById(R.id.tv_empty_feed);
        FloatingActionButton fabCreatePost = findViewById(R.id.fab_create_post);
        TextView tvBack = findViewById(R.id.tv_back_feed);

        postAdapter = new PostAdapter(posts, getUserId(), this);
        rvFeed.setLayoutManager(new LinearLayoutManager(this));
        rvFeed.setAdapter(postAdapter);

        tvBack.setOnClickListener(v -> finish());
        fabCreatePost.setOnClickListener(v -> startActivity(new Intent(this, CreatePostActivity.class)));
    }

    @Override
    protected void onResume() {
        super.onResume();
        loadFeed();
    }

    private void loadFeed() {
        progressFeed.setVisibility(View.VISIBLE);
        tvEmptyFeed.setVisibility(View.GONE);

        RetrofitClient.getService().getHomeFeed(getBearerToken()).enqueue(new Callback<List<PostModel>>() {
            @Override
            public void onResponse(Call<List<PostModel>> call, Response<List<PostModel>> response) {
                progressFeed.setVisibility(View.GONE);
                if (response.isSuccessful() && response.body() != null) {
                    posts.clear();
                    posts.addAll(response.body());
                    postAdapter.notifyDataSetChanged();
                    tvEmptyFeed.setVisibility(posts.isEmpty() ? View.VISIBLE : View.GONE);
                } else {
                    Toast.makeText(FeedActivity.this, "Unable to load posts", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(Call<List<PostModel>> call, Throwable t) {
                progressFeed.setVisibility(View.GONE);
                Toast.makeText(FeedActivity.this, "Network error loading posts", Toast.LENGTH_SHORT).show();
            }
        });
    }

    @Override
    public void onViewPost(PostModel post) {
        openPost(post);
    }

    @Override
    public void onLikePost(PostModel post, int position) {
        RetrofitClient.getService().togglePostLike(getBearerToken(), post.getId()).enqueue(new Callback<PostInteractionResponse>() {
            @Override
            public void onResponse(Call<PostInteractionResponse> call, Response<PostInteractionResponse> response) {
                if (response.isSuccessful() && response.body() != null && position >= 0 && position < posts.size()) {
                    PostInteractionResponse body = response.body();
                    post.setLikedByMe(body.isLiked());
                    post.setLikesCount(body.getCount());
                    postAdapter.notifyItemChanged(position);
                }
            }

            @Override
            public void onFailure(Call<PostInteractionResponse> call, Throwable t) {
                Toast.makeText(FeedActivity.this, "Could not update like", Toast.LENGTH_SHORT).show();
            }
        });
    }

    @Override
    public void onCommentPost(PostModel post) {
        openPost(post);
    }

    @Override
    public void onDeletePost(PostModel post, int position) {
        new AlertDialog.Builder(this)
                .setTitle("Delete post?")
                .setMessage("This will remove the post from the feed.")
                .setNegativeButton("Cancel", null)
                .setPositiveButton("Delete", (dialog, which) -> deletePost(post, position))
                .show();
    }

    private void deletePost(PostModel post, int position) {
        RetrofitClient.getService().deletePost(getBearerToken(), post.getId()).enqueue(new Callback<ResponseBody>() {
            @Override
            public void onResponse(Call<ResponseBody> call, Response<ResponseBody> response) {
                if (response.isSuccessful()) {
                    postAdapter.removePostAt(position);
                    tvEmptyFeed.setVisibility(posts.isEmpty() ? View.VISIBLE : View.GONE);
                    Toast.makeText(FeedActivity.this, "Post deleted", Toast.LENGTH_SHORT).show();
                } else {
                    Toast.makeText(FeedActivity.this, "You can only delete your own posts", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(Call<ResponseBody> call, Throwable t) {
                Toast.makeText(FeedActivity.this, "Could not delete post", Toast.LENGTH_SHORT).show();
            }
        });
    }

    private void openPost(PostModel post) {
        Intent intent = new Intent(this, PostDetailActivity.class);
        intent.putExtra("post_id", post.getId());
        startActivity(intent);
    }

    private String getBearerToken() {
        return "Bearer " + getSharedPreferences("UserPrefs", MODE_PRIVATE).getString("token", "");
    }

    private String getUserId() {
        return getSharedPreferences("UserPrefs", MODE_PRIVATE).getString("userId", "0");
    }
}
