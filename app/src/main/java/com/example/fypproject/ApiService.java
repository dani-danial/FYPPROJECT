package com.example.fypproject;

import java.util.List;
import java.util.Map;
import okhttp3.MultipartBody;
import okhttp3.RequestBody;
import okhttp3.ResponseBody;
import retrofit2.Call;
import retrofit2.http.Body;
import retrofit2.http.DELETE;
import retrofit2.http.Field;
import retrofit2.http.FormUrlEncoded;
import retrofit2.http.GET;
import retrofit2.http.Header;
import retrofit2.http.Headers;
import retrofit2.http.Multipart;
import retrofit2.http.POST;
import retrofit2.http.Part;
import retrofit2.http.Path;
import retrofit2.http.PUT;
import retrofit2.http.Query;

public interface ApiService {

    @GET("api/posts/feed")
    Call<List<PostModel>> getHomeFeed(@Header("Authorization") String token);

    @Headers("Accept: application/json")
    @GET("api/posts/{id}")
    Call<PostModel> getPost(
            @Header("Authorization") String token,
            @Path("id") int postId
    );

    // ===========================================
    // 1. AUTHENTICATION
    // ===========================================

    @Headers("Accept: application/json")
    @POST("api/login")
    Call<LoginResponse> login(@Body LoginRequest request);

    @Headers("Accept: application/json")
    @POST("api/register")
    Call<LoginResponse> register(@Body RegisterRequest request);


    // ===========================================
    // 2. PROFILE & USERS
    // ===========================================

    @Headers("Accept: application/json")
    @GET("api/profile")
    Call<UserModel> getUserProfile(@Header("Authorization") String token);

    @Headers("Accept: application/json")
    @GET("api/users/search")
    Call<List<UserModel>> searchUsers(
            @Header("Authorization") String token,
            @Query("query") String query
    );

    @Headers("Accept: application/json")
    @GET("api/users/{id}")
    Call<UserModel> getUserProfileById(
            @Header("Authorization") String token,
            @Path("id") String userId
    );

    @Headers("Accept: application/json")
    @POST("api/users/{id}/follow")
    Call<ResponseBody> toggleFollow(
            @Header("Authorization") String token,
            @Path("id") String userId
    );

    // New: Fetch the list of users the current runner is following
    @Headers("Accept: application/json")
    @GET("api/following")
    Call<List<UserModel>> getFollowing(@Header("Authorization") String token);

    @FormUrlEncoded
    @Headers("Accept: application/json")
    @POST("api/profile")
    Call<UserModel> updateProfile(
            @Header("Authorization") String token,
            @Field("name") String name,
            @Field("username") String username,
            @Field("email") String email,
            @Field("about_me") String about,
            @Field("phone") String phone,
            @Field("weight_kg") String weight,
            @Field("height_cm") String height,
            @Field("base_pace_min_km") String pace
    );

    @Multipart
    @Headers("Accept: application/json")
    @POST("api/profile")
    Call<UserModel> updateProfileWithImage(
            @Header("Authorization") String token,
            @Part("name") RequestBody name,
            @Part("username") RequestBody username,
            @Part("email") RequestBody email,
            @Part("about_me") RequestBody about,
            @Part("phone") RequestBody phone,
            @Part("weight_kg") RequestBody weight,
            @Part("height_cm") RequestBody height,
            @Part("base_pace_min_km") RequestBody pace,
            @Part("remove_photo") RequestBody removePhoto,
            @Part MultipartBody.Part profile_picture
    );

    @Headers("Accept: application/json")
    @DELETE("api/user/delete")
    Call<Void> deleteAccount(@Header("Authorization") String token);


    // ===========================================
    // 3. GROUP FEATURES
    // ===========================================

    @Headers("Accept: application/json")
    @GET("api/groups")
    Call<List<GroupModel>> getGroups(@Header("Authorization") String token);

    @Headers("Accept: application/json")
    @GET("api/groups/{id}")
    Call<GroupModel> getGroupDetails(
            @Header("Authorization") String token,
            @Path("id") int groupId
    );

    @Headers("Accept: application/json")
    @GET("api/groups/{id}/leaderboard")
    Call<List<GroupStatsAdapter.MemberStat>> getGroupLeaderboard(
            @Header("Authorization") String token,
            @Path("id") int groupId
    );

    @Multipart
    @Headers("Accept: application/json")
    @POST("api/groups")
    Call<GroupModel> createGroup(
            @Header("Authorization") String token,
            @Part("name") RequestBody name,
            @Part("location") RequestBody location,
            @Part("description") RequestBody description,
            @Part("target_km") RequestBody targetKm,
            @Part("status") RequestBody status,
            @Part("creator_id") RequestBody creatorId,
            @Part MultipartBody.Part icon,
            @Part MultipartBody.Part banner
    );

    @POST("api/groups/join")
    @Headers("Accept: application/json")
    Call<JoinResponse> joinGroup(
            @Header("Authorization") String token,
            @Body JoinRequest request
    );

