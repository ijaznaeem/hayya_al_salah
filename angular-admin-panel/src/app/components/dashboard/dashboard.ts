import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { Api } from '../../services/api';

@Component({
  selector: 'app-dashboard',
  imports: [
    CommonModule,
    MatCardModule,
    MatIconModule,
    MatButtonModule,
    MatProgressSpinnerModule
  ],
  templateUrl: './dashboard.html',
  styleUrl: './dashboard.css'
})
export class Dashboard implements OnInit {
  isLoading = false;
  stats = {
    totalUsers: 0,
    totalLectures: 0,
    totalTopics: 0,
    recentActivity: [] as string[]
  };

  constructor(private api: Api) {}

  ngOnInit() {
    this.loadDashboardData();
  }

  loadDashboardData() {
    this.isLoading = true;

    // Load topics
    this.api.getTopics().subscribe({
      next: (response) => {
        this.stats.totalTopics = response.count || response.data.length;
      },
      error: (error) => console.error('Error loading topics:', error)
    });

    // Load lectures with pagination to get total count
    this.api.getLectures(1, 1).subscribe({
      next: (response) => {
        this.stats.totalLectures = response.total;
      },
      error: (error) => console.error('Error loading lectures:', error)
    });

    // Load users
    this.api.getUsers(1, 1).subscribe({
      next: (response) => {
        this.stats.totalUsers = response.total || 0;
        this.isLoading = false;
      },
      error: (error) => {
        console.error('Error loading users:', error);
        this.isLoading = false;
        // Use demo data if API fails
        this.stats = {
          totalUsers: 150,
          totalLectures: 45,
          totalTopics: 8,
          recentActivity: [
            'New user registered',
            'Lecture "Introduction to Prayer" uploaded',
            'Topic "Fundamentals" updated'
          ]
        };
      }
    });

    // Mock recent activity for now
    this.stats.recentActivity = [
      'New user registered',
      'Lecture "Introduction to Prayer" uploaded',
      'Topic "Fundamentals" updated',
      'System backup completed',
      'Video processing finished'
    ];
  }
}
