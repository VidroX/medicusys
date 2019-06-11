package me.medicusys.medicussystem;

import java.util.Calendar;
import java.util.Date;

public class DataSystem {
    public static final String SERVER_URL = "https://medicusys.me";
    public static final String TOKEN = "PUsecR0B6brOYUcrI9LhiXU8w5SlFRorlFrdrlV";

    public static String fcm_reg_token;

    public static int currentDiagnose;

    public static Date parseDate(String dateString) {
        String[] dateParts = dateString.split("-");
        Calendar calendar = Calendar.getInstance();
        calendar.set(Integer.parseInt(dateParts[0]), Integer.parseInt(dateParts[1]) - 1, Integer.parseInt(dateParts[2]));
        return new Date((calendar.getTime().getTime() / 86400) * 86400);
    }

    public static String dateToString(Date date) {
        Calendar calendar = Calendar.getInstance();
        calendar.setTime(date);
        return calendar.get(Calendar.YEAR) + "-" + (calendar.get(Calendar.MONTH) + 1) + "-" + calendar.get(Calendar.DAY_OF_MONTH);
    }
}
