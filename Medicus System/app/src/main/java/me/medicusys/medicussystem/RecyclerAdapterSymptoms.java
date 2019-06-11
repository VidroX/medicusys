package me.medicusys.medicussystem;

import android.content.Context;
import android.content.Intent;
import android.net.Uri;
import android.support.annotation.NonNull;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.RelativeLayout;

public class RecyclerAdapterSymptoms extends RecyclerView.Adapter<RecyclerView.ViewHolder> {
    Context context;

    RecyclerAdapterSymptoms(Context context) {
        this.context = context;
    }

    @NonNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NonNull ViewGroup viewGroup, int i) {
        View view = LayoutInflater.from(viewGroup.getContext()).inflate(R.layout.list_item_symptom, viewGroup, false);
        return new RecyclerAdapterSymptoms.ViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder viewHolder, final int position) {
        final RecyclerAdapterSymptoms.ViewHolder holder = (RecyclerAdapterSymptoms.ViewHolder)viewHolder;

        final DiagnosisRecord record = DataUserMedical.diagnosisRecords.get(DataSystem.currentDiagnose);
        final String symptom = record.symptoms.get(position);

        holder.symptomLinkButton.setText(symptom);
        holder.symptomLinkButton.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Uri uri = Uri.parse("https://www.google.com/search?q=" + symptom);
                Intent intent = new Intent(Intent.ACTION_VIEW, uri);
                context.startActivity(intent);
            }
        });
    }

    @Override
    public int getItemCount() {
        return DataUserMedical.diagnosisRecords.get(DataSystem.currentDiagnose).symptoms.size();
    }

    public class ViewHolder extends RecyclerView.ViewHolder {
        Button symptomLinkButton;
        RelativeLayout parentLayout;

        public ViewHolder(View itemView) {
            super(itemView);
            symptomLinkButton = itemView.findViewById(R.id.symptomLinkButton);
            parentLayout = itemView.findViewById(R.id.symptom_parent_layout);
        }
    }
}
