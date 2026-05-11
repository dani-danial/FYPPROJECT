package com.example.fypproject;

import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;
import android.widget.TextView;
import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import com.google.firebase.auth.FirebaseAuth;
import com.google.firebase.firestore.DocumentSnapshot;
import com.google.firebase.firestore.FirebaseFirestore;
import com.google.firebase.firestore.Query;
import java.util.ArrayList;
import java.util.List;

public class NotificationActivity extends AppCompatActivity {

    private RecyclerView rvNotifications;
    private NotificationAdapter adapter;
    private List<NotificationModel> notifList = new ArrayList<>();
    private FirebaseFirestore db;
    private String myID;
    private TextView tvEmpty;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_notifications);

        db = FirebaseFirestore.getInstance();
        myID = FirebaseAuth.getInstance().getUid();

        rvNotifications = findViewById(R.id.rv_notifications);
        tvEmpty = findViewById(R.id.tv_empty_state);
        ImageView btnBack = findViewById(R.id.btn_back);

        rvNotifications.setLayoutManager(new LinearLayoutManager(this));
        adapter = new NotificationAdapter(notifList);
        rvNotifications.setAdapter(adapter);

        btnBack.setOnClickListener(v -> finish()); // Close screen

        loadNotifications();
    }

    private void loadNotifications() {
        // 1. Get List of Following
        db.collection("users").document(myID).collection("following").get()
                .addOnSuccessListener(snap -> {
                    List<String> friendIds = new ArrayList<>();
                    for (DocumentSnapshot doc : snap) friendIds.add(doc.getId());

                    if (friendIds.isEmpty()) {
                        tvEmpty.setVisibility(View.VISIBLE);
                        return;
                    }

                    // 2. Fetch Recent Runs from Friends
                    db.collection("runs")
                            .whereIn("userId", friendIds)
                            .orderBy("date", Query.Direction.DESCENDING)
                            .limit(20)
                            .get()
                            .addOnSuccessListener(runSnap -> {
                                notifList.clear();
                                for (DocumentSnapshot doc : runSnap) {
                                    // We need to fetch the User Name for each run
                                    String userId = doc.getString("userId");
                                    Double dist = doc.getDouble("distance_km");
                                    String date = doc.getString("date");

                                    // Quick user fetch (Nested call - simplified for FYP)
                                    db.collection("users").document(userId).get().addOnSuccessListener(userDoc -> {
                                        String name = userDoc.getString("name");

                                        // Add to list
                                        notifList.add(new NotificationModel(
                                                "New Run Alert! 🏃‍♂️",
                                                name + " just completed a " + String.format("%.2f", dist) + "km run.",
                                                date,
                                                "run"
                                        ));

                                        adapter.notifyDataSetChanged();
                                        tvEmpty.setVisibility(View.GONE);
                                    });
                                }
                            });
                });

        // Note: You can add another query here for "Group Posts" and merge the lists!
    }
}