package com.example.fypproject;

import android.content.SharedPreferences;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.ImageView;
import android.widget.ProgressBar;
import android.widget.TextView;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import java.util.ArrayList;
import java.util.List;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class GroupStatsActivity extends AppCompatActivity {

    private static final String TAG = "GroupStatsActivity";
    private int groupId;
    private RecyclerView rvStats;
    private ProgressBar pbLoading;
    private List<GroupStatsAdapter.MemberStat> memberStatsList = new ArrayList<>();
    private GroupStatsAdapter adapter;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_group_stats);

        String groupIdStr = getIntent().getStringExtra("GROUP_ID");
        try {
            groupId = Integer.parseInt(groupIdStr);
        } catch (NumberFormatException e) {
            groupId = -1;
        }
        
        String groupName = getIntent().getStringExtra("GROUP_NAME");

        TextView tvTitle = findViewById(R.id.tv_stats_title);
        if (groupName != null) tvTitle.setText(groupName + " Leaderboard");

        ImageView btnBack = findViewById(R.id.btn_back);
        btnBack.setOnClickListener(v -> finish());

        rvStats = findViewById(R.id.rv_stats);
        rvStats.setLayoutManager(new LinearLayoutManager(this));
        pbLoading = findViewById(R.id.pb_loading);

        if (groupId == -1) {
            pbLoading.setVisibility(View.GONE);
            Toast.makeText(this, "Invalid group ID", Toast.LENGTH_SHORT).show();
            return;
        }

        fetchGroupLeaderboard();
    }

    private void fetchGroupLeaderboard() {
        String token = "Bearer " + getSavedToken();
        pbLoading.setVisibility(View.VISIBLE);
        rvStats.setVisibility(View.GONE);

        RetrofitClient.getService().getGroupLeaderboard(token, groupId)
                .enqueue(new Callback<List<GroupStatsAdapter.MemberStat>>() {
                    @Override
                    public void onResponse(Call<List<GroupStatsAdapter.MemberStat>> call, Response<List<GroupStatsAdapter.MemberStat>> response) {
                        if (isFinishing() || isDestroyed()) return;
                        pbLoading.setVisibility(View.GONE);
                        if (response.isSuccessful() && response.body() != null) {
                            memberStatsList.clear();
                            memberStatsList.addAll(response.body());
                            displayStats();
                        } else {
                            Log.e(TAG, "Failed to load leaderboard. HTTP " + response.code());
                            Toast.makeText(GroupStatsActivity.this, "Failed to load leaderboard", Toast.LENGTH_SHORT).show();
                        }
                    }

                    @Override
                    public void onFailure(Call<List<GroupStatsAdapter.MemberStat>> call, Throwable t) {
                        if (isFinishing() || isDestroyed()) return;
                        pbLoading.setVisibility(View.GONE);
                        Log.e(TAG, "Network error fetching leaderboard", t);
                        Toast.makeText(GroupStatsActivity.this, "Network error loading leaderboard", Toast.LENGTH_SHORT).show();
                    }
                });
    }

    private void displayStats() {
        rvStats.setVisibility(View.VISIBLE);
        adapter = new GroupStatsAdapter(memberStatsList);
        rvStats.setAdapter(adapter);
    }

    private String getSavedToken() {
        SharedPreferences prefs = getSharedPreferences("UserPrefs", MODE_PRIVATE);
        return prefs.getString("token", "");
    }
}