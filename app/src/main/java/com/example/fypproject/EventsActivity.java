package com.example.fypproject;

import android.content.Context;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.util.Log;
import android.widget.ImageView;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import java.util.ArrayList;
import java.util.List;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class EventsActivity extends AppCompatActivity {

    private RecyclerView rvEvents;
    private EventAdapter adapter;
    private List<Event> eventList;
    private SharedPreferences sharedPreferences;
    private static final String TAG = "RUNNERS_DEBUG";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_events);

        // 1. Initialize UI
        rvEvents = findViewById(R.id.rv_events);
        ImageView btnBack = findViewById(R.id.btn_back);

        // Standardizing to "UserPrefs"
        sharedPreferences = getSharedPreferences("UserPrefs", Context.MODE_PRIVATE);
        eventList = new ArrayList<>();

        // 2. Setup Recycler View
        rvEvents.setLayoutManager(new LinearLayoutManager(this));

        // 🛠️ FIXED: Implementation of onViewClick to trigger the Dialog
        adapter = new EventAdapter(eventList, new EventAdapter.OnEventClickListener() {
            @Override
            public void onJoinClick(Event event) {
                joinEventRequest(event.getId());
            }

            @Override
            public void onViewClick(Event event) {
                // 🆕 Safe way to show the detail popup
                // Using newInstance ensures that if the screen rotates, the app won't crash
                EventDetailDialog detailDialog = EventDetailDialog.newInstance(event);
                detailDialog.show(getSupportFragmentManager(), "EventDetail");
            }
        });

        rvEvents.setAdapter(adapter);

        // 3. Navigation
        if (btnBack != null) {
            btnBack.setOnClickListener(v -> finish());
        }

        // 4. Load Data
        loadEvents();
    }

    private void loadEvents() {
        String token = sharedPreferences.getString("token", "");
        Log.d(TAG, "Token Check: " + (token.isEmpty() ? "EMPTY" : "EXISTS"));

        if (token.isEmpty()) {
            Toast.makeText(this, "Session expired. Please login again.", Toast.LENGTH_LONG).show();
            finish();
            return;
        }

        String authHeader = "Bearer " + token;

        RetrofitClient.getService().getEvents(authHeader).enqueue(new Callback<List<Event>>() {
            @Override
            public void onResponse(Call<List<Event>> call, Response<List<Event>> response) {
                if (response.isSuccessful() && response.body() != null) {
                    List<Event> fetchedEvents = response.body();
                    eventList.clear();
                    eventList.addAll(fetchedEvents);
                    adapter.notifyDataSetChanged();

                    if (fetchedEvents.isEmpty()) {
                        Toast.makeText(EventsActivity.this, "No upcoming events found.", Toast.LENGTH_SHORT).show();
                    }
                } else {
                    Log.e(TAG, "Server Error: " + response.code());
                    Toast.makeText(EventsActivity.this, "Failed to load events: " + response.code(), Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(Call<List<Event>> call, Throwable t) {
                Log.e(TAG, "Network Error: " + t.getMessage());
                Toast.makeText(EventsActivity.this, "Connection Error.", Toast.LENGTH_LONG).show();
            }
        });
    }

    private void joinEventRequest(int eventId) {
        String token = sharedPreferences.getString("token", "");

        // 🛠️ SAFE CHECK: Fetching userId as a String first to avoid ClassCastException
        String userIdStr = sharedPreferences.getString("userId", "0");
        int userId = Integer.parseInt(userIdStr);

        if (userId == 0) {
            Toast.makeText(this, "User identity error. Re-login required.", Toast.LENGTH_SHORT).show();
            return;
        }

        String authHeader = "Bearer " + token;
        EventJoinRequest request = new EventJoinRequest(userId, eventId);

        RetrofitClient.getService().joinEvent(authHeader, request).enqueue(new Callback<JoinResponse>() {
            @Override
            public void onResponse(Call<JoinResponse> call, Response<JoinResponse> response) {
                if (response.isSuccessful()) {
                    Toast.makeText(EventsActivity.this, "🏃 Event Joined!", Toast.LENGTH_SHORT).show();
                    loadEvents(); // Refresh counts on the list
                } else {
                    Toast.makeText(EventsActivity.this, "Already joined or full.", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(Call<JoinResponse> call, Throwable t) {
                Toast.makeText(EventsActivity.this, "Request failed.", Toast.LENGTH_SHORT).show();
            }
        });
    }
}