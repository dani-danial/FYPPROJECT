package com.example.fypproject;

import java.util.List;

public class MessagesResponse {

    private List<Message> messages;
    private int user_id;

    public MessagesResponse() {
    }

    public List<Message> getMessages() {
        return messages;
    }

    public void setMessages(List<Message> messages) {
        this.messages = messages;
    }
    public int getUser_id() {
        return user_id;
    }

    public void setUser_id(int user_id) {
        this.user_id = user_id;
    }
}