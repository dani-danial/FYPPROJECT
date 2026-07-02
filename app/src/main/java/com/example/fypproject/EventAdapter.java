package com.example.fypproject;

import android.graphics.drawable.Drawable;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.recyclerview.widget.RecyclerView;

// 🛠️ Glide Imports
import com.bumptech.glide.Glide;
import com.bumptech.glide.load.DataSource;
import com.bumptech.glide.load.engine.DiskCacheStrategy;
import com.bumptech.glide.load.engine.GlideException;
import com.bumptech.glide.request.RequestListener;
import com.bumptech.glide.request.target.Target;
import com.bumptech.glide.signature.ObjectKey;

import java.util.List;
import java.util.Locale;

public class EventAdapter extends RecyclerView.Adapter<EventAdapter.EventViewHolder> {

    private List<Event> eventList;
    private OnEventClickListener listener;

    public interface OnEventClickListener {
        void onJoinClick(Event event);
        void onViewClick(Event event);
    }

    public EventAdapter(List<Event> eventList, OnEventClickListener listener) {
        this.eventList = eventList;
        this.listener = listener;
    }

    @NonNull
    @Override
    public EventViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_event, parent, false);
        return new EventViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull EventViewHolder holder, int position) {
        Event event = eventList.get(position);

        // 1. Set Text Info
        holder.tvTitle.setText(event.getTitle());

        String subtitle = String.format(Locale.getDefault(), "%s • %.2f km",
                event.getState(),
                event.getDistanceKm());
        holder.tvSubtitle.setText(subtitle);

        // 2. Set Participant Count
        holder.tvParticipants.setText(String.format(Locale.getDefault(), "%d Runners Joined",
                event.getParticipantsCount()));

        // --- 🛠️ DEBUG LOG ---
        Log.d("EVENT_IMAGE_TEST", "Event: " + event.getTitle() + " | URL: " + event.getLogoPath());

        // 🛠️ 3. Setup Logo using Super-Charged Glide (Debugging Version)
        if (event.getLogoPath() != null && !event.getLogoPath().isEmpty()) {
            Glide.with(holder.itemView.getContext())
                    .load(event.getLogoPath()) // URL from Hostinger
                    .diskCacheStrategy(DiskCacheStrategy.NONE) // Turn off cache completely for testing
                    .skipMemoryCache(true) // Force fresh download
                    .placeholder(android.R.drawable.ic_menu_gallery)
                    .error(android.R.drawable.ic_menu_report_image)
                    .centerCrop()
                    .listener(new RequestListener<Drawable>() {
                        @Override
                        public boolean onLoadFailed(@Nullable GlideException e, Object model, Target<Drawable> target, boolean isFirstResource) {
                            // This will print exactly why Android is blocking the image
                            Log.e("GLIDE_DEBUG", "❌ FAILED TO LOAD EVENT IMAGE! Reason: " + (e != null ? e.getMessage() : "Unknown"));
                            return false;
                        }

                        @Override
                        public boolean onResourceReady(Drawable resource, Object model, Target<Drawable> target, DataSource dataSource, boolean isFirstResource) {
                            // This will print if the image loaded perfectly but is just invisible on screen
                            Log.d("GLIDE_DEBUG", "✅ SUCCESS! Event image loaded into the app.");
                            return false;
                        }
                    })
                    .into(holder.ivLogo);

            // 🛠️ Fix any weird XML properties that might be hiding the image
            holder.ivLogo.setPadding(0, 0, 0, 0);
            holder.ivLogo.setColorFilter(null);
            holder.ivLogo.setVisibility(View.VISIBLE);

        } else {
            // Default placeholder if no logo exists in database
            holder.ivLogo.setImageResource(android.R.drawable.ic_menu_gallery);
            holder.ivLogo.setPadding(12, 12, 12, 12);
            holder.ivLogo.setColorFilter(0xFFFFFFFF);
        }

        // 4. Click Listeners
        holder.btnView.setOnClickListener(v -> {
            if (listener != null) listener.onViewClick(event);
        });

        holder.btnJoin.setOnClickListener(v -> {
            if (listener != null) listener.onJoinClick(event);
        });

        // 5. Recommendation Badge
        if (event.getRecommendationStatus() != null) {
            holder.cvBadge.setVisibility(View.VISIBLE);
            if ("challenge".equalsIgnoreCase(event.getRecommendationStatus())) {
                holder.tvBadge.setText("CHALLENGE ⚡");
                holder.cvBadge.setCardBackgroundColor(0xFFE65100); // Dark Orange
            } else {
                holder.tvBadge.setText("RECOMMENDED");
                holder.cvBadge.setCardBackgroundColor(0xFF2E7D32); // Dark Green
            }
        } else {
            holder.cvBadge.setVisibility(View.GONE);
        }
    }

    @Override
    public int getItemCount() {
        return eventList.size();
    }

    public static class EventViewHolder extends RecyclerView.ViewHolder {
        TextView tvTitle, tvSubtitle, tvParticipants, btnView, tvBadge;
        Button btnJoin;
        ImageView ivLogo;
        androidx.cardview.widget.CardView cvBadge;

        public EventViewHolder(@NonNull View itemView) {
            super(itemView);
            tvTitle = itemView.findViewById(R.id.tv_event_title);
            tvSubtitle = itemView.findViewById(R.id.tv_event_subtitle);
            tvParticipants = itemView.findViewById(R.id.tv_event_participants);
            ivLogo = itemView.findViewById(R.id.iv_event_logo);
            btnJoin = itemView.findViewById(R.id.btn_join_event);
            btnView = itemView.findViewById(R.id.btn_view_details);
            cvBadge = itemView.findViewById(R.id.cv_event_badge);
            tvBadge = itemView.findViewById(R.id.tv_event_badge);
        }
    }
}