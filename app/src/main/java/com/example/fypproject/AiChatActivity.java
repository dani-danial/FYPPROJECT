package com.example.fypproject;

import android.content.SharedPreferences;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.util.Log;
import android.view.View;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.ScrollView;
import android.widget.TextView;
import androidx.appcompat.app.AppCompatActivity;
import okhttp3.*;
import org.json.JSONException;
import org.json.JSONObject;
import java.io.IOException;
import java.util.concurrent.TimeUnit;

public class AiChatActivity extends AppCompatActivity {

    private EditText etQuery;
    private TextView tvResponse;
    private ImageView btnSend, btnBack;
    private ScrollView scrollView;

    // 🛠️ UPDATED: Now calling your Laravel Server instead of Google
    private static final String API_URL = "https://runtracker.fun/api/ai-coach";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_ai_chat);

        etQuery = findViewById(R.id.et_query);
        tvResponse = findViewById(R.id.tv_chat_response);
        btnSend = findViewById(R.id.btn_send_ai);
        btnBack = findViewById(R.id.btn_back);
        scrollView = findViewById(R.id.scroll_view);

        btnBack.setOnClickListener(v -> finish());

        btnSend.setOnClickListener(v -> {
            String question = etQuery.getText().toString().trim();
            if (!question.isEmpty()) {
                tvResponse.append("\n\n🧑‍💻 You: " + question);
                tvResponse.append("\n\n🤖 Coach: Thinking...");
                etQuery.setText("");
                scrollToBottom();

                callCoachAPI(question);
            }
        });
    }

    private void callCoachAPI(String question) {
        // 1. Get the Auth Token from SharedPreferences
        SharedPreferences prefs = getSharedPreferences("UserPrefs", MODE_PRIVATE);
        String token = prefs.getString("token", "");

        OkHttpClient client = new OkHttpClient.Builder()
                .connectTimeout(60, TimeUnit.SECONDS)
                .writeTimeout(60, TimeUnit.SECONDS)
                .readTimeout(60, TimeUnit.SECONDS)
                .build();

        // 2. Build the Simple Payload (Laravel handles the rest)
        JSONObject jsonBody = new JSONObject();
        try {
            jsonBody.put("message", question);
        } catch (JSONException e) {
            e.printStackTrace();
        }

        RequestBody body = RequestBody.create(
                jsonBody.toString(),
                MediaType.get("application/json; charset=utf-8")
        );

        // 3. Build Request with Bearer Token for Laravel Auth
        Request request = new Request.Builder()
                .url(API_URL)
                .addHeader("Authorization", "Bearer " + token) // Crucial for identifying the user
                .addHeader("Content-Type", "application/json")
                .post(body)
                .build();

        client.newCall(request).enqueue(new Callback() {
            @Override
            public void onFailure(Call call, IOException e) {
                runOnUiThread(() -> tvResponse.append("\n\n❌ Connection Error: " + e.getMessage()));
            }

            @Override
            public void onResponse(Call call, Response response) throws IOException {
                String respBody = response.body().string();
                if (response.isSuccessful()) {
                    try {
                        // 4. Parse Laravel's Response Format: {"reply": "..."}
                        JSONObject jsonObject = new JSONObject(respBody);
                        String text = jsonObject.getString("reply");

                        new Handler(Looper.getMainLooper()).post(() -> {
                            String currentText = tvResponse.getText().toString();
                            String newText = currentText.replace("\n\n🤖 Coach: Thinking...", "\n\n🤖 Coach: " + text);
                            tvResponse.setText(newText);
                            scrollToBottom();
                        });

                    } catch (Exception e) {
                        e.printStackTrace();
                        runOnUiThread(() -> tvResponse.append("\n\n❌ Parsing Error: " + e.getMessage()));
                    }
                } else {
                    Log.e("COACH_API_ERROR", "Code: " + response.code() + " Body: " + respBody);
                    runOnUiThread(() -> {
                        if (response.code() == 401) {
                            tvResponse.append("\n\n❌ Session expired. Please log in again.");
                        } else {
                            tvResponse.append("\n\n❌ Coach is busy (Error " + response.code() + ")");
                        }
                    });
                }
            }
        });
    }

    private void scrollToBottom() {
        scrollView.post(() -> scrollView.fullScroll(View.FOCUS_DOWN));
    }
}