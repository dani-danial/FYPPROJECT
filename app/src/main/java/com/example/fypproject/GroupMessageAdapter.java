package com.example.fypproject;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.bumptech.glide.Glide;

import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.List;
import java.util.Locale;
import java.util.TimeZone;

public class GroupMessageAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private static final int TYPE_SENT = 1;
    private static final int TYPE_RECEIVED = 2;
    private final List<Message> messageList;
    private final String currentUserId;

    public GroupMessageAdapter(List<Message> messageList, String currentUserId) {
        this.messageList = messageList;
        this.currentUserId = currentUserId;
    }

    @Override
    public int getItemViewType(int position) {
        Message message = messageList.get(position);
        String senderId = String.valueOf(message.getSenderId());

        if (senderId.equals(currentUserId)) {
            return TYPE_SENT;
        } else {
            return TYPE_RECEIVED;
        }
    }

    @NonNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        if (viewType == TYPE_SENT) {
            View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_message_sent, parent, false);
            return new SentViewHolder(view);
        } else {
            View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_group_message_received, parent, false);
            return new ReceivedViewHolder(view);
        }
    }

    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {
        Message message = messageList.get(position);
        String messageText = message.getText();
        String time = formatTime(message.getTimestamp());

        if (holder instanceof SentViewHolder) {
            SentViewHolder sent = (SentViewHolder) holder;
            sent.tvMessage.setText(messageText);
            sent.tvTime.setText(time);
            sent.tvReceipt.setText(message.isRead() ? "✓✓" : "✓");
        } else if (holder instanceof ReceivedViewHolder) {
            ReceivedViewHolder received = (ReceivedViewHolder) holder;
            received.tvMessage.setText(messageText);
            received.tvTime.setText(time);

            UserModel sender = message.getUser();
            String name = "User";
            String photo = null;

            if (sender != null) {
                name = (sender.getUsername() != null && !sender.getUsername().isEmpty()) ? sender.getUsername() : sender.getName();
                photo = sender.getProfilePhotoPath();
            }

            received.tvSenderName.setText(name);

            Glide.with(received.itemView.getContext())
                    .load(photo)
                    .placeholder(android.R.drawable.sym_def_app_icon)
                    .circleCrop()
                    .into(received.ivAvatar);
        }
    }

    @Override
    public int getItemCount() {
        return messageList.size();
    }

    private String formatTime(String rawDate) {
        if (rawDate == null || rawDate.isEmpty()) return "";
        String[] patterns = {
                "yyyy-MM-dd'T'HH:mm:ss.SSSSSS'Z'",
                "yyyy-MM-dd'T'HH:mm:ss'Z'",
                "yyyy-MM-dd HH:mm:ss"
        };
        for (String pattern : patterns) {
            try {
                SimpleDateFormat input = new SimpleDateFormat(pattern, Locale.getDefault());
                input.setTimeZone(TimeZone.getTimeZone("UTC"));
                Date date = input.parse(rawDate);
                return new SimpleDateFormat("h:mm a", Locale.getDefault()).format(date);
            } catch (Exception ignored) {}
        }
        return rawDate;
    }

    static class SentViewHolder extends RecyclerView.ViewHolder {
        TextView tvMessage, tvTime, tvReceipt;
        SentViewHolder(@NonNull View itemView) {
            super(itemView);
            tvMessage = itemView.findViewById(R.id.tv_message_sent);
            tvTime = itemView.findViewById(R.id.tv_message_sent_time);
            tvReceipt = itemView.findViewById(R.id.tv_message_receipt);
        }
    }

    static class ReceivedViewHolder extends RecyclerView.ViewHolder {
        ImageView ivAvatar;
        TextView tvSenderName, tvMessage, tvTime;
        ReceivedViewHolder(@NonNull View itemView) {
            super(itemView);
            ivAvatar = itemView.findViewById(R.id.iv_received_avatar);
            tvSenderName = itemView.findViewById(R.id.tv_sender_name);
            tvMessage = itemView.findViewById(R.id.tv_message_received);
            tvTime = itemView.findViewById(R.id.tv_message_received_time);
        }
    }
}
