package com.example.fypproject;

import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;
import android.widget.ProgressBar;
import android.widget.TextView;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import com.google.android.gms.tasks.Task;
import com.google.android.gms.tasks.Tasks;
import com.google.firebase.firestore.DocumentSnapshot;
import com.google.firebase.firestore.FirebaseFirestore;
import com.google.firebase.firestore.QuerySnapshot;
import java.util.ArrayList;
import java.util.Collections;
import java.util.List;

public class GroupStatsActivity extends AppCompatActivity {

    private String groupId;
    private RecyclerView rvStats;
    private ProgressBar pbLoading;
    private FirebaseFirestore db;
    private List<GroupStatsAdapter.MemberStat> memberStatsList = new ArrayList<>();
    private int membersProcessed = 0;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_group_stats);

        groupId = getIntent().getStringExtra("GROUP_ID");
        String groupName = getIntent().getStringExtra("GROUP_NAME");

        TextView tvTitle = findViewById(R.id.tv_stats_title);
        if (groupName != null) tvTitle.setText(groupName + " Leaderboard");

        ImageView btnBack = findViewById(R.id.btn_back);
        btnBack.setOnClickListener(v -> finish());

        rvStats = findViewById(R.id.rv_stats);
        rvStats.setLayoutManager(new LinearLayoutManager(this));
        pbLoading = findViewById(R.id.pb_loading);

        db = FirebaseFirestore.getInstance();
        fetchGroupMembers();
    }

    private void fetchGroupMembers() {
        db.collection("groups").document(groupId).get().addOnSuccessListener(doc -> {
            if (doc.exists()) {
                List<String> memberIds = (List<String>) doc.get("members");
                if (memberIds == null || memberIds.isEmpty()) {
                    pbLoading.setVisibility(View.GONE);
                    Toast.makeText(this, "No members found", Toast.LENGTH_SHORT).show();
                    return;
                }
                processMembers(memberIds);
            }
        }).addOnFailureListener(e -> pbLoading.setVisibility(View.GONE));
    }

    private void processMembers(List<String> memberIds) {
        int totalMembers = memberIds.size();
        for (String memberId : memberIds) {
            GroupStatsAdapter.MemberStat stat = new GroupStatsAdapter.MemberStat();
            stat.userId = memberId;
            memberStatsList.add(stat);

            Task<DocumentSnapshot> userTask = db.collection("users").document(memberId).get();
            Task<QuerySnapshot> runsTask = db.collection("runs").whereEqualTo("userId", memberId).get();

            Tasks.whenAllSuccess(userTask, runsTask).addOnSuccessListener(results -> {
                DocumentSnapshot userDoc = (DocumentSnapshot) results.get(0);
                stat.userName = userDoc.getString("name");
                stat.userImageBase64 = userDoc.getString("imageBase64");

                QuerySnapshot runsSnapshot = (QuerySnapshot) results.get(1);
                for (DocumentSnapshot runDoc : runsSnapshot.getDocuments()) {
                    Double dist = runDoc.getDouble("distance_km");
                    if (dist != null) stat.totalDistance += dist;
                    stat.totalRuns++;
                }

                membersProcessed++;
                if (membersProcessed == totalMembers) displayStats();
            }).addOnFailureListener(e -> {
                membersProcessed++;
                if (membersProcessed == totalMembers) displayStats();
            });
        }
    }

    private void displayStats() {
        pbLoading.setVisibility(View.GONE);
        rvStats.setVisibility(View.VISIBLE);
        Collections.sort(memberStatsList);
        GroupStatsAdapter adapter = new GroupStatsAdapter(memberStatsList);
        rvStats.setAdapter(adapter);
    }
}