package com.example.fypproject;

import android.content.Context;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.util.Log;
import android.widget.ImageView;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.List;
import java.util.Locale;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class EventsActivity extends AppCompatActivity {

    private RecyclerView rvJoinedEvents, rvAvailableEvents;
    private TextView tvJoinedEventsHeader;
    private EventAdapter joinedAdapter, availableAdapter;
    private List<Event> joinedEventsList = new ArrayList<>();
    private List<Event> availableEventsList = new ArrayList<>();
    private SharedPreferences sharedPreferences;
    private static final String TAG = "RUNNERS_DEBUG";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_events);

        // 1. Initialize UI
        rvJoinedEvents = findViewById(R.id.rv_joined_events);
        rvAvailableEvents = findViewById(R.id.rv_available_events);
        tvJoinedEventsHeader = findViewById(R.id.tv_joined_events_header);
        ImageView btnBack = findViewById(R.id.btn_back);

        // Standardizing to "UserPrefs"
        sharedPreferences = getSharedPreferences("UserPrefs", Context.MODE_PRIVATE);

        // 2. Setup Recycler Views
        if (rvJoinedEvents != null) rvJoinedEvents.setLayoutManager(new LinearLayoutManager(this));
        if (rvAvailableEvents != null) rvAvailableEvents.setLayoutManager(new LinearLayoutManager(this));

        EventAdapter.OnEventClickListener eventClickListener = new EventAdapter.OnEventClickListener() {
            @Override
            public void onJoinClick(Event event) {
                joinEventRequest(event.getId());
            }

            @Override
            public void onViewClick(Event event) {
                EventDetailDialog detailDialog = EventDetailDialog.newInstance(event);
                detailDialog.show(getSupportFragmentManager(), "EventDetail");
            }
        };

        joinedAdapter = new EventAdapter(joinedEventsList, eventClickListener);
        availableAdapter = new EventAdapter(availableEventsList, eventClickListener);

        if (rvJoinedEvents != null) rvJoinedEvents.setAdapter(joinedAdapter);
        if (rvAvailableEvents != null) rvAvailableEvents.setAdapter(availableAdapter);

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
                    List<Event> upcomingEvents = filterUpcomingEvents(fetchedEvents);
                    
                    joinedEventsList.clear();
                    availableEventsList.clear();
                    for (Event event : upcomingEvents) {
                        if (event.isJoined()) {
                            joinedEventsList.add(event);
                        } else {
                            availableEventsList.add(event);
                        }
                    }

                    if (joinedEventsList.isEmpty()) {
                        if (tvJoinedEventsHeader != null) tvJoinedEventsHeader.setVisibility(android.view.View.GONE);
                        if (rvJoinedEvents != null) rvJoinedEvents.setVisibility(android.view.View.GONE);
                    } else {
                        if (tvJoinedEventsHeader != null) tvJoinedEventsHeader.setVisibility(android.view.View.VISIBLE);
                        if (rvJoinedEvents != null) rvJoinedEvents.setVisibility(android.view.View.VISIBLE);
                    }

                    if (joinedAdapter != null) joinedAdapter.notifyDataSetChanged();
                    if (availableAdapter != null) availableAdapter.notifyDataSetChanged();

                    if (upcomingEvents.isEmpty()) {
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

    private List<Event> filterUpcomingEvents(List<Event> events) {
        List<Event> upcomingEvents = new ArrayList<>();
        SimpleDateFormat dateFormat = new SimpleDateFormat("yyyy-MM-dd", Locale.US);
        dateFormat.setLenient(false);

        String todayText = dateFormat.format(new Date());
        Date today;
        try {
            today = dateFormat.parse(todayText);
        } catch (ParseException e) {
            return events;
        }

        for (Event event : events) {
            String eventDateText = event.getDate();
            if (eventDateText == null || eventDateText.isEmpty()) {
                continue;
            }

            try {
                Date eventDate = dateFormat.parse(eventDateText);
                if (eventDate != null && !eventDate.before(today)) {
                    upcomingEvents.add(event);
                }
            } catch (ParseException e) {
                Log.w(TAG, "Skipping event with invalid date: " + eventDateText);
            }
        }

        return upcomingEvents;
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
                if (response.isSuccessful() && response.body() != null) {
                    JoinResponse jr = response.body();
                    if ("payment_required".equals(jr.getStatus()) && jr.getPaymentUrl() != null) {
                        Toast.makeText(EventsActivity.this, "Redirecting to Payment...", Toast.LENGTH_LONG).show();
                        // Open the payment URL in the browser
                        android.content.Intent browserIntent = new android.content.Intent(android.content.Intent.ACTION_VIEW, android.net.Uri.parse(jr.getPaymentUrl()));
                        startActivity(browserIntent);
                    } else {
                        Toast.makeText(EventsActivity.this, "🏃 Event Joined!", Toast.LENGTH_SHORT).show();
                        loadEvents(); // Refresh counts on the list
                    }
                } else {
                    String errorMsg = "Already joined or full.";
                    try {
                        if (response.errorBody() != null) {
                            String errJson = response.errorBody().string();
                            if (errJson.contains("\"message\"")) {
                                int start = errJson.indexOf("\"message\"") + 10;
                                int end = errJson.indexOf("\"", start + 1);
                                if (start > 9 && end > start) {
                                    errorMsg = errJson.substring(start + 1, end);
                                }
                            }
                        }
                    } catch (Exception e) {
                        Log.e(TAG, "Error parsing error body", e);
                    }
                    Toast.makeText(EventsActivity.this, errorMsg, Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(Call<JoinResponse> call, Throwable t) {
                Toast.makeText(EventsActivity.this, "Request failed.", Toast.LENGTH_SHORT).show();
            }
        });
    }
}
