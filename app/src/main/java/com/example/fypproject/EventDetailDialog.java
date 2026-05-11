package com.example.fypproject;

import android.content.Intent;
import android.net.Uri;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import com.google.android.material.bottomsheet.BottomSheetDialogFragment;
import java.util.Locale;

public class EventDetailDialog extends BottomSheetDialogFragment {

    private Event event;

    // 🆕 Static method to create a new instance correctly
    public static EventDetailDialog newInstance(Event event) {
        EventDetailDialog fragment = new EventDetailDialog();
        fragment.event = event; // In a full app, you'd use arguments, but this is fine for now
        return fragment;
    }

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        // Ensure this matches your file name in res/layout exactly
        View v = inflater.inflate(R.layout.dialog_event_details, container, false);

        if (event == null) {
            dismiss();
            return v;
        }

        try {
            // Find Views & Set Data
            ((TextView) v.findViewById(R.id.tv_detail_title)).setText(event.getTitle());
            ((TextView) v.findViewById(R.id.tv_detail_type)).setText(event.getRunType() != null ? event.getRunType().toUpperCase() : "ROAD RUN");

            TextView desc = v.findViewById(R.id.tv_detail_description);
            if (desc != null) desc.setText(event.getDescription() != null ? event.getDescription() : "No description provided.");

            ((TextView) v.findViewById(R.id.tv_detail_distance)).setText(String.format(Locale.getDefault(), "%.2f km", event.getDistanceKm()));
            ((TextView) v.findViewById(R.id.tv_detail_state)).setText(event.getState());
            ((TextView) v.findViewById(R.id.tv_detail_date)).setText(event.getDate());
            ((TextView) v.findViewById(R.id.tv_detail_fee)).setText("RM " + String.format(Locale.getDefault(), "%.2f", event.getEntryFee()));
            ((TextView) v.findViewById(R.id.tv_detail_address)).setText(event.getLocation());
            ((TextView) v.findViewById(R.id.tv_detail_runners)).setText(event.getParticipantsCount() + " runners");

            v.findViewById(R.id.btn_open_map).setOnClickListener(view -> {
                String uri = "geo:" + event.getLatitude() + "," + event.getLongitude() + "?q=" + Uri.encode(event.getLocation());
                Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse(uri));
                intent.setPackage("com.google.android.apps.maps");
                startActivity(intent);
            });

            v.findViewById(R.id.btn_close_details).setOnClickListener(view -> dismiss());

        } catch (Exception e) {
            e.printStackTrace(); // This shows the error in Logcat
            dismiss();
        }

        return v;
    }
}