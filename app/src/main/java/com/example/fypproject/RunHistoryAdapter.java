package com.example.fypproject;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import java.util.List;

public class RunHistoryAdapter extends RecyclerView.Adapter<RunHistoryAdapter.RunViewHolder> {

    // CHANGED: List<RunData> instead of QueryDocumentSnapshot
    private List<RunData> runList;

    public RunHistoryAdapter(List<RunData> runList) {
        this.runList = runList;
    }

    @NonNull
    @Override
    public RunViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_run_history, parent, false);
        return new RunViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull RunViewHolder holder, int position) {
        RunData run = runList.get(position);

        // 1. Date
        holder.tvDate.setText(run.getDate() != null ? run.getDate() : "Unknown Date");

        // 2. Distance (Format to 2 decimal places)
        holder.tvDistance.setText(String.format("%.2f km", run.getDistanceKm()));

        // 3. Time
        holder.tvTime.setText(run.getTimeDuration() != null ? run.getTimeDuration() : "--:--");

        // 4. Pace
        holder.tvPace.setText(run.getPace() != null ? run.getPace() : "--'--\"");
    }

    @Override
    public int getItemCount() {
        return runList != null ? runList.size() : 0;
    }

    public static class RunViewHolder extends RecyclerView.ViewHolder {
        TextView tvDate, tvDistance, tvTime, tvPace;

        public RunViewHolder(@NonNull View itemView) {
            super(itemView);
            // Ensure these IDs match your item_run_history.xml layout
            tvDate = itemView.findViewById(R.id.tv_run_date);
            tvDistance = itemView.findViewById(R.id.tv_run_distance);
            tvTime = itemView.findViewById(R.id.tv_run_time);
            tvPace = itemView.findViewById(R.id.tv_run_pace);
        }
    }
}