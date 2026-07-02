package com.example.fypproject;

import android.content.Context;
import android.content.Intent;
import android.graphics.drawable.Drawable;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.ProgressBar;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.recyclerview.widget.RecyclerView;

import com.bumptech.glide.Glide;
import com.bumptech.glide.load.DataSource;
import com.bumptech.glide.load.engine.DiskCacheStrategy;
import com.bumptech.glide.load.engine.GlideException;
import com.bumptech.glide.request.RequestListener;
import com.bumptech.glide.request.target.Target;
import com.bumptech.glide.signature.ObjectKey; // 🛠️ Required for signature

import java.util.List;

public class GroupAdapter extends RecyclerView.Adapter<GroupAdapter.GroupViewHolder> {

    private Context context;
    private List<GroupModel> groupList;
    private String currentUserId;
    private OnGroupInteractionListener interactionListener;

    public interface OnGroupInteractionListener {
        void onJoinClick(int groupId);
        void onLeaveClick(int groupId);
    }

    public GroupAdapter(Context context, List<GroupModel> groupList, String currentUserId, OnGroupInteractionListener listener) {
        this.context = context;
        this.groupList = groupList;
        this.currentUserId = currentUserId;
        this.interactionListener = listener;
    }

    public void updateList(List<GroupModel> newList) {
        this.groupList = newList;
        notifyDataSetChanged();
    }

    @NonNull
    @Override
    public GroupViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View v = LayoutInflater.from(context).inflate(R.layout.item_group, parent, false);
        return new GroupViewHolder(v);
    }

    @Override
    public void onBindViewHolder(@NonNull GroupViewHolder holder, int position) {
        GroupModel group = groupList.get(position);

        // --- DEBUG LOG ---
        Log.d("IMAGE_CHECK", "Group: " + group.getName() + " | URL: " + group.getIconUrl());

        holder.tvName.setText(group.getName());
        holder.tvMembers.setText(group.getMembersCount() + " Runners Joined");

        if (group.getTargetKm() > 0) {
            holder.tvTarget.setText(String.format(java.util.Locale.getDefault(), "Goal: %.1f / %.1f km", group.getCurrentKm(), group.getTargetKm()));
            holder.pbProgress.setVisibility(View.VISIBLE);
            holder.pbProgress.setMax((int) Math.max(group.getTargetKm(), 1));
            holder.pbProgress.setProgress((int) group.getCurrentKm());
        } else {
            holder.tvTarget.setText("No Target");
            holder.pbProgress.setVisibility(View.GONE);
        }

        // --- 🛠️ GLIDE LOADING WITH CACHE REFRESH FIX ---
        if (group.getIconUrl() != null && !group.getIconUrl().isEmpty()) {
            Glide.with(context)
                    .load(group.getIconUrl())
                    .diskCacheStrategy(DiskCacheStrategy.ALL)
                    // Forces Glide to ignore the local cache if the image was updated recently
                    .signature(new ObjectKey(String.valueOf(System.currentTimeMillis() / (1000 * 60 * 10))))
                    .listener(new RequestListener<Drawable>() {
                        @Override
                        public boolean onLoadFailed(@Nullable GlideException e, Object model, Target<Drawable> target, boolean isFirstResource) {
                            Log.e("GLIDE_ERROR", "Failed for: " + group.getIconUrl(), e);
                            return false;
                        }

                        @Override
                        public boolean onResourceReady(Drawable resource, Object model, Target<Drawable> target, DataSource dataSource, boolean isFirstResource) {
                            return false;
                        }
                    })
                    .placeholder(android.R.drawable.ic_menu_myplaces)
                    .error(android.R.drawable.ic_menu_report_image)
                    .into(holder.ivIcon);

            holder.ivIcon.setPadding(0, 0, 0, 0);
            holder.ivIcon.setColorFilter(null);
        } else {
            holder.ivIcon.setImageResource(android.R.drawable.ic_menu_myplaces);
            holder.ivIcon.setPadding(12, 12, 12, 12);
            holder.ivIcon.setColorFilter(0xFFFFFFFF);
        }

        // --- CLICK LISTENERS ---
        holder.btnView.setOnClickListener(v -> {
            Intent intent = new Intent(context, GroupDetailActivity.class);
            intent.putExtra("groupId", group.getId());
            context.startActivity(intent);
        });

        if (group.isMember()) {
            holder.btnJoin.setText("Leave");
            holder.btnJoin.setEnabled(true);
            holder.btnJoin.setBackgroundTintList(android.content.res.ColorStateList.valueOf(0xFFD32F2F));
            holder.btnJoin.setOnClickListener(v -> interactionListener.onLeaveClick(group.getId()));
        } else {
            holder.btnJoin.setText("Join");
            holder.btnJoin.setEnabled(true);
            holder.btnJoin.setBackgroundTintList(android.content.res.ColorStateList.valueOf(0xFF4CAF50));
            holder.btnJoin.setOnClickListener(v -> interactionListener.onJoinClick(group.getId()));
        }
    }

    @Override
    public int getItemCount() {
        return groupList != null ? groupList.size() : 0;
    }

    public static class GroupViewHolder extends RecyclerView.ViewHolder {
        TextView tvName, tvTarget, tvMembers;
        Button btnJoin, btnView;
        ImageView ivIcon;
        ProgressBar pbProgress;

        public GroupViewHolder(@NonNull View itemView) {
            super(itemView);
            tvName = itemView.findViewById(R.id.tv_group_name);
            tvTarget = itemView.findViewById(R.id.tv_group_target);
            tvMembers = itemView.findViewById(R.id.tv_group_members);
            btnJoin = itemView.findViewById(R.id.btn_join_group);
            btnView = itemView.findViewById(R.id.btn_view_group);
            ivIcon = itemView.findViewById(R.id.iv_group_icon);
            pbProgress = itemView.findViewById(R.id.pb_group_progress);
        }
    }
}