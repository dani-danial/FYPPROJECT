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

import com.google.firebase.firestore.DocumentSnapshot;
import com.google.firebase.firestore.FirebaseFirestore;

import java.util.List;

public class HomeFeedAdapter extends RecyclerView.Adapter<HomeFeedAdapter.ViewHolder> {

    private List<DocumentSnapshot> runList;
    private FirebaseFirestore db;

    public HomeFeedAdapter(List<DocumentSnapshot> runList) {
        this.runList = runList;
        this.db = FirebaseFirestore.getInstance();
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_home_feed, parent, false);
        return new ViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
        DocumentSnapshot doc = runList.get(position);

        // 1. Set Run Data
        Double dist = doc.getDouble("distance_km");
        holder.tvDist.setText(String.format("%.2f km", dist != null ? dist : 0));
        holder.tvTime.setText(doc.getString("time"));
        holder.tvPace.setText(doc.getString("pace"));
        holder.tvDate.setText(doc.getString("date"));

        // 2. Fetch Author Details (Name & Pic)
        String userId = doc.getString("userId");
        if (userId != null) {
            db.collection("users").document(userId).get().addOnSuccessListener(userDoc -> {
                if (userDoc.exists()) {
                    holder.tvName.setText(userDoc.getString("name"));

                    // Decode Profile Pic
                    String base64Image = userDoc.getString("imageBase64");
                    if (base64Image != null && !base64Image.isEmpty()) {
                        try {
                            byte[] decodedString = Base64.decode(base64Image, Base64.DEFAULT);
                            Bitmap decodedByte = BitmapFactory.decodeByteArray(decodedString, 0, decodedString.length);
                            holder.ivAvatar.setImageBitmap(decodedByte);
                            holder.ivAvatar.setImageTintList(null); // Remove grey tint
                        } catch (Exception e) { e.printStackTrace(); }
                    }
                }
            });
        }
    }

    @Override
    public int getItemCount() {
        return runList.size();
    }

    public static class ViewHolder extends RecyclerView.ViewHolder {
        TextView tvName, tvDate, tvDist, tvTime, tvPace;
        ImageView ivAvatar;

        public ViewHolder(@NonNull View itemView) {
            super(itemView);
            tvName = itemView.findViewById(R.id.tv_feed_name);
            tvDate = itemView.findViewById(R.id.tv_feed_date);
            tvDist = itemView.findViewById(R.id.tv_feed_dist);
            tvTime = itemView.findViewById(R.id.tv_feed_time);
            tvPace = itemView.findViewById(R.id.tv_feed_pace);
            ivAvatar = itemView.findViewById(R.id.iv_feed_avatar);
        }
    }
}