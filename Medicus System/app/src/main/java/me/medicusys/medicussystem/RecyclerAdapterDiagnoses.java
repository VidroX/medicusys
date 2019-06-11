package me.medicusys.medicussystem;

import android.content.Context;
import android.content.Intent;
import android.support.annotation.NonNull;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.RelativeLayout;
import android.widget.TextView;

public class RecyclerAdapterDiagnoses extends RecyclerView.Adapter<RecyclerView.ViewHolder> {
    Context context;

    RecyclerAdapterDiagnoses(Context context) {
        this.context = context;
    }

    @NonNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NonNull ViewGroup viewGroup, int i) {
        View view = LayoutInflater.from(viewGroup.getContext()).inflate(R.layout.list_item_diagnose, viewGroup, false);
        return new ViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder viewHolder, final int position) {
        final RecyclerAdapterDiagnoses.ViewHolder holder = (RecyclerAdapterDiagnoses.ViewHolder)viewHolder;
        final DiagnosisRecord record = DataUserMedical.diagnosisRecords.get(position);
        holder.diagnoseNameText.setText(record.name);
        holder.diagnosePeriodText.setText(DataSystem.dateToString(record.detectionDate));
        holder.parentLayout.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                DataSystem.currentDiagnose = position;
                Intent intent = new Intent(context, ActivityDiagnose.class);
                context.startActivity(intent);
            }
        });
    }

    @Override
    public int getItemCount() {
        return DataUserMedical.diagnosisRecords.size();
    }

    public class ViewHolder extends RecyclerView.ViewHolder {
        TextView diagnoseNameText;
        TextView diagnosePeriodText;
        RelativeLayout parentLayout;

        public ViewHolder(View itemView) {
            super(itemView);
            diagnoseNameText = itemView.findViewById(R.id.diagnoseNameText);
            diagnosePeriodText = itemView.findViewById(R.id.diagnosePeriodText);
            parentLayout = itemView.findViewById(R.id.diagnose_parent_layout);
        }
    }
}
