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
import com.google.firebase.firestore.QueryDocumentSnapshot;
import java.util.List;

public class UserSearchAdapter extends RecyclerView.Adapter<UserSearchAdapter.UserViewHolder> {

    private List<QueryDocumentSnapshot> userList;

    public UserSearchAdapter(List<QueryDocumentSnapshot> userList) {
        this.userList = userList;
    }

    @NonNull
    @Override
    public UserViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_user_search, parent, false);
        return new UserViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull UserViewHolder holder, int position) {
        QueryDocumentSnapshot document = userList.get(position);

        String name = document.getString("name");
        String username = document.getString("username"); // Get Username
        String imageBase64 = document.getString("imageBase64");

        holder.tvName.setText(name != null ? name : "Unknown");
        // Show username like "@john123"
        holder.tvAbout.setText(username != null ? "@" + username : "@unknown");

        if (imageBase64 != null && !imageBase64.isEmpty()) {
            try {
                byte[] decodedString = Base64.decode(imageBase64, Base64.DEFAULT);
                Bitmap decodedByte = BitmapFactory.decodeByteArray(decodedString, 0, decodedString.length);
                holder.ivImage.setImageBitmap(decodedByte);
            } catch (Exception e) {}
        }
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