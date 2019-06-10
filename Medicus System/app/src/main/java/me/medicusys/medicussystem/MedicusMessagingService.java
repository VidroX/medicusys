package me.medicusys.medicussystem;

import com.google.firebase.messaging.FirebaseMessagingService;
import com.google.firebase.messaging.RemoteMessage;

public class MedicusMessagingService extends FirebaseMessagingService {
    @Override
    public void onNewToken(String token) {
        SystemData.fcm_reg_token = token;
    }

    @Override
    public void onMessageReceived(RemoteMessage remoteMessage) {
        NotificationForm form = new NotificationForm(this);
        form.showNotification(remoteMessage.getNotification().getTitle(), remoteMessage.getNotification().getBody());
    }
}
