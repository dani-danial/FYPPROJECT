package com.example.fypproject;

import com.google.gson.annotations.SerializedName;
import java.util.ArrayList;
import java.util.List;

public class PostModel {
    private int id;
    private String content;

    // The backend now sends a nested user object
    @SerializedName("user")
    private UserModel user;

    @SerializedName("image_url")
    private Object imageUrl;  // Laravel may send either a string or an array

    @SerializedName("created_at")
    private String createdAt;

    @SerializedName("author_name")
    private String authorName;

    @SerializedName("author_username")
    private String authorUsername;

    @SerializedName("user_image")
    private String userImage;

    @SerializedName("likes_count")
    private int likesCount;

    @SerializedName("comments_count")
    private int commentsCount;

    @SerializedName("liked_by_me")
    private boolean likedByMe;

    @SerializedName("user_id")
    private int userId;

    @SerializedName("comments")
    private List<CommentModel> comments;

    // --- Getters ---
    public int getId() { return id; }
    public String getContent() { return content; }

    // Use this to get the nested user object containing the profile picture
    public UserModel getUser() { return user; }

    public String getImageUrl() {
        if (imageUrl instanceof String) return (String) imageUrl;
        if (imageUrl instanceof List) {
            List<?> images = (List<?>) imageUrl;
            return images.isEmpty() ? null : String.valueOf(images.get(0));
        }
        return null;
    }

    public String getCreatedAt() { return createdAt; }
    public int getLikesCount() { return likesCount; }
    public int getCommentsCount() { return commentsCount; }
    public boolean isLikedByMe() { return likedByMe; }
    public int getUserId() { return userId; }
    public List<CommentModel> getComments() { return comments != null ? comments : new ArrayList<>(); }

    public void setLikesCount(int likesCount) { this.likesCount = likesCount; }
    public void setCommentsCount(int commentsCount) { this.commentsCount = commentsCount; }
    public void setLikedByMe(boolean likedByMe) { this.likedByMe = likedByMe; }
    public void addComment(CommentModel comment) {
        if (comments == null) comments = new ArrayList<>();
        comments.add(comment);
        commentsCount = comments.size();
    }

    // Fallback getters for legacy compatibility
    public String getUserName() {
        return (user != null) ? user.getName() : authorName;
    }

    public String getUserImage() {
        return (user != null) ? user.getProfilePhotoPath() : userImage;
    }

    public boolean isOwnedBy(String currentUserId) {
        try {
            return Integer.parseInt(currentUserId) == userId;
        } catch (Exception e) {
            return false;
        }
    }
}
