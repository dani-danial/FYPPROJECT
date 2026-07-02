package com.example.fypproject;

import android.net.Uri;
import android.os.Bundle;
import android.view.View;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.TextView;
import android.widget.Toast;

import androidx.activity.result.ActivityResultLauncher;
import androidx.activity.result.contract.ActivityResultContracts;
import androidx.appcompat.app.AppCompatActivity;

import com.google.android.material.button.MaterialButton;

import java.io.File;
import java.io.FileOutputStream;
import java.io.InputStream;

import okhttp3.MediaType;
import okhttp3.MultipartBody;
import okhttp3.RequestBody;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class CreatePostActivity extends AppCompatActivity {
    private EditText etPostContent;
    private ImageView ivSelectedPostImage;
    private MaterialButton btnSharePost;
    private Uri selectedImageUri;

    private final ActivityResultLauncher<String> pickPostImageLauncher = registerForActivityResult(
            new ActivityResultContracts.GetContent(),
            uri -> {
                if (uri != null) {
                    selectedImageUri = uri;
                    ivSelectedPostImage.setImageURI(uri);
                    ivSelectedPostImage.setVisibility(View.VISIBLE);
                }
            }
    );

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_create_post);

        etPostContent = findViewById(R.id.et_post_content);
        ivSelectedPostImage = findViewById(R.id.iv_selected_post_image);
        btnSharePost = findViewById(R.id.btn_share_post);
        MaterialButton btnPickImage = findViewById(R.id.btn_pick_post_image);
        TextView tvBack = findViewById(R.id.tv_back_create_post);

        tvBack.setOnClickListener(v -> finish());
        btnPickImage.setOnClickListener(v -> pickPostImageLauncher.launch("image/*"));
        btnSharePost.setOnClickListener(v -> submitPost());
    }

    private void submitPost() {
        String content = etPostContent.getText().toString().trim();
        if (content.isEmpty()) {
            etPostContent.setError("Write something first");
            return;
        }

        btnSharePost.setEnabled(false);
        RequestBody groupId = RequestBody.create(MediaType.parse("text/plain"), "");
        RequestBody contentBody = RequestBody.create(MediaType.parse("text/plain"), content);
        MultipartBody.Part imagePart = selectedImageUri != null ? prepareImagePart(selectedImageUri) : null;

        RetrofitClient.getService().createPost(getBearerToken(), groupId, contentBody, imagePart).enqueue(new Callback<PostModel>() {
            @Override
            public void onResponse(Call<PostModel> call, Response<PostModel> response) {
                btnSharePost.setEnabled(true);
                if (response.isSuccessful()) {
                    Toast.makeText(CreatePostActivity.this, "Post shared", Toast.LENGTH_SHORT).show();
                    finish();
                } else {
                    Toast.makeText(CreatePostActivity.this, "Could not share post", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(Call<PostModel> call, Throwable t) {
                btnSharePost.setEnabled(true);
                Toast.makeText(CreatePostActivity.this, "Network error sharing post", Toast.LENGTH_SHORT).show();
            }
        });
    }

    private MultipartBody.Part prepareImagePart(Uri uri) {
        try {
            File file = new File(getCacheDir(), "post_upload.jpg");
            InputStream inputStream = getContentResolver().openInputStream(uri);
            FileOutputStream outputStream = new FileOutputStream(file);
            byte[] buffer = new byte[4096];
            int read;
            while ((read = inputStream.read(buffer)) != -1) {
                outputStream.write(buffer, 0, read);
            }
            outputStream.close();
            inputStream.close();

            String type = getContentResolver().getType(uri);
            RequestBody requestFile = RequestBody.create(MediaType.parse(type != null ? type : "image/jpeg"), file);
            return MultipartBody.Part.createFormData("media[]", file.getName(), requestFile);
        } catch (Exception e) {
            return null;
        }
    }

    private String getBearerToken() {
        return "Bearer " + getSharedPreferences("UserPrefs", MODE_PRIVATE).getString("token", "");
    }
}
