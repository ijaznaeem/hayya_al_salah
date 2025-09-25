import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { Api } from '../../services/api';

@Component({
  selector: 'app-analytics',
  imports: [
    CommonModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    MatSnackBarModule
  ],
  templateUrl: './analytics.html',
  styleUrl: './analytics.css'
})
export class Analytics implements OnInit {
  isLoading = false;
  analytics = {
    totalUsers: 0,
    totalLectures: 0,
    totalTopics: 0,
    totalViews: 0,
    popularLectures: [] as any[],
    recentActivity: [] as any[],
    userGrowth: [] as any[]
  };

  constructor(
    private api: Api,
    private snackBar: MatSnackBar
  ) {}

  ngOnInit() {
    this.loadAnalytics();
  }

  loadAnalytics() {
    this.isLoading = true;
    this.api.getAnalytics().subscribe({
      next: (response) => {
        this.analytics = {
          totalUsers: response.totalUsers || 0,
          totalLectures: response.totalLectures || 0,
          totalTopics: response.totalTopics || 0,
          totalViews: response.totalViews || 0,
          popularLectures: response.popularLectures || [],
          recentActivity: response.recentActivity || [],
          userGrowth: response.userGrowth || []
        };
        this.isLoading = false;
      },
      error: (error) => {
        this.isLoading = false;
        // Show mock data if API fails
        this.analytics = {
          totalUsers: 156,
          totalLectures: 89,
          totalTopics: 12,
          totalViews: 2847,
          popularLectures: [
            { title: 'Introduction to Prayer', views: 345 },
            { title: 'Wudu Guide', views: 289 },
            { title: 'Quran Reading Basics', views: 234 }
          ],
          recentActivity: [
            { action: 'User registered', timestamp: new Date() },
            { action: 'New lecture uploaded', timestamp: new Date() },
            { action: 'Topic updated', timestamp: new Date() }
          ],
          userGrowth: [
            { month: 'Jan', users: 120 },
            { month: 'Feb', users: 135 },
            { month: 'Mar', users: 156 }
          ]
        };
        this.snackBar.open('Using demo data - API not available', 'Close', { duration: 3000 });
      }
    });
  }

  refreshAnalytics() {
    this.loadAnalytics();
  }
}
