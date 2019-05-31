package me.medicusys.medicussystem;

import android.os.AsyncTask;
import android.widget.TextView;

import java.io.BufferedInputStream;
import java.io.BufferedOutputStream;
import java.io.BufferedWriter;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.io.OutputStreamWriter;
import java.io.UnsupportedEncodingException;
import java.net.HttpURLConnection;
import java.net.MalformedURLException;
import java.net.URL;
import java.util.Scanner;

class Network extends AsyncTask<String, Void, String> {
    TextView textView;
    String encoding;
    String response;

    Network(TextView textView, String encoding)
    {
        super();
        this.textView = textView;
        this.encoding = encoding;
    }

    @Override
    protected String doInBackground(String... params) {
        if (params.length < 2) {
            return "Not enough params: two params of url and request data required.";
        }
        String urlName = params[0];
        String requestData = params[1];
        OutputStream out;
        String io = "none";
        HttpURLConnection urlConnection = null;
        String result = "";
        try {
            URL url = new URL(urlName);
            urlConnection = (HttpURLConnection) url.openConnection();
            urlConnection.setRequestMethod("POST");
            urlConnection.setDoInput(true);
            urlConnection.setDoOutput(true);
            urlConnection.setRequestProperty("Content-Type", "application/json; charset=utf-8");
            out = new BufferedOutputStream(urlConnection.getOutputStream());
            BufferedWriter writer = new BufferedWriter(new OutputStreamWriter(out, "UTF-8"));
            writer.write(requestData);
            writer.flush();
            writer.close();
            out.close();
            urlConnection.connect();
            Scanner scanner = new Scanner(urlConnection.getInputStream());
            StringBuilder stringBuilder = new StringBuilder();
            while (scanner.hasNextLine()) {
                stringBuilder.append(scanner.nextLine());
            }
            response = stringBuilder.toString();
        } catch (MalformedURLException e) {
            System.out.println("Bad URL: " + urlName);
        } catch (UnsupportedEncodingException e) {
            System.out.println("Unsupported encoding: " + encoding);
        } catch (IOException e) {
            System.out.println("Bad internet connection: " + e.getMessage());
            e.printStackTrace();
        }

        finally {
            if (urlConnection != null) {
                urlConnection.disconnect();
            }
        }
        return result;
    }

    @Override
    protected void onPostExecute(String result) {
        textView.setText(response);
        System.out.println("Request response was \"" + result + "\"");
        System.out.println("Response response was \"" + response + "\"");
        super.onPostExecute(result);
    }
}
