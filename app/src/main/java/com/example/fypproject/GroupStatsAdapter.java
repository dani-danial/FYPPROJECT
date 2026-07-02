package com.example.fypproject;

import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.util.Base64;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import java.util.List;
import java.util.Locale;

import com.bumptech.glide.Glide;
import com.google.gson.annotations.SerializedName;

public class GroupStatsAdapter extends RecyclerView.Adapter<GroupStatsAdapter.StatViewHolder> {

    private List<MemberStat> statsList;

    public GroupStatsAdapter(List<MemberStat> statsList) {
        this.statsList = statsList;
    }

    @NonNull
    @Override
    public StatViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_group_stat, parent, false);
        return new StatViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull StatViewHolder holder, int position) {
        MemberStat stat = statsList.get(position);
        holder.tvRank.setText(String.valueOf(position + 1));
        holder.tvName.setText(stat.userName != null ? stat.userName : "Unknown");
        holder.tvDistance.setText(String.format(Locale.getDefault(), "%.1f km", stat.totalDistance));
        holder.tvRuns.setText(stat.totalRuns + " runs");

        if (stat.userImageBase64 != null && !stat.userImageBase64.isEmpty()) {
            if (stat.userImageBase64.startsWith("http") || stat.userImageBase64.contains("serve-image")) {
                Glide.with(holder.itemView.getContext())
                        .load(stat.userImageBase64)
                        .placeholder(android.R.drawable.sym_def_app_icon)
                        .error(android.R.drawable.sym_def_app_icon)
                        .into(holder.ivUser);
            } else {
                try {
                    byte[] decodedString = Base64.decode(stat.userImageBase64, Base64.DEFAULT);
                    Bitmap decodedByte = BitmapFactory.decodeByteArray(decodedString, 0, decodedString.length);
                    holder.ivUser.setImageBitmap(decodedByte);
                } catch (Exception e) {
                    holder.ivUser.setImageResource(android.R.drawable.sym_def_app_icon);
                }
            }
        } else {
            holder.ivUser.setImageResource(android.R.drawable.sym_def_app_icon);
        }
    }

    @Override
    public int getItemCount() {
        return statsList.size();
    }

    public static class StatViewHolder extends RecyclerView.ViewHolder {
        TextView tvRank, tvName, tvDistance, tvRuns;
        ImageView ivUser;

        public StatViewHolder(@NonNull View itemView) {
            super(itemView);
            tvRank = itemView.findViewById(R.id.tv_stat_rank);
            tvName = itemView.findViewById(R.id.tv_stat_name);
            tvDistance = itemView.findViewById(R.id.tv_stat_distance);
            tvRuns = itemView.findViewById(R.id.tv_stat_runs);
            ivUser = itemView.findViewById(R.id.iv_stat_user);
        }
    }

    public static class MemberStat implements Comparable<MemberStat> {
        @SerializedName("user_id")
        public String userId;

        @SerializedName("user_name")
        public String userName;

        @SerializedName("user_image_base64")
        public String userImageBase64;

        @SerializedName("total_distance")
        public double totalDistance = 0;

        @SerializedName("total_runs")
        public int totalRuns = 0;

        @Override
        public int compareTo(MemberStat other) {
            return Double.compare(other.totalDistance, this.totalDistance);
        }
    }
}