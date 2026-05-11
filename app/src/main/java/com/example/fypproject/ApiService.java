package com.example.fypproject;

import java.util.List;
import okhttp3.MultipartBody;
import okhttp3.RequestBody;
import okhttp3.ResponseBody; // 🛠️ Added for deleteGroup response
import retrofit2.Call;
import retrofit2.http.Body;
import retrofit2.http.DELETE; // 🛠️ Added for deleteGroup
import retrofit2.http.Field;
import retrofit2.http.FormUrlEncoded;
import retrofit2.http.GET;
import retrofit2.http.Header;
import retrofit2.http.Headers;
import retrofit2.http.Multipart;
import retrofit2.http.POST;
import retrofit2.http.Part;
import retrofit2.http.Path;

public interface ApiService {


    @GET("api/posts/feed") // Replace with your actual server endpoint
    Call<List<PostModel>> getHomeFeed(@Header("Authorization") String token);
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
    // 2. PROFILE FEATURES (🍫 Full Web-to-Mobile Sync)
    // ===========================================

    @Headers("Accept: application/json")
    @GET("api/profile")
    Call<UserModel> getUserProfile(@Header("Authorization") String token);

    /**
     * Standard Profile Update (No Image)
     */
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

    /**
     * Full Profile Update with Image Sync
     */
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

    /**
     * 🛠️ DELETE GROUP (🍫 Added for Deletion Sync)
     * This matches the destroy method in your Laravel GroupController
     */
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
            @Part MultipartBody.Part image
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

    @Headers("Accept: application/json")
    @POST("api/runs")
    Call<RunData> saveRun(
            @Header("Authorization") String token,
            @Body RunData runData
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
    @Headers("Accept: application/json") // 🛠️ CRITICAL: Forces Laravel to treat this as an API call
    @POST("api/classify-runner") // Kept "api/" as per your preference
    Call<CategoryResponse> classifyRunner(
            @Header("Authorization") String token,
            @Body PreferenceRequest request
    );



}