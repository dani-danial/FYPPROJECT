package com.example.fypproject;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.cardview.widget.CardView;
import androidx.recyclerview.widget.RecyclerView;

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
    public void onBindViewHolder(
            @NonNull ViewHolder holder,
            int position
    ) {

        ConversationModel model = list.get(position);

        holder.tvName.setText(
                "Conversation #" + model.getId()
        );

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
        TextView tvName;

        public ViewHolder(@NonNull View itemView) {
            super(itemView);

            cardConversation =
                    itemView.findViewById(R.id.card_conversation);

            tvName =
                    itemView.findViewById(R.id.tv_conversation_name);
        }
    }
}