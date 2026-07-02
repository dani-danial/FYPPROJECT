package com.example.fypproject;

import com.google.gson.annotations.SerializedName;

public class PostInteractionResponse {
    private boolean liked;
    private int count;

    @SerializedName("comments_count")
    private int commentsCount;

    public boolean isLiked() {
        return liked;
    }

    public int getCount() {
        return count;
    }

    public int getCommentsCount() {
        return commentsCount;
    }
}