    @POST("api/groups/leave")
    @Headers("Accept: application/json")
    Call<JoinResponse> leaveGroup(
            @Header("Authorization") String token,
            @Body JoinRequest request
    );

    @Multipart
    @Headers("Accept: application/json")
    @POST("api/groups/{id}")
    Call<GroupModel> updateGroup(
            @Header("Authorization") String token,
            @Path("id") int groupId,
            @Part("name") RequestBody name,
            @Part("location") RequestBody location,
            @Part("description") RequestBody description,
            @Part("target_km") RequestBody targetKm,
            @Part MultipartBody.Part icon,
            @Part MultipartBody.Part banner
    );

    @Headers("Accept: application/json")
    @DELETE("api/groups/{id}")
    Call<ResponseBody> deleteGroup(
            @Header("Authorization") String token,
            @Path("id") int groupId
    );


    // ===========================================
    // 4. POSTS & SOCIAL
    // ===========================================

    @Headers("Accept: application/json")
    @GET("api/groups/{id}/posts")
    Call<List<PostModel>> getGroupPosts(
            @Header("Authorization") String token,
            @Path("id") int groupId
    );

    @Multipart
    @Headers("Accept: application/json")
    @POST("api/posts")
    Call<PostModel> createPost(
            @Header("Authorization") String token,
            @Part("group_id") RequestBody groupId,
            @Part("content") RequestBody content,
            @Part MultipartBody.Part media
    );

    @Headers("Accept: application/json")
    @DELETE("api/posts/{id}")
    Call<ResponseBody> deletePost(
            @Header("Authorization") String token,
            @Path("id") int postId
    );

    @Headers("Accept: application/json")
    @POST("api/posts/{id}/like")
    Call<PostInteractionResponse> togglePostLike(
            @Header("Authorization") String token,
            @Path("id") int postId
    );

    @FormUrlEncoded
    @Headers("Accept: application/json")
    @POST("api/posts/{id}/comments")
    Call<CommentModel> addPostComment(
            @Header("Authorization") String token,
            @Path("id") int postId,
            @Field("comment") String comment
    );


    // ===========================================
    // 5. RUN TRACKING & SOS
    // ===========================================

    @Headers("Accept: application/json")
    @POST("api/sos/send")
    Call<SosResponse> sendSosSignal(
            @Header("Authorization") String token,
            @Body SosRequest request
    );

    @Headers("Accept: application/json")
    @GET("api/runs")
    Call<List<RunData>> getRunHistory(@Header("Authorization") String token);

    @Multipart
    @Headers("Accept: application/json")
    @POST("api/runs")
    Call<SaveRunResponse> saveRun(
            @Header("Authorization") String token,
            @Part("distance_km") RequestBody distanceKm,
            @Part("duration_seconds") RequestBody durationSeconds,
            @Part("average_pace") RequestBody averagePace,
            @Part("route_path") RequestBody routePath,
            @Part("share_to_feed") RequestBody shareToFeed,
            @Part MultipartBody.Part image
    );

    @Headers("Accept: application/json")
    @GET("api/clear-coach-cache")
    Call<ResponseBody> clearCoachCache(
            @Header("Authorization") String token
    );


    // ===========================================
    // 6. EVENT FEATURES
    // ===========================================

    @Headers("Accept: application/json")
    @GET("api/events")
    Call<List<Event>> getEvents(@Header("Authorization") String token);

    @Headers("Accept: application/json")
    @POST("api/events/join")
    Call<JoinResponse> joinEvent(
            @Header("Authorization") String token,
            @Body EventJoinRequest request
    );

    @Headers("Accept: application/json")
    @POST("api/classify-runner")
    Call<CategoryResponse> classifyRunner(
            @Header("Authorization") String token,
            @Body PreferenceRequest request
    );

    // ===========================================
    // 7. CHAT FEATURES
    // ===========================================

    @Headers("Accept: application/json")
    @POST("api/chat/start/{userId}")
    Call<ConversationModel> startConversation(
            @Header("Authorization") String token,
            @Path("userId") int userId
    );

    @Headers("Accept: application/json")
    @GET("api/conversations/{id}/messages")
    Call<List<Message>> getMessages(
            @Header("Authorization") String token,
            @Path("id") int conversationId
    );

    @Headers("Accept: application/json")
    @POST("api/conversations/{id}/messages")
    Call<Message> sendMessage(
            @Header("Authorization") String token,
            @Path("id") int conversationId,
            @Body SendMessageRequest request
    );

    @Headers("Accept: application/json")
    @GET("api/conversations")
    Call<List<ConversationModel>> getConversations(
            @Header("Authorization") String token
    );

    @Headers("Accept: application/json")
    @GET("api/groups/{id}/messages")
    Call<List<Message>> getGroupMessages(
            @Header("Authorization") String token,
            @Path("id") int groupId
    );

    @Headers("Accept: application/json")
    @POST("api/groups/{id}/messages")
    Call<Message> sendGroupMessage(
            @Header("Authorization") String token,
            @Path("id") int groupId,
            @Body SendMessageRequest request
    );
}