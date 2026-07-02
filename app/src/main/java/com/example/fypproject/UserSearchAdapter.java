package com.example.fypproject;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import com.bumptech.glide.Glide;
import java.util.List;

public class UserSearchAdapter extends RecyclerView.Adapter<UserSearchAdapter.UserViewHolder> {

    private List<UserModel> userList;
    private OnUserClickListener listener;

    public interface OnUserClickListener {
        void onUserClick(UserModel user);
    }

    public UserSearchAdapter(List<UserModel> userList, OnUserClickListener listener) {
        this.userList = userList;
        this.listener = listener;
    }

    public void updateList(List<UserModel> newList) {
        this.userList = newList;
        notifyDataSetChanged();
    }

    @NonNull
    @Override
    public UserViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_user_search, parent, false);
        return new UserViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull UserViewHolder holder, int position) {
        UserModel user = userList.get(position);

        holder.tvName.setText(user.getName());
        holder.tvAbout.setText("@" + user.getUsername() + " | " + user.getRunnerTier());

        Glide.with(holder.itemView.getContext())
                .load(user.getProfilePhotoPath())
                .circleCrop()
                .placeholder(android.R.drawable.ic_menu_report_image)
                .into(holder.ivImage);

        holder.itemView.setOnClickListener(v -> {
            if (listener != null) listener.onUserClick(user);
        });
    }

    @Override
    public int getItemCount() {
        return userList.size();
    }

    public static class UserViewHolder extends RecyclerView.ViewHolder {
        TextView tvName, tvAbout;
        ImageView ivImage;

        public UserViewHolder(@NonNull View itemView) {
            super(itemView);
            tvName = itemView.findViewById(R.id.tv_user_name);
            tvAbout = itemView.findViewById(R.id.tv_user_about);
            ivImage = itemView.findViewById(R.id.iv_user_image);
        }
    }
}
