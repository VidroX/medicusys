package me.medicusys.medicussystem;

import android.content.Intent;
import android.os.Bundle;
import android.support.design.widget.NavigationView;
import android.support.v4.view.GravityCompat;
import android.support.v4.widget.DrawerLayout;
import android.support.v7.app.ActionBarDrawerToggle;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.TextView;

public class ActivityCabinet extends AppCompatActivity
        implements NavigationView.OnNavigationItemSelectedListener {
    FragmentHealth healthFragment;
    FragmentHealing healingFragment;
    Toolbar toolbar;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_cabinet);

        toolbar = findViewById(R.id.toolbar);
        setSupportActionBar(toolbar);

        DrawerLayout drawer = findViewById(R.id.drawer_layout);
        ActionBarDrawerToggle toggle = new ActionBarDrawerToggle(this, drawer, toolbar, R.string.navigation_drawer_open, R.string.navigation_drawer_close);
        drawer.addDrawerListener(toggle);
        toggle.syncState();

        NavigationView navigationView = findViewById(R.id.nav_view);
        navigationView.setNavigationItemSelectedListener(this);

        healthFragment = new FragmentHealth();
        healingFragment = new FragmentHealing();

        navigationView.getMenu().getItem(0).setChecked(true);
        getSupportFragmentManager().beginTransaction().replace(R.id.fragment_container, healthFragment).commit();
        toolbar.setTitle(R.string.menu_health);

        View navigationHeader = navigationView.getHeaderView(0);
        navigationHeader.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent intent = new Intent(v.getContext(), ActivityPersonal.class);
                v.getContext().startActivity(intent);
            }
        });
        TextView personalNameView = navigationHeader.findViewById(R.id.personalName);
        personalNameView.setText(DataUserPersonal.firstName + " " + DataUserPersonal.patronymic + " " + DataUserPersonal.lastName);
        TextView personalPhoneView = navigationHeader.findViewById(R.id.personalPhone);
        personalPhoneView.setText(DataUserPersonal.phone);
        TextView personalEmailView = navigationHeader.findViewById(R.id.personalEmail);
        personalEmailView.setText(DataUserPersonal.email);
    }

    @Override
    public void onBackPressed() {
        DrawerLayout drawer = findViewById(R.id.drawer_layout);
        if (drawer.isDrawerOpen(GravityCompat.START)) {
            drawer.closeDrawer(GravityCompat.START);
        } else {
            super.onBackPressed();
        }
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        getMenuInflater().inflate(R.menu.cabinet, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        int id = item.getItemId();
        if (id == R.id.action_settings) {
            return true;
        }

        return super.onOptionsItemSelected(item);
    }

    @SuppressWarnings("StatementWithEmptyBody")
    @Override
    public boolean onNavigationItemSelected(MenuItem item) {
        int id = item.getItemId();
        if (id == R.id.nav_health) {
            getSupportFragmentManager().beginTransaction().replace(R.id.fragment_container, healthFragment).commit();
            toolbar.setTitle(R.string.menu_health);
        } else if (id == R.id.nav_healing) {
            getSupportFragmentManager().beginTransaction().replace(R.id.fragment_container, healingFragment).commit();
            toolbar.setTitle(R.string.menu_healing);
        }

        DrawerLayout drawer = findViewById(R.id.drawer_layout);
        drawer.closeDrawer(GravityCompat.START);
        return true;
    }

    public void LogOut(View view) {
        DataUserPersonal.logOut(this);
        finish();
    }
}
