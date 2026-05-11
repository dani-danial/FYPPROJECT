package com.example.fypproject;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import java.util.List;

public class NotificationAdapter extends RecyclerView.Adapter<NotificationAdapter.ViewHolder> {

    private List<NotificationModel> list;

    public NotificationAdapter(List<NotificationModel> list) {
        this.list = list;
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_notification, parent, false);
        return new ViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
        NotificationModel item = list.get(position);
        holder.tvTitle.setText(item.title);
        holder.tvMessage.setText(item.message);
        holder.tvTime.setText(item.time);

        // Change Icon based on Type
        if (item.type.equals("run")) {
            holder.ivIcon.setImageResource(android.R.drawable.ic_menu_myplaces); // Run Icon
        } else if (item.type.equals("group")) {
            holder.ivIcon.setImageResource(android.R.drawable.ic_menu_add); // WORKS
        } else {
            holder.ivIcon.setImageResource(android.R.drawable.sym_action_chat); // Post Icon
        }
    }

    @Override
    public int getItemCount() { return list.size(); }

    public static class ViewHolder extends RecyclerView.ViewHolder {
        TextView tvTitle, tvMessage, tvTime;
        ImageView ivIcon;

        public ViewHolder(@NonNull View itemView) {
            super(itemView);
            tvTitle = itemView.findViewById(R.id.tv_notif_title);
            tvMessage = itemView.findViewById(R.id.tv_notif_message);
            tvTime = itemView.findViewById(R.id.tv_notif_time);
            ivIcon = itemView.findViewById(R.id.iv_notif_icon);
        }
    }
}