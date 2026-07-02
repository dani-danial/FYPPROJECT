package com.example.fypproject;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.cardview.widget.CardView;
import androidx.recyclerview.widget.RecyclerView;

import com.bumptech.glide.Glide;

import java.util.List;

public class ConversationAdapter extends RecyclerView.Adapter<ConversationAdapter.ViewHolder> {

    public interface OnConversationClickListener {
        void onConversationClick(ConversationModel conversation);
    }

    private List<ConversationModel> list;
    private OnConversationClickListener listener;

    public ConversationAdapter(
            List<ConversationModel> list,
            OnConversationClickListener listener
    ) {
        this.list = list;
        this.listener = listener;
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(
            @NonNull ViewGroup parent,
            int viewType
    ) {

        View view = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_conversation, parent, false);

        return new ViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
        ConversationModel model = list.get(position);

        // 1. Get the actual user data we passed from the API
        UserModel friend = model.getReceiver();

        // 2. Safely display their name instead of "Conversation #1"
        if (friend != null && friend.getName() != null) {
            holder.tvName.setText(friend.getName());
            holder.tvStatus.setText(friend.isOnline() ? "online" : "offline");
            holder.onlineDot.setVisibility(friend.isOnline() ? View.VISIBLE : View.GONE);
            Glide.with(holder.itemView.getContext())
                    .load(friend.getProfilePhotoPath())
                    .placeholder(android.R.drawable.sym_def_app_icon)
                    .circleCrop()
                    .into(holder.ivAvatar);
        } else {
            holder.tvName.setText("Unknown User");
            holder.tvStatus.setText("offline");
            holder.onlineDot.setVisibility(View.GONE);
        }
        holder.tvLastMessage.setText("Tap to start chatting");
        holder.tvTime.setText("");

        holder.cardConversation.setOnClickListener(v -> {
            listener.onConversationClick(model);
        });
    }

    @Override
    public int getItemCount() {
        return list.size();
    }

    static class ViewHolder extends RecyclerView.ViewHolder {

        CardView cardConversation;
        ImageView ivAvatar;
        View onlineDot;
        TextView tvName, tvStatus, tvLastMessage, tvTime;

        public ViewHolder(@NonNull View itemView) {
            super(itemView);

            cardConversation =
                    itemView.findViewById(R.id.card_conversation);

            tvName =
                    itemView.findViewById(R.id.tv_conversation_name);
            tvStatus = itemView.findViewById(R.id.tv_conversation_status);
            tvLastMessage = itemView.findViewById(R.id.tv_conversation_last_message);
            tvTime = itemView.findViewById(R.id.tv_conversation_time);
            ivAvatar = itemView.findViewById(R.id.iv_conversation_avatar);
            onlineDot = itemView.findViewById(R.id.view_online_dot);
        }
    }
}
