package com.example.fypproject;

import com.google.gson.Gson;
import com.google.gson.GsonBuilder;
import java.util.concurrent.TimeUnit;
import okhttp3.OkHttpClient;
import okhttp3.logging.HttpLoggingInterceptor;
import retrofit2.Retrofit;
import retrofit2.converter.gson.GsonConverterFactory;

public class RetrofitClient {

    // 🛠️ Using HTTPS for production; ensure the trailing slash is present
    private static final String BASE_URL = "https://runtracker.fun/public/";

    private static Retrofit retrofit = null;

    public static Retrofit getInstance() {
        if (retrofit == null) {
            // Logging interceptor to help you debug response codes in Logcat
            HttpLoggingInterceptor logging = new HttpLoggingInterceptor();
            logging.setLevel(HttpLoggingInterceptor.Level.BODY);

            // 🛠️ INCREASED TIMEOUTS: 60s is the "Sweet Spot" for live web hosting
            OkHttpClient client = new OkHttpClient.Builder()
                    .addInterceptor(logging)
                    .connectTimeout(60, TimeUnit.SECONDS) // Time to establish connection
                    .readTimeout(60, TimeUnit.SECONDS)    // Time to wait for server response
                    .writeTimeout(60, TimeUnit.SECONDS)   // Time to send data to server
                    .retryOnConnectionFailure(true)       // Automatically reconnect on shaky 4G/5G
                    .build();

            // Lenient GSON helps if Hostinger adds any extra characters to the JSON response
            Gson gson = new GsonBuilder()
                    .setLenient()
                    .create();

            retrofit = new Retrofit.Builder()
                    .baseUrl(BASE_URL)
                    .addConverterFactory(GsonConverterFactory.create(gson))
                    .client(client)
                    .build();
        }
        return retrofit;
    }

    public static ApiService getService() {
        return getInstance().create(ApiService.class);
    }
}