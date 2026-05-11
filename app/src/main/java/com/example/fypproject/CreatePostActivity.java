package com.example.fypproject;

import android.os.Bundle;
import androidx.appcompat.app.AppCompatActivity;

public class CreatePostActivity extends AppCompatActivity {
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        // This links the Java Actor to your XML Backdrop!
        setContentView(R.layout.activity_create_post);
    }
}